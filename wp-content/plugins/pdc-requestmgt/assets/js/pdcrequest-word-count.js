jQuery(document).ready(function($) {
    // Inside of this function, $() will work as an alias for jQuery()
    // and other libraries also using $ will not be accessible under this shortcut
	
        
        
        $( '#page' ).on( 'change', "#pdc_request textarea", function() {
            update_count(); 
	});
        
        
        function update_count() {
            var data = {
                'action': 'pdcrequest_word_count',
                'content': $("#form_content").val(),
                'lang': $('#from-lang').val(),
                'nonce': $('#pdcrequestnonce').val()
            };
	
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            $.post(pdcrequest.ajaxurl, data, function(response) {
                //show response in total
                $( '#pdcrequest-word-count-total').text(response);
            });
        }
});