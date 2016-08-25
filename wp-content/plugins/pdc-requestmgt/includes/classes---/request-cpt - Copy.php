<?php
/**
 * Request-CPT
 * set up request Custom Post TYpe
 */
 
class Request_CPT {
	
	public function __construct() {
		$this->create();
	}
	
	public static function create() {
		$labels = array(
			'name'               => _x( 'Requests', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Request', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Requests', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Request', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'request', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Request', 'pdcrequest' ),
			'new_item'           => __( 'New Request', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Request', 'pdcrequest' ),
			'view_item'          => __( 'View Request', 'pdcrequest' ),
			'all_items'          => __( 'All Requests', 'pdcrequest' ),
			'search_items'       => __( 'Search Requests', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Requests:', 'pdcrequest' ),
			'not_found'          => __( 'No requests found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No requests found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'request' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' )
		);
	
		register_post_type( 'request', $args );
	}
}