
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Chat {

    public function __construct() {

        add_action(
            'init',
            array( $this, 'create_chat_table' )
        );

        add_action(
            'wp_ajax_wpm_send_message',
            array( $this, 'send_message' )
        );

        add_action(
            'wp_ajax_wpm_load_chat',
            array( $this, 'load_chat' )
        );

        add_action(
            'wp_ajax_wpm_edit_message',
            array( $this, 'edit_message' )
        );

        add_action(
            'wp_ajax_wpm_delete_message',
            array( $this, 'delete_message' )
        );

        add_action(
            'wp_ajax_wpm_mark_chat_read',
            array( $this, 'mark_chat_read' )
        );

        add_action(
            'wp_ajax_wpm_load_chat_users',
            array( $this, 'load_chat_users' )
        );

        add_action(
            'wp_ajax_wpm_get_unread_count',
            array( $this, 'get_unread_count' )
        );

        add_action(
            'wp_ajax_wpm_block_user',
            array( $this, 'block_user' )
        );

        add_action(
            'wp_ajax_wpm_unblock_user',
            array( $this, 'unblock_user' )
        );

        add_action(
            'wp_ajax_wpm_send_file',
            array(
                $this,
                'send_file'
            )
        );

        add_action(
            'rest_api_init',
            array( $this, 'register_rest_routes' )
        );
    }

    public function register_rest_routes() {

        register_rest_route(
            'wpm/v1',
            '/chat/users',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'rest_load_chat_users' ),
                'permission_callback' => array( $this, 'rest_require_login' ),
            )
        );

        register_rest_route(
            'wpm/v1',
            '/chat/messages/(?P<user_id>\d+)',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'rest_load_chat_messages' ),
                'permission_callback' => array( $this, 'rest_require_login' ),
            )
        );

        register_rest_route(
            'wpm/v1',
            '/chat/send',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_send_message' ),
                'permission_callback' => array( $this, 'rest_require_login' ),
            )
        );

    }

    /*
    =========================================
    CREATE TABLE
    =========================================
    */

    public function create_chat_table() {

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $charset_collate =
            $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (

            id BIGINT(20) NOT NULL AUTO_INCREMENT,

            sender_id BIGINT(20) NOT NULL,

            receiver_id BIGINT(20) NOT NULL,

            message LONGTEXT NOT NULL,

            is_file TINYINT(1) DEFAULT 0,

            is_read TINYINT(1) DEFAULT 0,

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY (id)

        ) $charset_collate;";

        require_once
            ABSPATH .
            'wp-admin/includes/upgrade.php';

        dbDelta( $sql );

    }

    /*
    =========================================
    SEND MESSAGE
    =========================================
    */

    public function send_message() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {

            wp_send_json_error();

        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {

            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to continue chatting.'
                )
            );

        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $sender_id =
            get_current_user_id();

        $receiver_id =
            intval(
                $_POST['receiver_id']
            );

        $message =
            sanitize_textarea_field(
                $_POST['message']
            );

        if ( empty( $message ) ) {

            wp_send_json_error();

        }

        $blocked_by_sender = get_user_meta(
            $sender_id,
            'wpm_blocked_user_' . $receiver_id,
            true
        );

        $blocked_by_receiver = get_user_meta(
            $receiver_id,
            'wpm_blocked_user_' . $sender_id,
            true
        );

        if ( $blocked_by_sender == 1 ) {

            wp_send_json_error(
                array(
                    'message' => 'You have blocked this user. Unblock them to send messages.'
                )
            );

        }

        if ( $blocked_by_receiver == 1 ) {

            wp_send_json_error(
                array(
                    'message' => 'You cannot send a message because this user has blocked you.'
                )
            );

        }

        $wpdb->insert(

            $table_name,

            array(

                'sender_id'   => $sender_id,

                'receiver_id' => $receiver_id,

                'message'     => $message,

                'is_read'     => 0

            ),

            array(

                '%d',

                '%d',

                '%s',

                '%d'

            )

        );

        /*
        =========================================
        SEND EMAIL ONLY FOR FIRST UNREAD MESSAGE
        =========================================
        */

        $unread_count = $wpdb->get_var(

            $wpdb->prepare(

                "

                SELECT COUNT(*)

                FROM $table_name

                WHERE

                sender_id = %d

                AND

                receiver_id = %d

                AND

                is_read = 0

                ",

                $sender_id,

                $receiver_id

            )

        );

        if ( intval( $unread_count ) === 1 ) {

            $receiver = get_userdata( $receiver_id );

            $sender = get_userdata( $sender_id );

            if ( $receiver && $sender ) {

                $subject = 'You Received a New Message';

                $headers = array(
                    'Content-Type: text/html; charset=UTF-8'
                );

                $email_message = '
                <div style="font-family:Arial,sans-serif;padding:20px;">

                    <h2 style="color:#6c5ce7;">
                        New Message Received
                    </h2>

                    <p>
                        Hello <strong>' . esc_html( $receiver->display_name ) . '</strong>,
                    </p>

                    <p>
                        You received a new message from
                        <strong>' . esc_html( $sender->display_name ) . '</strong>.
                    </p>

                    <p>
                        Login to your account to view and reply.
                    </p>

                    <p>
                        <a href="' . home_url('/profile/' . $receiver->user_login . '/') . '"
                        style="
                                background:#6c5ce7;
                                color:#ffffff;
                                padding:12px 20px;
                                text-decoration:none;
                                border-radius:5px;
                                display:inline-block;
                        ">
                            Open Inbox
                        </a>
                    </p>

                    <p>
                        Thank you,<br>
                        ' . get_bloginfo('name') . '
                    </p>

                </div>';

                wp_mail(

                    $receiver->user_email,

                    $subject,

                    $email_message,

                    $headers

                );

            }

        }

        wp_send_json_success();

    }

    public function rest_load_chat_users() {

        $current_user =
            get_current_user_id();

        return rest_ensure_response(
            array(
                'users' => $this->get_chat_users_payload(
                    $current_user
                ),
            )
        );

    }

    public function rest_load_chat_messages( WP_REST_Request $request ) {

        $current_user =
            get_current_user_id();

        if ( ! wpm_has_active_membership( $current_user ) ) {

            return new WP_REST_Response(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to view chats.',
                ),
                403
            );

        }

        $other_user = intval(
            $request['user_id']
        );

        return rest_ensure_response(
            array(
                'messages' => $this->get_chat_messages_payload(
                    $current_user,
                    $other_user,
                    true
                ),
            )
        );

    }

    public function rest_send_message( WP_REST_Request $request ) {

        $current_user =
            get_current_user_id();

        if ( ! wpm_has_active_membership( $current_user ) ) {

            return new WP_REST_Response(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to continue chatting.',
                ),
                403
            );

        }

        $payload = $request->get_json_params();

        if ( empty( $payload ) ) {
            $payload = $request->get_params();
        }

        $receiver_id = intval(
            $payload['receiver_id'] ?? 0
        );

        $message = sanitize_textarea_field(
            $payload['message'] ?? ''
        );

        if ( ! $receiver_id || empty( $message ) ) {

            return new WP_REST_Response(
                array(
                    'message' => 'Receiver and message are required.',
                ),
                400
            );

        }

        $result = $this->insert_chat_message(
            $current_user,
            $receiver_id,
            $message
        );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response(
                array(
                    'message' => $result->get_error_message(),
                ),
                400
            );
        }

        return rest_ensure_response(
            array(
                'success' => true,
                'message' => 'Message sent successfully.',
                'chat_message' => $result,
            )
        );

    }

    /*
    =========================================
    LOAD CHAT
    =========================================
    */

    public function load_chat() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {

            wp_send_json_error();

        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {

            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to view chats.'
                )
            );

        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $current_user =
            get_current_user_id();

        $other_user =
            intval(
                $_POST['user_id']
            );

        /*
        =========================================
        MARK READ ONLY WHEN OPENED
        =========================================
        */

        $mark_read =
            isset($_POST['mark_read'])
            ? intval($_POST['mark_read'])
            : 0;

        if($mark_read === 1){

            $wpdb->update(

                $table_name,

                array(
                    'is_read' => 1
                ),

                array(
                    'sender_id'   => $other_user,
                    'receiver_id' => $current_user,
                    'is_read'     => 0
                ),

                array('%d'),

                array('%d', '%d', '%d')

            );

        }

        $messages =
            $wpdb->get_results(

                $wpdb->prepare(

                    "

                    SELECT *

                    FROM $table_name

                    WHERE

                    (
                        sender_id = %d
                        AND
                        receiver_id = %d
                    )

                    OR

                    (
                        sender_id = %d
                        AND
                        receiver_id = %d
                    )

                    ORDER BY id ASC

                    ",

                    $current_user,
                    $other_user,
                    $other_user,
                    $current_user

                )

            );

        if ( $messages ) {

            foreach ( $messages as $message ) {

                $class =
                    $message->sender_id ==
                    $current_user
                    ? 'sent'
                    : 'received';

                ?>

                <div
                    class="wpm-chat-message <?php echo esc_attr( $class ); ?>"
                    data-message-id="<?php echo esc_attr( $message->id ); ?>"
                >

                    <div class="wpm-chat-bubble">

                        <?php if ( $class === 'sent' ) : ?>

                            <div class="wpm-message-actions">

                                <?php if ( empty( $message->is_file ) ) : ?>

                                    <button
                                        type="button"
                                        class="wpm-message-edit-btn"
                                        data-message="<?php echo esc_attr( $message->message ); ?>"
                                        aria-label="<?php esc_attr_e( 'Edit message', 'wp-matrimony-pro' ); ?>"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="wpm-message-delete-btn"
                                    aria-label="<?php esc_attr_e( 'Delete message', 'wp-matrimony-pro' ); ?>"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </div>

                        <?php endif; ?>

                       <div class="wpm-chat-text">

    <?php if(
        !empty($message->is_file)
        &&
        $message->is_file == 1
    ) : ?>

        <?php

        $file_url =
            esc_url(
                $message->message
            );

        $file_path =
            wp_parse_url(
                $message->message,
                PHP_URL_PATH
            );

        $file_name =
            $file_path
            ? wp_basename( $file_path )
            : __( 'Download File', 'wp-matrimony-pro' );

        $ext =
            strtolower(
                pathinfo(
                    $file_name,
                    PATHINFO_EXTENSION
                )
            );

        $image_width = 0;
        $image_height = 0;

        if (
            in_array(
                $ext,
                array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ),
                true
            )
        ) {

            $uploads =
                wp_upload_dir();

            if (
                ! empty( $uploads['baseurl'] )
                &&
                ! empty( $uploads['basedir'] )
                &&
                strpos(
                    $message->message,
                    $uploads['baseurl']
                ) === 0
            ) {

                $local_image_path =
                    str_replace(
                        $uploads['baseurl'],
                        $uploads['basedir'],
                        $message->message
                    );

                if (
                    file_exists(
                        $local_image_path
                    )
                ) {

                    $image_size =
                        @getimagesize(
                            $local_image_path
                        );

                    if ( $image_size ) {

                        $image_width =
                            (int) $image_size[0];

                        $image_height =
                            (int) $image_size[1];

                    }

                }

            }

        }

        ?>

        <?php if(
            in_array(
                $ext,
                ['jpg','jpeg','png','gif','webp']
            )
        ) : ?>

            <img
                src="<?php echo $file_url; ?>"
                alt="<?php echo esc_attr( $file_name ); ?>"
                class="wpm-chat-image"
                <?php if ( $image_width > 0 && $image_height > 0 ) : ?>
                    width="<?php echo esc_attr( $image_width ); ?>"
                    height="<?php echo esc_attr( $image_height ); ?>"
                <?php endif; ?>
                loading="eager"
                decoding="sync"
            >

        <?php else : ?>

            <a
                href="<?php echo $file_url; ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="wpm-chat-file"
            >

                <i class="fa-solid fa-file"></i>

                <?php echo esc_html( $file_name ); ?>

            </a>

        <?php endif; ?>

    <?php else : ?>

        <?php echo nl2br(
            esc_html(
                $message->message
            )
        ); ?>

    <?php endif; ?>

</div>

                        <div class="wpm-chat-time">

                            <?php

                            echo esc_html(
                                get_date_from_gmt(
                                    $message->created_at,
                                    'h:i A'
                                )
                            );

                            ?>

                            <?php if ( $class === 'sent' ) : ?>

                                <span class="wpm-message-status">

                                    <?php if ( $message->is_read ) : ?>

                                        ✓✓ Seen

                                    <?php else : ?>

                                        ✓ Sent

                                    <?php endif; ?>

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <?php

            }

        }

        wp_die();

    }

    /*
    =========================================
    EDIT MESSAGE
    =========================================
    */

    public function edit_message() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to edit messages.'
                )
            );
        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $message_id =
            intval(
                $_POST['message_id']
            );

        $message =
            sanitize_textarea_field(
                $_POST['message']
            );

        if ( empty( $message ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Message cannot be empty.'
                )
            );
        }

        $updated = $wpdb->update(

            $table_name,

            array(
                'message' => $message
            ),

            array(
                'id'        => $message_id,
                'sender_id' => get_current_user_id(),
                'is_file'   => 0
            ),

            array(
                '%s'
            ),

            array(
                '%d',
                '%d',
                '%d'
            )

        );

        if ( false === $updated ) {
            wp_send_json_error(
                array(
                    'message' => 'Unable to update message.'
                )
            );
        }

        wp_send_json_success();

    }

    /*
    =========================================
    DELETE MESSAGE
    =========================================
    */

    public function delete_message() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to delete messages.'
                )
            );
        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $message_id =
            intval(
                $_POST['message_id']
            );

        $deleted = $wpdb->delete(

            $table_name,

            array(
                'id'        => $message_id,
                'sender_id' => get_current_user_id()
            ),

            array(
                '%d',
                '%d'
            )

        );

        if ( false === $deleted ) {
            wp_send_json_error(
                array(
                    'message' => 'Unable to delete message.'
                )
            );
        }

        wp_send_json_success();

    }

    /*
    =========================================
    MARK CHAT READ
    =========================================
    */

    public function mark_chat_read() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {

            wp_send_json_error();

        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {

            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to use chat.'
                )
            );

        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $current_user =
            get_current_user_id();

        $other_user =
            intval(
                $_POST['user_id']
            );

        $updated = $wpdb->update(

            $table_name,

            array(
                'is_read' => 1
            ),

            array(
                'sender_id'   => $other_user,
                'receiver_id' => $current_user,
                'is_read'     => 0
            ),

            array('%d'),

            array('%d', '%d', '%d')

        );

        wp_send_json_success(array(
            'updated' => $updated
        ));

    }

    /*
    =========================================
    LOAD CHAT USERS
    =========================================
    */

    public function load_chat_users() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {

            wp_send_json_error();

        }

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $current_user =
            get_current_user_id();

        $users =
            $wpdb->get_results(

                $wpdb->prepare(

                    "

                    SELECT DISTINCT

                    IF(
                        sender_id = %d,
                        receiver_id,
                        sender_id
                    ) AS user_id

                    FROM $table_name

                    WHERE
                    sender_id = %d
                    OR
                    receiver_id = %d

                    ",

                    $current_user,
                    $current_user,
                    $current_user

                )

            );

        if ( $users ) {

            foreach ( $users as $chat_user ) {

                $user = get_userdata(
                    $chat_user->user_id
                );

                if ( ! $user ) {
                    continue;
                }

                if ( in_array( 'administrator', (array) $user->roles ) ) {
                    continue;
                }

                $photo = wpm_get_profile_photo(
                    $user->ID
                );

                $is_online = wpm_is_user_online(
                    $user->ID
                );

                $last_activity =
                    get_user_meta(
                        $user->ID,
                        'wpm_last_activity',
                        true
                    );

                $status_text =
                    $is_online
                    ? __( 'Online', 'wp-matrimony-pro' )
                    : (
                        ! empty( $last_activity )
                        ? sprintf(
                            /* translators: %s: relative time */
                            __( 'Last seen %s ago', 'wp-matrimony-pro' ),
                            human_time_diff(
                                (int) $last_activity,
                                time()
                            )
                        )
                        : __( 'Last seen recently', 'wp-matrimony-pro' )
                    );

                $last_message = $wpdb->get_row(

                    $wpdb->prepare(

                        "

                        SELECT *

                        FROM $table_name

                        WHERE

                        (
                            sender_id = %d
                            AND
                            receiver_id = %d
                        )

                        OR

                        (
                            sender_id = %d
                            AND
                            receiver_id = %d
                        )

                        ORDER BY id DESC

                        LIMIT 1

                        ",

                        $current_user,
                        $user->ID,
                        $user->ID,
                        $current_user

                    )

                );

                $message_preview = '';

                if($last_message){

                    $message_preview = wp_trim_words(
                        $last_message->message,
                        6,
                        '...'
                    );

                }

                $unread = $wpdb->get_var(

                    $wpdb->prepare(

                        "

                        SELECT COUNT(*)

                        FROM $table_name

                        WHERE

                        sender_id = %d

                        AND

                        receiver_id = %d

                        AND

                        is_read = 0

                        ",

                        $user->ID,
                        $current_user

                    )

                );

                ?>

                <div
                    class="wpm-chat-user"
                    data-user-id="<?php echo esc_attr( $user->ID ); ?>"
                    data-status="<?php echo esc_attr( $status_text ); ?>"
                >

                    <div class="wpm-chat-user-left">

                        <div class="wpm-chat-user-image">

                            <img
                                src="<?php echo esc_url( $photo ); ?>"
                                alt=""
                            >

                            <?php if ( $is_online ) : ?>

                                <span class="wpm-online-dot"></span>

                            <?php endif; ?>

                        </div>

                        <div class="wpm-chat-user-content">

                            <h5>
                                <?php echo esc_html( $user->display_name ); ?>
                            </h5>

                            <p>
                                <?php echo esc_html( $message_preview ); ?>
                            </p>

                        </div>

                    </div>

                    <div class="wpm-chat-user-right">

                        <?php if($unread > 0): ?>

                            <span class="wpm-unread-count">
                                <?php echo intval($unread); ?>
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

                <?php

            }

        }

        wp_die();

    }

    /*
    =========================================
    UNREAD COUNT
    =========================================
    */

    public function get_unread_count() {

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $current_user =
            get_current_user_id();

        /*
        TOTAL UNREAD
        */

        $count =
            $wpdb->get_var(

                $wpdb->prepare(

                    "

                    SELECT COUNT(*)

                    FROM $table_name

                    WHERE

                    receiver_id = %d

                    AND

                    is_read = 0

                    ",

                    $current_user

                )

            );

        /*
        LAST USER WHO SENT MESSAGE
        */

        $last_sender =
            $wpdb->get_var(

                $wpdb->prepare(

                    "

                    SELECT sender_id

                    FROM $table_name

                    WHERE

                    receiver_id = %d

                    AND

                    is_read = 0

                    ORDER BY id DESC

                    LIMIT 1

                    ",

                    $current_user

                )

            );

        wp_send_json(

            array(

                'count' => intval($count),

                'last_sender' => intval($last_sender)

            )

        );

    }

    public function rest_require_login() {

        return is_user_logged_in();

    }

    private function get_chat_users_payload( $current_user ) {

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $users =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT DISTINCT
                    IF(
                        sender_id = %d,
                        receiver_id,
                        sender_id
                    ) AS user_id
                    FROM $table_name
                    WHERE
                    sender_id = %d
                    OR
                    receiver_id = %d
                    ",
                    $current_user,
                    $current_user,
                    $current_user
                )
            );

        $payload = array();

        foreach ( (array) $users as $chat_user ) {

            $user = get_userdata(
                $chat_user->user_id
            );

            if ( ! $user ) {
                continue;
            }

            if ( in_array( 'administrator', (array) $user->roles, true ) ) {
                continue;
            }

            $photo = wpm_get_profile_photo(
                $user->ID
            );

            $is_online = wpm_is_user_online(
                $user->ID
            );

            $last_activity =
                get_user_meta(
                    $user->ID,
                    'wpm_last_activity',
                    true
                );

            $status_text =
                $is_online
                ? __( 'Online', 'wp-matrimony-pro' )
                : (
                    ! empty( $last_activity )
                    ? sprintf(
                        __( 'Last seen %s ago', 'wp-matrimony-pro' ),
                        human_time_diff(
                            (int) $last_activity,
                            time()
                        )
                    )
                    : __( 'Last seen recently', 'wp-matrimony-pro' )
                );

            $last_message = $wpdb->get_row(
                $wpdb->prepare(
                    "
                    SELECT *
                    FROM $table_name
                    WHERE
                    (
                        sender_id = %d
                        AND receiver_id = %d
                    )
                    OR
                    (
                        sender_id = %d
                        AND receiver_id = %d
                    )
                    ORDER BY id DESC
                    LIMIT 1
                    ",
                    $current_user,
                    $user->ID,
                    $user->ID,
                    $current_user
                )
            );

            $message_preview = '';

            if ( $last_message ) {
                $message_preview =
                    ! empty( $last_message->is_file )
                    ? __( 'Shared a file', 'wp-matrimony-pro' )
                    : wp_trim_words(
                        wp_strip_all_tags( $last_message->message ),
                        6,
                        '...'
                    );
            }

            $unread = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "
                    SELECT COUNT(*)
                    FROM $table_name
                    WHERE
                    sender_id = %d
                    AND receiver_id = %d
                    AND is_read = 0
                    ",
                    $user->ID,
                    $current_user
                )
            );

            $payload[] = array(
                'id'          => (int) $user->ID,
                'name'        => $user->display_name,
                'image'       => $photo,
                'status'      => $status_text,
                'message'     => $message_preview,
                'unreadCount' => $unread,
                'isOnline'    => (bool) $is_online,
            );

        }

        usort(
            $payload,
            function ( $a, $b ) {
                return $b['unreadCount'] <=> $a['unreadCount'];
            }
        );

        return $payload;

    }

    private function get_chat_messages_payload( $current_user, $other_user, $mark_read = false ) {

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        if ( $mark_read ) {
            $wpdb->update(
                $table_name,
                array(
                    'is_read' => 1,
                ),
                array(
                    'sender_id'   => $other_user,
                    'receiver_id' => $current_user,
                    'is_read'     => 0,
                ),
                array( '%d' ),
                array( '%d', '%d', '%d' )
            );
        }

        $messages =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT *
                    FROM $table_name
                    WHERE
                    (
                        sender_id = %d
                        AND receiver_id = %d
                    )
                    OR
                    (
                        sender_id = %d
                        AND receiver_id = %d
                    )
                    ORDER BY id ASC
                    ",
                    $current_user,
                    $other_user,
                    $other_user,
                    $current_user
                )
            );

        return array_map(
            function ( $message ) use ( $current_user ) {
                return array(
                    'id'         => (int) $message->id,
                    'type'       => (int) $message->sender_id === (int) $current_user
                        ? 'me'
                        : 'them',
                    'text'       => $message->message,
                    'time'       => mysql2date(
                        'M j, Y g:i A',
                        $message->created_at
                    ),
                    'senderId'   => (int) $message->sender_id,
                    'receiverId' => (int) $message->receiver_id,
                    'isFile'     => ! empty( $message->is_file ),
                );
            },
            (array) $messages
        );

    }

    private function insert_chat_message( $sender_id, $receiver_id, $message ) {

        global $wpdb;

        $table_name =
            $wpdb->prefix .
            'wpm_messages';

        $blocked_by_sender = get_user_meta(
            $sender_id,
            'wpm_blocked_user_' . $receiver_id,
            true
        );

        $blocked_by_receiver = get_user_meta(
            $receiver_id,
            'wpm_blocked_user_' . $sender_id,
            true
        );

        if ( $blocked_by_sender == 1 ) {
            return new WP_Error(
                'blocked_by_sender',
                'You have blocked this user. Unblock them to send messages.'
            );
        }

        if ( $blocked_by_receiver == 1 ) {
            return new WP_Error(
                'blocked_by_receiver',
                'You cannot send a message because this user has blocked you.'
            );
        }

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'sender_id'   => $sender_id,
                'receiver_id' => $receiver_id,
                'message'     => $message,
                'is_read'     => 0,
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%d',
            )
        );

        if ( ! $inserted ) {
            return new WP_Error(
                'message_insert_failed',
                'Message could not be saved.'
            );
        }

        return array(
            'id'         => (int) $wpdb->insert_id,
            'type'       => 'me',
            'text'       => $message,
            'time'       => current_time( 'mysql' ),
            'senderId'   => (int) $sender_id,
            'receiverId' => (int) $receiver_id,
            'isFile'     => false,
        );

    }

    /*
    =========================================
    BLOCK USER
    =========================================
    */

    public function block_user() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {
            wp_send_json_error(
                array(
                    'message' => 'Your membership is inactive. Please activate a plan to use chat.'
                )
            );
        }

        $current_user = get_current_user_id();

        $blocked_user = intval(
            $_POST['user_id']
        );

        update_user_meta(
            $current_user,
            'wpm_blocked_user_' . $blocked_user,
            1
        );

        wp_send_json_success();

    }

    /*
    =========================================
    UNBLOCK USER
    =========================================
    */

    public function unblock_user() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        $current_user = get_current_user_id();

        $blocked_user = intval(
            $_POST['user_id']
        );

        delete_user_meta(
            $current_user,
            'wpm_blocked_user_' . $blocked_user
        );

        wp_send_json_success();

    }

    /*
    =========================================
    ATTACH FILE
    =========================================
    */

    public function send_file() {

        require_once
            ABSPATH .
            'wp-admin/includes/file.php';

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        /*
        LOGIN CHECK
        */

        if ( ! is_user_logged_in() ) {

            wp_send_json_error(
                'User not logged in'
            );

        }

        if ( ! wpm_has_active_membership( get_current_user_id() ) ) {

            wp_send_json_error(
                'Your membership is inactive. Please activate a plan to send files.'
            );

        }

        /*
        FILE CHECK
        */

        if(
            empty($_FILES['chat_file'])
        ){

            wp_send_json_error(
                'No file received'
            );

        }

        /*
        UPLOAD FILE
        */

        $uploaded =
            wp_handle_upload(

                $_FILES['chat_file'],

                array(
                    'test_form' => false
                )

            );

        /*
        UPLOAD FAILED
        */

        if(
            isset($uploaded['error'])
        ){

            wp_send_json_error(
                $uploaded['error']
            );

        }

        /*
        SUCCESS
        */

        if(
            isset($uploaded['url'])
        ){

            global $wpdb;

            $table =
                $wpdb->prefix .
                'wpm_messages';

            /*
            INSERT FILE MESSAGE
            */

            $inserted =
                $wpdb->insert(

                    $table,

                    array(

                        'sender_id' =>

                            get_current_user_id(),

                        'receiver_id' =>

                            intval(
                                $_POST['receiver_id']
                            ),

                        'message' =>

                            $uploaded['url'],

                        'is_file' => 1,

                        'is_read' => 0

                    ),

                    array(

                        '%d',

                        '%d',

                        '%s',

                        '%d'

                    )

                );

            /*
            INSERT FAILED
            */

            if( ! $inserted ){

                wp_send_json_error(
                    $wpdb->last_error
                );

            }

            /*
            SUCCESS
            */

            wp_send_json_success(

                array(

                    'file_url' =>
                        $uploaded['url']

                )

            );

        }

        /*
        UNKNOWN ERROR
        */

        wp_send_json_error(
            'File upload failed'
        );

    }
};
