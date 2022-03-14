jQuery(function ($) {
    const parent = $('.search-field').parent();

    $('.search-field').autocomplete({
        source: function (request, response) {
            $.ajax({
                dataType: 'json',
                url: AutocompleteSearch.ajax_url,
                data: {
                    term: request.term,
                    action: 'autocompleteSearch',
                    security: AutocompleteSearch.ajax_nonce,
                },
                success: function (data) {
                    response(data);
                }
            });
        },
    }).on("keydown", e => {
        // deleting everything in the input
        const key = e.keyCode || e.charCode;
        if (key === 8 || key === 46) {
            $('.search-field').val("");
        }
    });
    parent.on("submit", (e) => {
        // change the link
        e.preventDefault();
        window.location = encodeURI(parent.attr("action").replace(/user_id=.*?&/g, "user_id=" + $('.search-field').val() + "&"));
    });
});