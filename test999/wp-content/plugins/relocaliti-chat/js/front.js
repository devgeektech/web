(function($) {
   window.get_updated_conversation = function() {
	   var user1 = $( '#user1' ).val();
	   var user2 = $( '#user2' ).val();
	   var last_li = $( '.relochat-conversation li' ).last();
	   var last_time = $( '.relochat-conversation li:last-child .relochat-time' ).text();
	   $.post( relochat.ajaxurl, 
	   				{ 
						action: 'relochat_get_updated_conversation', 
						user1_id: user1, 
						user2_id: user2, 
						since: last_time 
					}, 
		/* success */
		function( response ) {
			var ul = $('.relochat-conversation');
			ul.append( response );
		});
	//set big enough interval to avoid db collision
	setInterval( get_updated_conversation, 120000 );
	return this;
   }
   $( 'body' ).on( 'submit', '.relochat-form', function( e ) {
	   e.preventDefault();
	   var form = $(".relochat-form").serialize();
	   
	   $.post( relochat.ajaxurl, { action: 'relochat-new', data: form }, function( response ) {
		   //empty #message textarea
		   $( '#message').val('');
		   //append new message to the conversation
		   window.setTimeout( get_updated_conversation(), '15000' );
	   });
   });
   get_updated_conversation();
})(jQuery);