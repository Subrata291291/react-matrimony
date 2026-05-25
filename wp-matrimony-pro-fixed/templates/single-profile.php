<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$username = get_query_var('wpm_profile');
$user = get_user_by('login', $username);

if ( ! $user ) {
    echo '<div class="container py-5"><h2>User not found</h2></div>';
    get_footer();
    return;
}

$user_id = $user->ID;

if ( ! wpm_is_user_approved( $user_id ) ) {
    echo '<div class="bg-color p-80"><h2 class="text-center">Profile is waiting for the approval</h2></div>';
    get_footer();
    return;
}

$cover = wpm_get_cover_photo($user_id);
$photo = wpm_get_profile_photo($user_id);
$name = wpm_get_user_full_name($user_id);
$age = wpm_get_user_age($user_id);
$location = wpm_get_user_location($user_id);
$is_online = wpm_is_user_online($user_id);

$gender = get_user_meta($user_id, 'wpm_gender', true);
$religion = get_user_meta($user_id, 'wpm_religion', true);
$profession = get_user_meta($user_id, 'wpm_profession', true);
$education = get_user_meta($user_id, 'wpm_education', true);
$about = get_user_meta($user_id, 'wpm_about', true);
$hobbies = get_user_meta($user_id, 'wpm_hobbies', true);
$partner_expectation = get_user_meta($user_id, 'wpm_partner_expectation', true);
$gallery = get_user_meta($user_id, 'wpm_gallery', true);

$is_logged_in = is_user_logged_in();
$viewer_membership = $is_logged_in
    ? wpm_get_membership_details( get_current_user_id() )
    : null;
$viewer_has_membership = $is_logged_in
    ? ! empty( $viewer_membership['is_active'] )
    : false;
$membership_checkout_url = wpm_get_membership_checkout_url();

?>
<section class="wpm-profile-page">

    <div class="wpm-hero" style="background-image:url('<?php echo esc_url($cover); ?>')">

        <div class="wpm-hero-overlay"></div>

        <div class="container position-relative">

            <div class="wpm-profile-hero-content">

                <div class="wpm-profile-left">

                    <div class="wpm-avatar-wrap">
                        <img src="<?php echo esc_url($photo); ?>" alt="" class="wpm-avatar">

                        <?php if ( $is_online ) : ?>

                            <span class="wpm-online-dot"></span>

                        <?php endif; ?>
                    </div>

                    <div class="wpm-profile-info">

                        <h1>
                            <?php echo esc_html($name); ?>
                            <?php if($age): ?>
                                <span><?php echo esc_html($age); ?></span>
                            <?php endif; ?>
                        </h1>

                        <div class="wpm-location">
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo esc_html($location); ?>
                        </div>

                        <div class="wpm-tags">
                            <?php if($profession): ?>
                                <span><?php echo esc_html($profession); ?></span>
                            <?php endif; ?>

                            <?php if($religion): ?>
                                <span><?php echo esc_html($religion); ?></span>
                            <?php endif; ?>

                            <?php if($education): ?>
                                <span><?php echo esc_html($education); ?></span>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>

                <div class="wpm-profile-actions">

                    <?php if($is_logged_in && get_current_user_id() != $user_id): ?>

    <?php

    $current_user_id = get_current_user_id();

    /*
    -----------------------------------------
    CHECK IF CURRENT USER IS ACCEPTED
    -----------------------------------------
    */

    /*
-----------------------------------------
CHECK ACCEPTED CONNECTION
-----------------------------------------
*/

/* people accepted by profile owner */
$their_accepted = get_user_meta(
    $user_id,
    'wpm_accepted_interests',
    true
);

/* people accepted by current user */
$my_accepted = get_user_meta(
    $current_user_id,
    'wpm_accepted_interests',
    true
);

$is_accepted = false;

/*
-----------------------------------------
IF EITHER SIDE ACCEPTED
-----------------------------------------
*/

if(
    !empty($their_accepted)
    && is_array($their_accepted)
    && in_array($current_user_id, $their_accepted)
){
    $is_accepted = true;
}

