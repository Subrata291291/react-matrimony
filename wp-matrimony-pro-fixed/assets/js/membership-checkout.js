jQuery(document).ready(function ($) {

    function showMembershipAlert(icon, title, text) {
        if (window.Swal) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#ff5a73',
                background: '#ffffff',
                color: '#111827'
            });
            return;
        }

        alert(text);
    }

    $(document).on('click', '.wpm-razorpay-pay', function (e) {
        e.preventDefault();

        let button = $(this);
        let planKey = button.data('plan');

        if (!planKey) {
            return;
        }

        button.prop('disabled', true).text('Preparing payment...');

        $.ajax({
            url: wpm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpm_create_razorpay_order',
                security: wpm_ajax.nonce,
                plan_key: planKey
            },
            success: function (response) {
                if (!response.success) {
                    button.prop('disabled', false).text(button.data('label'));
                    showMembershipAlert(
                        'error',
                        'Payment setup failed',
                        response.data && response.data.message
                            ? response.data.message
                            : 'Unable to create payment order.'
                    );
                    return;
                }

                let data = response.data;

                if (typeof Razorpay === 'undefined') {
                    button.prop('disabled', false).text(button.data('label'));
                    showMembershipAlert('error', 'Razorpay not loaded', 'Checkout script could not be loaded.');
                    return;
                }

                let options = {
                    key: data.key,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    description: data.description,
                    order_id: data.order_id,
                    prefill: data.prefill || {},
                    notes: data.notes || {},
                    theme: {
                        color: '#ff5a73'
                    },
                    handler: function (paymentResponse) {
                        $.ajax({
                            url: wpm_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'wpm_verify_razorpay_payment',
                                security: wpm_ajax.nonce,
                                razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                razorpay_order_id: paymentResponse.razorpay_order_id,
                                razorpay_signature: paymentResponse.razorpay_signature
                            },
                            success: function (verifyResponse) {
                                if (verifyResponse.success) {
                                    window.location.href = verifyResponse.data.redirect;
                                    return;
                                }

                                button.prop('disabled', false).text(button.data('label'));
                                showMembershipAlert(
                                    'error',
                                    'Verification failed',
                                    verifyResponse.data && verifyResponse.data.message
                                        ? verifyResponse.data.message
                                        : 'Payment was made, but verification failed.'
                                );
                            },
                            error: function () {
                                button.prop('disabled', false).text(button.data('label'));
                                showMembershipAlert('error', 'Verification failed', 'Could not verify the payment on the server.');
                            }
                        });
                    },
                    modal: {
                        ondismiss: function () {
                            button.prop('disabled', false).text(button.data('label'));
                        }
                    }
                };

                let rzp = new Razorpay(options);

                rzp.on('payment.failed', function (failedResponse) {
                    button.prop('disabled', false).text(button.data('label'));

                    let errorText = 'Payment failed. Please try again.';

                    if (
                        failedResponse.error &&
                        failedResponse.error.description
                    ) {
                        errorText = failedResponse.error.description;
                    }

                    showMembershipAlert('error', 'Payment failed', errorText);
                });

                rzp.open();
            },
            error: function () {
                button.prop('disabled', false).text(button.data('label'));
                showMembershipAlert('error', 'Payment setup failed', 'Something went wrong while creating the payment order.');
            }
        });
    });

});
