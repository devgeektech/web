<?php
/*-----------------------------------------------------------------------------------*/
/* Custom Post Type - Testimonials */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'pwd_add_testimonials' ) ) {
	function pwd_add_testimonials() {		
		$labels = array(
			'name' => _x( 'Testimonials', 'post type general name', 'tp' ),
			'singular_name' => _x( 'Testimonial', 'post type singular name', 'tp' ),
			'add_new' => _x( 'Add New', 'testimonials', 'tp' ),
			'add_new_item' => __( 'Add New Testimonial', 'tp' ),
			'edit_item' => __( 'Edit Testimonial', 'tp' ),
			'new_item' => __( 'New Testimonial', 'tp' ),
			'view_item' => __( 'View Testimonial', 'tp' ),
			'search_items' => __( 'Search Testimonials', 'tp' ),
			'not_found' =>  __( 'No Testimonials found', 'tp' ),
			'not_found_in_trash' => __( 'No Testimonials found in Trash', 'tp' ), 
			'parent_item_colon' => ''
		);
		
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => true,
			'exclude_from_search' => true, 
			'_builtin' => false,
			'show_ui' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_icon' => plugins_url('testimonials/images/testimonials-icon.png', dirname(__FILE__)),
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
		);
		
		register_post_type( 'testimonial', $args );

	} // End pwd_add_testimonials()
}

add_action( 'init', 'pwd_add_testimonials', 10 );

add_action( 'add_meta_boxes', 'pwd_add_custom_box' );
add_action( 'save_post', 'metabox_save', 1, 2 );
function pwd_add_custom_box(){
  add_meta_box('testimonials_details', __( 'Testimonials Settings', 'tp' ), 'testimonails_meta_box', 'testimonial', 'side', 'high');
}

function testimonails_meta_box(){
  include( dirname( __FILE__ ) . '/testimonials-metabox.php' );
}

function metabox_save( $post_id, $post ) {
  /** Run only on testimonials post type save */
	if ( 'testimonial' == $post->post_type ) {
		/** Verify the nonce */
	    if ( ! wp_verify_nonce( $_POST['testimonials_metabox_nonce'], 'testimonials_metabox_save' ) )
	        return;

	    /** Don't try to save the data under autosave, ajax, or future post */
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	    if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;

	    /** Check permissions */
	    if ( ! current_user_can( 'edit_post', $post_id ) )
	        return;

	    $tm_details = $_POST['tm'];

	    /** Store the custom fields */
	    foreach ( (array) $tm_details as $key => $value ) {
	        /** Save/Update/Delete */
	        if ( $value ) {
	            update_post_meta($post->ID, $key, $value);
	        } else {
	            delete_post_meta($post->ID, $key);
	        }
	    }
  }
}  
?>