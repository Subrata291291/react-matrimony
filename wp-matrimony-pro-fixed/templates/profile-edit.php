<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {

    wp_redirect(
        home_url()
    );

    exit;

}

$user_id = get_current_user_id();

/*
-----------------------------------------
GET USER DATA
-----------------------------------------
*/

$full_name = get_user_meta(
    $user_id,
    'full_name',
    true
);

$gender = get_user_meta(
    $user_id,
    'wpm_gender',
    true
);

$looking_for = get_user_meta(
    $user_id,
    'wpm_looking_for',
    true
);

$dob = get_user_meta(
    $user_id,
    'wpm_dob',
    true
);

$religion = get_user_meta(
    $user_id,
    'wpm_religion',
    true
);

$profession = get_user_meta(
    $user_id,
    'wpm_profession',
    true
);

$education = get_user_meta(
    $user_id,
    'wpm_education',
    true
);

$country = get_user_meta(
    $user_id,
    'wpm_country',
    true
);

$state = get_user_meta(
    $user_id,
    'wpm_state',
    true
);

$city = get_user_meta(
    $user_id,
    'wpm_city',
    true
);

$about = get_user_meta(
    $user_id,
    'wpm_about',
    true
);

$hobbies = get_user_meta(
    $user_id,
    'wpm_hobbies',
    true
);

$partner_expectation = get_user_meta(
    $user_id,
    'wpm_partner_expectation',
    true
);

$profile_photo = wpm_get_profile_photo(
    $user_id
);

$cover_photo = wpm_get_cover_photo(
    $user_id
);

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

<div class="wpm-profile-edit-page py-5 mt-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-12">

                <div class="wpm-profile-card">

                    <h2 class="mb-4">

                        Edit Profile

                    </h2>

                    <form
                        id="wpm-profile-form"
                        enctype="multipart/form-data"
                    >

                        <div class="row">

                            <!-- FULL NAME -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Full Name

                                    </label>

                                    <input
                                        type="text"
                                        name="full_name"
                                        class="form-control"
                                        value="<?php echo esc_attr( $full_name ); ?>"
                                    >

                                </div>

                            </div>

                            <!-- GENDER -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Gender

                                    </label>

                                    <select
                                        name="gender"
                                        class="form-control"
                                    >

                                        <option value="man"
                                            <?php selected( $gender, 'man' ); ?>
                                        >

                                            Man

                                        </option>

                                        <option value="women"
                                            <?php selected( $gender, 'women' ); ?>
                                        >

                                            Woman

                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- LOOKING FOR -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Looking For

                                    </label>

                                    <select
                                        name="looking_for"
                                        class="form-control"
                                    >

                                        <option value="man"
                                            <?php selected( $looking_for, 'man' ); ?>
                                        >

                                            Man

                                        </option>

                                        <option value="women"
                                            <?php selected( $looking_for, 'women' ); ?>
                                        >

                                            Woman

                                        </option>

                                    </select>

                                </div>

                            </div>

                            <!-- DOB -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Date of Birth

                                    </label>

                                    <input
                                        type="date"
                                        name="dob"
                                        class="form-control"
                                        value="<?php echo esc_attr( $dob ); ?>"
                                    >

                                </div>

                            </div>

                            <!-- RELIGION -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>Religion</label>

                                    <?php
                                    $religions = [
                                        'Hindu',
                                        'Muslim',
                                        'Christian',
                                        'Sikh',
                                        'Buddhist',
                                        'Jain',
                                        'Parsi',
                                        'Other'
                                    ];
                                    ?>

                                    <select
                                        name="religion"
                                        class="form-control"
                                    >

                                        <option value="">
                                            Select Religion
                                        </option>

                                        <?php foreach($religions as $item): ?>

                                            <option
                                                value="<?php echo esc_attr($item); ?>"
                                                <?php selected($religion, $item); ?>
                                            >

                                                <?php echo esc_html($item); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <!-- PROFESSION -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>Profession</label>

                                    <?php
                                    $professions = [
                                        'Software Engineer',
                                        'Doctor',
                                        'Teacher',
                                        'Business',
                                        'Designer',
                                        'Government Job',
                                        'Lawyer',
                                        'Student',
                                        'Makeup Artist',
                                        'Photographer',
                                        'Marketing',
                                        'Other'
                                    ];
                                    ?>

                                    <select
                                        name="profession"
                                        class="form-control"
                                    >

                                        <option value="">
                                            Select Profession
                                        </option>

                                        <?php foreach($professions as $item): ?>

                                            <option
                                                value="<?php echo esc_attr($item); ?>"
                                                <?php selected($profession, $item); ?>
                                            >

                                                <?php echo esc_html($item); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <!-- EDUCATION -->

                            <div class="col-md-6">

                                <div class="form-group">

    <label>Education</label>

    <?php
    $education_options = [
        'High School',
        'Secondary School',
        'Higher Secondary',
        'Diploma',
        'ITI',
        'Bachelor of Arts (BA)',
        'Bachelor of Science (BSc)',
        'Bachelor of Commerce (BCom)',
        'Bachelor of Technology (BTech)',
        'Bachelor of Engineering (BE)',
        'Bachelor of Computer Applications (BCA)',
        'Bachelor of Business Administration (BBA)',
        'Bachelor of Medicine and Bachelor of Surgery (MBBS)',
        'Bachelor of Dental Surgery (BDS)',
        'Bachelor of Pharmacy (BPharm)',
        'Bachelor of Architecture (BArch)',
        'Bachelor of Education (BEd)',
        'Master of Arts (MA)',
        'Master of Science (MSc)',
        'Master of Commerce (MCom)',
        'Master of Technology (MTech)',
        'Master of Engineering (ME)',
        'Master of Computer Applications (MCA)',
        'Master of Business Administration (MBA)',
        'Master of Pharmacy (MPharm)',
        'Master of Education (MEd)',
        'Doctor of Philosophy (PhD)',
        'Chartered Accountant (CA)',
        'Company Secretary (CS)',
        'ICWA / CMA',
        'LLB',
        'LLM',
        'Nursing',
        'Fashion Designing',
        'Hotel Management',
        'Animation & Multimedia',
        'Aviation',
        'Other'
    ];
    ?>

    <select name="education" class="form-control">
        <option value="">Select Education</option>

        <?php foreach ( $education_options as $option ) : ?>
            <option value="<?php echo esc_attr( $option ); ?>" 
                <?php selected( $education, $option ); ?>>
                <?php echo esc_html( $option ); ?>
            </option>
        <?php endforeach; ?>

    </select>

