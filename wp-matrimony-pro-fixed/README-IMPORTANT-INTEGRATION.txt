=========================================
IMPORTANT INTEGRATION STEPS
=========================================

1. FLUSH REWRITE RULES
-----------------------------------------

After activating plugin:

Go to:

Settings → Permalinks

Click:

Save Changes

IMPORTANT

Otherwise:

/profile/john

will not work.


=========================================
2. UPDATE YOUR LOGIN/REGISTER FORM
=========================================

CHANGE:

<form action="">

TO:

<form id="wpm-login-form">

AND

<form action="">

TO:

<form id="wpm-register-form">


=========================================
3. FIX INPUT NAMES
=========================================

LOGIN FORM:

-----------------------------------------

<input type="text" name="username">

<input type="password" name="password">

<input type="checkbox" name="remember">


REGISTER FORM:

-----------------------------------------

<input type="text" name="username">

<input type="email" name="email">

<input type="password" name="password">

<select name="gender">


=========================================
4. HOME MEMBER SECTION
=========================================

CHANGE:

<div class="row">

TO:

<div class="row" id="wpm-members-wrapper">


=========================================
5. HOME SEARCH FORM
=========================================

CHANGE:

<form action="">

TO:

<form id="wpm-home-search-form">


=========================================
6. SEARCH FIELD NAMES
=========================================

FIRST SELECT:

name="gender"

SECOND SELECT:

name="seeking"

THIRD SELECT:

name="age_from"

FOURTH SELECT:

name="age_to"


=========================================
7. ADD CHAT TEMPLATE
=========================================

Before:

<?php get_footer(); ?>

ADD:

<?php

if (
    file_exists(
        WPM_PLUGIN_PATH .
        'templates/chat-box.php'
    )
) {

    include
    WPM_PLUGIN_PATH .
    'templates/chat-box.php';

}

?>


=========================================
8. CREATE DEFAULT IMAGES
=========================================

CREATE:

assets/images/


ADD:

default-profile.jpg

default-cover.jpg


=========================================
9. IMPORTANT SECURITY
=========================================

Make uploads folder protected.

Recommended later:

- image moderation
- spam prevention
- rate limiting
- email verification


=========================================
10. FUTURE FEATURES READY
=========================================

System already ready for:

- premium memberships
- profile boost
- verified badge
- advanced filters
- notifications
- video call
- mobile app api


=========================================
11. PROFILE EDIT PAGE
=========================================

Create a page:

Edit Profile

Add shortcode later OR include:

templates/profile-edit.php


=========================================
12. MEMBERS PAGE
=========================================

Now working:

/members


=========================================
13. PROFILE PAGE
=========================================

Now working:

/profile/username


=========================================
DONE
=========================================