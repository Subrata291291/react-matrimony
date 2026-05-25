<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( ! is_user_logged_in() ) {
    echo '<div class="container py-5"><h2>Login required</h2></div>';
    get_footer();
    return;
}

$payment_row_id = isset( $_GET['payment_id'] )
    ? intval( $_GET['payment_id'] )
    : 0;

global $wpdb;

$payment = $wpdb->get_row(
    $wpdb->prepare(
        'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments WHERE id = %d LIMIT 1',
        $payment_row_id
    )
);

if ( ! $payment || intval( $payment->user_id ) !== get_current_user_id() ) {
    echo '<div class="container py-5"><h2>Receipt not found</h2></div>';
    get_footer();
    return;
}

$user = wp_get_current_user();
$plan = wpm_get_membership_plan( $payment->plan_key );
$payload = json_decode( $payment->payload, true );
?>
<section class="common-banner romantic-banner">

    <div class="love-bg-shape shape-1"></div>
    <div class="love-bg-shape shape-2"></div>
    <div class="love-bg-shape shape-3"></div>

    <div class="floating-hearts">
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
    </div>

    <div class="container romantic-banner-inner">

        <div class="love-person left-person">

            <img
                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=800&auto=format&fit=crop"
                alt="Man"
            >

        </div>

        <div class="banner-content">

            <h3 class="title">
                FIND YOUR FOREVER
            </h3>

            <h1>Start Your <span>Journey</span>

        </h1>

            <p>
                Where hearts connect, stories begin, and forever starts.
            </p>

        </div>

        <div class="love-person right-person">

            <img
                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=800&auto=format&fit=crop"
                alt="Woman"
            >

        </div>

    </div>

</section>

<section id="receipt-print-area" style="padding:80px 0;background:#f8fafc;">
    <div class="container">
        <div style="background:#fff;border-radius:24px;padding:40px;box-shadow:0 20px 60px rgba(0,0,0,0.08);">
            <div style="display:flex;justify-content:space-between;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-bottom:28px;">
                <div>
                    <h1 style="margin-bottom:8px;">Payment Receipt</h1>
                    <p style="margin:0;color:#5f5f76;">Membership payment confirmation for your account.</p>
                </div>
                <button onclick="printReceipt()" style="border:0;border-radius:12px;padding:12px 18px;background:#ff5a73;color:#fff;font-weight:600;">
                    Print Receipt
                </button>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div style="padding:20px;border-radius:16px;background:#f8fafc;height:100%;">
                        <h3 style="margin-bottom:14px;">Customer</h3>
                        <p style="margin:0 0 8px;"><strong><?php echo esc_html( $user->display_name ); ?></strong></p>
                        <p style="margin:0;"><?php echo esc_html( $user->user_email ); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="padding:20px;border-radius:16px;background:#f8fafc;height:100%;">
                        <h3 style="margin-bottom:14px;">Payment</h3>
                        <p style="margin:0 0 8px;">Status: <strong><?php echo esc_html( ucfirst( $payment->status ) ); ?></strong></p>
                        <p style="margin:0;">Date: <?php echo esc_html( $payment->created_at ); ?></p>
                    </div>
                </div>
            </div>

            <div style="margin-top:28px;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;">
                    <tbody>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Receipt ID</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><?php echo esc_html( $payment->id ); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Plan</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><?php echo esc_html( $plan ? $plan['label'] : $payment->plan_key ); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Amount</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;">Rs <?php echo esc_html( $payment->amount ); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Currency</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><?php echo esc_html( $payment->currency ); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Razorpay Order ID</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><code><?php echo esc_html( $payment->razorpay_order_id ); ?></code></td>
                        </tr>
                        <tr>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><strong>Razorpay Payment ID</strong></td>
                            <td style="padding:16px;border-bottom:1px solid #e5e7eb;"><code><?php echo esc_html( $payment->razorpay_payment_id ); ?></code></td>
                        </tr>
                        <!-- <tr>
                            <td style="padding:16px;"><strong>Reference</strong></td>
                            <td style="padding:16px;"><?php echo ! empty( $payload['receipt'] ) ? esc_html( $payload['receipt'] ) : 'N/A'; ?></td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
function printReceipt() {

    var printContents = document.getElementById('receipt-print-area').innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.print();

    document.body.innerHTML = originalContents;

    location.reload();
}
</script>
<?php get_footer(); ?>
