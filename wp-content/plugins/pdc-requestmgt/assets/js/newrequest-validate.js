jQuery(document).ready(function($) {
    // Inside of this function, $() will work as an alias for jQuery()
    // and other libraries also using $ will not be accessible under this shortcut
	
	
	
	$( '#profile-update' ).hide();
	
	$( '#page' ).on( 'click', '#profile-toggle', function() {
            $( '#profile-update' ).toggle();
	});
        
        $( '#page').on( 'click', '#setlr-cancel', function() {
            $( '#profile-update' ).toggle();
        });

});