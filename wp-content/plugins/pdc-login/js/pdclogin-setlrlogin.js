jQuery(function($) {
	
	if ( $( "#main_role" ).length ) {
    	$( 'input[name="redirect_to"]').val('https://setlr.com/wp-login.php?checkemail=register&type=helper');
	}
	
    $('#login').on('change','#main_role', function(){
        var type = $(this).val();
        $( 'input[name="redirect_to"]').val('https://setlr.com/wp-login.php?checkemail=register&type='+type);
    });
});
