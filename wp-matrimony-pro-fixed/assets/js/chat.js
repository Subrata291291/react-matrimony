let activeChatUserId = '';
let isEditingMessage = false;

jQuery(document).ready(function ($) {

    $('.wpm-chat-window').hide();

    function getChatBox() {
        return $('#wpm-chat-messages');
    }

    function getChatElement() {
        return document.getElementById(
            'wpm-chat-messages'
        );
    }

    function scrollChatToBottom() {
        let chat = getChatElement();

        if (!chat) {
            return;
        }

        chat.scrollTop =
            chat.scrollHeight;
    }

    function clearUnreadForUser(userId) {

        if (!userId) {
            return;
        }

        $(
            '.wpm-chat-user[data-user-id="' +
            userId +
            '"] .wpm-unread-count'
        ).remove();

        $('.wpm-chat-notification')
        .hide()
        .removeClass('active')
        .text('0');

        $('.wpm-chat-toggle')
            .removeAttr('data-user-id');
    }

    function loadChatUsers(onLoaded = null) {

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_load_chat_users',

                security: wpm_ajax.nonce

            },

            success: function (response) {

                if (
                    response &&
                    response.success === false
                ) {
                    let message =
                        response.data &&
                        response.data.message
                        ? response.data.message
                        : 'Unable to load chats.';

                    $('#wpm-chat-users')
                        .html('<div class="p-3">' + message + '</div>');
                    return;
                }

                $('#wpm-chat-users')
                    .html(response);

                if (activeChatUserId) {
                    $(
                        '.wpm-chat-user[data-user-id="' +
                        activeChatUserId +
                        '"]'
                    ).addClass('active');
                }

                if (
                    typeof onLoaded ===
                    'function'
                ) {
                    onLoaded();
                }

            }

        });

    }

    function checkUnread() {

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_get_unread_count',

                security: wpm_ajax.nonce

            },

            success: function (response) {

                let count =
                    parseInt(
                        response.count,
                        10
                    ) || 0;

                if (count > 0) {

    $('.wpm-chat-notification')
        .stop(true, true)
        .css('display', 'flex')
        .removeClass('hide-notification')
        .text(count);

    $('.wpm-chat-toggle')
        .attr(
            'data-user-id',
            response.last_sender
        );

                    } else {

                        $('.wpm-chat-notification')
                            .stop(true, true)
                            .addClass('hide-notification')
                            .removeClass('active')
                            .text('0');

                        setTimeout(function () {

                            $('.wpm-chat-notification').hide();

                        }, 200);

                    }

            }

        });

    }

    function markChatRead(userId) {

        if (!userId) {
            return;
        }

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_mark_chat_read',

                security: wpm_ajax.nonce,

                user_id: userId

            },

            success: function (response) {

                if (response.success) {

    clearUnreadForUser(userId);

    // Reload user list so unread badges disappear
    loadChatUsers(function () {

        if (activeChatUserId) {
            $(
                '.wpm-chat-user[data-user-id="' +
                activeChatUserId +
                '"]'
            ).addClass('active');
        }

            });

            // Recheck total unread count
            checkUnread();
        }

            }

        });

    }

    function loadMessages(
        userId,
        options = {}
    ) {

        if (!userId) {
            return;
        }

        if (
            isEditingMessage &&
            options.preserveScroll
        ) {
            return;
        }

        let chatBox =
            getChatBox();

        let preserveScroll =
            !!options.preserveScroll;

        let forceBottom =
            !!options.forceBottom;

        let previousScrollTop =
            preserveScroll
            ? chatBox.scrollTop()
            : 0;

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_load_chat',

                security: wpm_ajax.nonce,

                user_id: userId,

                mark_read: options.markRead ? 1 : 0

            },

            success: function (response) {

                if (
                    response &&
                    response.success === false
                ) {
                    let errorMessage = 'Unable to load your chat.';

                    if (
                        response.data &&
                        response.data.message
                    ) {
                        errorMessage = response.data.message;
                    }

                    chatBox.html(
                        '<div class="p-3">' +
                        errorMessage +
                        '</div>'
                    );
                    return;
                }

                chatBox.html(response);

                if (forceBottom) {
                    scrollChatToBottom();
                    return;
                }

                if (preserveScroll) {
                    chatBox.scrollTop(
                        previousScrollTop
                    );
                }

            }

        });

    }

    function openChatUser(
        userId,
        userName,
        userImage,
        userStatus = ''
    ) {

        if (!userId) {
            return;
        }

        activeChatUserId =
            String(userId);

        $('.wpm-chat-user')
            .removeClass('active');

        $(
            '.wpm-chat-user[data-user-id="' +
            userId +
            '"]'
        ).addClass('active');

        $('.wpm-chat-username')
            .text(userName);

        $('.wpm-chat-header-avatar')
            .attr('src', userImage);

        $('.wpm-chat-status')
            .text(
                userStatus ||
                'Last seen recently'
            );

        $('.wpm-chat-box')
            .attr(
                'data-user-id',
                userId
            );

        $('.wpm-chat-window')
            .fadeIn(200);

        if (
            window.innerWidth < 768
        ) {
            $('.wpm-chat-window')
                .addClass('chat-opened');
        }

        clearUnreadForUser(userId);

        markChatRead(userId);

        loadMessages(userId, {
            forceBottom: true,
            markRead: true
        });

        checkUnread();
    }

    function getStartChatUserId(
        trigger
    ) {

        return (
            $(trigger).attr('data-user')
            ||
            $(trigger).attr('data-user-id')
            ||
            ''
        );

    }

    function getStartChatMeta(
        trigger,
        userId
    ) {

        let button =
            $(trigger);

        let userCard = $(
            '.wpm-chat-user[data-user-id="' +
            userId +
            '"]'
        );

        if (userCard.length) {
            return {
                userName:
                    userCard.find('h5').text(),
                userImage:
                    userCard.find('img').attr('src'),
                userStatus:
                    userCard.attr('data-status') || ''
            };
        }

        let friendItem =
            button.closest(
                '.wpm-friend-item'
            );

        if (friendItem.length) {
            return {
                userName:
                    friendItem
                    .find('.wpm-friend-info h4')
                    .first()
                    .text(),
                userImage:
                    friendItem
                    .find('.wpm-friend-image img')
                    .first()
                    .attr('src'),
                userStatus:
                    friendItem
                    .find('.online-text, .offline-text')
                    .first()
                    .text()
                    .trim()
            };
        }

        return {
            userName:
                $('.wpm-profile-info h1')
                .clone()
                .children()
                .remove()
                .end()
                .text()
                .trim(),
            userImage:
                $('.wpm-avatar')
                .first()
                .attr('src'),
            userStatus:
                $('.online-text, .offline-text, .wpm-location')
                .first()
                .text()
                .trim()
        };

    }

    function openChatFromUserCard(
        userId
    ) {

        let userCard = $(
            '.wpm-chat-user[data-user-id="' +
            userId +
            '"]'
        );

        if (!userCard.length) {
            return false;
        }

        openChatUser(
            userId,
            userCard.find('h5').text(),
            userCard.find('img').attr('src'),
            userCard.attr('data-status') || ''
        );

        return true;

    }

    loadChatUsers();
    checkUnread();

    setInterval(function () {

    loadChatUsers(function () {

        if (activeChatUserId) {

            clearUnreadForUser(activeChatUserId);

        }

    });

}, 5000);

    setInterval(function () {
        checkUnread();
    }, 3000);

    setInterval(function () {
        if (
            activeChatUserId &&
            $('.wpm-chat-window').is(':visible')
        ) {
            loadMessages(
                activeChatUserId,
                {
                    preserveScroll: true
                }
            );
        }
    }, 3000);

    $(document).on(
        'click',
        '.wpm-chat-user',
        function () {

            let userId =
                $(this).data('user-id');

            let userName =
                $(this)
                .find('h5')
                .text();

            let userImage =
                $(this)
                .find('img')
                .attr('src');

            openChatUser(
                userId,
                userName,
                userImage
            );

        }
    );

    $(document).on(
        'click',
        '.wpm-start-chat',
        function (e) {

            e.preventDefault();

            let userId =
                getStartChatUserId(
                    this
                );

            if (!userId) {
                return;
            }

            let meta =
                getStartChatMeta(
                    this,
                    userId
                );

            if (
                !openChatFromUserCard(
                    userId
                )
            ) {
                openChatUser(
                    userId,
                    meta.userName ||
                    'Chat',
                    meta.userImage ||
                    $('.wpm-chat-header-avatar')
                    .attr('src'),
                    meta.userStatus || ''
                );

                loadChatUsers(function () {
                    openChatFromUserCard(
                        userId
                    );
                });
            }

        }
    );

    $(document).on(
        'click',
        '.wpm-chat-toggle',
        function () {

            $('.wpm-chat-window')
                .fadeIn(200);

            let userId =
                $(this).attr(
                    'data-user-id'
                );

            if (!userId) {
                return;
            }

            let userCard = $(
                '.wpm-chat-user[data-user-id="' +
                userId +
                '"]'
            );

            if (!userCard.length) {
                loadChatUsers(function () {
                    openChatFromUserCard(
                        userId
                    );
                });
                return;
            }

            openChatFromUserCard(
                userId
            );

        }
    );

    $(document).on(
        'click',
        '.wpm-mobile-back',
        function () {
            $('.wpm-chat-window')
                .removeClass('chat-opened');
        }
    );

    $(document).on(
        'click',
        '.wpm-minimize-chat',
        function () {
            $('.wpm-chat-window')
                .fadeOut(200);
        }
    );

    $(document).off(
        'submit',
        '#wpm-chat-form'
    );

    function showChatAlert(message, title = 'Cannot send message') {
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#ff5a73',
                background: 'rgba(15,15,15,0.96)',
                color: '#ffffff'
            });
            return;
        }

        alert(message);
    }

    function escapeHtml(value) {
        return $('<div>')
            .text(value)
            .html();
    }

    function confirmChatAction(message, callback) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: message,
                showCancelButton: true,
                confirmButtonColor: '#ff5a73',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                background: 'rgba(15,15,15,0.96)',
                color: '#ffffff'
            }).then(function (result) {
                if (result.isConfirmed) {
                    callback();
                }
            });
            return;
        }

        if (confirm(message)) {
            callback();
        }
    }

    $(document).on(
        'click',
        '.wpm-message-edit-btn',
        function () {

            let button =
                $(this);

            let messageItem =
                button.closest('.wpm-chat-message');

            let messageText =
                button.attr('data-message') || '';

            let chatText =
                messageItem.find('.wpm-chat-text').first();

            isEditingMessage = true;

            chatText.html(
                '<div class="wpm-message-edit-form">' +
                    '<textarea class="wpm-message-edit-input">' +
                        escapeHtml(messageText) +
                    '</textarea>' +
                    '<div class="wpm-message-edit-actions">' +
                        '<button type="button" class="wpm-message-save-btn">Save</button>' +
                        '<button type="button" class="wpm-message-cancel-btn">Cancel</button>' +
                    '</div>' +
                '</div>'
            );

            chatText
                .find('.wpm-message-edit-input')
                .focus();

        }
    );

    $(document).on(
        'click',
        '.wpm-message-cancel-btn',
        function () {

            isEditingMessage = false;

            if (activeChatUserId) {
                loadMessages(
                    activeChatUserId,
                    {
                        preserveScroll: true
                    }
                );
            }

        }
    );

    $(document).on(
        'click',
        '.wpm-message-save-btn',
        function () {

            let button =
                $(this);

            let messageItem =
                button.closest('.wpm-chat-message');

            let messageId =
                messageItem.attr('data-message-id');

            let message =
                messageItem
                .find('.wpm-message-edit-input')
                .val();

            if (
                !message ||
                message.replace(/\s/g, '') === ''
            ) {
                showChatAlert(
                    'Message cannot be empty.',
                    'Cannot edit message'
                );
                return;
            }

            button.prop('disabled', true);

            $.ajax({

                url: wpm_ajax.ajax_url,

                type: 'POST',

                data: {

                    action: 'wpm_edit_message',

                    security: wpm_ajax.nonce,

                    message_id: messageId,

                    message: message

                },

                success: function (response) {

                    if (response.success) {
                        isEditingMessage = false;

                        loadMessages(
                            activeChatUserId,
                            {
                                preserveScroll: true
                            }
                        );

                        loadChatUsers();
                        return;
                    }

                    showChatAlert(
                        response.data && response.data.message
                        ? response.data.message
                        : 'Unable to edit message.',
                        'Cannot edit message'
                    );

                },

                error: function () {
                    showChatAlert(
                        'Something went wrong while editing your message.',
                        'Cannot edit message'
                    );
                },

                complete: function () {
                    button.prop('disabled', false);
                }

            });

        }
    );

    $(document).on(
        'click',
        '.wpm-message-delete-btn',
        function () {

            let messageItem =
                $(this).closest('.wpm-chat-message');

            let messageId =
                messageItem.attr('data-message-id');

            confirmChatAction(
                'This message will be deleted.',
                function () {

                    $.ajax({

                        url: wpm_ajax.ajax_url,

                        type: 'POST',

                        data: {

                            action: 'wpm_delete_message',

                            security: wpm_ajax.nonce,

                            message_id: messageId

                        },

                        success: function (response) {

                            if (response.success) {
                                messageItem.remove();
                                loadChatUsers();
                                return;
                            }

                            showChatAlert(
                                response.data && response.data.message
                                ? response.data.message
                                : 'Unable to delete message.',
                                'Cannot delete message'
                            );

                        },

                        error: function () {
                            showChatAlert(
                                'Something went wrong while deleting your message.',
                                'Cannot delete message'
                            );
                        }

                    });

                }
            );

        }
    );

    $(document).on(
        'submit',
        '#wpm-chat-form',
        function (e) {

            e.preventDefault();

            let receiverId =
                $('.wpm-chat-box')
                .attr('data-user-id');

            let message =
                $('#wpm-chat-message')
                .val();

            if (
                !receiverId
                ||
                !message
                ||
                message.replace(/\s/g, '') === ''
            ) {
                return;
            }

            $('.wpm-send-btn')
                .prop('disabled', true);

            $.ajax({

                url: wpm_ajax.ajax_url,

                type: 'POST',

                data: {

                    action: 'wpm_send_message',

                    security: wpm_ajax.nonce,

                    receiver_id: receiverId,

                    message: message

                },

                success: function (response) {

                    if (response.success) {
                        $('#wpm-chat-message')
                            .val('');

                        loadMessages(
                            receiverId,
                            {
                                forceBottom: true
                            }
                        );
                        return;
                    }

                    let errorMessage = 'Unable to send your message.';

                    if (
                        response.data &&
                        response.data.message
                    ) {
                        errorMessage = response.data.message;
                    }

                    showChatAlert(errorMessage);
                },

                error: function () {
                    showChatAlert('Something went wrong while sending your message.');
                },

                complete: function () {
                    $('.wpm-send-btn')
                        .prop('disabled', false);
                }

            });

        }
    );

    $('#wpm-chat-file').on(
        'change',
        function () {

            let file =
                this.files[0];

            if (!file) {
                return;
            }

            let receiverId =
                $('.wpm-chat-box')
                .attr('data-user-id');

            if (!receiverId) {
                alert('No receiver selected');
                return;
            }

            let formData =
                new window.FormData();

            formData.append(
                'action',
                'wpm_send_file'
            );

            formData.append(
                'security',
                wpm_ajax.nonce
            );

            formData.append(
                'receiver_id',
                receiverId
            );

            formData.append(
                'chat_file',
                file
            );

            $.ajax({

                url: wpm_ajax.ajax_url,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function (response) {

                    if (response.success) {
                        $('#wpm-chat-file')
                            .val('');

                        loadMessages(
                            receiverId,
                            {
                                forceBottom: true
                            }
                        );
                    } else {
                        showChatAlert(
                            response.data || 'Upload failed.',
                            'Cannot send file'
                        );
                    }

                },

                error: function () {
                    showChatAlert(
                        'Something went wrong while uploading your file.',
                        'Cannot send file'
                    );
                }

            });

        }
    );

    $(document).on(
        'click',
        '.wpm-block-user-btn',
        function () {

            let button =
                jQuery(this);

            let userId =
                button.data('user');

            let blocked =
                button.attr(
                    'data-blocked'
                );

            if (blocked == '0') {
                jQuery.post(
                    wpm_ajax.ajax_url,
                    {
                        action: 'wpm_block_user',
                        security: wpm_ajax.nonce,
                        user_id: userId
                    },
                    function (response) {
                        if (response.success) {
                            button.html(
                                '<i class="fa-solid fa-unlock"></i> Unblock'
                            );
                            button.attr(
                                'data-blocked',
                                '1'
                            );
                        }
                    }
                );
            } else {
                jQuery.post(
                    wpm_ajax.ajax_url,
                    {
                        action: 'wpm_unblock_user',
                        security: wpm_ajax.nonce,
                        user_id: userId
                    },
                    function (response) {
                        if (response.success) {
                            button.html(
                                '<i class="fa-solid fa-ban"></i> Block'
                            );
                            button.attr(
                                'data-blocked',
                                '0'
                            );
                        }
                    }
                );
            }

        }
    );

});
