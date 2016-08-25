//ouputs locale form fragement via ajax
//@version 0.2
//@todo: change on_change selector

jQuery(function($) {
    
    $("#content").on( 'change', "#to-lang",function(){
        
        var lang = $(this).val();
        var where = $(this).parent();
        
        var response = show_locales( lang, where );
        
    });
    
    function show_locales( lang, where ) {
            var data = {
                'action': 'pdcrequest_get_locales_for_request',
                'lang': lang,
                'nonce': $('#pdcrequestnonce').val()
            };
	
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                
                //remove existing locale
                $( ".setlr-locales-form" ).remove();
                //append response after select nativelang
                where.append(response);
            });
            
           
        }
});


