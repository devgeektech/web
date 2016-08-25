// @version 0.2
// @since pdcrequest 0.9.9

jQuery(function($) {


        function validate_form() {
            var form = $("#pdc_request_new").serialize();
            var data = {
                'action': 'pdcrequest_validate_request_profile',
                'nonce': $('#pdcrequestnonce').val(),
                'form': form
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                
                
                //append response after
                $("#pdc_request_new").append(response);
                
            });
            
           
        }    
        
        function validate_field( fieldname, type ) {
            switch (type) { 
                case 'text': 
                        alert('text');
                        break;
                case 'checkbox': 
                        alert('checkbox');
                        break;
                case 'float': 
                        alert('float');
                        break;		
                case 'array': 
                        alert('array');
                        break;
                default:
                        alert('Nobody Wins!');
            }
        }
        
        function validate_full_form() {
            var form = $("#pdc_request_new").serialize();
            var data = {
                'action': 'pdcrequest_validate_full_request_profile',
                'nonce': $('#pdcrequestnonce').val(),
                'form': form
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                  
                //append response after
                $("#pdc_request_new").empty();
                $("#pdc_request_new").parent().append(response);
                //$("#pdc_request_new").remove();
                
            });
            
           
        }    
        
    //if ( $("input[name='_wp_http_referer']").val() === '/add-project-profile' || $("input[name='_wp_http_referer']").val() === '/setlr/add-project-profile' ) {
       /* $("#page").on( 'change', '.field', function(e) {
            var fieldname = $(this).name();
            var fieldtype = $(this).attr("type");
            validate_field( fieldname, fieldtype );
        });
        */
        
        $("#page").on( 'click', '#submit', function(e) {
            e.preventDefault();
            $( '#submit').hide();
            validate_form();
            $('input[name="action"]').val( 'pdcrequest_validate_full_request_profile');
        });
        
        $("#page").on( 'click', '#submit2', function(e) {
            e.preventDefault();
            validate_full_form();
        })
   // }

});