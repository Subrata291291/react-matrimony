<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Admin_Members_Page {

    public function __construct() {

        add_action(
            'admin_menu',
            array( $this, 'add_admin_menu' )
        );

        add_action(
            'admin_post_wpm_update_member',
            array( $this, 'update_member' )
        );

    }

    /*
    -----------------------------------------
    ADMIN MENU
    -----------------------------------------
    */

    public function add_admin_menu() {

        add_menu_page(

            'Matrimony Members',

            'Matrimony Members',

            'manage_options',

            'wpm-members',

            array( $this, 'members_page' ),

            'dashicons-groups',

            6

        );

    }

    /*
    -----------------------------------------
    UPDATE MEMBER
    -----------------------------------------
    */

    public function update_member() {

        $user_id = intval(
            $_POST['user_id']
        );

        update_user_meta(
            $user_id,
            'wpm_gender',
            sanitize_text_field(
                $_POST['wpm_gender']
            )
        );

        update_user_meta(
            $user_id,
            'wpm_age',
            sanitize_text_field(
                $_POST['wpm_age']
            )
        );

        update_user_meta(
            $user_id,
            'wpm_mobile',
            sanitize_text_field(
                $_POST['wpm_phone']
            )
        );

        update_user_meta(
            $user_id,
            'wpm_location',
            sanitize_text_field(
                $_POST['wpm_location']
            )
        );

        update_user_meta(
            $user_id,
            'wpm_profile_status',
            sanitize_text_field(
                $_POST['wpm_status']
            )
        );

        wp_redirect(
            admin_url(
                'admin.php?page=wpm-members&updated=1'
            )
        );

        exit;

    }

    /*
    -----------------------------------------
    MEMBERS PAGE
    -----------------------------------------
    */

    public function members_page() {

        if ( isset( $_GET['edit_user'] ) ) {

            $this->edit_member_page(
                intval(
                    $_GET['edit_user']
                )
            );

            return;

        }

        $users = get_users();

        ?>

        <div class="wrap">

            <h1>Matrimony Members</h1>

            <table class="widefat striped">

                <thead>

                    <tr>

                        <th>Photo</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Gender</th>

                        <th>Phone</th>

                        <th>Status</th>

                        <th>Membership</th>

                        <th>Expires</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ( $users as $user ) :

                        $user_id = $user->ID;

                        $gender = get_user_meta(
                            $user_id,
                            'wpm_gender',
                            true
                        );

                        $status = get_user_meta(
                            $user_id,
                            'wpm_profile_status',
                            true
                        );

                        $phone = get_user_meta(
                            $user_id,
                            'wpm_mobile',
                            true
                        );

                        $membership = wpm_get_membership_details(
                            $user_id
                        );

                        /*
                        -----------------------------------------
                        PROFILE PHOTO
                        -----------------------------------------
                        */

                        $photo = '';

                        if ( function_exists( 'wpm_get_profile_photo' ) ) {

                            $photo = wpm_get_profile_photo(
                                $user_id
                            );

                        }

                        if ( empty( $photo ) ) {

                            $photo = get_avatar_url(
                                $user_id
                            );

                        }

                        ?>

                        <tr>

                            <td>

                                <img
                                    src="<?php echo esc_url( $photo ); ?>"
                                    width="70"
                                    height="70"
                                    style="
                                        width:70px;
                                        height:70px;
                                        object-fit:cover;
                                        border-radius:12px;
                                    "
                                >

                            </td>

                            <td>

                                <strong>

                                    <?php
                                    echo esc_html(
                                        $user->display_name
                                    );
                                    ?>

                                </strong>

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
                                    $gender
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo esc_html(
                                    $phone
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo esc_html(
                                    $status
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo esc_html(
                                    $membership['label']
                                );
                                ?>

                                <br>

                                <small>
                                    <?php
                                    echo esc_html(
                                        ucfirst( $membership['status'] )
                                    );
                                    ?>
                                </small>

                            </td>

                            <td>

                                <?php
                                echo ! empty( $membership['expires_at'] )
                                    ? esc_html(
                                        wp_date(
                                            'd M Y h:i A',
                                            $membership['expires_at']
                                        )
                                    )
                                    : 'N/A';
                                ?>

                            </td>

                            <td>

                                <a
                                    href="<?php echo admin_url( 'admin.php?page=wpm-members&edit_user=' . $user_id ); ?>"
                                    class="button button-primary"
                                >

                                    Edit

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <?php

    }

    /*
    -----------------------------------------
    EDIT MEMBER PAGE
    -----------------------------------------
    */

    public function edit_member_page(
        $user_id
    ) {

        $user = get_user_by(
            'ID',
            $user_id
        );

        $gender = get_user_meta(
            $user_id,
            'wpm_gender',
            true
        );

        $age = get_user_meta(
            $user_id,
            'wpm_age',
            true
        );

        $phone = get_user_meta(
            $user_id,
            'wpm_mobile',
            true
        );

        $location = get_user_meta(
            $user_id,
            'wpm_location',
            true
        );

        $status = get_user_meta(
            $user_id,
            'wpm_profile_status',
            true
        );

        $membership = wpm_get_membership_details(
            $user_id
        );

        /*
        -----------------------------------------
        PROFILE PHOTO
        -----------------------------------------
        */

        $photo = '';

        if ( function_exists( 'wpm_get_profile_photo' ) ) {

            $photo = wpm_get_profile_photo(
                $user_id
            );

        }

        if ( empty( $photo ) ) {

            $photo = get_avatar_url(
                $user_id
            );

        }

        ?>

        <div class="wrap">

            <h1>Edit Member</h1>

            <div
                style="
                    background:#fff;
                    padding:40px;
                    max-width:900px;
                    border-radius:12px;
                    margin-top:20px;
                "
            >

                <div
                    style="
                        text-align:center;
                        margin-bottom:30px;
                    "
                >

                    <img
                        src="<?php echo esc_url( $photo ); ?>"
                        width="140"
                        height="140"
                        style="
                            width:140px;
                            height:140px;
                            border-radius:50%;
                            object-fit:cover;
                            border:4px solid #eee;
                        "
                    >

                </div>

                <form
                    method="POST"
                    action="<?php echo admin_url( 'admin-post.php' ); ?>"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="wpm_update_member"
                    >

                    <input
                        type="hidden"
                        name="user_id"
                        value="<?php echo $user_id; ?>"
                    >

                    <table class="form-table">

                        <tr>

                            <th>Full Name</th>

                            <td>

                                <input
                                    type="text"
                                    value="<?php echo esc_attr( $user->display_name ); ?>"
                                    class="regular-text"
                                    disabled
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Email</th>

                            <td>

                                <input
                                    type="text"
                                    value="<?php echo esc_attr( $user->user_email ); ?>"
                                    class="regular-text"
                                    disabled
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Gender</th>

                            <td>

                                <input
                                    type="text"
                                    name="wpm_gender"
                                    value="<?php echo esc_attr( $gender ); ?>"
                                    class="regular-text"
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Age</th>

                            <td>

                                <input
                                    type="text"
                                    name="wpm_age"
                                    value="<?php echo esc_attr( $age ); ?>"
                                    class="regular-text"
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Phone</th>

                            <td>

                                <input
                                    type="text"
                                    name="wpm_phone"
                                    value="<?php echo esc_attr( $phone ); ?>"
                                    class="regular-text"
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Location</th>

                            <td>

                                <input
                                    type="text"
                                    name="wpm_location"
                                    value="<?php echo esc_attr( $location ); ?>"
                                    class="regular-text"
                                >

                            </td>

                        </tr>

                        <tr>

                            <th>Status</th>

                            <td>

                                <select name="wpm_status">

                                    <option
                                        value="approved"
                                        <?php selected( $status, 'approved' ); ?>
                                    >
                                        Approved
                                    </option>

                                    <option
                                        value="pending"
                                        <?php selected( $status, 'pending' ); ?>
                                    >
                                        Pending
                                    </option>

                                </select>

                            </td>

                        </tr>

                        <tr>

                            <th>Membership Status</th>

                            <td>

                                <strong>
                                    <?php
                                    echo esc_html(
                                        $membership['label']
                                    );
                                    ?>
                                </strong>

                                <p>
                                    <?php
                                    echo esc_html(
                                        ucfirst(
                                            $membership['status']
                                        )
                                    );
                                    ?>
                                </p>

                                <p>
                                    <?php
                                    echo ! empty( $membership['expires_at'] )
                                        ? esc_html(
                                            'Expires on ' . wp_date(
                                                'd M Y h:i A',
                                                $membership['expires_at']
                                            )
                                        )
                                        : esc_html__( 'No active expiry date.', 'wp-matrimony-pro' );
                                    ?>
                                </p>

                            </td>

                        </tr>

                    </table>

                    <p>

                        <button
                            class="button button-primary button-large"
                        >

                            Save Changes

                        </button>

                    </p>

                </form>

                <hr style="margin:30px 0;">

                <h2>Activate Membership</h2>

                <form
                    method="POST"
                    action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                >

                    <?php wp_nonce_field( 'wpm_admin_activate_membership' ); ?>

                    <input
                        type="hidden"
                        name="action"
                        value="wpm_admin_activate_membership"
                    >

                    <input
                        type="hidden"
                        name="user_id"
                        value="<?php echo esc_attr( $user_id ); ?>"
                    >

                    <p>

                        <select name="plan_key">

                            <?php foreach ( wpm_get_membership_plans() as $plan_key => $plan ) : ?>

                                <option value="<?php echo esc_attr( $plan_key ); ?>">
                                    <?php
                                    echo esc_html(
                                        $plan['label'] . ' - Rs ' . $plan['price']
                                    );
                                    ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button class="button button-secondary">
                            Activate Plan
                        </button>

                    </p>

                </form>

            </div>

        </div>

        <?php

    }

}

new WPM_Admin_Members_Page();
