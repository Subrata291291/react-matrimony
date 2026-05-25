
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Admin_Approval {

    public function __construct() {

        /*
        -----------------------------------------
        ADMIN MENU
        -----------------------------------------
        */

        add_action(

            'admin_menu',

            array(
                $this,
                'admin_menu'
            )

        );

        /*
        -----------------------------------------
        APPROVE USER
        -----------------------------------------
        */

        add_action(

            'admin_post_wpm_approve_user',

            array(
                $this,
                'approve_user'
            )

        );

        /*
        -----------------------------------------
        REJECT USER
        -----------------------------------------
        */

        add_action(

            'admin_post_wpm_reject_user',

            array(
                $this,
                'reject_user'
            )

        );

    }

    /*
    =========================================
    ADMIN MENU
    =========================================
    */

    public function admin_menu() {

        add_menu_page(

            'Matrimony Users',

            'Matrimony Users',

            'manage_options',

            'wpm-users',

            array(
                $this,
                'admin_page'
            ),

            'dashicons-groups',

            26

        );

    }

    /*
    =========================================
    ADMIN PAGE
    =========================================
    */

    public function admin_page() {

        $users = get_users(array(

            'meta_key'   =>
            'wpm_profile_status',

            'meta_value' =>
            'pending'

        ));

        ?>

        <div class="wrap">

            <h1>

                Pending Matrimony Profiles

            </h1>

            <table class="widefat striped">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Username</th>

                        <th>Email</th>

                        <th>Gender</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if ( $users ) : ?>

                        <?php foreach ( $users as $user ) : ?>

                            <?php

                            $gender = get_user_meta(

                                $user->ID,

                                'wpm_gender',

                                true

                            );

                            ?>

                            <tr>

                                <td>

                                    <?php echo $user->ID; ?>

                                </td>

                                <td>

                                    <?php
                                    echo esc_html(
                                        $user->user_login
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo esc_html(
                                        $user->user_email
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo esc_html(
                                        ucfirst($gender)
                                    );
                                    ?>

                                </td>

                                <td>

                                    <!-- APPROVE -->

                                    <a
                                        class="button button-primary"
                                        href="<?php echo wp_nonce_url(

                                        admin_url(
                                            'admin-post.php?action=wpm_approve_user&user_id=' .
                                            $user->ID
                                        ),

                                        'wpm_approve_user_' . $user->ID

                                    ); ?>"
                                    >

                                        Approve

                                    </a>

                                    <!-- REJECT -->

                                    <a
                                        class="button button-secondary"
                                        href="<?php echo admin_url(

                                            'admin-post.php?action=wpm_reject_user&user_id=' .
                                            $user->ID

                                        ); ?>"
                                    >

                                        Reject

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="5">

                                No pending users found.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php

    }

    /*
    =========================================
    APPROVE USER
    =========================================
    */

    public function approve_user() {

        if (
            ! current_user_can(
                'manage_options'
            )
        ) {
            return;
        }

        $user_id = intval(
            $_GET['user_id']
        );

        check_admin_referer(
            'wpm_approve_user_' . $user_id
        );

        update_user_meta(

            $user_id,

            'wpm_profile_status',

            'approved'

        );

        $user = get_userdata( $user_id );

        if ( $user ) {

            $to = $user->user_email;

            $subject = 'Your Matrimony Profile Has Been Approved';

            $headers = array(
                'Content-Type: text/html; charset=UTF-8'
            );

            $message = '
            <div style="font-family:Arial,sans-serif;padding:20px;">
            
                <h2 style="color:#8e44ad;">
                    Profile Approved
                </h2>

                <p>
                    Hello <strong>' . esc_html( $user->display_name ) . '</strong>,
                </p>

                <p>
                    Congratulations! Your matrimony profile has been approved successfully.
                </p>

                <p>
                    You can now login and access your account.
                </p>

                <p>
                    <a href="' . home_url('/') . '" 
                    style="
                            background:#8e44ad;
                            color:#fff;
                            padding:12px 20px;
                            text-decoration:none;
                            border-radius:5px;
                            display:inline-block;
                    ">
                        Login Now
                    </a>
                </p>

                <p>
                    Thank you,<br>
                    ' . get_bloginfo('name') . '
                </p>

            </div>';

            wp_mail(
                $to,
                $subject,
                $message,
                $headers
            );
        }

        wp_redirect(

            admin_url(
                'admin.php?page=wpm-users'
            )

        );

        exit;
                        
    }

    /*
    =========================================
    REJECT USER
    =========================================
    */

    public function reject_user() {

        if (
            ! current_user_can(
                'manage_options'
            )
        ) {
            return;
        }

        $user_id = intval(
            $_GET['user_id']
        );

        $user = get_userdata( $user_id );

        if ( $user ) {

            $to = $user->user_email;

            $subject = 'Your Matrimony Profile Has Been Rejected';

            $headers = array(
                'Content-Type: text/html; charset=UTF-8'
            );

            $message = '
            <div style="font-family:Arial,sans-serif;padding:20px;">

                <h2 style="color:#e74c3c;">
                    Profile Rejected
                </h2>

                <p>
                    Hello <strong>' . esc_html( $user->display_name ) . '</strong>,
                </p>

                <p>
                    We are sorry to inform you that your matrimony profile has been rejected by the admin team.
                </p>

                <p>
                    This may happen due to incomplete information, invalid details, or profile verification issues.
                </p>

                <p>
                    You may register again using valid information.
                </p>

                <p>
                    Thank you,<br>
                    ' . get_bloginfo('name') . '
                </p>

            </div>';

            wp_mail(
                $to,
                $subject,
                $message,
                $headers
            );

        }

        wp_delete_user(
            $user_id
        );

        wp_redirect(

            admin_url(
                'admin.php?page=wpm-users'
            )

        );

        exit;

    }

}