if(
    !empty($my_accepted)
    && is_array($my_accepted)
    && in_array($user_id, $my_accepted)
){
    $is_accepted = true;
}

    /*
    -----------------------------------------
    CHECK IF CURRENT USER SENT REQUEST
    -----------------------------------------
    */

    $pending_requests = get_user_meta(
        $user_id,
        'wpm_interest_requests',
        true
    );

    $is_pending = false;

    if(
        !empty($pending_requests)
        && is_array($pending_requests)
        && in_array($current_user_id, $pending_requests)
    ){
        $is_pending = true;
    }

    ?>

    <?php if($is_accepted && $viewer_has_membership): ?>

    <button class="wpm-btn-primary wpm-start-chat common-btn"
            data-user-id="<?php echo esc_attr($user_id); ?>">

        <i class="fa-solid fa-paper-plane"></i>
        <span>Send Message</span>

    </button>

<?php elseif($is_accepted && !$viewer_has_membership): ?>

    <a class="common-btn"
       href="<?php echo esc_url( $membership_checkout_url ); ?>">

        <i class="fa-solid fa-crown"></i>
        <span>Activate Plan To Chat</span>

    </a>

<?php elseif($is_pending): ?>

    <button class="wpm-btn-secondary common-btn" disabled>

        <i class="fa-solid fa-clock"></i>
        <span>Request Sent</span>

    </button>

<?php elseif($viewer_has_membership): ?>

    <button class="wpm-btn-secondary wpm-send-interest common-btn"
            data-user-id="<?php echo esc_attr($user_id); ?>">

        <i class="fa-solid fa-heart"></i>
        <span>Send Interest</span>

    </button>

<?php else: ?>

    <a class="wpm-btn-secondary common-btn"
       href="<?php echo esc_url( $membership_checkout_url ); ?>">

        <i class="fa-solid fa-crown"></i>
        <span>Activate Plan To Connect</span>

    </a>

<?php endif; ?>
<!--     <button
        class="wpm-report-user-btn common-btn"
        data-user-id="<?php echo esc_attr( $user->ID ); ?>"
    >
        Report Profile
    </button> -->
