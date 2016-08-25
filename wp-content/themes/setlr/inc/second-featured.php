<?php

/**
 * Add a second featured image to post
 * 
 */



/**
 * Calls the class on the post edit screen.
 */
function call_Second_Featured() {
    new Second_Featured();
}



if ( is_admin() ) {
    add_action( 'load-post.php', 'call_Second_Featured' );
    add_action( 'load-post-new.php', 'call_Second_Featured' );
    add_action( 'admin_enqueue_scripts', array( 'Second_Featured', 'admin_scripts' ) );
    add_action( 'wp_ajax_setlr_remove_second_featured', array( 'Second_Featured', 'remove_image' ) ); 
}

/** 
 * The Class.
 */
class Second_Featured {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
                add_image_size('setlr_second_featured', 1280, 300, true );
                add_image_size('setlr_second_featured_preview', 427, 100, true );
                add_filter('attachment_fields_to_edit', array( $this,'action_button'), 20, 2 );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            $post_types = array( 'post' );     //limit meta box to certain post types
			
            if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'second_featured'
					, __( 'Second Featured Image', 'setlr' )
					, array( $this, 'render_meta_box_content' )
					, $post_type
					, 'side'
					, 'low'
				);
            }
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['second_featured_nonce'] ) )
			return $post_id;

		$nonce = $_POST['second_featured_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'setlr_featured_image' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */
                 
		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['upload_image'] );

		// Update the meta field.
		update_post_meta( $post_id, '_second_featured', $mydata );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'setlr_featured_image', 'second_featured_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$attachment_id = get_post_meta( $post->ID, '_second_featured', true );

		// Display the form, using the current value.
		
                if ( ! empty( $attachment_id ) && absint($attachment_id)) :
                    echo '<div id="second_featured_frame" class="">';
                    echo wp_get_attachment_image( $attachment_id, 'setlr_second_featured_preview', false );
                   
                    echo '<p class="hide-if-no-js">';
                    echo '<a href="#" id="remove-second-image">' . __( 'remove second featured image', 'setlr' ) . '</a>';
                    echo '</p>';
                     echo '</div>';
                else :
                    echo '<p class="hide-if-no-js">';
                    echo '<a id="upload_image_button" href="#">' . __( 'add second featured image', 'setlr' ) . '</a>';
                    echo '</p>';
                endif;
		echo '<input id="upload_image" type="hidden" name="upload_image" value="" />';
                
	}
        
        
        public static function admin_scripts() {    
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            
            wp_enqueue_script('second-upload', get_template_directory_uri() . '/inc/second-upload.js', array('jquery','media-upload','thickbox'));
            
            wp_enqueue_style('thickbox');
        }

    
        public function action_button($form_fields, $post) {
 
        $send = "<input type='submit' class='button' name='send[$post->ID]' value='" . esc_attr__( 'Use as Second Featured' ) . "' />";
 
        $form_fields['buttons'] = array('tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$send</td></tr>\n");
        //$form_fields['context'] = array( 'input' => 'hidden', 'value' => 'shiba-gallery-default-image' );
        return $form_fields;
    }
    
        public static function remove_image() {
            //security
            check_ajax_referer('setlr_featured_image', 'nonce', true );
            
            
            $post_id = ( absint($_POST['post_id'] ) ) ? absint( $_POST['post_id']) : '';
            if ( absint( $post_id ) ) :
                delete_post_meta($post_id, '_second_featured');
            endif;
            
            echo true;
            exit();
        }
        
        public static function show_second_featured_image( $post_id, $size = 'full' ) {
            $attachment_id = get_post_meta( $post_id, '_second_featured', true );
            switch ( $size ) :
                case 'thumbnail':
                    echo wp_get_attachment_image( $attachment_id, 'setlr_second_featured_preview', false );
                    break;
                default:
                    echo wp_get_attachment_image( $attachment_id, 'setlr_second_featured', false );
            endswitch;
        }
}
