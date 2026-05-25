<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Members {

    public function __construct() {

        /*
        -----------------------------------------
        AJAX LOAD MEMBERS
        -----------------------------------------
        */

        add_action(
            'wp_ajax_wpm_load_members',
            array( $this, 'load_members' )
        );

        add_action(
            'wp_ajax_nopriv_wpm_load_members',
            array( $this, 'load_members' )
        );

    }

    /*
    -----------------------------------------
    LOAD MEMBERS
    -----------------------------------------
    */

    public function load_members() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        $tab = isset($_POST['tab'])
            ? sanitize_text_field($_POST['tab'])
            : 'all';

        /*
        -----------------------------------------
        GET FILTER VALUES
        -----------------------------------------
        */

        $gender = isset($_POST['gender'])
            ? sanitize_text_field($_POST['gender'])
            : '';

        $seeking = isset($_POST['seeking'])
            ? sanitize_text_field($_POST['seeking'])
            : '';

        $age_from = isset($_POST['age_from'])
            ? intval($_POST['age_from'])
            : '';

        $age_to = isset($_POST['age_to'])
            ? intval($_POST['age_to'])
            : '';

        /*
        -----------------------------------------
        META QUERY
        -----------------------------------------
        */

        $meta_query = array(

            'relation' => 'AND',

            array(
                'key'     => 'wpm_profile_status',
                'value'   => 'approved',
                'compare' => '='
            )

        );

        /*
        -----------------------------------------
        TAB FILTERS
        -----------------------------------------
        */

        if ($tab === 'man') {

            $meta_query[] = array(
                'key'     => 'wpm_gender',
                'value'   => 'man',
                'compare' => '='
            );

        }

        if ($tab === 'women') {

            $meta_query[] = array(
                'key'     => 'wpm_gender',
                'value'   => 'women',
                'compare' => '='
            );

        }

        /*
        -----------------------------------------
        SEARCH FILTERS
        -----------------------------------------
        */

        if (!empty($gender)) {

            $meta_query[] = array(
                'key'     => 'wpm_gender',
                'value'   => $gender,
                'compare' => '='
            );

        }

        if (!empty($seeking)) {

            $meta_query[] = array(
                'key'     => 'wpm_seeking',
                'value'   => $seeking,
                'compare' => '='
            );

        }

        if (!empty($age_from)) {

            $meta_query[] = array(
                'key'     => 'wpm_age',
                'value'   => $age_from,
                'type'    => 'NUMERIC',
                'compare' => '>='
            );

        }

        if (!empty($age_to)) {

            $meta_query[] = array(
                'key'     => 'wpm_age',
                'value'   => $age_to,
                'type'    => 'NUMERIC',
                'compare' => '<='
            );

        }

        /*
        -----------------------------------------
        GET USERS
        -----------------------------------------
        */

        $users = get_users(array(

            /*
            ONLY 8 USERS
            */

            'number' => 8,

            /*
            NEWEST USERS FIRST
            */

            'orderby' => 'registered',

            'order' => 'DESC',

            'meta_query' => $meta_query

        ));

        /*
        -----------------------------------------
        ONLINE FILTER
        -----------------------------------------
        */

        if ($tab === 'online-members') {

            $online_users = array();

            foreach ($users as $user) {

                if (wpm_is_user_online($user->ID)) {

                    $online_users[] = $user;

                }

            }

            $users = $online_users;

        }

        /*
        -----------------------------------------
        NO USERS
        -----------------------------------------
        */

        if (empty($users)) {

            echo '<div class="col-12">';
            echo '<p>No members found.</p>';
            echo '</div>';

            wp_die();

        }

        /*
        -----------------------------------------
        LOOP MEMBERS
        -----------------------------------------
        */

        foreach ($users as $user) {

            $user_id = $user->ID;

            include WPM_PLUGIN_PATH . 'templates/member-card-grid.php';

        }

        wp_die();

    }

}