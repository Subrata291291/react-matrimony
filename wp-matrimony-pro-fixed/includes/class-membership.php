<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Membership {

    public function __construct() {

        add_action(
            'init',
            array( $this, 'maybe_create_payments_table' )
        );

        add_action(
            'admin_menu',
            array( $this, 'add_settings_page' )
        );

        add_action(
            'admin_init',
            array( $this, 'register_settings' )
        );

        add_action(
            'wp_ajax_wpm_create_razorpay_order',
            array( $this, 'create_razorpay_order' )
        );

        add_action(
            'wp_ajax_wpm_verify_razorpay_payment',
            array( $this, 'verify_razorpay_payment' )
        );

        add_action(
            'admin_post_wpm_activate_membership',
            array( $this, 'activate_membership' )
        );

        add_action(
            'admin_post_wpm_admin_activate_membership',
            array( $this, 'admin_activate_membership' )
        );

        add_action(
            'admin_post_wpm_refund_payment',
            array( $this, 'refund_payment' )
        );

        add_action(
            'admin_post_wpm_cancel_membership',
            array( $this, 'cancel_membership' )
        );

        add_action(
            'rest_api_init',
            array( $this, 'register_rest_routes' )
        );

    }

    public function maybe_create_payments_table() {

        global $wpdb;

        $installed_version = get_option(
            'wpm_membership_db_version'
        );

        if ( $installed_version === '1.0.0' ) {
            return;
        }

        $table_name = $wpdb->prefix . 'wpm_payments';
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            plan_key VARCHAR(100) NOT NULL,
            amount INT(11) NOT NULL,
            currency VARCHAR(10) NOT NULL DEFAULT 'INR',
            razorpay_order_id VARCHAR(100) DEFAULT '',
            razorpay_payment_id VARCHAR(100) DEFAULT '',
            status VARCHAR(50) NOT NULL DEFAULT 'created',
            payload LONGTEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY razorpay_order_id (razorpay_order_id),
            KEY razorpay_payment_id (razorpay_payment_id)
        ) $charset_collate;";

        dbDelta( $sql );

        update_option(
            'wpm_membership_db_version',
            '1.0.0'
        );

    }

    public function add_settings_page() {

        add_submenu_page(
            'wpm-members',
            'Payment History',
            'Payment History',
            'manage_options',
            'wpm-payment-history',
            array( $this, 'render_payments_page' )
        );

        add_submenu_page(
            'wpm-members',
            'Razorpay Settings',
            'Razorpay Settings',
            'manage_options',
            'wpm-razorpay-settings',
            array( $this, 'render_settings_page' )
        );

    }

    public function register_settings() {

        register_setting(
            'wpm_razorpay_settings_group',
            'wpm_razorpay_key_id'
        );

        register_setting(
            'wpm_razorpay_settings_group',
            'wpm_razorpay_key_secret'
        );

        register_setting(
            'wpm_razorpay_settings_group',
            'wpm_razorpay_webhook_secret'
        );

        register_setting(
            'wpm_razorpay_settings_group',
            'wpm_razorpay_mode'
        );

    }

    public function render_settings_page() {

        $webhook_url = rest_url( 'wpm/v1/razorpay/webhook' );
        ?>
        <div class="wrap">
            <h1>Razorpay Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'wpm_razorpay_settings_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Mode</th>
                        <td>
                            <select name="wpm_razorpay_mode">
                                <option value="test" <?php selected( get_option( 'wpm_razorpay_mode', 'test' ), 'test' ); ?>>Test</option>
                                <option value="live" <?php selected( get_option( 'wpm_razorpay_mode', 'test' ), 'live' ); ?>>Live</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Key ID</th>
                        <td>
                            <input type="text" class="regular-text" name="wpm_razorpay_key_id" value="<?php echo esc_attr( get_option( 'wpm_razorpay_key_id', '' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Key Secret</th>
                        <td>
                            <input type="password" class="regular-text" name="wpm_razorpay_key_secret" value="<?php echo esc_attr( get_option( 'wpm_razorpay_key_secret', '' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook Secret</th>
                        <td>
                            <input type="password" class="regular-text" name="wpm_razorpay_webhook_secret" value="<?php echo esc_attr( get_option( 'wpm_razorpay_webhook_secret', '' ) ); ?>">
                            <p class="description">Use the same secret in your Razorpay dashboard webhook configuration.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <code><?php echo esc_html( $webhook_url ); ?></code>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'Save Razorpay Settings' ); ?>
            </form>
        </div>
        <?php

    }

    public function register_rest_routes() {

        register_rest_route(
            'wpm/v1',
            '/razorpay/webhook',
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'handle_webhook' ),
                'permission_callback' => '__return_true',
            )
        );

    }

    private function get_api_credentials() {

        return array(
            'key_id' => trim( (string) get_option( 'wpm_razorpay_key_id', '' ) ),
            'key_secret' => trim( (string) get_option( 'wpm_razorpay_key_secret', '' ) ),
            'mode' => get_option( 'wpm_razorpay_mode', 'test' ),
        );

    }

    private function is_configured() {

        $credentials = $this->get_api_credentials();

        return ! empty( $credentials['key_id'] ) && ! empty( $credentials['key_secret'] );

    }

    private function create_payment_record( $user_id, $plan_key, $amount, $currency, $order_id, $payload = array() ) {

        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'wpm_payments',
            array(
                'user_id' => $user_id,
                'plan_key' => $plan_key,
                'amount' => $amount,
                'currency' => $currency,
                'razorpay_order_id' => $order_id,
                'status' => 'created',
                'payload' => wp_json_encode( $payload ),
            ),
            array( '%d', '%s', '%d', '%s', '%s', '%s', '%s' )
        );

    }

    private function update_payment_record( $order_id, $payment_id, $status, $payload = array() ) {

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'wpm_payments',
            array(
                'razorpay_payment_id' => $payment_id,
                'status' => $status,
                'payload' => wp_json_encode( $payload ),
            ),
            array(
                'razorpay_order_id' => $order_id,
            ),
            array( '%s', '%s', '%s' ),
            array( '%s' )
        );

    }

    private function get_payment_record_by_order( $order_id ) {

        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments WHERE razorpay_order_id = %s ORDER BY id DESC LIMIT 1',
                $order_id
            )
        );

    }

    private function get_payment_record_by_id( $payment_row_id ) {

        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments WHERE id = %d LIMIT 1',
                $payment_row_id
            )
        );

    }

    public function get_payment_by_id( $payment_row_id ) {

        return $this->get_payment_record_by_id( $payment_row_id );

    }

    public function get_user_payments( $user_id ) {

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments WHERE user_id = %d ORDER BY id DESC',
                $user_id
            )
        );

    }

    public function get_receipt_url( $payment_row_id ) {

        return add_query_arg(
            'payment_id',
            intval( $payment_row_id ),
            home_url( '/membership-receipt/' )
        );

    }

    private function get_basic_auth_header() {

        $credentials = $this->get_api_credentials();

        return 'Basic ' . base64_encode(
            $credentials['key_id'] . ':' . $credentials['key_secret']
        );

    }

    public function create_razorpay_order() {

        check_ajax_referer( 'wpm_nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Login required.' ) );
        }

        if ( ! $this->is_configured() ) {
            wp_send_json_error( array( 'message' => 'Razorpay is not configured yet. Please add API keys in admin settings.' ) );
        }

        $user_id = get_current_user_id();

        if ( ! wpm_is_user_approved( $user_id ) ) {
            wp_send_json_error( array( 'message' => 'Your profile must be approved before purchasing a membership.' ) );
        }

        $plan_key = isset( $_POST['plan_key'] )
            ? sanitize_text_field( wp_unslash( $_POST['plan_key'] ) )
            : '';

        $available_plans = wpm_get_available_membership_plans( $user_id );

        if ( ! isset( $available_plans[ $plan_key ] ) ) {
            wp_send_json_error( array( 'message' => 'Selected plan is not available.' ) );
        }

        $plan = $available_plans[ $plan_key ];
        $amount = intval( $plan['price'] ) * 100;
        $currency = 'INR';

        $body = array(
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => 'wpm_' . $user_id . '_' . time(),
            'notes' => array(
                'user_id' => (string) $user_id,
                'plan_key' => $plan_key,
            ),
        );

        $response = wp_remote_post(
            'https://api.razorpay.com/v1/orders',
            array(
                'headers' => array(
                    'Authorization' => $this->get_basic_auth_header(),
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode( $body ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code < 200 || $status_code >= 300 || empty( $response_body['id'] ) ) {
            $message = ! empty( $response_body['error']['description'] )
                ? $response_body['error']['description']
                : 'Unable to create Razorpay order.';
            wp_send_json_error( array( 'message' => $message ) );
        }

        $this->create_payment_record(
            $user_id,
            $plan_key,
            intval( $plan['price'] ),
            $currency,
            $response_body['id'],
            $response_body
        );

        $user = wp_get_current_user();

        wp_send_json_success(
            array(
                'key' => $this->get_api_credentials()['key_id'],
                'amount' => $amount,
                'currency' => $currency,
                'name' => get_bloginfo( 'name' ),
                'description' => $plan['label'] . ' Membership',
                'order_id' => $response_body['id'],
                'prefill' => array(
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'contact' => get_user_meta( $user_id, 'wpm_mobile', true ),
                ),
                'notes' => array(
                    'plan_key' => $plan_key,
                ),
                'plan_label' => $plan['label'],
                'success_redirect' => add_query_arg(
                    'membership',
                    'activated',
                    wpm_get_membership_checkout_url( $plan_key )
                ),
            )
        );

    }

    public function verify_razorpay_payment() {

        check_ajax_referer( 'wpm_nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Login required.' ) );
        }

        if ( ! $this->is_configured() ) {
            wp_send_json_error( array( 'message' => 'Razorpay is not configured yet.' ) );
        }

        $order_id = isset( $_POST['razorpay_order_id'] )
            ? sanitize_text_field( wp_unslash( $_POST['razorpay_order_id'] ) )
            : '';

        $payment_id = isset( $_POST['razorpay_payment_id'] )
            ? sanitize_text_field( wp_unslash( $_POST['razorpay_payment_id'] ) )
            : '';

        $signature = isset( $_POST['razorpay_signature'] )
            ? sanitize_text_field( wp_unslash( $_POST['razorpay_signature'] ) )
            : '';

        if ( ! $order_id || ! $payment_id || ! $signature ) {
            wp_send_json_error( array( 'message' => 'Missing Razorpay payment details.' ) );
        }

        $record = $this->get_payment_record_by_order( $order_id );

        if ( ! $record || intval( $record->user_id ) !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => 'Order not found for this user.' ) );
        }

        if ( ! wpm_is_user_approved( get_current_user_id() ) ) {
            wp_send_json_error( array( 'message' => 'Your profile must be approved before activating a membership.' ) );
        }

        $generated_signature = hash_hmac(
            'sha256',
            $order_id . '|' . $payment_id,
            $this->get_api_credentials()['key_secret']
        );

        if ( ! hash_equals( $generated_signature, $signature ) ) {
            $this->update_payment_record( $order_id, $payment_id, 'signature_failed', $_POST );
            wp_send_json_error( array( 'message' => 'Payment signature verification failed.' ) );
        }

        $payment_response = wp_remote_get(
            'https://api.razorpay.com/v1/payments/' . rawurlencode( $payment_id ),
            array(
                'headers' => array(
                    'Authorization' => $this->get_basic_auth_header(),
                    'Content-Type' => 'application/json',
                ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $payment_response ) ) {
            wp_send_json_error( array( 'message' => $payment_response->get_error_message() ) );
        }

        $payment_body = json_decode( wp_remote_retrieve_body( $payment_response ), true );
        $payment_status = ! empty( $payment_body['status'] ) ? $payment_body['status'] : 'unknown';

        if ( ! in_array( $payment_status, array( 'authorized', 'captured' ), true ) ) {
            $this->update_payment_record( $order_id, $payment_id, $payment_status, $payment_body );
            wp_send_json_error( array( 'message' => 'Payment is not successful yet. Current status: ' . $payment_status ) );
        }

        $this->update_payment_record( $order_id, $payment_id, $payment_status, $payment_body );

        if ( ! wpm_activate_membership(
            get_current_user_id(),
            $record->plan_key,
            array(
                'payment_id' => $payment_id,
                'order_id' => $order_id,
            )
        ) ) {
            wp_send_json_error( array( 'message' => 'Payment received but membership activation failed.' ) );
        }

        wp_send_json_success(
            array(
                'message' => 'Payment verified and membership activated.',
                'redirect' => add_query_arg(
                    'membership',
                    'activated',
                    wpm_get_membership_checkout_url( $record->plan_key )
                ),
                'receipt_url' => $this->get_receipt_url( $record->id ),
            )
        );

    }

    public function activate_membership() {

        if ( ! is_user_logged_in() ) {
            wp_safe_redirect( wp_login_url() );
            exit;
        }

        check_admin_referer( 'wpm_activate_membership' );

        $user_id = get_current_user_id();
        $plan_key = isset( $_POST['plan_key'] )
            ? sanitize_text_field( wp_unslash( $_POST['plan_key'] ) )
            : '';

        $available_plans = wpm_get_available_membership_plans( $user_id );

        if ( ! isset( $available_plans[ $plan_key ] ) ) {
            wp_safe_redirect(
                add_query_arg(
                    'membership',
                    'invalid',
                    wpm_get_membership_checkout_url()
                )
            );
            exit;
        }

        wpm_activate_membership( $user_id, $plan_key );

        wp_safe_redirect(
            add_query_arg(
                'membership',
                'activated',
                wpm_get_membership_checkout_url( $plan_key )
            )
        );
        exit;

    }

    public function admin_activate_membership() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to do this.', 'wp-matrimony-pro' ) );
        }

        check_admin_referer( 'wpm_admin_activate_membership' );

        $user_id = isset( $_POST['user_id'] )
            ? intval( $_POST['user_id'] )
            : 0;

        $plan_key = isset( $_POST['plan_key'] )
            ? sanitize_text_field( wp_unslash( $_POST['plan_key'] ) )
            : '';

        if ( ! $user_id || ! wpm_get_membership_plan( $plan_key ) ) {
            wp_safe_redirect(
                admin_url( 'admin.php?page=wpm-members&membership=invalid' )
            );
            exit;
        }

        wpm_activate_membership( $user_id, $plan_key );

        wp_safe_redirect(
            admin_url(
                'admin.php?page=wpm-members&edit_user=' . $user_id . '&membership=activated'
            )
        );
        exit;

    }

    public function render_payments_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;

        $payments = $wpdb->get_results(
            'SELECT * FROM ' . $wpdb->prefix . 'wpm_payments ORDER BY id DESC LIMIT 200'
        );
        ?>
        <div class="wrap">
            <h1>Membership Payment History</h1>

            <?php if ( isset( $_GET['refund'] ) && $_GET['refund'] === 'success' ) : ?>
                <div class="notice notice-success"><p>Refund created successfully.</p></div>
            <?php elseif ( isset( $_GET['refund'] ) && $_GET['refund'] === 'failed' ) : ?>
                <div class="notice notice-error"><p>Refund request failed.</p></div>
            <?php elseif ( isset( $_GET['cancel'] ) && $_GET['cancel'] === 'success' ) : ?>
                <div class="notice notice-success"><p>Membership cancelled successfully.</p></div>
            <?php endif; ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Order ID</th>
                        <th>Payment ID</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $payments ) ) : ?>
                        <?php foreach ( $payments as $payment ) : ?>
                            <?php $user = get_user_by( 'id', $payment->user_id ); ?>
                            <tr>
                                <td><?php echo esc_html( $payment->id ); ?></td>
                                <td>
                                    <?php echo $user ? esc_html( $user->display_name ) : 'Unknown User'; ?>
                                    <br>
                                    <small><?php echo $user ? esc_html( $user->user_email ) : ''; ?></small>
                                </td>
                                <td><?php echo esc_html( wpm_get_membership_label( $payment->plan_key ) ); ?></td>
                                <td>Rs <?php echo esc_html( $payment->amount ); ?></td>
                                <td><code><?php echo esc_html( $payment->razorpay_order_id ); ?></code></td>
                                <td><code><?php echo esc_html( $payment->razorpay_payment_id ); ?></code></td>
                                <td><?php echo esc_html( ucfirst( $payment->status ) ); ?></td>
                                <td><?php echo esc_html( $payment->created_at ); ?></td>
                                <td>
                                    <a class="button button-secondary" href="<?php echo esc_url( $this->get_receipt_url( $payment->id ) ); ?>" target="_blank">Receipt</a>
                                    <?php if ( in_array( $payment->status, array( 'captured', 'authorized' ), true ) && ! empty( $payment->razorpay_payment_id ) ) : ?>
                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:6px;">
                                            <?php wp_nonce_field( 'wpm_refund_payment_' . $payment->id ); ?>
                                            <input type="hidden" name="action" value="wpm_refund_payment">
                                            <input type="hidden" name="payment_row_id" value="<?php echo esc_attr( $payment->id ); ?>">
                                            <button class="button">Refund</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ( $user ) : ?>
                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:6px;">
                                            <?php wp_nonce_field( 'wpm_cancel_membership_' . $user->ID ); ?>
                                            <input type="hidden" name="action" value="wpm_cancel_membership">
                                            <input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>">
                                            <button class="button">Cancel Plan</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">No payments found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php

    }

    public function refund_payment() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to do this.', 'wp-matrimony-pro' ) );
        }

        $payment_row_id = isset( $_POST['payment_row_id'] )
            ? intval( $_POST['payment_row_id'] )
            : 0;

        check_admin_referer( 'wpm_refund_payment_' . $payment_row_id );

        $record = $this->get_payment_record_by_id( $payment_row_id );

        if ( ! $record || empty( $record->razorpay_payment_id ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&refund=invalid' ) );
            exit;
        }

        if ( ! $this->is_configured() ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&refund=config' ) );
            exit;
        }

        $response = wp_remote_post(
            'https://api.razorpay.com/v1/payments/' . rawurlencode( $record->razorpay_payment_id ) . '/refund',
            array(
                'headers' => array(
                    'Authorization' => $this->get_basic_auth_header(),
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(
                    array(
                        'notes' => array(
                            'source' => 'wordpress_admin',
                            'membership_payment_row_id' => (string) $record->id,
                        ),
                    )
                ),
                'timeout' => 30,
            )
        );

        if ( is_wp_error( $response ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&refund=failed' ) );
            exit;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $refund_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code < 200 || $status_code >= 300 || empty( $refund_body['id'] ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&refund=failed' ) );
            exit;
        }

        $this->update_payment_record(
            $record->razorpay_order_id,
            $record->razorpay_payment_id,
            'refunded',
            $refund_body
        );

        $current_membership_payment_id = get_user_meta(
            $record->user_id,
            'wpm_membership_payment_id',
            true
        );

        if ( $current_membership_payment_id === $record->razorpay_payment_id ) {
            wpm_cancel_membership( $record->user_id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&refund=success' ) );
        exit;

    }

    public function cancel_membership() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to do this.', 'wp-matrimony-pro' ) );
        }

        $user_id = isset( $_POST['user_id'] )
            ? intval( $_POST['user_id'] )
            : 0;

        check_admin_referer( 'wpm_cancel_membership_' . $user_id );

        if ( $user_id ) {
            wpm_cancel_membership( $user_id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wpm-payment-history&cancel=success' ) );
        exit;

    }

    public function handle_webhook( WP_REST_Request $request ) {

        $webhook_secret = trim( (string) get_option( 'wpm_razorpay_webhook_secret', '' ) );
        $signature = $request->get_header( 'x_razorpay_signature' );
        $body = $request->get_body();

        if ( ! $webhook_secret || ! $signature ) {
            return new WP_REST_Response( array( 'message' => 'Webhook secret not configured.' ), 400 );
        }

        $generated_signature = hash_hmac(
            'sha256',
            $body,
            $webhook_secret
        );

        if ( ! hash_equals( $generated_signature, $signature ) ) {
            return new WP_REST_Response( array( 'message' => 'Invalid webhook signature.' ), 400 );
        }

        $payload = json_decode( $body, true );
        $event = ! empty( $payload['event'] ) ? $payload['event'] : '';

        if ( ! in_array( $event, array( 'payment.authorized', 'payment.captured', 'payment.refunded' ), true ) ) {
            return new WP_REST_Response( array( 'message' => 'Event ignored.' ), 200 );
        }

        $payment_entity = $payload['payload']['payment']['entity'] ?? array();
        $order_id = ! empty( $payment_entity['order_id'] ) ? $payment_entity['order_id'] : '';
        $payment_id = ! empty( $payment_entity['id'] ) ? $payment_entity['id'] : '';
        $status = ! empty( $payment_entity['status'] ) ? $payment_entity['status'] : 'unknown';

        if ( ! $order_id ) {
            return new WP_REST_Response( array( 'message' => 'Order ID missing.' ), 400 );
        }

        $record = $this->get_payment_record_by_order( $order_id );

        if ( ! $record ) {
            return new WP_REST_Response( array( 'message' => 'Order not found.' ), 404 );
        }

        $this->update_payment_record( $order_id, $payment_id, $status, $payload );

        if ( in_array( $status, array( 'authorized', 'captured' ), true ) ) {
            wpm_activate_membership(
                intval( $record->user_id ),
                $record->plan_key,
                array(
                    'payment_id' => $payment_id,
                    'order_id' => $order_id,
                )
            );
        }

        if ( $event === 'payment.refunded' || $status === 'refunded' ) {
            $current_membership_payment_id = get_user_meta(
                $record->user_id,
                'wpm_membership_payment_id',
                true
            );

            if ( $current_membership_payment_id === $payment_id ) {
                wpm_cancel_membership( intval( $record->user_id ) );
            }
        }

        return new WP_REST_Response( array( 'message' => 'Webhook processed.' ), 200 );

    }

}
