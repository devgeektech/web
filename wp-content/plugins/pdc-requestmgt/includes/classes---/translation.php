<?php
/**
 * Translation
 * set up translation Custom Post TYpe
 */
 
class Setlr_Translation {
	
	public function __construct() {
            if ( ! post_type_exists( 'translation' )) :
		$this->create();
            endif;
	}
	
	public static function create() {
		$labels = array(
			'name'               => _x( 'Translations', 'post type general name', 'pdcrequest' ),
			'singular_name'      => _x( 'Translation', 'post type singular name', 'pdcrequest' ),
			'menu_name'          => _x( 'Translations', 'admin menu', 'pdcrequest' ),
			'name_admin_bar'     => _x( 'Translation', 'add new on admin bar', 'pdcrequest' ),
			'add_new'            => _x( 'Add New', 'translation', 'pdcrequest' ),
			'add_new_item'       => __( 'Add New Translation', 'pdcrequest' ),
			'new_item'           => __( 'New Translation', 'pdcrequest' ),
			'edit_item'          => __( 'Edit Translation', 'pdcrequest' ),
			'view_item'          => __( 'View Translation', 'pdcrequest' ),
			'all_items'          => __( 'All Translations', 'pdcrequest' ),
			'search_items'       => __( 'Search Translations', 'pdcrequest' ),
			'parent_item_colon'  => __( 'Parent Translations:', 'pdcrequest' ),
			'not_found'          => __( 'No translations found.', 'pdcrequest' ),
			'not_found_in_trash' => __( 'No Translations found in Trash.', 'pdcrequest' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'translation' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' )
		);
	
		register_post_type( 'translation', $args );
	}
	
	
	public static function mark_as_selected( $post ) {
		
		update_post_meta( $post->ID, 'pdcrequest_selected', 'selected' );
		update_post_meta( $post->post_parent, 'pdcrequest_closed', 'closed' );
		do_action( 'pdcrequest_translation_selected' );
	}
	
	public static function check_if_applied( $request_id ) {
		global $wpdb, $current_user;
		
                $sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'translation' AND post_parent = %d ", $request_id );
		
                $translations = $wpdb->get_var( $sql );
		
		return ( $translations ) ? true : false;
	}
        
        
        public static function update_translation() {
            global $current_user;
            get_currentuserinfo();
            $update = '';
            
            if ( !wp_verify_nonce( $_POST['pdcrequestnonce'], 'translator-dashboard') ) die( 'Busted!');
            
            $translation_id = absint( $_POST['post_id'] );
           
            $request_id = pdcrequest_get_request_for_translation( $translation_id );
            
            if ( isset( $_POST['send_review']) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'under_review');
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'pending' );
                
                $message = urlencode( sprintf( __( 'Setlr has been notified and the review will be undertaken shortly.', 'pdcrequest '), $_POST['post_id'] ) );
                $url = get_permalink( pdcrequest_dashboard() );
                
            elseif ( isset( $_POST['reject']) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'rejected');
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'closed' );
                
                $message = urlencode( sprintf( __( 'Setlr has been notified of the rejection. Sorry.', 'pdcrequest '), $_POST['post_id'] ) );
                $url = get_permalink( pdcrequest_dashboard() );
                
            elseif ( isset( $_POST['accept'] ) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'accepted');
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'closed' );
                
                $message = urlencode( sprintf( __( 'Setlr has been notified of your acceptance. Thank you.', 'pdcrequest '), $_POST['post_id'] ) );
                $url = get_permalink( pdcrequest_dashboard() );
                
            else :
                if ( ! current_user_can('helper')) die( 'Not enough rights!');
                // current user must be a translator
                if ( isset( $_POST['post-title']) && isset( $_POST['post-content']) && absint( $_POST['post_id'] )) :
                    $arg = array(
                        'ID' => $translation_id,
                        'post_title' => sanitize_text_field( $_POST['post-title']),
                        'post_content' => $_POST['post-content']
                    );
                    $update = wp_update_post( $arg );
                endif;
                
                if ( isset( $_POST['finished'] ) && $update != '' ) :
                    //update translation status to finished
                    $finished = translation_status::update_status($translation_id, 'finished');


                    if ( absint( $request_id ) ) :
                        //update request status to customer_review
                        $review = request_status::update_status( $request_id, 'customer_review');
                    endif;
                 
                else :
                    //update translation status to in-progress
                    $draft = translation_status::update_status( $translation_id, 'in-progress');
                endif;
                
                if ( isset( $_POST['finished'] ) && $update != '' ) :
                    //update translation status to finished
                    $finished = translation_status::update_status($translation_id, 'finished');


                    if ( absint( $request_id ) ) :
                        //update request status to customer_review
                        $review = request_status::update_status( $request_id, 'customer_review');
                    endif;

                else :
                    //update translation status to in-progress
                    $draft = translation_status::update_status( $translation_id, 'in-progress');
                endif;
                
                if ( isset( $_POST['save']) ) :
                    $message = urlencode( __( 'Your translation has been saved.', 'pdcrequest ') );
                    $url = get_permalink( $translation_id );
                else :
                    $message = urlencode( sprintf( __( 'Translation %s status has been updated to FINISHED. Congratulations!', 'pdcrequest '), $_POST['post_id'] ) );
                    $url = get_permalink( pdcrequest_dashboard() );
                endif;
            endif;
                    
            $complete = add_query_arg('message', $message, $url);
            
            wp_safe_redirect( $complete );
        }
        
        
        public static function add_language_values_column( $columns ) {
            
            unset($columns['language']);
             
            $new_columns = array(
                'request' => __( 'Request', 'pdcrequest'),
               // 'from_lang' => __( 'From', 'pdcrequest'),
                'status' => __( 'Status', 'pdcrequest'),
                'to_lang' => __( 'Language', 'pdcrequest')
            );
            return array_merge( $columns, $new_columns);
            
        }
        
        
        public static function add_language_values_columns_content( $column_name, $post_ID ) {
            
            $post = get_post( $post_ID );
            $post_parent = $post->post_parent;
            switch( $column_name):
                case 'request' :
                    echo get_the_title( $post_parent );
                    break;
                case 'status' :
                    $status = translation_status::get_translation_status( $post_ID );
                    echo '<span class="setlr-status status-' . esc_attr( $status ) . '">' . esc_html( $status) . '</span>';
                    break;
                case 'from_lang':
                    echo get_post_meta( $post_parent, 'doclang', true );
                    //$content = 'ok';
                    break;
                case 'to_lang':
                    echo get_post_meta( $post_parent, 'requestlang', true );
                    break;
            endswitch;
            wp_reset_postdata();
        }
}