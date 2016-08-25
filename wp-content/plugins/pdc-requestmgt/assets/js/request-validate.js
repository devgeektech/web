// @version 0.4
// @since pdcrequest 0.9.9

jQuery(function($) {


        function validate_form() {
            var form = $("#pdc_request").serialize();
			var formData = new FormData($('#pdc_request')[0]);
            var data = {
                'action': 'pdcrequest_validate_request',
                'nonce': $('#pdcrequestnonce').val(),
                'form': form		
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            $.post(pdcrequest.ajaxurl, data, function(response) {
                $("#pdc_request").append(response);
                
            });
            
           
        }    
        
        
        function validate_full_form() {
            var form = $("#pdc_request").serialize();
			var formData = new FormData($('#pdc_request')[0]);
            var data = {
                'action': 'pdcrequest_validate_full_request',
                'nonce': $('#pdcrequestnonce').val(),
                'form': form
			
            };
            
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(pdcrequest.ajaxurl, data, function(response) {
                $("#pdc_request").hide();
                $(".entry-content").append(response);
                get_client_token();
            });  
        }   
        
        
        
        function get_client_token() {
            var clientToken;

            //Firstly we generate a client token
            var data = {
                    'action': 'get_braintree_client_token'
                };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations

            $.post(pdcrequest.ajaxurl, data, function(response) {
                    clientToken = response;
                    //secondly we send the clientToken to BrainTree
                    braintree.setup(clientToken, "dropin", { container: "payment-method" } );
            });
        }
        
   
        
        $("#page").on( 'click', '#submit', function(e) {
            e.preventDefault();
            $( '#submit').hide();
            validate_form();
            $('input[name="action"]').val( 'pdcrequest_validate_full_request');
        });
        
        $("#page").on( 'click', '#submit2', function(e) {
            var braintree;
            e.preventDefault();
            validate_full_form();  
        });
   // }

    $( "#page").on( 'click','#register', function(e) {
        e.preventDefault();
        
        var user_email = $('#user_email').val();
        var user_pass = $('#user_pass').val();
        var user_pass2 = $('#user_pass2').val();
        var nickname = $('#nickname').val();
        var nonce = $('#registernonce').val();
        var total = $('#total').val();
        
        var data = {
            'user_email': user_email,
            'nickname': nickname,
            'user_pass': user_pass,
            'user_pass2': user_pass2,
            'action': 'pdcrequest_register_customer',
            'nonce': nonce,
            'total': total
        };
      
        $.post(pdcrequest.ajaxurl, data, function(response) {
            $(".setlr-register").hide();
            $(".setlr-login").hide();
            $("#pdc_request").append(response);
        });
    });
    
    $("#page").on( 'click', '#login', function(e) {
        e.preventDefault();
        var user_login = $('#user_login').val();
        var user_pwd = $('#user_pwd').val();
        var nonce = $('#loginnonce').val();
        var total = $('#total').val();
        
        var data = {
            'user_login': user_login,
            'user_pwd': user_pwd,
            'nonce': nonce,
            'total': total,
            'action': 'pdcrequest_login_customer'
        };
        
        $.post(pdcrequest.ajaxurl, data, function(response) {
            $(".setlr-register").hide();
            $(".setlr-login").hide();
            $("#pdc_request").append(response);
        });
        
    });
});