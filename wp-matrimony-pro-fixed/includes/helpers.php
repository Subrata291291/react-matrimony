<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpm_get_profile_url( $user_id ) {
    $user = get_userdata( $user_id );

    if ( ! $user ) {
        return '#';
    }

    return home_url( '/profile/' . $user->user_login );
}

function wpm_get_profile_photo( $user_id ) {
    $photo_id = get_user_meta( $user_id, 'wpm_profile_photo', true );

    if ( $photo_id ) {
        return wp_get_attachment_url( $photo_id );
    }

    return WPM_PLUGIN_URL . 'assets/images/default-profile.jpg';
}

function wpm_get_cover_photo( $user_id ) {
    $cover_id = get_user_meta( $user_id, 'wpm_cover_photo', true );

    if ( $cover_id ) {
        return wp_get_attachment_url( $cover_id );
    }

    return WPM_PLUGIN_URL . 'assets/images/default-cover.jpg';
}

function wpm_get_user_full_name( $user_id ) {
    $name = get_user_meta( $user_id, 'full_name', true );

    if ( $name ) {
        return $name;
    }

    $user = get_userdata( $user_id );

    return $user ? $user->display_name : '';
}

function wpm_get_user_age( $user_id ) {
    $dob = get_user_meta( $user_id, 'wpm_dob', true );

    if ( empty( $dob ) ) {
        return false;
    }

    try {
        $birth_date = new DateTime( $dob );
    } catch ( Exception $e ) {
        return false;
    }

    $today = new DateTime();
    $age   = $today->diff( $birth_date )->y;

    if ( $age < 18 || $age > 100 ) {
        return false;
    }

    return $age;
}

function wpm_get_user_location( $user_id ) {
    $city    = get_user_meta( $user_id, 'wpm_city', true );
    $state   = get_user_meta( $user_id, 'wpm_state', true );
    $country = get_user_meta( $user_id, 'wpm_country', true );

    return implode( ', ', array_filter( array( $city, $state, $country ) ) );
}

function wpm_is_user_online( $user_id ) {
    $last_activity = get_user_meta( $user_id, 'wpm_last_activity', true );

    if ( ! $last_activity ) {
        return false;
    }

    return ( time() - $last_activity ) <= 300;
}

function wpm_is_user_approved( $user_id ) {
    $status = get_user_meta( $user_id, 'wpm_profile_status', true );

    return $status === 'approved';
}

function wpm_blur_class() {
    return ! is_user_logged_in() ? 'wpm-guest-profile-btn' : '';
}

function wpm_get_membership_plans() {
    return array(
        'starter_day' => array(
            'label'         => 'Starter Day',
            'price'         => 5,
            'duration_days' => 1,
            'description'   => 'Intro plan for the first 24 hours.',
        ),
        'monthly' => array(
            'label'         => 'Monthly',
            'price'         => 49,
            'duration_days' => 30,
            'description'   => '30 days premium access.',
        ),
        'quarterly' => array(
            'label'         => 'Quarterly',
            'price'         => 129,
            'duration_days' => 90,
            'description'   => '90 days premium access.',
        ),
        'half_yearly' => array(
            'label'         => 'Half Yearly',
            'price'         => 399,
            'duration_days' => 180,
            'description'   => '180 days premium access.',
        ),
        'full_year' => array(
            'label'         => 'Full Year',
            'price'         => 599,
            'duration_days' => 365,
            'description'   => '365 days premium access.',
        ),
    );
}

function wpm_get_membership_plan( $plan_key ) {
    $plans = wpm_get_membership_plans();

    return isset( $plans[ $plan_key ] ) ? $plans[ $plan_key ] : null;
}

function wpm_get_membership_label( $plan_key ) {
    $plan = wpm_get_membership_plan( $plan_key );

    return $plan ? $plan['label'] : 'No Plan';
}

function wpm_get_membership_checkout_url( $plan = '' ) {
    $url = home_url( '/membership-checkout/' );

    if ( $plan ) {
        $url = add_query_arg( 'plan', $plan, $url );
    }

    return $url;
}

function wpm_expire_membership( $user_id ) {
    update_user_meta( $user_id, 'wpm_membership_status', 'expired' );
}

function wpm_activate_membership( $user_id, $plan_key, $context = array() ) {
    $plan = wpm_get_membership_plan( $plan_key );

    if ( ! $plan ) {
        return false;
    }

    $started_at = time();

    $expires_at =
        $started_at +
        (
            intval( $plan['duration_days'] ) *
            DAY_IN_SECONDS
        );

    update_user_meta( $user_id, 'wpm_membership_plan', $plan_key );
    update_user_meta( $user_id, 'wpm_membership_status', 'active' );
    update_user_meta( $user_id, 'wpm_membership_started_at', $started_at );
    update_user_meta( $user_id, 'wpm_membership_expires_at', $expires_at );
    update_user_meta( $user_id, 'wpm_membership_price', intval( $plan['price'] ) );

    if ( ! empty( $context['payment_id'] ) ) {
        update_user_meta( $user_id, 'wpm_membership_payment_id', sanitize_text_field( $context['payment_id'] ) );
    }

    if ( ! empty( $context['order_id'] ) ) {
        update_user_meta( $user_id, 'wpm_membership_order_id', sanitize_text_field( $context['order_id'] ) );
    }

    if ( $plan_key === 'starter_day' ) {
        update_user_meta( $user_id, 'wpm_membership_has_used_starter', 1 );
    }

    return true;
}

