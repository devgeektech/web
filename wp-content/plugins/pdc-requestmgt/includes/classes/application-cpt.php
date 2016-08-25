<?php
/**
 * Application-CPT
 * set up application Custom Post TYpe
 */
 
class Application_CPT {
	
	public function __construct() {
		$this->create();
	}
	
	public static function create() {
		$labels = array(
			'name'               => _x( 'Applications', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Application', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Applications', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Application', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'application', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Application', 'pdcrequest' ),
			'new_item'           => __( 'New Application', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Application', 'pdcrequest' ),
			'view_item'          => __( 'View Application', 'pdcrequest' ),
			'all_items'          => __( 'All Applications', 'pdcrequest' ),
			'search_items'       => __( 'Search Applications', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Applications:', 'pdcrequest' ),
			'not_found'          => __( 'No applications found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No Applications found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'application' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' )
		);
	
		register_post_type( 'application', $args );
	}
	
	
	public static function mark_as_selected( $post ) {
		
		update_post_meta( $post->ID, 'pdcrequest_selected', 'selected' );
		update_post_meta( $post->post_parent, 'pdcrequest_closed', 'closed' );
		do_action( 'pdcrequest_application_selected' );
	}
	
	public static function check_if_applied( $request_id ) {
		global $wpdb, $current_user;
		
		$applications = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $wpdb->posts WHERE post_author = %d AND post_type = 'application' AND post_parent = %d LIMIT 1", $current_user->ID, $request_id ) );
		
		return ( $applications ) ? true : false;
	}
}