</div>

                            </div>

                            <!-- COUNTRY -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>Country</label>

                                    <?php
                                    $countries = [
                                        'India',
                                    ];
                                    ?>

                                    <select
                                        name="country"
                                        class="form-control"
                                    >

                                        <option value="">
                                            Select Country
                                        </option>

                                        <?php foreach($countries as $item): ?>

                                            <option
                                                value="<?php echo esc_attr($item); ?>"
                                                <?php selected($country, $item); ?>
                                            >

                                                <?php echo esc_html($item); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <!-- STATE -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>State</label>

                                    <?php
                                    $states = [
                                        'Andhra Pradesh',
                                        'Arunachal Pradesh',
                                        'Assam',
                                        'Bihar',
                                        'Chhattisgarh',
                                        'Goa',
                                        'Gujarat',
                                        'Haryana',
                                        'Himachal Pradesh',
                                        'Jharkhand',
                                        'Karnataka',
                                        'Kerala',
                                        'Madhya Pradesh',
                                        'Maharashtra',
                                        'Manipur',
                                        'Meghalaya',
                                        'Mizoram',
                                        'Nagaland',
                                        'Odisha',
                                        'Punjab',
                                        'Rajasthan',
                                        'Sikkim',
                                        'Tamil Nadu',
                                        'Telangana',
                                        'Tripura',
                                        'Uttar Pradesh',
                                        'Uttarakhand',
                                        'West Bengal',

                                        // Union Territories
                                        'Andaman and Nicobar Islands',
                                        'Chandigarh',
                                        'Dadra and Nagar Haveli and Daman and Diu',
                                        'Delhi',
                                        'Jammu and Kashmir',
                                        'Ladakh',
                                        'Lakshadweep',
                                        'Puducherry'
                                    ];
                                    ?>

                                    <select
                                        name="state"
                                        class="form-control"
                                    >

                                        <option value="">
                                            Select State
                                        </option>

                                        <?php foreach($states as $item): ?>

                                            <option
                                                value="<?php echo esc_attr($item); ?>"
                                                <?php selected($state, $item); ?>
                                            >

                                                <?php echo esc_html($item); ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <!-- CITY -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        City

                                    </label>

                                    <input
                                        type="text"
                                        name="city"
                                        class="form-control"
                                        value="<?php echo esc_attr( $city ); ?>"
                                    >

                                </div>

                            </div>

                            <!-- ABOUT -->

                            <div class="col-12">

                                <div class="form-group">

                                    <label>

                                        About Me

                                    </label>

                                    <textarea
                                        name="about"
                                        class="form-control"
                                    ><?php echo esc_textarea( $about ); ?></textarea>

                                </div>

                            </div>

                            <!-- HOBBIES -->

                            <div class="col-12">

                                <div class="form-group">

                                    <label>

                                        Hobbies

                                    </label>

                                    <textarea
                                        name="hobbies"
                                        class="form-control"
                                    ><?php echo esc_textarea( $hobbies ); ?></textarea>

                                </div>

                            </div>

                            <!-- EXPECTATION -->

                            <div class="col-12">

                                <div class="form-group">

                                    <label>

                                        Partner Expectations

                                    </label>

                                    <textarea
                                        name="partner_expectation"
                                        class="form-control"
                                    ><?php echo esc_textarea( $partner_expectation ); ?></textarea>

                                </div>

                            </div>

                            <!-- PROFILE PHOTO -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Profile Photo

                                    </label>

                                    <input
                                        type="file"
                                        id="profile_photo"
                                        name="profile_photo"
                                        class="form-control"
                                    >

                                    <img
                                        id="wpm-profile-preview"
                                        src="<?php echo esc_url( $profile_photo ); ?>"
                                    >

                                </div>

                            </div>

                            <!-- COVER PHOTO -->

                            <div class="col-md-6">

                                <div class="form-group">

                                    <label>

                                        Cover Photo

                                    </label>

                                    <input
                                        type="file"
                                        id="cover_photo"
                                        name="cover_photo"
                                        class="form-control"
                                    >

                                    <div
                                        id="wpm-cover-preview"
                                        style="background-image:url('<?php echo esc_url( $cover_photo ); ?>')"
                                    ></div>

                                </div>

                            </div>

                            <!-- GALLERY -->

                            <div class="col-12">

                                <div class="form-group">

                                    <label>

                                        Gallery Images

                                    </label>

                                    <input
                                        type="file"
                                        id="gallery"
                                        name="gallery[]"
                                        multiple
                                        class="form-control"
                                    >

                                    <div
                                        id="wpm-gallery-preview"
                                    ></div>

                                </div>

                            </div>

                            <!-- BUTTON -->

                            <div class="col-12">

                                <button
                                    type="submit"
                                    id="wpm-save-profile"
                                    class="common-btn"
                                >

                                    Save Profile

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>