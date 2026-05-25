<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Profile {

    public function __construct() {

        /*
        -----------------------------------------
        AJAX SAVE PROFILE
        -----------------------------------------
        */

        add_action(
            'wp_ajax_wpm_save_profile',
            array( $this, 'save_profile' )
        );

    }

    /*
    -----------------------------------------
    SAVE PROFILE
    -----------------------------------------
    */

    public function save_profile() {

        check_ajax_referer(
            'wpm_nonce',
            'security'
        );

        /*
        -----------------------------------------
        LOGIN CHECK
        -----------------------------------------
        */

        if ( ! is_user_logged_in() ) {

            wp_send_json_error(array(
                'message' => 'Login required'
            ));

        }

        $user_id = get_current_user_id();

        /*
        -----------------------------------------
        BASIC FIELDS
        -----------------------------------------
        */

        $full_name = sanitize_text_field(
            $_POST['full_name']
        );

        $gender = sanitize_text_field(
            $_POST['gender']
        );

        $looking_for = sanitize_text_field(
            $_POST['looking_for']
        );

        $dob = sanitize_text_field(
            $_POST['dob']
        );

        $religion = sanitize_text_field(
            $_POST['religion']
        );

        $profession = sanitize_text_field(
            $_POST['profession']
        );

        $education = sanitize_text_field(
            $_POST['education']
        );

        $country = sanitize_text_field(
            $_POST['country']
        );

        $state = sanitize_text_field(
            $_POST['state']
        );

        $city = sanitize_text_field(
            $_POST['city']
        );

        $about = sanitize_textarea_field(
            $_POST['about']
        );

        $hobbies = sanitize_textarea_field(
            $_POST['hobbies']
        );

        $partner_expectation = sanitize_textarea_field(
            $_POST['partner_expectation']
        );

        /*
        -----------------------------------------
        SAVE USER META
        -----------------------------------------
        */

        update_user_meta(
            $user_id,
            'full_name',
            $full_name
        );

        update_user_meta(
            $user_id,
            'wpm_gender',
            $gender
        );

        update_user_meta(
            $user_id,
            'wpm_looking_for',
            $looking_for
        );

        update_user_meta(
            $user_id,
            'wpm_dob',
            $dob
        );

        update_user_meta(
            $user_id,
            'wpm_religion',
            $religion
        );

        update_user_meta(
            $user_id,
            'wpm_profession',
            $profession
        );

        update_user_meta(
            $user_id,
            'wpm_education',
            $education
        );

        update_user_meta(
            $user_id,
            'wpm_country',
            $country
        );

        update_user_meta(
            $user_id,
            'wpm_state',
            $state
        );

        update_user_meta(
            $user_id,
            'wpm_city',
            $city
        );

        update_user_meta(
            $user_id,
            'wpm_about',
            $about
        );

        update_user_meta(
            $user_id,
            'wpm_hobbies',
            $hobbies
        );

        update_user_meta(
            $user_id,
            'wpm_partner_expectation',
            $partner_expectation
        );

        /*
        -----------------------------------------
        PROFILE IMAGE UPLOAD
        -----------------------------------------
        */

        if ( ! empty( $_FILES['profile_photo']['name'] ) ) {

            $profile_photo = media_handle_upload(
                'profile_photo',
                0
            );

            if ( ! is_wp_error( $profile_photo ) ) {

                update_user_meta(
                    $user_id,
                    'wpm_profile_photo',
                    $profile_photo
                );

            }

        }

        /*
        -----------------------------------------
        COVER PHOTO UPLOAD
        -----------------------------------------
        */

        if ( ! empty( $_FILES['cover_photo']['name'] ) ) {

            $cover_photo = media_handle_upload(
                'cover_photo',
                0
            );

            if ( ! is_wp_error( $cover_photo ) ) {

                update_user_meta(
                    $user_id,
                    'wpm_cover_photo',
                    $cover_photo
                );

            }

        }

        /*
        -----------------------------------------
        GALLERY UPLOAD
        -----------------------------------------
        */

        if ( ! empty( $_FILES['gallery']['name'][0] ) ) {

            $gallery_ids = array();

            $files = $_FILES['gallery'];

            foreach ( $files['name'] as $key => $value ) {

                if ( $files['name'][ $key ] ) {

                    $file = array(
                        'name'     => $files['name'][ $key ],
                        'type'     => $files['type'][ $key ],
                        'tmp_name' => $files['tmp_name'][ $key ],
                        'error'    => $files['error'][ $key ],
                        'size'     => $files['size'][ $key ]
                    );

                    $_FILES['single_gallery'] = $file;

                    $attachment_id = media_handle_upload(
                        'single_gallery',
                        0
                    );

                    if ( ! is_wp_error( $attachment_id ) ) {

                        $gallery_ids[] = $attachment_id;

                    }

                }

            }

            update_user_meta(
                $user_id,
                'wpm_gallery',
                $gallery_ids
            );

        }

        /*
        -----------------------------------------
        SUCCESS
        -----------------------------------------
        */

        wp_send_json_success(array(

            'message' => 'Profile updated successfully'

        ));

    }

}