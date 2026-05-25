jQuery(document).ready(function ($) {

    /*
    -----------------------------------------
    SAVE PROFILE
    -----------------------------------------
    */

    let lastFriendRequestCount = null;

    function refreshInterestRequestList() {
        let requestsWrapper = $('#new-requests');

        if (!requestsWrapper.length) {
            return;
        }

        $.ajax({
            url: wpm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpm_get_interest_requests',
                security: wpm_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    requestsWrapper.html(response.data.html);
                }
            }
        });
    }

    function refreshFriendRequestBadge() {
        let badge = $('.wpm-nav-badge');

        if (!badge.length) {
            return;
        }

        $.ajax({
            url: wpm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpm_get_friend_request_count',
                security: wpm_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    let count = parseInt(response.data.count, 10) || 0;

                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.hide();
                    }

                    if (lastFriendRequestCount === null) {
                        lastFriendRequestCount = count;
                        refreshInterestRequestList();
                        return;
                    }

                    if (count !== lastFriendRequestCount) {
                        refreshInterestRequestList();
                        lastFriendRequestCount = count;
                    }
                }
            }
        });
    }

    refreshFriendRequestBadge();
    setInterval(refreshFriendRequestBadge, 5000);

    $(document).on('submit', '#wpm-profile-form', function (e) {

        e.preventDefault();

        let form = $('#wpm-profile-form')[0];

        let formData = new FormData(form);

        formData.append(
            'action',
            'wpm_save_profile'
        );

        formData.append(
            'security',
            wpm_ajax.nonce
        );

        let button = $('#wpm-save-profile');

        button.prop('disabled', true);

        button.text('Saving...');

        /*
        -----------------------------------------
        AJAX REQUEST
        -----------------------------------------
        */

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: formData,

            processData: false,

            contentType: false,

            success: function (response) {

                button.prop('disabled', false);

                button.text('Save Profile');

                if (response.success) {

                Swal.fire({

                    icon: 'success',

                    title: 'Profile Updated',

                    text: response.data.message,

                    showConfirmButton: false,

                    timer: 2200,

                    background: 'rgba(15,15,15,0.96)',

                    color: '#ffffff',

                    backdrop: `
                        rgba(0,0,0,0.7)
                    `,

                    customClass: {

                        popup: 'premium-swal',

                        title: 'premium-swal-title',

                        htmlContainer:
                            'premium-swal-text'

                    },

                    showClass: {

                        popup:
                        'animate__animated animate__fadeInUp'

                    },

                    hideClass: {

                        popup:
                        'animate__animated animate__fadeOutDown'

                    }

                });

            } else {

                Swal.fire({

                    icon: 'error',

                    title: 'Oops...',

                    text: response.data.message,

                    confirmButtonColor:
                        '#ff5a73',

                    background:
                        'rgba(15,15,15,0.96)',

                    color: '#ffffff'

                });

            }

            },

            error: function () {

                button.prop('disabled', false);

                button.text('Save Profile');

                alert(
                    'Something went wrong'
                );

            }

        });

    });

    /*
    -----------------------------------------
    PREVIEW PROFILE IMAGE
    -----------------------------------------
    */

    $(document).on('change', '#profile_photo', function () {

        let input = this;

        if (
            input.files &&
            input.files[0]
        ) {

            let reader = new FileReader();

            reader.onload = function (e) {

                $('#wpm-profile-preview')

                    .attr(
                        'src',
                        e.target.result
                    );

            };

            reader.readAsDataURL(
                input.files[0]
            );

        }

    });

    /*
    -----------------------------------------
    PREVIEW COVER IMAGE
    -----------------------------------------
    */

    $(document).on('change', '#cover_photo', function () {

        let input = this;

        if (
            input.files &&
            input.files[0]
        ) {

            let reader = new FileReader();

            reader.onload = function (e) {

                $('#wpm-cover-preview')

                    .css(
                        'background-image',
                        'url(' + e.target.result + ')'
                    );

            };

            reader.readAsDataURL(
                input.files[0]
            );

        }

    });

    /*
    -----------------------------------------
    GALLERY PREVIEW
    -----------------------------------------
    */

    $(document).on('change', '#gallery', function () {

        $('#wpm-gallery-preview').html('');

        let files = this.files;

        if (files) {

            for (let i = 0; i < files.length; i++) {

                let reader = new FileReader();

                reader.onload = function (e) {

                    $('#wpm-gallery-preview').append(

                        '<div class=\"wpm-gallery-preview-item\">' +

                        '<img src=\"' + e.target.result + '\">' +

                        '</div>'

                    );

                };

                reader.readAsDataURL(
                    files[i]
                );

            }

        }

    });

    jQuery(document).on(

    'click',

    '.wpm-report-user-btn',

    function () {

        let user_id =

            jQuery(this).data('user-id');

        let report_button =
            jQuery(this);

        Swal.fire({

            title: 'Report Profile',

            text: 'Why are you reporting this profile?',

            input: 'textarea',

            inputPlaceholder:
                'Write your reason here...',

            showCancelButton: true,

            confirmButtonText:
                'Submit Report',

            cancelButtonText:
                'Cancel',

            confirmButtonColor:
                '#ff5c7a',

            cancelButtonColor:
                '#6c757d',

            background:
                '#1e1e1e',

            color:
                '#ffffff',

            inputValidator: (value) => {

                if (!value) {

                    return 'Please enter a reason';

                }

            }

        }).then((result) => {

            if (result.isConfirmed) {

                jQuery.ajax({

                    url:
                        wpm_ajax.ajax_url,

                    type:
                        'POST',

                    data: {

                        action:
                            'wpm_report_user',

                        security:
                            wpm_ajax.nonce,

                        user_id:
                            user_id,

                        reason:
                            result.value

                    },

                    success: function (response) {

                        console.log(response);

                        if (response.success) {

                            report_button

                                .html(
                                    '<i class="fa-solid fa-check"></i> Reported'
                                )

                                .prop(
                                    'disabled',
                                    true
                                )

                                .css({

                                    'opacity':
                                        '0.7',

                                    'cursor':
                                        'not-allowed'

                                });

                            Swal.fire({

                                icon:
                                    'success',

                                title:
                                    'Report Submitted',

                                text:
                                    'Thank you. Our team will review this profile.',

                                confirmButtonColor:
                                    '#ff5c7a',

                                background:
                                    '#1e1e1e',

                                color:
                                    '#ffffff'

                            });

                        } else {

                            Swal.fire({

                                icon:
                                    'error',

                                title:
                                    'Error',

                                text:
                                    'Unable to submit report.',

                                confirmButtonColor:
                                    '#ff5c7a',

                                background:
                                    '#1e1e1e',

                                color:
                                    '#ffffff'

                            });

                        }

                    },

                    error: function(xhr){

                        console.log(
                            xhr.responseText
                        );

                        Swal.fire({

                            icon:
                                'error',

                            title:
                                'AJAX Error',

                            text:
                                'Something went wrong.',

                            confirmButtonColor:
                                '#ff5c7a',

                            background:
                                '#1e1e1e',

                            color:
                                '#ffffff'

                        });

                    }

                });

            }

        });

    }

);

});
