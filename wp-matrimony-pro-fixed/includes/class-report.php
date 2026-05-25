<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Report {

    public function __construct() {

        add_action(
            'wp_ajax_wpm_report_user',
            array( $this, 'report_user' )
        );

    }

    public function report_user() {

        /*
        -----------------------------------------
        CHECK LOGIN
        -----------------------------------------
        */

        if ( ! is_user_logged_in() ) {

            wp_send_json_error(
                'Login required'
            );

        }

        /*
        -----------------------------------------
        CHECK DATA
        -----------------------------------------
        */

        if (
            empty($_POST['user_id'])
            ||
            empty($_POST['reason'])
        ) {

            wp_send_json_error(
                'Missing data'
            );

        }

        /*
        -----------------------------------------
        SANITIZE
        -----------------------------------------
        */

        $reported_user_id =
            isset($_POST['user_id'])
            ? intval($_POST['user_id'])
            : 0;

        $reason =
            isset($_POST['reason'])
            ? sanitize_textarea_field(
                wp_unslash($_POST['reason'])
            )
            : '';

        $reporter_id =
            get_current_user_id();

        /*
        -----------------------------------------
        GET USERS
        -----------------------------------------
        */

        $reported_user =
            get_userdata(
                $reported_user_id
            );

        $reporter =
            get_userdata(
                $reporter_id
            );

        if (
            ! $reported_user
            ||
            ! $reporter
        ) {

            wp_send_json_error(
                'Invalid user'
            );

        }

        /*
        -----------------------------------------
        EMAIL
        -----------------------------------------
        */

        $admin_email =
            get_option(
                'admin_email'
            );

        $subject =
            'Profile Report Submitted';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );

        $message = '

        <div style="font-family:Arial;padding:20px;">

            <h2 style="color:#ff5c7a;">
                Profile Report Alert
            </h2>

            <p>
                A profile has been reported.
            </p>

            <p>
                <strong>Reported User:</strong><br>
                '.esc_html(
                    $reported_user->display_name
                ).'
            </p>

            <p>
                <strong>Reported By:</strong><br>
                '.esc_html(
                    $reporter->display_name
                ).'
            </p>

            <p>
                <strong>Reason:</strong><br>
                '.esc_html(
                    $reason
                ).'
            </p>

        </div>';

        /*
        -----------------------------------------
        SEND MAIL
        -----------------------------------------
        */

        wp_mail(

            $admin_email,

            $subject,

            $message,

            $headers

        );

        /*
        -----------------------------------------
        SUCCESS
        -----------------------------------------
        */

        wp_send_json_success();

    }

}