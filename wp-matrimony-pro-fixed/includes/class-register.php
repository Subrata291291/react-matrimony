
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Register {

    public function __construct() {

        add_action(
            'wp_ajax_nopriv_wpm_register_user',
            array($this, 'register_user')
        );

        add_action(
            'rest_api_init',
            array( $this, 'register_rest_routes' )
        );

    }

    public function register_rest_routes() {

        register_rest_route(
            'wpm/v1',
            '/register',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_register_user' ),
                'permission_callback' => '__return_true',
            )
        );

    }

    /*
    =========================================
    REGISTER USER
    =========================================
    */

    public function register_user() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        $result = $this->handle_registration(
            wp_unslash( $_POST )
        );

        if ( is_wp_error( $result ) ) {

            wp_send_json_error(
                array(
                    'message' => $result->get_error_message(),
                )
            );

        }

        wp_send_json_success( $result );

    }

    public function rest_register_user( WP_REST_Request $request ) {

        $payload = $request->get_json_params();

        if ( empty( $payload ) ) {
            $payload = $request->get_params();
        }

        $response = $this->handle_registration( $payload );

        if ( is_wp_error( $response ) ) {
            return new WP_REST_Response(
                array(
                    'message' => $response->get_error_message(),
                ),
                intval( $response->get_error_data( 'status' ) ?: 400 )
            );
        }

        return rest_ensure_response( $response );

    }

    private function handle_registration( $data ) {

        $username = sanitize_user(
            $data['username'] ?? $data['name'] ?? ''
        );

        $email = sanitize_email(
            $data['email'] ?? ''
        );

        $password = $data['password'] ?? '';

        $gender = $this->normalize_gender(
            sanitize_text_field(
                $data['gender'] ?? ''
            )
        );

        if (
            empty($username) ||
            empty($email) ||
            empty($password)
        ) {

            return new WP_Error(
                'missing_fields',
                'All fields required.',
                array( 'status' => 400 )
            );

        }

        if (
            ! preg_match(
                '/^[a-zA-Z0-9_]+$/',
                $username
            )
        ) {

            return new WP_Error(
                'invalid_username',
                'Username can only contain letters, numbers, and underscore.',
                array( 'status' => 400 )
            );

        }
        if (
            ! is_email( $email )
        ) {

            return new WP_Error(
                'invalid_email',
                'Please enter a valid email address.',
                array( 'status' => 400 )
            );

        }

        $domain = substr(
            strrchr($email, "@"),
            1
        );

        if (
            function_exists( 'checkdnsrr' ) &&
            ! checkdnsrr(
                $domain,
                'MX'
            )
        ) {

            return new WP_Error(
                'invalid_email_domain',
                'Please enter a real email address.',
                array( 'status' => 400 )
            );

        }
        if (
            username_exists($username)
        ) {

            return new WP_Error(
                'username_exists',
                'Username already exists.',
                array( 'status' => 409 )
            );

        }

        if (
            email_exists($email)
        ) {

            return new WP_Error(
                'email_exists',
                'This email is already registered. Please login instead.',
                array( 'status' => 409 )
            );

        }

        /*
        -----------------------------------------
        CREATE USER
        -----------------------------------------
        */

        $user_id = wp_create_user(

            $username,

            $password,

            $email

        );

        if (
            is_wp_error($user_id)
        ) {

            return new WP_Error(
                'registration_failed',
                $user_id->get_error_message(),
                array( 'status' => 400 )
            );

        }

        /*
        -----------------------------------------
        ROLE
        -----------------------------------------
        */

        $user = new WP_User(
            $user_id
        );

        $user->set_role(
            'subscriber'
        );

        /*
        -----------------------------------------
        SAVE META
        -----------------------------------------
        */

        update_user_meta(

            $user_id,

            'wpm_gender',

            $gender

        );

        update_user_meta(

            $user_id,

            'wpm_profile_status',

            'pending'

        );

        update_user_meta(
            $user_id,
            'wpm_looking_for',
            $gender === 'man'
                ? 'women'
                : 'man'
        );

        /*
        -----------------------------------------
        EMAIL NOTIFICATIONS
        -----------------------------------------
        */

        $this->send_registration_emails(
            $user_id
        );

        /*
        -----------------------------------------
        LOGIN USER
        -----------------------------------------
        */

        wp_set_current_user(
            $user_id
        );

        wp_set_auth_cookie(
            $user_id
        );

        /*
        -----------------------------------------
        RESPONSE
        -----------------------------------------
        */

        return array(
            'message' =>
            'Registration successful.',

            'redirect' =>
            site_url('/edit-profile'),

            'user' => $this->get_auth_user_data( $user_id ),
        );

    }

    private function normalize_gender( $gender ) {

        $normalized = strtolower(
            trim( $gender )
        );

        if ( $normalized === 'woman' ) {
            return 'women';
        }

        if ( in_array( $normalized, array( 'man', 'women' ), true ) ) {
            return $normalized;
        }

        return '';

    }

    private function get_auth_user_data( $user_id ) {

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return null;
        }

        $full_name = get_user_meta(
            $user_id,
            'full_name',
            true
        );

        return array(
            'id'       => $user_id,
            'username' => $user->user_login,
            'name'     => $full_name
                ? $full_name
                : $user->display_name,
            'email'    => $user->user_email,
            'gender'   => get_user_meta( $user_id, 'wpm_gender', true ),
            'status'   => get_user_meta( $user_id, 'wpm_profile_status', true ),
        );

    }

    /*
    =========================================
    SEND REGISTRATION EMAILS
    =========================================
    */

    private function send_registration_emails( $user_id ) {

        $user = get_userdata(
            $user_id
        );

        if (
            ! $user
        ) {
            return;
        }

        $site_name = wp_specialchars_decode(
            get_bloginfo( 'name' ),
            ENT_QUOTES
        );

        $admin_email = get_option(
            'admin_email'
        );

        $gender = get_user_meta(
            $user_id,
            'wpm_gender',
            true
        );

        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );

        $admin_subject = sprintf(
            '[%s] New user registration',
            $site_name
        );

        $admin_message = sprintf(
            '<p>A new account has been registered on %1$s.</p>
            <p><strong>Username:</strong> %2$s</p>
            <p><strong>Email:</strong> %3$s</p>
            <p><strong>Gender:</strong> %4$s</p>
            <p><strong>Status:</strong> Pending approval</p>
            <p><a href="%5$s">Review pending profiles</a></p>',
            esc_html( $site_name ),
            esc_html( $user->user_login ),
            esc_html( $user->user_email ),
            esc_html( ucfirst( $gender ) ),
            esc_url( admin_url( 'admin.php?page=wpm-users' ) )
        );

        wp_mail(
            $admin_email,
            $admin_subject,
            $admin_message,
            $headers
        );

        $user_subject = sprintf(
            'Welcome to %s',
            $site_name
        );

        $user_message = sprintf(
            '<p>Hi %1$s,</p>
            <p>Thank you for registering on %2$s.</p>
            <p>Your account has been created successfully and your profile is pending admin approval.</p>
            <p>You can continue completing your profile here: <a href="%3$s">%3$s</a></p>',
            esc_html( $user->user_login ),
            esc_html( $site_name ),
            esc_url( site_url( '/edit-profile' ) )
        );

        $mail_sent = wp_mail(

            $user->user_email,

            $user_subject,

            $user_message,

            $headers

        );

        if ( ! $mail_sent ) {

            wp_mail(

                $admin_email,

                'User Email Delivery Failed',

                'Registration mail could not be delivered to: ' . $user->user_email

            );

        }

    }

}