<?php elseif(!$is_logged_in): ?>

                                            <button class="wpm-btn-primary common-btn"
                                                    data-bs-toggle="modal"
                                                data-bs-target="#exampleModal">

                                                Login To Connect

                                            </button>

                                        <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

    <div class="container wpm-main-wrapper mt-80 mb-80">

        <div class="row gx-lg-5 gy-5">

            <div class="col-lg-3">

                <div class="wpm-sidebar-card sticky-lg-top">

                    <div class="wpm-sidebar-profile">
                        <img src="<?php echo esc_url($photo); ?>" alt="">
                        <h3><?php echo esc_html($name); ?></h3>
                        <p><?php echo esc_html($location); ?></p>
                    </div>

                    <div class="wpm-sidebar-nav">

                        <a href="#about">
                            <i class="fa-solid fa-user"></i> About
                        </a>

                        <a href="#details">
                            <i class="fa-solid fa-id-card"></i> Personal Details
                        </a>

                        <a href="#hobbies">
                            <i class="fa-solid fa-masks-theater"></i> Hobbies
                        </a>

                        <a href="#partner">
                            <i class="fa-solid fa-heart-circle-check"></i> Partner Preference
                        </a>

                        <?php
                        $interest_request_count = 0;
                        if(is_user_logged_in() && get_current_user_id() == $user_id){
                            $interest_requests = get_user_meta(
                                get_current_user_id(),
                                'wpm_interest_requests',
                                true
                            );

                            if(is_array($interest_requests)){
                                $interest_request_count = count($interest_requests);
                            }
                        }
                        ?>

                        <?php if(is_user_logged_in() && get_current_user_id() == $user_id): ?>

                            <a href="#interests">
                                <i class="fa-solid fa-user-plus"></i> Friend Request
                                <span class="wpm-nav-badge" <?php echo $interest_request_count ? '' : 'style="display:none;"'; ?>><?php echo intval($interest_request_count); ?></span>
                            </a>

                            <a href="#connections">
                                <i class="fa-solid fa-users"></i> Connections
                            </a>

                        <?php endif; ?>

                        <a href="#gallery">
                            <i class="fa-solid fa-images"></i> Gallery
                        </a>

                    </div>

                    <?php if(is_user_logged_in() && get_current_user_id() == $user_id): ?>

                    <div class="wpm-membership-card">

                        <span class="wpm-badge">Premium</span>

                        <h4 class="mb-5">Upgrade Membership</h4>

                        <h5 class="mb-5">

                            <?php
                            echo esc_html(
                                wpm_get_membership_notice(
                                    get_current_user_id()
                                )
                            );
                            ?>

                        </h5>

                        <a class="wpm-upgrade-btn common-btn"
                        href="<?php echo esc_url( $membership_checkout_url ); ?>">

                            View Plans

                        </a>

                    </div>

                    <?php endif; ?>

                </div>

            </div>

            <div class="col-lg-9">

                <?php if(!$is_logged_in): ?>

                    <div class="wpm-lock-overlay">
                        <div class="wpm-lock-card">
                            <h2>Login To View Full Profile</h2>
                            <p>Join now to unlock profile details and start conversations.</p>

                            <button data-bs-toggle="modal"
                                            data-bs-target="#exampleModal">
                                Login / Register
                            </button>
                        </div>
                    </div>

                <?php endif; ?>

                <div class="<?php echo !$is_logged_in ? 'wpm-blur-content' : ''; ?>">

                <div class="wpm-top-matches wpm-card">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>New Profile Matches</h2>
                    <div class="wpm-slider-nav"></div>
                </div>

                <div class="wpm-matches-slider">

                    <?php

                    $match_users = get_users([
                        'number' => 8,
                        'exclude' => [$user_id]
                    ]);

                    foreach($match_users as $match):

                        $match_id = $match->ID;

                        $match_photo = wpm_get_profile_photo($match_id);
                        $match_name = wpm_get_user_full_name($match_id);
                        $match_age = wpm_get_user_age($match_id);
                        $match_location = wpm_get_user_location($match_id);
                        $match_online = wpm_is_user_online($match_id);

                    ?>

                    <div class="wpm-match-slide">

                        <a href="<?php echo site_url('/profile/' . $match->user_login); ?>" class="wpm-match-card">

                            <img src="<?php echo esc_url($match_photo); ?>" alt="">

                            <?php if($match_online): ?>
                                <span class="wpm-online-dot"></span>
                            <?php endif; ?>

                            <div class="wpm-match-content">
                                <h4><?php echo esc_html($match_name); ?></h4>
                                <p>
                                    <?php echo esc_html($match_location); ?>
                                    <?php if($match_age): ?>
                                        • <?php echo esc_html($match_age); ?> yrs
                                    <?php endif; ?>
                                </p>
                            </div>

                        </a>

                    </div>

                    <?php endforeach; ?>

                </div>

            </div>
                    <div class="wpm-card" id="about">
                        <div class="wpm-card-header">
                            <h3>About Me</h3>
                        </div>

                        <p>
                            <?php echo nl2br(esc_html($about)); ?>
                        </p>
                    </div>

                    <div class="wpm-card" id="details">

                        <div class="wpm-card-header">
                            <h3>Personal Details</h3>
                        </div>

                        <div class="wpm-details-grid">

                            <div class="wpm-detail-box">
                                <span>Gender</span>
                                <strong><?php echo esc_html($gender); ?></strong>
                            </div>

                            <div class="wpm-detail-box">
                                <span>Religion</span>
                                <strong><?php echo esc_html($religion); ?></strong>
                            </div>

                            <div class="wpm-detail-box">
                                <span>Profession</span>
                                <strong><?php echo esc_html($profession); ?></strong>
                            </div>

                            <div class="wpm-detail-box">
                                <span>Education</span>
                                <strong><?php echo esc_html($education); ?></strong>
                            </div>

                        </div>

                    </div>

                    <div class="wpm-card" id="hobbies">

                        <div class="wpm-card-header">
                            <h3>Hobbies & Interests</h3>
                        </div>

                        <p>
                            <?php echo nl2br(esc_html($hobbies)); ?>
                        </p>

                    </div>

                    <div class="wpm-card" id="partner">

                        <div class="wpm-card-header">
                            <h3>Partner Expectations</h3>
                        </div>

                        <p>
                            <?php echo nl2br(esc_html($partner_expectation)); ?>
                        </p>

                    </div>

                    <?php if(is_user_logged_in() && get_current_user_id() == $user_id): ?>

                    <div class="wpm-card" id="interests">

                        <div class="wpm-card-header">
                            <h3>Interest Requests</h3>
                        </div>

                        <ul class="nav wpm-request-tabs mb-4">

                            <li class="nav-item">
                                <button class="nav-link active"
                                        data-bs-toggle="tab"
                                        data-bs-target="#new-requests">
                                    New Requests
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link"
                                        data-bs-toggle="tab"
                                        data-bs-target="#accepted-requests">
                                    Accepted
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link"
                                        data-bs-toggle="tab"
                                        data-bs-target="#denied-requests">
                                    Denied
                                </button>
                            </li>

                        </ul>

                        <div class="tab-content">

                            <div class="tab-pane fade show active" id="new-requests">

                                <?php

