jQuery(document).ready(function($) {
    // Inside of this function, $() will work as an alias for jQuery()
    // and other libraries also using $ will not be accessible under this shortcut
	
	$( '#page' ).on( 'submit', '#pdc_request_new', function() {
		
		var formdata = $(this).serialize();
		var data = {
		'action': 'pdc_new_request',
		'formdata': formdata
		};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	$.post(pdcrequest.ajaxurl, data, function(response) {
		alert('Got this from the server: ' + response);
	});
	});
	
	$( '#page' ).on( 'submit', '#pdc_application_new', function() {
		
		var formdata = $(this).serialize();
		var data = {
		'action': 'pdc_new_application',
		'formdata': formdata
		};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	$.post(pdcrequest.ajaxurl, data, function(response) {
		alert('Got this from the server: ' + response);
		});
	return false;
	});
	
	$( '#profile-update' ).hide();
	
	$( '#page' ).on( 'click', '#profile-toggle', function() {
		$( '#profile-update' ).toggle();
	});
	
	/*
	$( '#page' ).on( 'submit', '#profile-update', function() {
		
		var formdata = $(this).serialize();
		var filedata = $( "#user-photo").val();
		console.log(filedata);
		var data = {
		'action': 'pdc_update_profile',
		'formdata': formdata,
		'file': filedata
		};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	$.post(pdcrequest.ajaxurl, data, function(response) {
		alert('Got this from the server: ' + response);
		});
	$( '#profile-update' ).hide();
		$( '#profile-toggle' ).show();
	return false;
	});
*/
});