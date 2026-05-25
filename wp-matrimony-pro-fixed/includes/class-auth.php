<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Auth {

    public function __construct() {

        /*
        -----------------------------------------
        AJAX LOGIN
        -----------------------------------------
        */

        add_action(
            'wp_ajax_nopriv_wpm_login_user',
            array( $this, 'login_user' )
        );

        /*
        -----------------------------------------
        AJAX LOGOUT
        -----------------------------------------
        */

        add_action(
            'wp_ajax_wpm_logout_user',
            array( $this, 'logout_user' )
        );

        add_action(
            'rest_api_init',
            array( $this, 'register_rest_routes' )
        );

    }

    public function register_rest_routes() {

        register_rest_route(
            'wpm/v1',
            '/login',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_login_user' ),
                'permission_callback' => '__return_true',
            )
        );

    }

    /*
    -----------------------------------------
    LOGIN USER
    -----------------------------------------
    */

    public function login_user() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        $result = $this->handle_login(
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

    public function rest_login_user( WP_REST_Request $request ) {

        $payload = $request->get_json_params();

        if ( empty( $payload ) ) {
            $payload = $request->get_params();
        }

        $response = $this->handle_login( $payload );

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

    private function handle_login( $data ) {

        $username = sanitize_text_field(
            $data['username'] ?? ''
        );

        $password = $data['password'] ?? '';

        $remember = ! empty( $data['remember'] );

        /*
        -----------------------------------------
        VALIDATION
        -----------------------------------------
        */

        if ( empty( $username ) ) {

            return new WP_Error(
                'missing_username',
                'Username or Email is required',
                array( 'status' => 400 )
            );

        }

        if ( empty( $password ) ) {

            return new WP_Error(
                'missing_password',
                'Password is required',
                array( 'status' => 400 )
            );

        }

        /*
        -----------------------------------------
        ALLOW EMAIL LOGIN
        -----------------------------------------
        */

        if ( is_email( $username ) ) {

            $user = get_user_by(
                'email',
                $username
            );

            if ( $user ) {
                $username = $user->user_login;
            }

        }

        /*
        -----------------------------------------
        LOGIN DATA
        -----------------------------------------
        */

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );

        $user = wp_signon(
            $creds,
            false
        );

        /*
        -----------------------------------------
        LOGIN FAILED
        -----------------------------------------
        */

        if ( is_wp_error( $user ) ) {

            return new WP_Error(
                'invalid_login',
                'Invalid login credentials',
                array( 'status' => 401 )
            );

        }

        /*
        -----------------------------------------
        CHECK APPROVAL
        -----------------------------------------
        */

        $status = get_user_meta(
            $user->ID,
            'wpm_profile_status',
            true
        );

        if ( $status !== 'approved' ) {

            wp_logout();

            return new WP_Error(
                'approval_pending',
                'Your profile is waiting for admin approval',
                array( 'status' => 403 )
            );

        }

        /*
        -----------------------------------------
        UPDATE LAST ACTIVITY
        -----------------------------------------
        */

        update_user_meta(
            $user->ID,
            'wpm_last_activity',
            time()
        );

        /*
        -----------------------------------------
        SUCCESS
        -----------------------------------------
        */

        return array(
            'message' => 'Login successful',

            'redirect' => home_url(),

            'user' => $this->get_auth_user_data( $user->ID ),
        );

    }

    /*
    -----------------------------------------
    LOGOUT USER
    -----------------------------------------
    */

    public function logout_user() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        wp_logout();

        wp_send_json_success(array(

            'message' => 'Logout successful',

            'redirect' => home_url()

        ));

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

}
