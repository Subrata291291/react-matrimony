<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPM_Rewrites {

    public function __construct() {

        add_action(
            'init',
            array( $this, 'add_rewrite_rules' )
        );

        add_filter(
            'query_vars',
            array( $this, 'add_query_vars' )
        );

        add_filter(
            'template_include',
            array( $this, 'load_templates' )
        );

    }

    /*
    -----------------------------------------
    REWRITE RULES
    -----------------------------------------
    */

    public function add_rewrite_rules() {

        /*
        MEMBERS
        */

        add_rewrite_rule(
            '^members/?$',
            'index.php?wpm_members=1',
            'top'
        );

        /*
        PROFILE
        */

        add_rewrite_rule(
            '^profile/([^/]*)/?',
            'index.php?wpm_profile=$matches[1]',
            'top'
        );

        /*
        CHAT
        */

        add_rewrite_rule(
            '^chat/?$',
            'index.php?wpm_chat=1',
            'top'
        );

        add_rewrite_rule(
            '^membership-checkout/?$',
            'index.php?wpm_membership_checkout=1',
            'top'
        );

        add_rewrite_rule(
            '^membership-receipt/?$',
            'index.php?wpm_membership_receipt=1',
            'top'
        );

    }

    /*
    -----------------------------------------
    QUERY VARS
    -----------------------------------------
    */

    public function add_query_vars( $vars ) {

        $vars[] = 'wpm_members';

        $vars[] = 'wpm_profile';

        $vars[] = 'wpm_chat';

        $vars[] = 'wpm_membership_checkout';

        $vars[] = 'wpm_membership_receipt';

        return $vars;

    }

    /*
    -----------------------------------------
    LOAD TEMPLATES
    -----------------------------------------
    */

    public function load_templates( $template ) {

        /*
        MEMBERS PAGE
        */

        if ( get_query_var( 'wpm_members' ) ) {

            return WPM_PLUGIN_PATH .
                'templates/search-results.php';

        }

        /*
        PROFILE PAGE
        */

        if ( get_query_var( 'wpm_profile' ) ) {

            return WPM_PLUGIN_PATH .
                'templates/single-profile.php';

        }

        /*
        CHAT PAGE
        */

        if ( get_query_var( 'wpm_chat' ) ) {

            return WPM_PLUGIN_PATH .
                'templates/chat-page.php';

        }

        if ( get_query_var( 'wpm_membership_checkout' ) ) {

            return WPM_PLUGIN_PATH .
                'templates/members-checkout.php';

        }

        if ( get_query_var( 'wpm_membership_receipt' ) ) {

            return WPM_PLUGIN_PATH .
                'templates/membership-receipt.php';

        }

        return $template;

    }

}

new WPM_Rewrites();
