
// @version 0.3
// @since pdcrequest 0.9.9

jQuery(function($) {
    console.log( 'request form fragment loaded');
    $("#submit-form").hide();
    var where = $("#container");
    
    show_form_fragment( 'translation', where );
    
    $("#submit-form").show();
   
    
    $("#page").on( 'change', "#setlr-service",function(){
        
        $("#submit-form").hide();
        var where = $("#container");
        var type = $("#setlr-service").val();
        console.log(type);
        show_form_fragment( type, where );
        $("#submit-form").show();
    });
    
    
     function show_form_fragment( type, where ) {
            var data = {
                'action': 'pdcrequest_get_request_form_by_type',
                'type': type,
                'nonce': $('#pdcrequestnonce').val()
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                
                //remove existing locale
                where.empty();
                
                //append response after select nativelang
                where.append(response);
                
            });
            
           
        }
            
    
});

