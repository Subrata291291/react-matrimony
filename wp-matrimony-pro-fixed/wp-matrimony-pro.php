<?php
/**
 * Plugin Name: WP Matrimony Pro
 * Plugin URI: https://subratahaldar.netlify.app/
 * Description: Premium Matrimony Plugin for WordPress
 * Version: 1.0.0
 * Author: Subrata
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
=========================================
DEFINE CONSTANTS
=========================================
*/

define( 'WPM_VERSION', '1.0.0' );

define( 'WPM_REWRITE_VERSION', '1.2.0' );

define(
    'WPM_PLUGIN_PATH',
    plugin_dir_path( __FILE__ )
);

define(
    'WPM_PLUGIN_URL',
    plugin_dir_url( __FILE__ )
);

/*
=========================================
LOAD HELPERS
=========================================
*/

require_once
WPM_PLUGIN_PATH .
'includes/helpers.php';

/*
=========================================
LOAD CLASSES
=========================================
*/

require_once
WPM_PLUGIN_PATH .
'includes/class-auth.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-register.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-profile.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-members.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-membership.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-search.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-online-users.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-admin-approval.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-admin-members-page.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-chat.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-ajax.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-rewrites.php';

require_once
WPM_PLUGIN_PATH .
'includes/class-report.php';

/*
=========================================
ACTIVATE PLUGIN
=========================================
*/

register_activation_hook(

    __FILE__,

    'wpm_activate_plugin'

);

function wpm_activate_plugin() {

    /*
    FLUSH RULES
    */

    flush_rewrite_rules();

    /*
    CREATE CONNECTION TABLE
    */

    global $wpdb;

    $table_name =

        $wpdb->prefix .
        'wpm_connections';

    $charset_collate =

        $wpdb->get_charset_collate();

    require_once(
        ABSPATH .
        'wp-admin/includes/upgrade.php'
    );

    $sql = "

    CREATE TABLE $table_name (

        id BIGINT(20)
        NOT NULL AUTO_INCREMENT,

        user_one BIGINT(20)
        NOT NULL,

        user_two BIGINT(20)
        NOT NULL,

        created_at DATETIME
        DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (id)

    ) $charset_collate;

    ";

    dbDelta($sql);

}

/*
=========================================
DEACTIVATE PLUGIN
=========================================
*/

register_deactivation_hook(

    __FILE__,

    'wpm_deactivate_plugin'

);

function wpm_deactivate_plugin() {

    flush_rewrite_rules();

}

add_action(
    'init',
    'wpm_maybe_flush_rewrite_rules',
    99
);

function wpm_maybe_flush_rewrite_rules() {

    $stored_version = get_option(
        'wpm_rewrite_version'
    );

    if ( $stored_version === WPM_REWRITE_VERSION ) {
        return;
    }

    flush_rewrite_rules(
        false
    );

    update_option(
        'wpm_rewrite_version',
        WPM_REWRITE_VERSION
    );

}

/*
=========================================
LOAD ASSETS
=========================================
*/

add_action(

    'wp_enqueue_scripts',

    'wpm_load_assets'

);