$current_user_id = get_current_user_id();

$interest_requests = get_user_meta(
    $current_user_id,
    'wpm_interest_requests',
    true
);

if(!empty($interest_requests) && is_array($interest_requests)):

    foreach($interest_requests as $sender_id):

        $sender = get_user_by('ID', $sender_id);

        if(!$sender){
            continue;
        }

        $sender_photo = wpm_get_profile_photo($sender_id);
        $sender_name = wpm_get_user_full_name($sender_id);
        $sender_age = wpm_get_user_age($sender_id);
        $sender_location = wpm_get_user_location($sender_id);
        $sender_profession = get_user_meta($sender_id,'wpm_profession',true);

?>

<div class="wpm-interest-row">

    <div class="wpm-interest-user">

        <img src="<?php echo esc_url($sender_photo); ?>" alt="">

        <span>Member</span>

    </div>

    <div class="wpm-interest-content">

        <h4>
            <?php echo esc_html($sender_name); ?>
        </h4>

        <div class="wpm-interest-meta">

            <span>
                Age:
                <strong>
                    <?php echo esc_html($sender_age); ?>
                </strong>
            </span>

            <span>
                City:
                <strong>
                    <?php echo esc_html($sender_location); ?>
                </strong>
            </span>

            <span>
                Profession:
                <strong>
                    <?php echo esc_html($sender_profession); ?>
                </strong>
            </span>

        </div>

        <p>
            Sent you an interest request
        </p>

        <a href="<?php echo site_url('/profile/' . $sender->user_login); ?>"
           class="wpm-view-btn">

            View Full Profile

        </a>

    </div>

    <div class="wpm-interest-actions">

    <button class="wpm-accept-btn"
            data-user="<?php echo esc_attr($sender_id); ?>">
        Accept
    </button>

    <button class="wpm-decline-btn"
            data-user="<?php echo esc_attr($sender_id); ?>">
        Decline
    </button>

    <button class="wpm-delete-btn"
            data-user="<?php echo esc_attr($sender_id); ?>">
        Delete
    </button>

</div>

</div>

<?php

    endforeach;

else:

?>

<div class="wpm-empty-state">

    <h4>No interest requests yet</h4>

</div>

<?php endif; ?>

                            </div>

                            <div class="tab-pane fade" id="accepted-requests">

<?php

$accepted = get_user_meta(
    get_current_user_id(),
    'wpm_accepted_interests',
    true
);

if(!empty($accepted) && is_array($accepted)):

    foreach($accepted as $accepted_id):

        $accepted_user = get_user_by('ID', $accepted_id);

        if(!$accepted_user){
            continue;
        }

        $accepted_photo = wpm_get_profile_photo($accepted_id);

        $accepted_name = wpm_get_user_full_name($accepted_id);

        $accepted_age = wpm_get_user_age($accepted_id);

        $accepted_location = wpm_get_user_location($accepted_id);

        $accepted_profession = get_user_meta(
            $accepted_id,
            'wpm_profession',
            true
        );

?>

