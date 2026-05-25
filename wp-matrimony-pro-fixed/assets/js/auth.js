jQuery(document).ready(function ($) {

    /*
    =====================================
    LOGIN / REGISTER TAB
    =====================================
    */

    $('.modal-tab-link').on('click', function () {

        let tab_id = $(this).data('tab');

        $('.modal-tab-link').removeClass('current');

        $('.modal-tab-content').removeClass('current');

        $(this).addClass('current');

        $('#' + tab_id).addClass('current');

    });

    /*
    =====================================
    SHOW / HIDE PASSWORD
    =====================================
    */

    $(document).on(
        'click',
        '.toggle-password',
        function () {

            let input = $(
                $(this).attr('toggle')
            );

            if (
                input.attr('type')
                === 'password'
            ) {

                input.attr(
                    'type',
                    'text'
                );

                $(this)
                    .removeClass(
                        'fa-eye'
                    )
                    .addClass(
                        'fa-eye-slash'
                    );

            } else {

                input.attr(
                    'type',
                    'password'
                );

                $(this)
                    .removeClass(
                        'fa-eye-slash'
                    )
                    .addClass(
                        'fa-eye'
                    );

            }

        }
    );

    /*
    =====================================
    AJAX REGISTER
    =====================================
    */

    $('#wpm-register-form').on(
        'submit',
        function (e) {

            e.preventDefault();

            let form = $(this);

            let button = form.find('button[type="submit"]');

            button.text(
                'Registering...'
            );

            $.ajax({

                url:
                    wpm_ajax.ajax_url,

                type: 'POST',

                data: {

                    action:
                        'wpm_register_user',

                    security:
                        wpm_ajax.nonce,

                    username:
                        form.find(
                            '[name="username"]'
                        ).val(),

                    email:
                        form.find(
                            '[name="email"]'
                        ).val(),

                    password:
                        form.find(
                            '[name="password"]'
                        ).val(),

                    gender:
                        form.find(
                            '[name="gender"]'
                        ).val()

                },

                success: function (
    response
) {

    button.text(
        'Login'
    );

    if (
        response.success
    ) {

        Swal.fire({

            icon: 'success',

            title: 'Success',

            text: 'Registration successful',

            confirmButtonText: 'Okay',

            confirmButtonColor: '#ff5a73',

            background: '#ffffff',

            color: '#111827',

            customClass: {

                popup: 'wpm-swal-popup',

                confirmButton: 'wpm-swal-button'

            }

        }).then(() => {

            location.reload();

        });

    }

    else {

        Swal.fire({

            icon: 'warning',

            title: 'Approval Pending',

            text: response.data.message,

            confirmButtonText: 'Okay',

            confirmButtonColor: '#ff5a73',

            background: '#ffffff',

            color: '#111827',

            customClass: {

                popup: 'wpm-swal-popup',

                confirmButton: 'wpm-swal-button'

            }

        });

    }

}

            });

        }
    );

    /*
    =====================================
    AJAX LOGIN
    =====================================
    */

    $('#wpm-login-form').on(
        'submit',
        function (e) {

            e.preventDefault();

            let form = $(this);

            let button = form.find('button[type="submit"]');

            button.text(
                'Logging in...'
            );

            $.ajax({

                url:
                    wpm_ajax.ajax_url,

                type: 'POST',

                data: {

                    action:
                        'wpm_login_user',

                    security:
                        wpm_ajax.nonce,

                    username:
                        form.find(
                            '[name="username"]'
                        ).val(),

                    password:
                        form.find(
                            '[name="password"]'
                        ).val()

                },

                success: function (
                    response
                ) {

                    button.text(
                        'Login'
                    );

                    if (
    response.success
) {

    Swal.fire({

        icon: 'success',

        title: 'Success',

        text: 'Login successful',

        confirmButtonText: 'Okay',

        confirmButtonColor: '#ff5a73',

        background: '#ffffff',

        color: '#111827',

        customClass: {

            popup: 'wpm-swal-popup',

            confirmButton:
                'wpm-swal-button'

        }

    }).then(() => {

        location.reload();

    });

} else {

    /*
    APPROVAL PENDING
    */

    if(
        response.data.message ===
        'Your profile is waiting for admin approval'
    ){

        Swal.fire({

            icon: 'warning',

            title: 'Approval Pending',

            text: response.data.message,

            confirmButtonText: 'Okay',

            confirmButtonColor: '#ff5a73',

            background: '#ffffff',

            color: '#111827',

            customClass: {

                popup: 'wpm-swal-popup',

                confirmButton:
                    'wpm-swal-button'

            }

        });

    }

    /*
    INVALID LOGIN
    */

    else{

        Swal.fire({

            icon: 'error',

            title: 'Login Failed',

            text: response.data.message,

            confirmButtonText: 'Try Again',

            confirmButtonColor: '#ff5a73',

            background: '#ffffff',

            color: '#111827',

            customClass: {

                popup: 'wpm-swal-popup',

                confirmButton:
                    'wpm-swal-button'

            }

        });

    }

}

                },

                error: function () {

                    button.text(
                        'Login'
                    );

                    Swal.fire({

                        icon: 'error',

                        title: 'Oops...',

                        text: 'Something went wrong. Please try again.',

                        confirmButtonColor: '#ff5a73'

                    });

                }

            });

        }
    );

});
