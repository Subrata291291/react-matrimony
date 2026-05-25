
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$profile_url = wpm_get_profile_url(
    $user_id
);

$photo = wpm_get_profile_photo(
    $user_id
);

$name = wpm_get_user_full_name(
    $user_id
);

$age = wpm_get_user_age(
    $user_id
);

$location = wpm_get_user_location(
    $user_id
);

$is_online = wpm_is_user_online(
    $user_id
);

?>

<div
    class="col-6 col-md-4 col-lg-3 wpm-member-item"
    data-user-id="<?php echo esc_attr( $user_id ); ?>"
    data-photo-id="<?php echo esc_attr( get_user_meta( $user_id, 'wpm_profile_photo', true ) ); ?>"
>

<?php

$can_view_profile = false;

/*
-----------------------------------------
CHECK LOGIN + APPROVAL
-----------------------------------------
*/

if ( is_user_logged_in() ) {

    $current_user_id = get_current_user_id();

    $status = get_user_meta(
        $current_user_id,
        'wpm_profile_status',
        true
    );

    if ( $status === 'approved' ) {

        $can_view_profile = true;

    }

}

?>

<?php if ( ! $can_view_profile ) : ?>

    <div class="profile-box">

        <button
            class="wpm-blur-wrap"
            data-bs-toggle="modal"
            data-bs-target="#exampleModal"
        >

            <img
                src="<?php echo esc_url(
                    $photo
                ); ?>"
                alt=""
            >
            <?php if($is_online): ?>
                <span class="wpm-online-dot"></span>
            <?php endif; ?>

            <div class="wpm-login-overlay">

                Login to View

            </div>

        </button>

    </div>

<?php else : ?>

    <div class="profile-box">

        <a
            href="<?php echo esc_url(
                $profile_url
            ); ?>"
        >

            <img
                src="<?php echo esc_url(
                    $photo
                ); ?>"
                alt=""
            >

            <?php if($is_online): ?>
                <span class="wpm-online-dot"></span>
            <?php endif; ?>

        </a>

    </div>

<?php endif; ?>

</div>