<div class="wpm-interest-row">

    <div class="wpm-interest-user">

        <img src="<?php echo esc_url($accepted_photo); ?>" alt="">

        <span>Member</span>

    </div>

    <div class="wpm-interest-content">

        <h4>
            <?php echo esc_html($accepted_name); ?>
        </h4>

        <div class="wpm-interest-meta">

            <span>
                Age:
                <strong>
                    <?php echo esc_html($accepted_age); ?>
                </strong>
            </span>

            <span>
                City:
                <strong>
                    <?php echo esc_html($accepted_location); ?>
                </strong>
            </span>

            <span>
                Profession:
                <strong>
                    <?php echo esc_html($accepted_profession); ?>
                </strong>
            </span>

        </div>

        <p>
            Interest Accepted
        </p>

        <a href="<?php echo site_url('/profile/' . $accepted_user->user_login); ?>"
           class="wpm-view-btn">

            View Full Profile

        </a>

    </div>

    <div class="wpm-interest-actions">

        <button class="wpm-decline-btn"
                data-user="<?php echo esc_attr($accepted_id); ?>">

            Decline

        </button>

    </div>

</div>

<?php endforeach; ?>

<?php else: ?>

<div class="wpm-empty-state">
    <h4>No accepted requests yet</h4>
</div>

<?php endif; ?>

</div>

                            <div class="tab-pane fade" id="denied-requests">

<?php

$declined = get_user_meta(
    get_current_user_id(),
    'wpm_declined_interests',
    true
);

if(!empty($declined) && is_array($declined)):

    foreach($declined as $declined_id):

        $declined_user = get_user_by('ID', $declined_id);

        if(!$declined_user){
            continue;
        }

        $declined_photo = wpm_get_profile_photo($declined_id);

        $declined_name = wpm_get_user_full_name($declined_id);

        $declined_age = wpm_get_user_age($declined_id);

        $declined_location = wpm_get_user_location($declined_id);

        $declined_profession = get_user_meta(
            $declined_id,
            'wpm_profession',
            true
        );

?>

<div class="wpm-interest-row">

    <div class="wpm-interest-user">

        <img src="<?php echo esc_url($declined_photo); ?>" alt="">

        <span>Member</span>

    </div>

    <div class="wpm-interest-content">

        <h4>
            <?php echo esc_html($declined_name); ?>
        </h4>

        <div class="wpm-interest-meta">

            <span>
                Age:
                <strong>
                    <?php echo esc_html($declined_age); ?>
                </strong>
            </span>

            <span>
                City:
                <strong>
                    <?php echo esc_html($declined_location); ?>
                </strong>
            </span>

            <span>
                Profession:
                <strong>
                    <?php echo esc_html($declined_profession); ?>
                </strong>
            </span>

        </div>

        <p>
            Interest Declined
        </p>

        <a href="<?php echo site_url('/profile/' . $declined_user->user_login); ?>"
           class="wpm-view-btn">

            View Full Profile

        </a>

    </div>
    <div class="wpm-interest-actions">

        <button class="wpm-accept-btn"
                data-user="<?php echo esc_attr($declined_id); ?>">

            Accept

        </button>

    </div>

</div>

<?php endforeach; ?>

<?php else: ?>

<div class="wpm-empty-state">
    <h4>No denied requests yet</h4>
</div>

<?php endif; ?>

</div>

                        </div>

                    </div>
                    <?php endif; ?>
                    <?php if(is_user_logged_in() && get_current_user_id() == $user_id): ?>

                    <div class="wpm-card" id="connections">
                        <?php

                        $current_user_id = get_current_user_id();

                        /*
                        -----------------------------------------
                        GET ACCEPTED FRIENDS
                        -----------------------------------------
                        */
                        /*
                        -----------------------------------------
                        GET MY ACCEPTED FRIENDS
                        -----------------------------------------
                        */

                        $my_friends = get_user_meta(
                            $current_user_id,
                            'wpm_accepted_interests',
                            true
                        );

                        if(!is_array($my_friends)){
                            $my_friends = [];
                        }

                        /*
                        -----------------------------------------
                        GET USERS WHO ACCEPTED ME
                        -----------------------------------------
                        */

                        $all_users = get_users();

                        $accepted_me = [];

                        foreach($all_users as $single_user){

                            $their_connections = get_user_meta(
                                $single_user->ID,
                                'wpm_accepted_interests',
                                true
                            );

                            if(
                                is_array($their_connections)
                                && in_array($current_user_id, $their_connections)
                            ){
                                $accepted_me[] = $single_user->ID;
                            }

                        }

                        /*
                        -----------------------------------------
                        MERGE BOTH
                        -----------------------------------------
                        */

                        $friends = array_unique(
                            array_merge(
                                $my_friends,
                                $accepted_me
                            )
                        ); 

                        if (!empty($friends) && is_array($friends)) :
                        ?>

