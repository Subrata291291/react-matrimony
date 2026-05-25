jQuery(document).ready(function ($) {

    /*
    -----------------------------------------
    LOAD MEMBERS TAB
    -----------------------------------------
    */

    $(document).on('click', '.tab-link', function () {

        let tab = $(this).data('tab');

        /*
        -----------------------------------------
        ACTIVE TAB
        -----------------------------------------
        */

        $('.tab-link').removeClass('current');

        $(this).addClass('current');

        /*
        -----------------------------------------
        AJAX REQUEST
        -----------------------------------------
        */

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_load_members',

                security: wpm_ajax.nonce,

                tab: tab

            },

            beforeSend: function () {

                $('#wpm-members-wrapper').html(

                    '<div class=\"text-center w-100\">Loading...</div>'

                );

            },

            success: function (response) {

                $('#wpm-members-wrapper').html(
                    response
                );

            }

        });

    });

    /*
    -----------------------------------------
    LOAD MORE MEMBERS
    -----------------------------------------
    */

    $(document).on('click', '#wpm-load-more', function () {

        let button = $(this);

        let page = button.data('page');
        let form = $('#wpm-search-form');
        let shownIds = [];
        let shownPhotoIds = [];

        $('#wpm-members-results .wpm-member-item').each(function () {
            let userId = $(this).data('user-id');
            let photoId = $(this).data('photo-id');

            if (userId) {
                shownIds.push(userId);
            }

            if (photoId) {
                shownPhotoIds.push(photoId);
            }
        });

        button.text('Loading...');

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_load_more_members',

                security: wpm_ajax.nonce,

                page: page,

                gender: form.find('[name="gender"]').val(),

                seeking: form.find('[name="seeking"]').val(),

                age_from: form.find('[name="age_from"]').val(),

                age_to: form.find('[name="age_to"]').val(),

                shown_ids: shownIds,

                shown_photo_ids: shownPhotoIds

            },

            success: function (response) {

                if (response.success) {

                    let existingIds = {};
                    let existingPhotoIds = {};

                    $('#wpm-members-results .wpm-member-item').each(function () {
                        let userId = $(this).data('user-id');
                        let photoId = $(this).data('photo-id');

                        if (userId) {
                            existingIds[userId] = true;
                        }

                        if (photoId) {
                            existingPhotoIds[photoId] = true;
                        }
                    });

                    let html = $('<div>').html(response.data.html);

                    html.find('.wpm-member-item').each(function () {
                        let userId = $(this).data('user-id');
                        let photoId = $(this).data('photo-id');

                        if (
                            (userId && existingIds[userId])
                            ||
                            (photoId && existingPhotoIds[photoId])
                        ) {
                            $(this).remove();
                        }
                    });

                    if (!html.find('.wpm-member-item').length) {
                        button.text('No More Members');
                        button.prop('disabled', true);
                        return;
                    }

                    $('#wpm-members-results').append(

                        html.html()

                    );

                    page++;

                    button.data('page', page);

                    button.text('Load More');

                } else {

                    button.text('No More Members');

                    button.prop('disabled', true);

                }

            }

        });

    });

    /*
    -----------------------------------------
    GRID VIEW
    -----------------------------------------
    */

    $(document).on('click', '.wpm-grid-view', function () {

        $('.wpm-list-view').removeClass('active');

        $(this).addClass('active');

        $('#wpm-members-results')

            .removeClass('wpm-list-layout')

            .addClass('wpm-grid-layout');

        localStorage.setItem(
            'wpm_view',
            'grid'
        );

    });

    /*
    -----------------------------------------
    LIST VIEW
    -----------------------------------------
    */

    $(document).on('click', '.wpm-list-view', function () {

        $('.wpm-grid-view').removeClass('active');

        $(this).addClass('active');

        $('#wpm-members-results')

            .removeClass('wpm-grid-layout')

            .addClass('wpm-list-layout');

        localStorage.setItem(
            'wpm_view',
            'list'
        );

    });

    /*
    -----------------------------------------
    LOAD SAVED VIEW
    -----------------------------------------
    */

    let savedView = localStorage.getItem(
        'wpm_view'
    );

    if (savedView === 'list') {

        $('.wpm-list-view').trigger('click');

    } else {

        $('.wpm-grid-view').trigger('click');

    }

});
