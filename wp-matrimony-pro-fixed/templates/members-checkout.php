<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$is_logged_in      = is_user_logged_in();
$current_user_id   = $is_logged_in ? get_current_user_id() : 0;
$membership        = $is_logged_in ? wpm_get_membership_details( $current_user_id ) : null;
$available_plans   = $is_logged_in ? wpm_get_available_membership_plans( $current_user_id ) : array();
$selected_plan     = isset( $_GET['plan'] ) ? sanitize_text_field( wp_unslash( $_GET['plan'] ) ) : '';
$notice            = isset( $_GET['membership'] ) ? sanitize_text_field( wp_unslash( $_GET['membership'] ) ) : '';
$razorpay_ready    = ! empty( get_option( 'wpm_razorpay_key_id', '' ) ) && ! empty( get_option( 'wpm_razorpay_key_secret', '' ) );
$payment_history   = array();

if ( $is_logged_in ) {
    global $wpdb;

    $payment_history = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments WHERE user_id = %d AND status != %s ORDER BY id DESC LIMIT 10',
            $current_user_id,
            'created'
        )
    );
}

if ( $selected_plan && isset( $available_plans[ $selected_plan ] ) ) {
    $available_plans = array(
        $selected_plan => $available_plans[ $selected_plan ],
    ) + $available_plans;
}
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

<section class="wpm-checkout-page" style="padding:80px 0;background:#fff7f8;">
    <div class="container">
        <div class="wpm-checkout-box" style="margin:0 auto;background:#ffffff;border-radius:24px;padding:40px;box-shadow:0 20px 60px rgba(0,0,0,0.08);">
            <h1 style="margin-bottom:10px;">Membership Plans</h1>
            <p style="margin-bottom:30px;color:#5f5f76;">
                First day plan is Rs 5 for 24 hours. After that, users can continue with monthly, quarterly, half yearly, or full year plans.
            </p>

            <?php if ( $notice === 'activated' ) : ?>
                <div style="padding:14px 18px;border-radius:14px;background:#ecfdf3;color:#0f6b3c;margin-bottom:24px;">
                    Your membership has been activated successfully.
                </div>
            <?php elseif ( $notice === 'invalid' ) : ?>
                <div style="padding:14px 18px;border-radius:14px;background:#fff1f2;color:#b42318;margin-bottom:24px;">
                    That plan is not available right now for this user.
                </div>
            <?php endif; ?>

            <?php if ( ! $is_logged_in ) : ?>
                <div style="padding:24px;border-radius:18px;background:#fff1f2;">
                    <h3 style="margin-bottom:8px;">Login Required</h3>
                    <p style="margin:0;">Please login first to activate a plan for your account.</p>
                </div>
            <?php else : ?>
                <?php if ( ! $razorpay_ready ) : ?>
                    <div style="padding:14px 18px;border-radius:14px;background:#fff8e8;color:#9a6700;margin-bottom:24px;">
                        Razorpay keys are not configured yet. Add them in WordPress admin under `Matrimony Members -> Razorpay Settings`.
                    </div>
                <?php endif; ?>

                <div style="padding:20px;border-radius:18px;background:#f8fafc;margin-bottom:28px;">
                    <h3 style="margin-bottom:8px;">Current Membership</h3>
                    <p style="margin:0 0 8px;"><?php echo esc_html( wpm_get_membership_notice( $current_user_id ) ); ?></p>

                    <?php if ( ! empty( $membership['is_active'] ) ) : ?>
                        <p style="margin:0;color:#0f6b3c;">
                            Active plan: <?php echo esc_html( $membership['label'] ); ?> | Rs <?php echo esc_html( $membership['price'] ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="row g-4">
                    <?php foreach ( $available_plans as $plan_key => $plan ) : ?>
                        <div class="col-lg-3 col-md-6">
                            <div style="height:100%;border:1px solid #f1d7db;border-radius:22px;padding:24px;background:#fff;">
                                <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#fff1f2;color:#be123c;font-size:13px;font-weight:600;">
                                    <?php echo esc_html( $plan['duration_days'] ); ?> Day<?php echo $plan['duration_days'] > 1 ? 's' : ''; ?>
                                </span>

                                <h3 style="margin:18px 0 6px;"><?php echo esc_html( $plan['label'] ); ?></h3>
                                <div style="font-size:34px;font-weight:700;line-height:1.1;">Rs <?php echo esc_html( $plan['price'] ); ?></div>
                                <p style="margin:12px 0 24px;color:#5f5f76;"><?php echo esc_html( $plan['description'] ); ?></p>

                                <button
                                    type="button"
                                    class="wpm-razorpay-pay"
                                    data-plan="<?php echo esc_attr( $plan_key ); ?>"
                                    data-label="<?php echo esc_attr( 'Pay Rs ' . $plan['price'] ); ?>"
                                    <?php disabled( ! $razorpay_ready ); ?>
                                    style="width:100%;border:0;border-radius:14px;padding:14px 16px;background:#ff5a73;color:#fff;font-weight:600;opacity:<?php echo $razorpay_ready ? '1' : '0.6'; ?>;"
                                >
                                    Pay Rs <?php echo esc_html( $plan['price'] ); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( empty( $available_plans ) ) : ?>
                    <div style="padding:20px;border-radius:18px;background:#fff1f2;margin-top:24px;">
                        No plans are available right now.
                    </div>
                <?php endif; ?>

                <div style="margin-top:36px;">
                    <h3 style="margin-bottom:16px;">My Payment History</h3>

                    <?php if ( ! empty( $payment_history ) ) : ?>
                        <div style="border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
                            <table style="width:100%;border-collapse:collapse;">
                                <thead style="background:#f8fafc;">
                                    <tr>
                                        <th style="padding:14px;text-align:left;">Plan</th>
                                        <th style="padding:14px;text-align:left;">Amount</th>
                                        <th style="padding:14px;text-align:left;">Status</th>
                                        <th style="padding:14px;text-align:left;">Date</th>
                                        <th style="padding:14px;text-align:left;">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $payment_history as $history_item ) : ?>
                                        <tr>
                                            <td style="padding:14px;border-top:1px solid #e5e7eb;"><?php echo esc_html( wpm_get_membership_label( $history_item->plan_key ) ); ?></td>
                                            <td style="padding:14px;border-top:1px solid #e5e7eb;">Rs <?php echo esc_html( $history_item->amount ); ?></td>
                                            <td style="padding:14px;border-top:1px solid #e5e7eb;"><?php echo esc_html( ucfirst( $history_item->status ) ); ?></td>
                                            <td style="padding:14px;border-top:1px solid #e5e7eb;"><?php echo esc_html( $history_item->created_at ); ?></td>
                                            <td style="padding:14px;border-top:1px solid #e5e7eb;">
                                                <a href="<?php echo esc_url( add_query_arg( 'payment_id', intval( $history_item->id ), home_url( '/membership-receipt/' ) ) ); ?>">
                                                    View Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div style="padding:18px;border-radius:16px;background:#f8fafc;">
                            No membership payments yet.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
