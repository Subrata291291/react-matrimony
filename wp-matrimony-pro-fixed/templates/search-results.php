<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

?>
<section class="common-banner romantic-banner">

    <div class="love-bg-shape shape-1"></div>
    <div class="love-bg-shape shape-2"></div>
    <div class="love-bg-shape shape-3"></div>

    <div class="floating-hearts">
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
        <span>❤</span>
    </div>

    <div class="container romantic-banner-inner">

        <div class="love-person left-person">

            <img
                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=800&auto=format&fit=crop"
                alt="Man"
            >

        </div>

        <div class="banner-content">

            <h3 class="title">
                FIND YOUR FOREVER
            </h3>

            <h1>Start Your <span>Journey</span>

        </h1>

            <p>
                Where hearts connect, stories begin, and forever starts.
            </p>

        </div>

        <div class="love-person right-person">

            <img
                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=800&auto=format&fit=crop"
                alt="Woman"
            >

        </div>

    </div>

</section>


<section class="wpm-members-page p-80">

    <div class="container">

        <!-- HEADER -->

        <div class="wpm-members-header d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2>Browse Members</h2>

                <p class="mt-3">
                    Find your perfect life partner
                </p>

            </div>

            <!-- GRID / LIST TOGGLE -->

            <div class="wpm-view-toggle">

                <button
                    class="wpm-grid-view active"
                    data-view="grid"
                >
                    Grid
                </button>

                <button
                    class="wpm-list-view"
                    data-view="list"
                >
                    List
                </button>

            </div>

        </div>

        <!-- SEARCH FILTER -->

        <div class="wpm-search-box">

            <form id="wpm-search-form" method="GET">

                <div class="row gy-4">

                    <div class="col-md-3">

                        <select
                            name="gender"
                            class="form-control"
                        >

                            <option value="">
                                I am
                            </option>

                            <option value="man"
                                <?php selected(
                                    isset($_GET['gender']) ? $_GET['gender'] : '',
                                    'man'
                                ); ?>
                            >
                                Man
                            </option>

                            <option value="women"
                                <?php selected(
                                    isset($_GET['gender']) ? $_GET['gender'] : '',
                                    'women'
                                ); ?>
                            >
                                Woman
                            </option>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <select
                            name="seeking"
                            class="form-control"
                        >

                            <option value="">
                                Seeking
                            </option>

                            <option value="man"
                                <?php selected(
                                    isset($_GET['seeking']) ? $_GET['seeking'] : '',
                                    'man'
                                ); ?>
                            >
                                Man
                            </option>

                            <option value="women"
                                <?php selected(
                                    isset($_GET['seeking']) ? $_GET['seeking'] : '',
                                    'women'
                                ); ?>
                            >
                                Woman
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <select
                            name="age_from"
                            class="form-control"
                        >

                            <option value="">
                                Age From
                            </option>

                            <?php for ( $i = 18; $i <= 60; $i++ ) : ?>

                                <option
                                    value="<?php echo $i; ?>"
                                    <?php selected(
                                        isset($_GET['age_from']) ? $_GET['age_from'] : '',
                                        $i
                                    ); ?>
                                >

                                    <?php echo $i; ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <select
                            name="age_to"
                            class="form-control"
                        >

                            <option value="">
                                Age To
                            </option>

                            <?php for ( $i = 18; $i <= 60; $i++ ) : ?>

                                <option
                                    value="<?php echo $i; ?>"
                                    <?php selected(
                                        isset($_GET['age_to']) ? $_GET['age_to'] : '',
                                        $i
                                    ); ?>
                                >

                                    <?php echo $i; ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="common-btn w-100"
                        >

                            Search

                        </button>

                    </div>

                </div>

            </form>

        </div>

        <!-- MEMBERS -->

        <div
            class="row mt-5 gy-4"
            id="wpm-members-results"
        >

            <?php

            /*
            -----------------------------------------
            GET FILTER VALUES
            -----------------------------------------
            */

            $gender   = isset($_GET['gender']) ? sanitize_text_field($_GET['gender']) : '';
            $seeking  = isset($_GET['seeking']) ? sanitize_text_field($_GET['seeking']) : '';
            $age_from = isset($_GET['age_from']) ? intval($_GET['age_from']) : '';
            $age_to   = isset($_GET['age_to']) ? intval($_GET['age_to']) : '';

            /*
            -----------------------------------------
            USER QUERY
            -----------------------------------------
            */

            $admin_user = get_user_by(
                'email',
                get_option('admin_email')
            );

            $args = array(

                'number' => -1,

                'orderby' => 'registered',

                'order' => 'DESC',

                'exclude' => get_users(array(
                    'role'   => 'administrator',
                    'fields' => 'ID'
                )),

                'meta_query' => array(

                    'relation' => 'AND',

                    array(
                        'key'   => 'wpm_profile_status',
                        'value' => 'approved'
                    )

                )

            );

            /*
            -----------------------------------------
            GENDER FILTER
            -----------------------------------------
            */

            if ( ! empty( $seeking ) ) {

                $args['meta_query'][] = array(

                    'key'   => 'wpm_gender',

                    'value' => $seeking,

                    'compare' => '='

                );

            }
            

            /*
-----------------------------------------
AGE FILTER
-----------------------------------------
*/

if ( ! empty( $age_from ) ) {

    $args['meta_query'][] = array(

        'key'     => 'wpm_age',

        'value'   => $age_from,

        'type'    => 'NUMERIC',

        'compare' => '>='

    );

}

if ( ! empty( $age_to ) ) {

    $args['meta_query'][] = array(

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

            $users = get_users( $args );

            $unique_users = array();
            $used_photo_ids = array();

            foreach ( $users as $user ) {

                $profile_photo_id = intval(
                    get_user_meta(
                        $user->ID,
                        'wpm_profile_photo',
                        true
                    )
                );

                if (
                    $profile_photo_id
                    &&
                    in_array( $profile_photo_id, $used_photo_ids, true )
                ) {
                    continue;
                }

                $unique_users[] = $user;

                if ( $profile_photo_id ) {
                    $used_photo_ids[] = $profile_photo_id;
                }

            }

            $users = array_slice(
                $unique_users,
                0,
                4
            );

            ?>

            <?php if ( $users ) : ?>

                <?php foreach ( $users as $user ) : ?>

                    <?php

                    $user_id = $user->ID;

                    include WPM_PLUGIN_PATH . 'templates/member-card-grid.php';

                    ?>

                <?php endforeach; ?>

            <?php else : ?>

                <div class="col-12">

                    <p>No members found.</p>

                </div>

            <?php endif; ?>

        </div>

        <!-- LOAD MORE -->
        <?php if(count($users) >= 4): ?>
        <div class="text-center mt-5">

            <button
                id="wpm-load-more"
                class="common-btn"
                data-page="2"
            >

                Load More

            </button>

        </div>
        <?php endif; ?>

    </div>

</section>

<?php get_footer(); ?>