<div class="wpm-friends-box">

    <div class="wpm-friends-header">
        <h3>Connections</h3>
        <span><?php echo count($friends); ?> Friends</span>
    </div>

    <div class="wpm-friends-list">

        <?php foreach ($friends as $friend_id) :

        $friend = get_userdata($friend_id);

        if (!$friend) {
            continue;
        }

        /*
        -----------------------------------------
        NAME
        -----------------------------------------
        */

        $name = get_user_meta(
            $friend_id,
            'wpm_name',
            true
        );

        if (empty($name)) {
            $name = $friend->display_name;
        }

        /*
        -----------------------------------------
        PROFILE IMAGE
        -----------------------------------------
        */

        /*
    -----------------------------------------
    PROFILE IMAGE
    -----------------------------------------
    */

    $profile_image = wpm_get_profile_photo($friend_id);

    if(empty($profile_image)){

        $profile_image = get_avatar_url(
            $friend_id,
            array(
                'size' => 150
            )
        );

    }

        /*
        -----------------------------------------
        LAST ACTIVITY
        -----------------------------------------
        */

        $last_activity = get_user_meta(
            $friend_id,
            'wpm_last_activity',
            true
        );

        $is_online = false;

        if (
            !empty($last_activity)
            && (time() - $last_activity) < 300
        ) {
            $is_online = true;
        }

    ?>

<div class="wpm-friend-item">

    <div class="wpm-friend-left">

        <div class="wpm-friend-image">

            <img
                src="<?php echo esc_url($profile_image); ?>"
                alt="<?php echo esc_attr($name); ?>"
            >

            <?php if ($is_online) : ?>
                <span class="online-dot"></span>
            <?php endif; ?>

        </div>

        <div class="wpm-friend-info">

            <h4>
                <?php echo esc_html($name); ?>
            </h4>

            <?php if ($is_online) : ?>

                <span class="online-text">
                    Online
                </span>

            <?php else : ?>

                <span class="offline-text">

                    Last seen
                    <?php echo human_time_diff(
                        $last_activity,
                        time()
                    ); ?> ago

                </span>

            <?php endif; ?>

        </div>

    </div>

    <div class="wpm-friend-right">

    <?php if ( $viewer_has_membership ) : ?>

    <button
        class="common-btn wpm-start-chat"
        data-user="<?php echo esc_attr($friend_id); ?>"
    >

        <i class="fa-solid fa-paper-plane"></i>
        <span>Chat Now</span>

    </button>

    <?php else : ?>

    <button class="common-btn" onclick="window.location.href='<?php echo esc_url( $membership_checkout_url ); ?>'"><i class="fa-solid fa-crown"></i> Activate Plan</button>

    <?php endif; ?>
    <?php

        $is_blocked = get_user_meta(

            get_current_user_id(),

            'wpm_blocked_user_' . $friend_id,

            true

        );

        ?>

        <button

            class="wpm-block-user-btn common-btn"

            data-user="<?php echo esc_attr( $friend_id ); ?>"

            data-blocked="<?php echo $is_blocked ? '1' : '0'; ?>"

        >

           <?php if ( $is_blocked ) : ?>

                <i class="fa-solid fa-unlock"></i>
                Unblock

            <?php else : ?>

                <i class="fa-solid fa-ban"></i>
                Block

            <?php endif; ?>

        </button>
    </div>

</div>

<?php endforeach; ?>

    </div>

</div>

<?php endif; ?>


                    </div>
                    <?php endif; ?>

                    <div class="wpm-card" id="gallery">

                        <div class="wpm-card-header">
                            <h3>Photo Gallery</h3>
                        </div>

                        <div class="row g-4">

                            <?php
                            if(!empty($gallery)):
                                foreach($gallery as $image_id):

                                    $image = wp_get_attachment_url($image_id);
                            ?>

                            <div class="col-md-4 col-6">

                                <div class="wpm-gallery-item">
                                    <img src="<?php echo esc_url($image); ?>" alt="">
                                </div>

                            </div>

                            <?php
                                endforeach;
                            endif;
                            ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<?php get_footer(); ?>

