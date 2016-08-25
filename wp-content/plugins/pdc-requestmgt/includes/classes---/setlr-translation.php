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
            
            //security check
            if ( !wp_verify_nonce( $_POST['pdcrequestnonce'], 'translator-dashboard') ) die( 'Busted!');
            
            //we are ok, let's process
            $translation_id = absint( $_POST['post_id'] );
           
            $request_id = pdcrequest_get_request_for_translation( $translation_id );
            
            $current_translation_status = get_post_meta( $translation_id, 'setlr_status', true );
            
            // start controller
            if ( isset( $_POST['send_review']) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'in_revision', $current_translation_status );
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'in_revision' );
                
                $message = '';
                $page_id = pdcrequest_send_review_page();
                $url_data = array( 'project_id' => $request_id );
                
            elseif ( isset( $_POST['reject']) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'rejected', $current_translation_status );
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'closed' );
                
                $message = '';
                $page_id = pdcrequest_send_rejection_page();
                $url_data = array( 'project_id' => $request_id );
                
            elseif ( isset( $_POST['accept'] ) ) :
                // current user must be a customer
                if ( ! current_user_can('customer')) die( 'Not enough rights!');
                
                // change status of translation
                $translation_status = translation_status::update_status($translation_id, 'accepted', $current_translation_status);
                
                // change status of request
                $request_status = request_status::update_status($request_id, 'closed' );
                
                $message = __( 'Task Accepted. Thanks for choosing Setlr! Now that your task is complete it will be accessible through your dashboard via the Finished tasks area. Thank you.', 'pdcrequest ');
                $page_id = pdcrequest_dashboard();
                
            else :
                
                // current user must be a translator
                if ( ! current_user_can('helper')) die( 'Not enough rights!');
                
                //save progress
                if ( isset( $_POST['post-title']) && isset( $_POST['post-content']) && absint( $_POST['post_id'] )) :
                    $arg = array(
                        'ID' => $translation_id,
                        'post_title' => sanitize_text_field( $_POST['post-title']),
                        'post_content' => $_POST['post-content']
                    );
                    $update = wp_update_post( $arg );
                    
                    $message = __( 'Your progress has been saved', 'pdcrequest ');
                    $page_id = pdcrequest_dashboard();
                endif;
                
                //if finished
                if ( isset( $_POST['finished'] ) ) :
                    //update translation status to finished
                    $finished = translation_status::update_status($translation_id, 'customer_review', $current_translation_status);


                    if ( absint( $request_id ) ) :
                        //update request status to customer_review
                        $review = request_status::update_status( $request_id, 'customer_review');
                        if ( $review == false ) write_log( sprintf('request_id %d status was not updated') );
                    endif;  
                    
                    $message = __( "Thanks! Your project is now in customer review. Keep an eye on your dashboard in case any revisions are requested. We will process your payment as soon as your project is accepted by the customer.", 'pdcrequest ');
                    $page_id = pdcrequest_dashboard();
                endif;
                
                
                //if saved
                if ( isset( $_POST['save']) ) :
                    $draft = translation_status::update_status( $translation_id, 'in_progress', $current_translation_status);
                
                    $message = __( 'Your translation has been saved.', 'pdcrequest ');
                    $page_id = $translation_id;
                endif;
            endif;
                    
            Pdc_Requestmgt::redirect_to_page($page_id, $message, $url_data );
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
        
        
        public static function get_author_for( $post_id ) {
            $post = get_post( $post_id );
            
            if ( $post ) :
                $user_id = $post->post_author;
                return $user_id;
            else :
                return false;
            endif;
            
        }
        
        public static function render_translation_form( $post_id ) {
            global $post;
        
            $meta = get_post_meta( $post_id );
            
            $request_id = $post->post_parent;
            $requestlang = get_post_meta( $request_id, 'requestlang', true );
            $locale = get_post_meta( $request_id, 'setlr-locale', true );
            $lang_to = ( isset( $locale ) && !empty( $locale ) ) ? $locale : $requestlang;
            $service = get_post_meta( $request_id, 'setlr_service', true );
            
            
            $html = '<form id="translation-' . absint($post_id ) . '" class="translator-dashboard" method="post" action="' . admin_url('admin.php') . '">';
            $html .= '<h2>' . sprintf( __( 'Your Translation To %s', 'pdcrequest'), $lang_to ) . '</h2>';

            $status = (isset($meta['setlr_status'])) ? $meta['setlr_status'][0] : ''; 
            
            if ( in_array( $status, array( 'pending', 'in_progress') ) ) :
                $html .= '<label>' . __( 'Title', 'pdcrequest' ) . '<input type="text" name="post-title" value="' . esc_html( get_the_title() ) . '" required></label>';
                $html .= '<label>' . __( 'Content', 'pdcrequest' ) . '<textarea name="post-content">' . esc_textarea( get_the_content()) . '</textarea></label>';
                
            else :
                if ( in_array($status, array('in_revision', 'customer_review', 'finished', 'accepted', 'rejected'))) :
                    $html .= '<p>' . sprintf( __( 'Date Finished: %s', 'pdcrequest'), format_datetime( $post->post_modified ) ) . '</p>';
                endif;
                
                $html .= '<div>' . wpautop( esc_textarea( get_the_content()) ) . '</div>';
            endif;
            $html .= self::get_author_for($post_id);
            $html .= pdcrequest_show_appropriate_buttons( $service, $status );
            $html .= '</form>';
            
            echo $html;
            exit;
        }
        
        public static function get_picture_for( $post_id, $arg = array() ) {
            $post = get_post( $post_id );
            
            if ( $post ) :
                $user_id = $post->post_author;
                
                if ( absint( $user_id ) ) :
                    $profile_picture = get_user_meta( $user_id, 'profile_picture', true );
                    write_log( $profile_picture);
                endif;
                $attachment = wp_get_attachment_image( $profile_picture, array( 96, 96 ), false, $arg );
                
                if ( $attachment ) :
                    return $attachment;
                else :
                    return get_avatar( $user_id );
                endif;
                
            else :
                return false;
            endif;
        }
}