function wpm_load_assets() {

    /*
    -----------------------------------------
    CSS
    -----------------------------------------
    */

    wp_enqueue_style(

        'wpm-frontend',

        WPM_PLUGIN_URL .
        'assets/css/frontend.css',

        array(),

        WPM_VERSION

    );

    wp_enqueue_style(

        'wpm-profile',

        WPM_PLUGIN_URL .
        'assets/css/profile.css',

        array(),

        WPM_VERSION

    );

    $wpm_chat_css_version =
        file_exists(
            WPM_PLUGIN_PATH .
            'assets/css/chat.css'
        )
        ? filemtime(
            WPM_PLUGIN_PATH .
            'assets/css/chat.css'
        )
        : WPM_VERSION;

    wp_enqueue_style(

        'wpm-chat',

        WPM_PLUGIN_URL .
        'assets/css/chat.css',

        array(),

        $wpm_chat_css_version

    );

    wp_enqueue_style(

        'wpm-auth',

        WPM_PLUGIN_URL .
        'assets/css/auth.css',

        array(),

        WPM_VERSION

    );

    /*
    -----------------------------------------
    SWEET ALERT CSS
    -----------------------------------------
    */

    wp_enqueue_style(

        'sweetalert2-css',

        'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',

        array(),

        null

    );

    /*
    -----------------------------------------
    JS
    -----------------------------------------
    */

    wp_enqueue_script(

        'wpm-auth',

        WPM_PLUGIN_URL .
        'assets/js/auth.js',

        array( 'jquery' ),

        WPM_VERSION,

        true

    );

    wp_enqueue_script(

        'wpm-members',

        WPM_PLUGIN_URL .
        'assets/js/members.js',

        array( 'jquery' ),

        WPM_VERSION,

        true

    );

    wp_enqueue_script(

        'wpm-search',

        WPM_PLUGIN_URL .
        'assets/js/search.js',

        array( 'jquery' ),

        WPM_VERSION,

        true

    );

    wp_enqueue_script(

        'wpm-profile',

        WPM_PLUGIN_URL .
        'assets/js/profile.js',

        array( 'jquery' ),

        WPM_VERSION,

        true

    );

    $wpm_chat_js_version =
        file_exists(
            WPM_PLUGIN_PATH .
            'assets/js/chat.js'
        )
        ? filemtime(
            WPM_PLUGIN_PATH .
            'assets/js/chat.js'
        )
        : WPM_VERSION;

    wp_enqueue_script(

        'wpm-chat',

        WPM_PLUGIN_URL .
        'assets/js/chat.js',

        array( 'jquery' ),

        $wpm_chat_js_version,

        true

    );

    wp_enqueue_script(

        'wpm-membership-checkout',

        WPM_PLUGIN_URL .
        'assets/js/membership-checkout.js',

        array( 'jquery' ),

        WPM_VERSION,

        true

    );

    if ( function_exists( 'is_page' ) || get_query_var( 'wpm_membership_checkout' ) ) {

        wp_enqueue_script(
            'razorpay-checkout',
            'https://checkout.razorpay.com/v1/checkout.js',
            array(),
            null,
            true
        );

    }

    /*
    -----------------------------------------
    SWEET ALERT JS
    -----------------------------------------
    */

    wp_enqueue_script(

        'sweetalert2',

        'https://cdn.jsdelivr.net/npm/sweetalert2@11',

        array(),

        null,

        true

    );

    /*
    -----------------------------------------
    LOCALIZE AJAX
    -----------------------------------------
    */

    $wpm_ajax = array(

        'ajax_url' => admin_url(
            'admin-ajax.php'
        ),

        'nonce' => wp_create_nonce(
            'wpm_nonce'
        )

    );

    /*
    AUTH
    */

    wp_localize_script(

        'wpm-auth',

        'wpm_ajax',

        $wpm_ajax

    );

    /*
    CHAT
    */

    wp_localize_script(

        'wpm-chat',

        'wpm_ajax',

        $wpm_ajax

    );

    /*
    MEMBERS
    */

    wp_localize_script(

        'wpm-members',

        'wpm_ajax',

        $wpm_ajax

    );

    /*
    SEARCH
    */

    wp_localize_script(

        'wpm-search',

        'wpm_ajax',

        $wpm_ajax

    );

    /*
    PROFILE
    */

    wp_localize_script(

        'wpm-profile',

        'wpm_ajax',

        $wpm_ajax

    );

    wp_localize_script(

        'wpm-membership-checkout',

        'wpm_ajax',

        $wpm_ajax

    );

}

/*
=========================================
INITIALIZE CLASSES
=========================================
*/

