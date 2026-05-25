<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    return;
}

?>

<div
    class="wpm-chat-wrapper minimized"
>

    <!-- CHAT TOGGLE BUTTON -->

    <div class="wpm-chat-toggle">

    <div class="wpm-chat-toggle-icon">

        <i class="fa-solid fa-comments"></i>

    </div>

    <div class="wpm-chat-toggle-text">

        <span>
            Messages
        </span>

    </div>

    <span class="wpm-chat-notification">
        0
    </span>

</div>

    <!-- CHAT WINDOW -->

    <div class="wpm-chat-window">

    <!-- LEFT SIDEBAR -->

    <div class="wpm-chat-sidebar">

        <div class="wpm-chat-sidebar-top">

            <h3>
                Chats
            </h3>
            <button class="wpm-minimize-chat">
                    <i class="fa-solid fa-minus"></i>
                </button>

        </div>

        <div
            id="wpm-chat-users"
            class="wpm-chat-users"
        ></div>

    </div>

    <!-- RIGHT CHAT AREA -->

    <div class="wpm-chat-box" data-user-id="">

        <!-- HEADER -->

        <div class="wpm-chat-header">

            <div class="wpm-chat-header-user">
                <button class="wpm-mobile-back">

                    <i class="fa-solid fa-arrow-left"></i>

                </button>

                <img
                    src="<?php echo plugin_dir_url(
                        dirname(__FILE__)
                    ); ?>assets/images/default-profile.jpg"
                    class="wpm-chat-header-avatar"
                >

                <div>

                    <h4 class="wpm-chat-username">
                        Select Chat
                    </h4>

                    <span class="wpm-chat-status">
                        Select a conversation
                    </span>

                </div>

            </div>

            <div class="wpm-chat-header-actions">

                <!-- <button>
                    <i class="fa-solid fa-phone"></i>
                </button>

                <button>
                    <i class="fa-solid fa-video"></i>
                </button> -->

                <button class="wpm-minimize-chat">
                    <i class="fa-solid fa-minus"></i>
                </button>

            </div>

        </div>

        <!-- MESSAGES -->

        <div
            id="wpm-chat-messages"
            class="wpm-chat-messages"
        ></div>

        <!-- FOOTER -->

        <div class="wpm-chat-footer">

            <form id="wpm-chat-form">

                <div class="wpm-chat-input-wrap">

                    <label class="wpm-file-upload-btn">

                        <i class="fa-solid fa-paperclip"></i>

                        <input
                            type="file"
                            id="wpm-chat-file"
                            hidden
                        >

                    </label>

                    <input
                        type="text"
                        id="wpm-chat-message"
                        placeholder="Write a message..."
                        autocomplete="off"
                    >

                    <button
                        type="submit"
                        class="wpm-send-btn"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

    <!-- NOTIFICATION SOUND -->

    <audio
        id="wpm-chat-sound"
        preload="auto"
    >

        <source
            src="<?php echo plugin_dir_url(
                dirname(__FILE__)
            ); ?>sounds/notification.mp3"
            type="audio/mpeg"
        >

    </audio>

</div>
