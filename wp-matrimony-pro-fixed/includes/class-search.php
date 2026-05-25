
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Search {

    public function __construct() {

        add_action(
            'wp_ajax_wpm_search_members',
            array(
                $this,
                'search_members'
            )
        );

        add_action(
            'wp_ajax_nopriv_wpm_search_members',
            array(
                $this,
                'search_members'
            )
        );

    }

    /*
    =========================================
    SEARCH MEMBERS
    =========================================
    */

    public function search_members() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        /*
        =========================================
        FORM DATA
        =========================================
        */

        $gender = isset(
            $_POST['gender']
        )
        ? strtolower(
            sanitize_text_field(
                $_POST['gender']
            )
        )
        : '';

        $seeking = isset(
            $_POST['seeking']
        )
        ? strtolower(
            sanitize_text_field(
                $_POST['seeking']
            )
        )
        : '';

        $age_from = isset(
            $_POST['age_from']
        )
        ? intval(
            $_POST['age_from']
        )
        : 0;

        $age_to = isset(
            $_POST['age_to']
        )
        ? intval(
            $_POST['age_to']
        )
        : 0;

        /*
        =========================================
        GET USERS
        =========================================
        */

        $users = get_users(array(

            'number' => -1,

            'exclude' => get_users(array(
                'role'   => 'administrator',
                'fields' => 'ID'
            )),

            'orderby' => 'registered',

            'order' => 'DESC',

            'meta_query' => array(

                array(

                    'key' =>
                        'wpm_profile_status',

                    'value' =>
                        'approved',

                    'compare' =>
                        '='

                )

            )

        ));

        $filtered_users = array();
        $used_photo_ids = array();

        /*
        =========================================
        LOOP USERS
        =========================================
        */

        foreach ( $users as $user ) {

            $user_id =
                $user->ID;

            /*
            =========================================
            USER META
            =========================================
            */

            $user_gender =
                strtolower(
                    trim(
                        get_user_meta(
                            $user_id,
                            'wpm_gender',
                            true
                        )
                    )
                );

            $user_seeking =
                strtolower(
                    trim(
                        get_user_meta(
                            $user_id,
                            'wpm_looking_for',
                            true
                        )
                    )
                );

            /*
            fix woman/women
            */

            if (
                $user_gender ===
                'woman'
            ) {

                $user_gender =
                    'women';

            }

            if (
                $user_seeking ===
                'woman'
            ) {

                $user_seeking =
                    'women';

            }


/*
=========================================
SEARCH TARGET GENDER
=========================================
*/

$target_gender = '';

if (
    ! empty( $seeking )
) {

    $target_gender =
        strtolower(
            trim(
                $seeking
            )
        );

}

/*
filter users
*/

if (
    ! empty(
        $target_gender
    )
) {

    if (
        $user_gender !==
        $target_gender
    ) {

        continue;

    }

}


            /*
            =========================================
            AGE
            =========================================
            */

            $age =
                intval(
                    wpm_get_user_age(
                        $user_id
                    )
                );

            if (
                empty(
                    $age
                )
            ) {

                continue;

            }

            /*
            age from
            */

            if (
                ! empty(
                    $age_from
                )
                &&
                $age <
                $age_from
            ) {

                continue;

            }

            /*
            age to
            */

            if (
                ! empty(
                    $age_to
                )
                &&
                $age >
                $age_to
            ) {

                continue;

            }

            /*
            add user
            */

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
                isset( $used_photo_ids[ $profile_photo_id ] )
            ) {
                continue;
            }

            $filtered_users[] =
                $user;

            if ( $profile_photo_id ) {
                $used_photo_ids[ $profile_photo_id ] = true;
            }

        }

        /*
        =========================================
        NO RESULT
        =========================================
        */

        if (
            empty(
                $filtered_users
            )
        ) {

            echo '
            <div class="col-12">
                <p>No members found.</p>
            </div>
            ';

            wp_die();

        }

        /*
        =========================================
        SHOW USERS
        =========================================
        */

        $filtered_users = array_slice(
            $filtered_users,
            0,
            4
        );

        foreach (
            $filtered_users
            as $user
        ) {

            $user_id =
                $user->ID;

            include WPM_PLUGIN_PATH .
            'templates/member-card-grid.php';

        }

        wp_die();

    }

}
