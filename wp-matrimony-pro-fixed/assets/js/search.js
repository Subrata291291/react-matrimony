jQuery(document).ready(function ($) {

    /*
    -----------------------------------------
    MEMBER SEARCH
    -----------------------------------------
    */

    $(document).on('submit', '#wpm-search-form', function (e) {

        e.preventDefault();

        let form = $(this);

        let button = form.find('button');

        button.prop('disabled', true);

        /*
        -----------------------------------------
        GET VALUES
        -----------------------------------------
        */

        let gender = form.find(
            '[name="gender"]'
        ).val();

        let seeking = form.find(
            '[name="seeking"]'
        ).val();

        let age_from = form.find(
            '[name="age_from"]'
        ).val();

        let age_to = form.find(
            '[name="age_to"]'
        ).val();

        /*
        -----------------------------------------
        AJAX SEARCH
        -----------------------------------------
        */

        $.ajax({

            url: wpm_ajax.ajax_url,

            type: 'POST',

            data: {

                action: 'wpm_search_members',

                security: wpm_ajax.nonce,

                gender: gender,

                seeking: seeking,

                age_from: age_from,

                age_to: age_to

            },

            beforeSend: function () {

                $('#wpm-members-results').html(

                    '<div class=\"col-12 text-center\">Searching...</div>'

                );

            },

            success: function (response) {

                button.prop('disabled', false);

                $('#wpm-members-results').html(
                    response
                );

                let resultCount =
                    $('#wpm-members-results .wpm-member-item').length;

                let loadMoreButton =
                    $('#wpm-load-more');

                loadMoreButton
                    .data('page', 2)
                    .text('Load More')
                    .prop('disabled', false);

                if (resultCount >= 4) {
                    loadMoreButton.closest('.text-center').show();
                } else {
                    loadMoreButton.closest('.text-center').hide();
                }

            },

            error: function () {

                button.prop('disabled', false);

                $('#wpm-members-results').html(

                    '<div class=\"col-12 text-center\">Something went wrong</div>'

                );

            }

        });

    });

    /*
-----------------------------------------
HOME BANNER SEARCH
-----------------------------------------
*/

$(document).on('submit', '#wpm-home-search-form', function (e) {

    e.preventDefault();

    let form = $(this);

    let gender = form.find(
        '[name="gender"]'
    ).val();

    let seeking = form.find(
        '[name="seeking"]'
    ).val();

    let age_from = form.find(
        '[name="age_from"]'
    ).val();

    let age_to = form.find(
        '[name="age_to"]'
    ).val();

    /*
    -----------------------------------------
    REDIRECT TO MEMBERS PAGE
    -----------------------------------------
    */

    let url =
        form.attr('action') +
        '?gender=' + encodeURIComponent(gender) +
        '&seeking=' + encodeURIComponent(seeking) +
        '&age_from=' + encodeURIComponent(age_from) +
        '&age_to=' + encodeURIComponent(age_to);

    window.location.href = url;

});

});
