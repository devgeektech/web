/*
 * @version 0.9
 */
jQuery(document).ready(function($) {
    function get_client_token() {
        var clientToken;
        
        //Firstly we generate a client token
	var data = {
		'action': 'get_braintree_client_token'
            };
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
        
	$.post(pdcrequest.ajaxurl, data, function(response) {
		clientToken = response;
                console.log( 'client token='. response);
                //secondly we send the clientToken to BrainTree
                braintree.setup(clientToken, "dropin", { container: "payment-method" } );
	});
    }
});