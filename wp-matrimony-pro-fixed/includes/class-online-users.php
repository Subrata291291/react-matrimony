<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Online_Users {

    public function __construct() {

        /*
        -----------------------------------------
        TRACK USER ACTIVITY
        -----------------------------------------
        */

        add_action(
            'wp',
            array( $this, 'track_user_activity' )
        );

    }

    /*
    -----------------------------------------
    TRACK USER ACTIVITY
    -----------------------------------------
    */

    public function track_user_activity() {

        /*
        -----------------------------------------
        CHECK LOGIN
        -----------------------------------------
        */

        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id = get_current_user_id();

        /*
        -----------------------------------------
        UPDATE LAST ACTIVITY
        -----------------------------------------
        */

        update_user_meta(

            $user_id,

            'wpm_last_activity',

            time()

        );

    }

    /*
    -----------------------------------------
    GET ONLINE USERS
    -----------------------------------------
    */

    public static function get_online_users() {

        $users = get_users(array(

            'meta_key' => 'wpm_profile_status',

            'meta_value' => 'approved',

            'number' => -1

        ));

        $online_users = array();

        foreach ( $users as $user ) {

            $last_activity = get_user_meta(

                $user->ID,

                'wpm_last_activity',

                true

            );

            /*
            -----------------------------------------
            CHECK ACTIVE TIME
            -----------------------------------------
            */

            if ( $last_activity ) {

                $difference = time() - $last_activity;

                /*
                -----------------------------------------
                5 MINUTES ACTIVE
                -----------------------------------------
                */

                if ( $difference <= 300 ) {

                    $online_users[] = $user;

                }

            }

        }

        return $online_users;

    }

    /*
    -----------------------------------------
    CHECK USER ONLINE
    -----------------------------------------
    */

    public static function is_online( $user_id ) {

        $last_activity = get_user_meta(

            $user_id,

            'wpm_last_activity',

            true

        );

        if ( ! $last_activity ) {
            return false;
        }

        $difference = time() - $last_activity;

        return $difference <= 300;

    }

}