function wpm_cancel_membership( $user_id ) {
    update_user_meta( $user_id, 'wpm_membership_status', 'cancelled' );
    update_user_meta(
        $user_id,
        'wpm_membership_expires_at',
        time()
    );
}

function wpm_get_membership_time_left_human( $expires_at ) {

    $remaining_seconds =
    intval( $expires_at )
    - time();

    if ( $remaining_seconds <= 0 ) {
        return '';
    }

    $days = floor(
        $remaining_seconds / DAY_IN_SECONDS
    );

    $remaining_seconds -=
        $days * DAY_IN_SECONDS;

    $hours = floor(
        $remaining_seconds / HOUR_IN_SECONDS
    );

    $remaining_seconds -=
        $hours * HOUR_IN_SECONDS;

    $minutes = floor(
        $remaining_seconds / MINUTE_IN_SECONDS
    );

    /*
    -----------------------------------------
    LESS THAN 24 HOURS
    -----------------------------------------
    */

    if ( $days <= 0 ) {

        if ( $hours > 0 ) {

            return $hours . ' hour' .
                ( $hours > 1 ? 's' : '' ) .
                ' ' .
                $minutes . ' min';

        }

        return $minutes . ' min';

    }

    /*
    -----------------------------------------
    MORE THAN 24 HOURS
    -----------------------------------------
    */

    $parts = array();

    $parts[] =
        $days . ' day' .
        ( $days > 1 ? 's' : '' );

    if ( $hours > 0 ) {

        $parts[] =
            $hours . ' hour' .
            ( $hours > 1 ? 's' : '' );

    }

    return implode( ' ', $parts );

}

function wpm_get_membership_details( $user_id ) {
    $plan_key   = get_user_meta( $user_id, 'wpm_membership_plan', true );
    $status     = get_user_meta( $user_id, 'wpm_membership_status', true );
    $started_at = intval( get_user_meta( $user_id, 'wpm_membership_started_at', true ) );
    $expires_at = intval( get_user_meta( $user_id, 'wpm_membership_expires_at', true ) );
    $now        = time();
    $offset     = current_time( 'timestamp' ) - $now;

    if (
        $status === 'active'
        &&
        $offset !== 0
        &&
        $started_at > ( $now + 300 )
    ) {
        $started_at -= $offset;
        $expires_at -= $offset;

        update_user_meta( $user_id, 'wpm_membership_started_at', $started_at );
        update_user_meta( $user_id, 'wpm_membership_expires_at', $expires_at );
    }

    if (
        $status === 'active'
        &&
        $expires_at > 0
        &&
        $now >= $expires_at
    ) {
        wpm_expire_membership( $user_id );
        $status = 'expired';
    }

    $plan = wpm_get_membership_plan( $plan_key );

    return array(
        'plan_key'        => $plan_key,
        'plan'            => $plan,
        'label'           => $plan ? $plan['label'] : 'No Plan',
        'status'          => $status ? $status : 'inactive',
        'started_at'      => $started_at,
        'expires_at'      => $expires_at,
        'price'           => $plan ? intval( $plan['price'] ) : 0,
        'is_active'       => $status === 'active' && $expires_at > $now,
        'time_left_human' => $status === 'active' && $expires_at > $now
            ? wpm_get_membership_time_left_human( $expires_at )
            : '',
    );
}

function wpm_has_active_membership( $user_id ) {
    $membership = wpm_get_membership_details( $user_id );

    return ! empty( $membership['is_active'] );
}

function wpm_has_used_starter_plan( $user_id ) {
    return (bool) get_user_meta( $user_id, 'wpm_membership_has_used_starter', true );
}

function wpm_get_available_membership_plans( $user_id ) {
    $plans = wpm_get_membership_plans();

    if ( ! wpm_has_used_starter_plan( $user_id ) ) {
        return array(
            'starter_day' => $plans['starter_day'],
        );
    }

    unset( $plans['starter_day'] );

    return $plans;
}

function wpm_get_membership_notice( $user_id ) {
    $membership = wpm_get_membership_details( $user_id );

    if ( $membership['is_active'] ) {
        $notice = sprintf(
            '%s plan active from %s until %s.',
            $membership['label'],
            wp_date( 'd M Y h:i A', $membership['started_at'] ),
            wp_date( 'd M Y h:i A', $membership['expires_at'] )
        );

        if ( ! empty( $membership['time_left_human'] ) ) {
            $notice .= sprintf( ' %s left.', $membership['time_left_human'] );
        }

        return $notice;
    }

    if ( wpm_has_used_starter_plan( $user_id ) ) {
        return 'Your membership has expired. Please choose a monthly, quarterly, half yearly, or full year plan.';
    }

    return 'Choose the Starter Day plan for Rs 5 to unlock premium access for the first 24 hours.';
}
