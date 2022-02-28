console.log("ajax");
jQuery(function($) {
    $('.search-field').autocomplete({
        source: function(request, response) {
            console.log("requete");
            $.ajax({
                dataType: 'json',
                url: AutocompleteSearch.ajax_url,
                data: {
                    term: request.term,
                    action: 'autocompleteSearch',
                    security: AutocompleteSearch.ajax_nonce,
                },
                success: function(data) {
                    console.log(data);
                    response(data);
                }
            });
        },
    });
});