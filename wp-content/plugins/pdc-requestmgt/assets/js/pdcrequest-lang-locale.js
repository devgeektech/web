//ouputs locale form fragement via ajax
//@version 0.4

jQuery(function($) {
    
    $("#profile-update, #your-profile").on( 'change', "select[name='nativelang']",function(){
        var lang = $(this).val();
        
        var where = $(this).parent().parent();
        
        
        show_locales( lang, where );
        
    });
    
    function show_locales( lang, where ) {
            var data = {
                'action': 'pdcrequest_get_locales',
                'lang': lang,
                'nonce': $('#pdcrequestnonce').val()
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                
                //remove existing locale
                var locale = $( ".setlr-locales-form" );
                var locale_parent = $( ".setlr-language-form" );
                locale.remove();
                //append response after select nativelang
                locale_parent.append(response);
            });
            
           
        }
});


