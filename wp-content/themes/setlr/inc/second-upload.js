/* 
 * add image upload functionalities to second-feautred
 *
 */
jQuery(document).ready(function($){
	var _custom_media = true,
	_orig_send_attachment = wp.media.editor.send.attachment;

	$('#upload_image_button').click(function(e) {
		var send_attachment_bkp = wp.media.editor.send.attachment;
		var button = $(this);
		var id = button.attr('id').replace('_button', '');
		_custom_media = true;
		wp.media.editor.send.attachment = function(props, attachment){
			if ( _custom_media ) {
				$("#"+id).val(attachment.id);
			} else {
				return _orig_send_attachment.apply( this, [props, attachment] );
			};
		}

		wp.media.editor.open(button);
		return false;
	});

	$('.add_media').on('click', function(){
		_custom_media = false;
	});
        
        $('#remove-second-image').click(function(e){
            var data = {
                'action': 'setlr_remove_second_featured',
                'nonce': $('#second_featured_nonce').val(),
                'post_id': $('#post_ID').val()
            };
	
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            
            $.post(ajaxurl, data, function(response) {
                $('#second_featured_frame').addClass('hidden');
            });
        });
});