jQuery(function($) {
    $('.search-field').autocomplete({
        // request contain information about input value (request.term)
        // response : function called for recuperate data
        source: function(request, response) {
            console.log('sending');
            $.ajax({
                dataType: 'json',
                url: AutocompleteSearch.ajax_url,
                data: {
                    term: request.term,
                    action: 'autocompleteSearch',
                    security: AutocompleteSearch.ajax_nonce,
                },
                success: function(data) {
                    console.log('reponse');
                    response(data);
                }
            });
        },
    });
});