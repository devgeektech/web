//ouputs locale form fragement via ajax
//@version 0.4

jQuery(function($) {
    
    $("#profile-update, #your-profile").on( 'change', "select[name='nativelang']",function(){
        var lang = $(this).val();
        
        var where = $(this).parent();
        
        
        show_locales( lang, where );
        
    });
    
    function show_locales( lang, where ) {
            var data = {
                'action': 'pdcrequest_get_locales',
                'lang': lang,
                'nonce': $('#pdcrequestnonce').val()
            };
            
            // ajaxurl is defined as global in admin
            
            $.post(ajaxurl, data, function(response) {
                console.log(response);
                //remove existing locale
                var locale = $( ".setlr-locales-form" );
                
                locale.remove();
                //append response after select nativelang
                where.append(response);
            });
            
           
        }
});


