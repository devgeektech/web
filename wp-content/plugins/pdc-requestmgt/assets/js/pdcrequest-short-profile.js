// @version 0.6
jQuery(function($) {
    
    $("#page").on( 'click', ".setlr-user",function(){   
        var user_id = $(this).data('user_id');
        var where = $(this).parent();
        show_profile( user_id, where );
    });
    
    
    $("#page").on( 'change', '#services input[value="question"]', function() {
        var user_id = $(this).data('user_id');
        var where = $("#container-countries");
        
        show_question_countries( user_id, where );
    })
    
    function show_profile( user_id, where ) {
            var data = {
                'action': 'pdcrequest_get_short_profile',
                'nonce': pdcrequest.ajax_nonce,
                'user_id': user_id
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                
                //remove existing locale
                $( '.vcard').hide();
                //append response after select nativelang
                where.append(response);
            });
           
        }
        
    function show_question_countries( user_id, where ) {
            var data = {
                'action': 'pdcrequest_question_countries_form',
                'nonce': pdcrequest.ajax_nonce,
                'user_id': user_id
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                console.log(response);
                //append response at end of form
                where.append(response);
            });
           
        }
});


