<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Ajax {

    public function __construct() {

        /*
        -----------------------------------------
        LOAD MORE MEMBERS
        -----------------------------------------
        */

        add_action(
            'wp_ajax_wpm_load_more_members',
            array( $this, 'load_more_members' )
        );

        add_action(
            'wp_ajax_nopriv_wpm_load_more_members',
            array( $this, 'load_more_members' )
        );

        add_action(
            'wp_ajax_wpm_get_unread_count',
            array(
                $this,
                'wpm_get_unread_count'
            )
        );

        add_action(
            'wp_ajax_wpm_get_live_connection',
            array(
                $this,
                'get_live_connection'
            )
        );

        add_action(
            'wp_ajax_nopriv_wpm_get_live_connection',
            array(
                $this,
                'get_live_connection'
            )
        );

        add_action(
            'wp_ajax_wpm_get_friend_request_count',
            array(
                $this,
                'get_friend_request_count'
            )
        );

        add_action(
            'wp_ajax_wpm_get_interest_requests',
            array(
                $this,
                'get_interest_requests'
            )
        );

        add_action(
            'wp_ajax_wpm_report_user',
            array( $this, 'report_user' )
        );

        /*
        -----------------------------------------
        PROFILE VIEW CHECK
        -----------------------------------------
        */

        add_action(
            'wp_ajax_nopriv_wpm_check_profile_access',
            array( $this, 'check_profile_access' )
        );

        add_action(
            'wp_ajax_wpm_check_profile_access',
            array( $this, 'check_profile_access' )
        );

    }
    public function wpm_get_unread_count() {

    global $wpdb;

    $current_user = get_current_user_id();

    $table =
        $wpdb->prefix .
        'wpm_chat_messages';

    $count = $wpdb->get_var(

        $wpdb->prepare(

            "
            SELECT COUNT(*)
            FROM $table
            WHERE receiver_id = %d
            AND is_read = 0
            ",

            $current_user

        )

    );

    wp_send_json(

        array(

            'count' => intval(
                $count
            )

        )

    );

}

    public function get_friend_request_count() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        $current_user = get_current_user_id();

        $requests = get_user_meta(
            $current_user,
            'wpm_interest_requests',
            true
        );

        $count = 0;

        if ( ! empty( $requests ) && is_array( $requests ) ) {
            $count = count( $requests );
        }

        wp_send_json_success(
            array(
                'count' => intval( $count )
            )
        );

    }

    public function get_interest_requests() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        $current_user_id = get_current_user_id();

        $interest_requests = get_user_meta(
            $current_user_id,
            'wpm_interest_requests',
            true
        );

        if ( empty( $interest_requests ) || ! is_array( $interest_requests ) ) {
            $html = '<div class="wpm-empty-state"><h4>No interest requests yet</h4></div>';
            wp_send_json_success( array( 'html' => $html ) );
        }

        ob_start();

        foreach ( $interest_requests as $sender_id ) {

            $sender = get_user_by( 'ID', $sender_id );

            if ( ! $sender ) {
                continue;
            }

            $sender_photo = wpm_get_profile_photo( $sender_id );
            $sender_name = wpm_get_user_full_name( $sender_id );
            $sender_age = wpm_get_user_age( $sender_id );
            $sender_location = wpm_get_user_location( $sender_id );
            $sender_profession = get_user_meta( $sender_id, 'wpm_profession', true );

            ?>
            <div class="wpm-interest-row">

                <div class="wpm-interest-user">

                    <img src="<?php echo esc_url( $sender_photo ); ?>" alt="">

                    <span>Member</span>

                </div>

                <div class="wpm-interest-content">

                    <h4><?php echo esc_html( $sender_name ); ?></h4>

                    <div class="wpm-interest-meta">

                        <span>Age: <strong><?php echo esc_html( $sender_age ); ?></strong></span>
                        <span>City: <strong><?php echo esc_html( $sender_location ); ?></strong></span>
                        <span>Profession: <strong><?php echo esc_html( $sender_profession ); ?></strong></span>

                    </div>

                    <p>Sent you an interest request</p>

                    <a href="<?php echo esc_url( site_url( '/profile/' . $sender->user_login ) ); ?>" class="wpm-view-btn">View Full Profile</a>

                </div>

                <div class="wpm-interest-actions">

                    <button class="wpm-accept-btn" data-user="<?php echo esc_attr( $sender_id ); ?>">Accept</button>

                    <button class="wpm-decline-btn" data-user="<?php echo esc_attr( $sender_id ); ?>">Decline</button>

                    <button class="wpm-delete-btn" data-user="<?php echo esc_attr( $sender_id ); ?>">Delete</button>

                </div>

            </div>
            <?php

        }

        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );

    }

    /*
    -----------------------------------------
    LOAD MORE MEMBERS
    -----------------------------------------
    */

    public function load_more_members() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        $page = isset( $_POST['page'] )
            ? intval( $_POST['page'] )
            : 1;

        $offset = ( $page - 1 ) *4;

        $gender = isset( $_POST['gender'] )
            ? strtolower( sanitize_text_field( $_POST['gender'] ) )
            : '';

        $seeking = isset( $_POST['seeking'] )
            ? strtolower( sanitize_text_field( $_POST['seeking'] ) )
            : '';

        $age_from = isset( $_POST['age_from'] )
            ? intval( $_POST['age_from'] )
            : 0;

        $age_to = isset( $_POST['age_to'] )
            ? intval( $_POST['age_to'] )
            : 0;

        $shown_ids = isset( $_POST['shown_ids'] ) && is_array( $_POST['shown_ids'] )
            ? array_map( 'intval', $_POST['shown_ids'] )
            : array();

        $shown_photo_ids = isset( $_POST['shown_photo_ids'] ) && is_array( $_POST['shown_photo_ids'] )
            ? array_map( 'intval', $_POST['shown_photo_ids'] )
            : array();

        $excluded_users = get_users(array(
            'role'   => 'administrator',
            'fields' => 'ID'
        ));

        $excluded_users = array_merge(
            $excluded_users,
            $shown_ids
        );

        /*
        -----------------------------------------
        GET USERS
        -----------------------------------------
        */

        $users = get_users(array(

            'number' => -1,

            'exclude' => array_unique( $excluded_users ),

            'orderby' => 'registered',

            'order' => 'DESC',

            'meta_query' => array(

                array(

                    'key' => 'wpm_profile_status',

                    'value' => 'approved'

                )

            )

        ));

        $filtered_users = array();
        $used_photo_ids = array_filter( $shown_photo_ids );

        foreach ( $users as $user ) {

            $user_id = $user->ID;
            $profile_photo_id = intval(
                get_user_meta(
                    $user_id,
                    'wpm_profile_photo',
                    true
                )
            );

            if (
                $profile_photo_id
                &&
                in_array( $profile_photo_id, $used_photo_ids, true )
            ) {
                continue;
            }

            $user_gender = strtolower(
                trim(
                    get_user_meta(
                        $user_id,
                        'wpm_gender',
                        true
                    )
                )
            );

            if ( $user_gender === 'woman' ) {
                $user_gender = 'women';
            }

            $target_gender = ! empty( $seeking )
                ? trim( $seeking )
                : '';

            if ( ! empty( $target_gender ) && $user_gender !== $target_gender ) {
                continue;
            }

            if ( ! empty( $age_from ) || ! empty( $age_to ) ) {

                $age = intval(
                    wpm_get_user_age(
                        $user_id
                    )
                );

                if ( empty( $age ) ) {
                    continue;
                }

                if ( ! empty( $age_from ) && $age < $age_from ) {
                    continue;
                }

                if ( ! empty( $age_to ) && $age > $age_to ) {
                    continue;
                }

            }

            $filtered_users[] = $user;

            if ( $profile_photo_id ) {
                $used_photo_ids[] = $profile_photo_id;
            }

        }

        $slice_offset = ! empty( $shown_ids )
            ? 0
            : $offset;

        $users = array_slice(
            $filtered_users,
            $slice_offset,
            4
        );

        /*
        -----------------------------------------
        EMPTY USERS
        -----------------------------------------
        */

        if ( empty( $users ) ) {

            wp_send_json_error(array(
                'message' => 'No more members'
            ));

        }

        ob_start();

        /*
        -----------------------------------------
        LOOP USERS
        -----------------------------------------
        */

        foreach ( $users as $user ) {

            $user_id = $user->ID;

            include WPM_PLUGIN_PATH .
            'templates/member-card-grid.php';

        }

        $html = ob_get_clean();

        wp_send_json_success(array(

            'html' => $html

        ));

    }

    /*
    -----------------------------------------
    CHECK PROFILE ACCESS
    -----------------------------------------
    */

    public function check_profile_access() {

        /*
        -----------------------------------------
        GUEST USER
        -----------------------------------------
        */

        if ( ! is_user_logged_in() ) {

            wp_send_json_error(array(

                'logged_in' => false,

                'message' => 'Login required'

            ));

        }

        /*
        -----------------------------------------
        APPROVED USER
        -----------------------------------------
        */

        $user_id = get_current_user_id();

        $status = get_user_meta(

            $user_id,

            'wpm_profile_status',

            true

        );

        if ( $status !== 'approved' ) {

            wp_send_json_error(array(

                'logged_in' => true,

                'approved' => false,

                'message' => 'Profile not approved yet'

            ));

        }

        /*
        -----------------------------------------
        ACCESS GRANTED
        -----------------------------------------
        */

        wp_send_json_success(array(

            'logged_in' => true,

            'approved' => true,

            'membership_active' => wpm_has_active_membership( $user_id ),

            'membership_notice' => wpm_get_membership_notice( $user_id )

        ));

    }

    public function get_live_connection() {

    global $wpdb;

    $table =
        $wpdb->prefix .
        'wpm_connections';

    $current_user_id = get_current_user_id();

    if ( $current_user_id ) {
        $connection = $wpdb->get_row(
            $wpdb->prepare(
                "
                SELECT c.*
                FROM $table c
                INNER JOIN $wpdb->users u1 ON c.user_one = u1.ID
                INNER JOIN $wpdb->users u2 ON c.user_two = u2.ID
                WHERE c.user_one = %d OR c.user_two = %d
                ORDER BY c.created_at DESC
                LIMIT 1
                ",
                $current_user_id,
                $current_user_id
            )
        );
    }

    if ( empty( $connection ) ) {
        $connection = $wpdb->get_row(
            "
            SELECT c.*
            FROM $table c
            INNER JOIN $wpdb->users u1 ON c.user_one = u1.ID
            INNER JOIN $wpdb->users u2 ON c.user_two = u2.ID
            ORDER BY c.created_at DESC
            LIMIT 1
            "
        );
    }

    $user1 = null;
    $user2 = null;

    if ( $connection ) {
        $user1 = get_user_by( 'ID', $connection->user_one );
        $user2 = get_user_by( 'ID', $connection->user_two );
    }

    if ( ! $user1 || ! $user2 ) {
        if ( $current_user_id ) {
            $accepted = get_user_meta( $current_user_id, 'wpm_accepted_interests', true );

            if ( is_array( $accepted ) && ! empty( $accepted ) ) {
                $partner_id = end( $accepted );
                $potential_user1 = get_user_by( 'ID', $current_user_id );
                $potential_user2 = get_user_by( 'ID', $partner_id );

                if ( $potential_user1 && $potential_user2 ) {
                    $user1 = $potential_user1;
                    $user2 = $potential_user2;
                    $connection = (object) array( 'created_at' => current_time( 'mysql' ) );
                }
            }
        }
    }

    if ( ! $user1 || ! $user2 ) {
        $accepted_rows = $wpdb->get_results(
            "
            SELECT user_id, meta_value
            FROM $wpdb->usermeta
            WHERE meta_key = 'wpm_accepted_interests'
            ORDER BY umeta_id DESC
            LIMIT 20
            "
        );

        if ( $accepted_rows ) {
            foreach ( $accepted_rows as $accepted_row ) {
                $accepted = maybe_unserialize( $accepted_row->meta_value );

                if ( is_array( $accepted ) && ! empty( $accepted ) ) {
                    $partner_id = end( $accepted );
                    $potential_user1 = get_user_by( 'ID', $accepted_row->user_id );
                    $potential_user2 = get_user_by( 'ID', $partner_id );

                    if ( $potential_user1 && $potential_user2 ) {
                        $user1 = $potential_user1;
                        $user2 = $potential_user2;
                        $connection = (object) array( 'created_at' => current_time( 'mysql' ) );
                        break;
                    }
                }
            }
        }
    }

    if ( ! $user1 || ! $user2 ) {
        wp_send_json_error();
    }

    $photo =
        wpm_get_profile_photo(
            $user1->ID
        );

    $location =
        wpm_get_user_location(
            $user1->ID
        );

    $created_at =
        isset( $connection->created_at )
        ? $connection->created_at
        : current_time( 'mysql' );

    $date = new DateTime( $created_at, new DateTimeZone( 'UTC' ) );
    $date->setTimezone( new DateTimeZone( 'Asia/Kolkata' ) );
    $created_at_formatted = $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

    wp_send_json_success(array(

        'name' =>
            $user1->display_name,

        'partner' =>
            $user2->display_name,

        'location' =>
            $location,

        'image' =>
            $photo,

        'time' =>
            $created_at_formatted

    ));

}
}