add_action(

    'plugins_loaded',

    'wpm_init_plugin'

);

function wpm_init_plugin() {

    new WPM_Auth();

    new WPM_Register();

    new WPM_Profile();

    new WPM_Members();

    new WPM_Membership();

    new WPM_Search();

    new WPM_Online_Users();

    new WPM_Admin_Approval();

    new WPM_Chat();

    new WPM_Ajax();

    new WPM_Rewrites();

    new WPM_Report();

}

/*
-----------------------------------------
HIDE ADMIN BAR FOR USERS
-----------------------------------------
*/

add_action(

    'after_setup_theme',

    function () {

        if (

            is_user_logged_in() &&

            ! current_user_can(
                'administrator'
            )

        ) {

            show_admin_bar(
                false
            );

        }

    }

);

/*
=========================================
GLOBAL CHAT BOX
=========================================
*/

function wpm_load_global_chat() {

    /*
    only logged in users
    */

    if ( ! is_user_logged_in() ) {

        return;

    }

    /*
    avoid admin
    */

    if ( is_admin() ) {

        return;

    }

    /*
    load chat template
    */

    $chat_file =

        plugin_dir_path(
            __FILE__
        ) .

        'templates/chat-box.php';

    if (

        file_exists(
            $chat_file
        )

    ) {

        include $chat_file;

    }

}

add_action(

    'wp_footer',

    'wpm_load_global_chat',

    9999

);




add_action('rest_api_init', function () {

    register_rest_route('wpm/v1', '/members', array(
        'methods'  => 'GET',
        'callback' => 'get_wpm_members',
        'permission_callback' => '__return_true'
    ));

});

function get_wpm_members() {

    $users = get_users(array(

        'role__not_in' => array('Administrator'),

        'orderby' => 'ID',

        'order' => 'DESC'

    ));

    $members = array();

    foreach ($users as $user) {

        /*
        PROFILE PHOTO
        */

        $photo_id = get_user_meta(
            $user->ID,
            'wpm_profile_photo',
            true
        );

        $photo = '';

        if ($photo_id) {

            $photo = wp_get_attachment_url(
                $photo_id
            );

        }

        /*
        AGE
        */

        $dob = get_user_meta(
            $user->ID,
            'wpm_dob',
            true
        );

        $age = '';

        if ($dob) {

            $birthDate = new DateTime($dob);

            $today = new DateTime();

            $age = $today->diff($birthDate)->y;

        }

        /*
        FINAL ARRAY
        */

        $members[] = array(

            'id' => $user->ID,

            'name' => get_user_meta(
                $user->ID,
                'full_name',
                true
            ),

            'gender' => get_user_meta(
                $user->ID,
                'wpm_gender',
                true
            ),

            'looking_for' => get_user_meta(
                $user->ID,
                'wpm_looking_for',
                true
            ),

            'age' => $age,

            'religion' => get_user_meta(
                $user->ID,
                'wpm_religion',
                true
            ),

            'profession' => get_user_meta(
                $user->ID,
                'wpm_profession',
                true
            ),

            'education' => get_user_meta(
                $user->ID,
                'wpm_education',
                true
            ),

            'country' => get_user_meta(
                $user->ID,
                'wpm_country',
                true
            ),

            'state' => get_user_meta(
                $user->ID,
                'wpm_state',
                true
            ),

            'city' => get_user_meta(
                $user->ID,
                'wpm_city',
                true
            ),

            'about' => get_user_meta(
                $user->ID,
                'wpm_about',
                true
            ),

            'hobbies' => get_user_meta(
                $user->ID,
                'wpm_hobbies',
                true
            ),

            'partner_expectation' => get_user_meta(
                $user->ID,
                'wpm_partner_expectation',
                true
            ),

            'photo' => $photo,

            'status' => get_user_meta(
                $user->ID,
                'wpm_profile_status',
                true
            )

        );

    }

    return rest_ensure_response($members);

}