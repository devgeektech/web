<?php
/**
 * Request-CPT
 * set up request Custom Post TYpe
 */
 
class Request_CPT {
	
	public function __construct() {
		//$this->create_taxonomy();
		$this->create();
		//$this->create_gift_taxonomy();
                
	}
	
        
        /**
         * create the request custom post type
         * 
         * requests are setlr's term for orders (they are issued by cutomers directly or via api
         * and are answered by helpers (Setlr's term for translators)
         */
	public static function create() {

		$labels = array(
			'name'                => _x( 'Requests', 'Post Type General Name', 'pdcrequest' ),
			'singular_name'       => _x( 'Request', 'Post Type Singular Name', 'pdcrequest' ),
			'menu_name'           => __( 'Request', 'pdcrequest' ),
			'parent_item_colon'   => __( 'Parent Request:', 'pdcrequest' ),
			'all_items'           => __( 'All Requests', 'pdcrequest' ),
			'view_item'           => __( 'View Request', 'pdcrequest' ),
			'add_new_item'        => __( 'Add New Request', 'pdcrequest' ),
			'add_new'             => __( 'Add New', 'pdcrequest' ),
			'edit_item'           => __( 'Edit Request', 'pdcrequest' ),
			'update_item'         => __( 'Update Request', 'pdcrequest' ),
			'search_items'        => __( 'Search request', 'pdcrequest' ),
			'not_found'           => __( 'Not found', 'pdcrequest' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'pdcrequest' ),
		);
		$args = array(
			'label'               => __( 'request', 'pdcrequest' ),
			'description'         => __( 'Requests for help by settlers', 'pdcrequest' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'custom-fields', 'page-attributes', ),
			'taxonomies'          => array( 'request_types' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-post',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'request', $args );
	}
	
	public static function create_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Request Types', 'Taxonomy General Name', 'pdcrequest' ),
			'singular_name'              => _x( 'Request Type', 'Taxonomy Singular Name', 'pdcrequest' ),
			'menu_name'                  => __( 'Request Types', 'pdcrequest' ),
			'all_items'                  => __( 'All Request Types', 'pdcrequest' ),
			'parent_item'                => __( 'Parent Item', 'pdcrequest' ),
			'parent_item_colon'          => __( 'Parent Item:', 'pdcrequest' ),
			'new_item_name'              => __( 'New Request Type', 'pdcrequest' ),
			'add_new_item'               => __( 'Add New Request Type', 'pdcrequest' ),
			'edit_item'                  => __( 'Edit Request Type', 'pdcrequest' ),
			'update_item'                => __( 'Update Request Type', 'pdcrequest' ),
			'separate_items_with_commas' => __( 'Separate Request Types with commas', 'pdcrequest' ),
			'search_items'               => __( 'Search Request Types', 'pdcrequest' ),
			'add_or_remove_items'        => __( 'Add or remove Request Types', 'pdcrequest' ),
			'choose_from_most_used'      => __( 'Choose from the most used Request Types', 'pdcrequest' ),
			'not_found'                  => __( 'Not Found', 'pdcrequest' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'rewrite'                    => false,
		);
		//register_taxonomy( 'request_types', array( 'request' ), $args );
	}
        /**
         * no longer used
         * 
         */
	public static function create_gift_taxonomy() {
	
		$labels = array(
			'name'                       => _x( 'Gifts', 'Taxonomy General Name', 'pdcrequest' ),
			'singular_name'              => _x( 'Gift', 'Taxonomy Singular Name', 'pdcrequest' ),
			'menu_name'                  => __( 'Gifts', 'pdcrequest' ),
			'all_items'                  => __( 'All Gifts', 'pdcrequest' ),
			'parent_item'                => __( 'Parent Gift', 'pdcrequest' ),
			'parent_item_colon'          => __( 'Parent Gift:', 'pdcrequest' ),
			'new_item_name'              => __( 'New Gift Name', 'pdcrequest' ),
			'add_new_item'               => __( 'Add New Gift', 'pdcrequest' ),
			'edit_item'                  => __( 'Edit Gift', 'pdcrequest' ),
			'update_item'                => __( 'Update Gift', 'pdcrequest' ),
			'separate_items_with_commas' => __( 'Separate gifts with commas', 'pdcrequest' ),
			'search_items'               => __( 'Search gifts', 'pdcrequest' ),
			'add_or_remove_items'        => __( 'Add or remove gifts', 'pdcrequest' ),
			'choose_from_most_used'      => __( 'Choose from the most used gifts', 'pdcrequest' ),
			'not_found'                  => __( 'Not Found', 'pdcrequest' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'gift', array( 'request' ), $args );
        
	}
	
	
	public static function add_meta_fields() {
		?>
        <div class="form-field">
        	<label for="gift_value"><?php _e( 'Value', 'pdcrequest' ); ?></label>
            <input type="text" name="gift_value" id="gift_value" value="">
            <p class="description"><?php _e( 'value of gift in base currency', 'pdcrequest' ); ?></p>
       </div>
        <?php
	}
	
	public static function edit_meta_fields( $term ) {
 
		// put the term ID into a variable
		$t_id = $term->term_id;
	 
		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "taxonomy_$t_id" ); 
		 ?>
		<tr class="form-field">
		<th scope="row" valign="top"><label for="gift_value"><?php _e( 'Value', 'pdcrequest' ); ?></label></th>
			<td>
				<input type="text" name="gift_value" id="gift_value" value="<?php echo ( isset( $term_meta ) ) ? esc_attr( $term_meta ) : ''; ?>">
				<p class="description"><?php _e( 'value of gift in base currency', 'pdcrequest' ); ?></p>
			</td>
		</tr>
	<?php
	}
	
	
	public static function save_gift_custom_meta( $term_id ) {
		if ( isset( $_POST['gift_value'] ) ) :
			$t_id = $term_id;
			
			$term_meta = esc_attr( $_POST['gift_value'] );
			/*
			if ( is_array( $term_meta ) ) :
				$cat_keys = array_keys( $_POST['term_meta'] );
				foreach ( $cat_keys as $key ) :
					if ( isset ( $_POST['term_meta'][$key] ) ) :
						$term_meta[$key] = $_POST['term_meta'][$key];
					endif;
				endforeach;
			endif;
			*/
			$option_name = 'taxonomy_' . $term_id;
			// Save the option array.
			$update = update_option( $option_name, $term_meta );
			
		endif;
	} 
	
	
	public static function add_gift_value_column( $columns ) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name'),
		//	'header_icon' => '',
	    // 'description' => __('Description'),
			'slug' => __('Slug'),
			'gift_value'	=> __( 'Value', 'pdcrequest' ),
			'count' => __('Count')
        );
    	return $new_columns;
	}
	
	
	public static function add_gift_value_column_content( $content, $column_name, $term_id ) {
		$term = get_term( $term_id, 'gift' );
		switch ( $column_name ) {
			case 'gift_value':
				//do your stuff here with $term or $term_id
				$content = get_option( "taxonomy_$term_id");
				
				break;
			default:
				break;
		}
		return $content;
	}
	

        public static function add_language_values_columns( $columns ) {
            unset($columns['language']);
            unset($columns['taxonomy-request_types']);
            $new_columns = array(
                'from_lang' => __( 'From', 'pdcrequest'),
                'to_lang' => __( 'To', 'pdcrequest'),
                'status' => __('Status','pdcrequest'),
                'payment_status'    => __('Payment Status', 'pdcrequest'),
                'amount'    => __('Amount', 'pdcrequest'),
                'word_count' => __( 'Word Count', 'pdcrequest')
            );
            return array_merge( $columns, $new_columns);
        }
        
        
        public static function add_language_values_columns_content( $column_name, $post_ID ) {
            switch( $column_name):
                case 'from_lang':
                    echo get_post_meta( $post_ID, 'doclang', true );
                    //$content = 'ok';
                    break;
                case 'to_lang':
                    echo get_post_meta( $post_ID, 'requestlang', true );
                    break;
                case 'status' :
                    echo request_status::get_request_status( $post_ID );
                    break;
                case 'word_count' :
                    $count = pdcrequest_get_word_count( $post_ID );
                    echo $count;
                    break;
                case 'payment_status' :
                    echo Payment_Status::get_payment_status( $post_ID );
                    break;
                case 'amount' :
                    echo Payment_Status::get_amount( $post_ID );
                    break;
            endswitch;
        }
        
        
        
        public static function show_original_request( $request_id ) {
            global $post;
            $post = get_post( $request_id );
            
            $lang = get_post_meta( $post->ID, 'doclang', true);
            if ( $post ) :
                $html = '<article class="request original-post hentry ' . esc_attr( $lang ) . '">';
                $html .= '<header class="entry-header">';
                $html .= '<h1 class="entry-title">' . esc_html( $post->post_title ) . '</h1>';
                $html .= '</header>';
                $html .= Request_Form::view_project($request_id);
                /*
                $html .= '<div class="entry-content">' . wpautop( wp_kses_post( $post->post_content ), true ) . '</div>';
                 */
                $html .= '<footer class="entry-footer">';
                
                $html .= pdcrequest_show_author( $post->post_author );
                $html .= '</footer>';
                
                $html .= '</article>';
            else :
                $html = '<p class="error no-original-post">' . __( "Can't find original post! Sorry", 'pdcrequest' ) . '</p>';
            endif;
            wp_reset_postdata();
            return $html;
        }
	
        
        
        public static function cancel_request() {
            
            //verify nonce
            if (!isset($_GET['pdcrequest_nonce']) || !wp_verify_nonce($_GET['pdcrequest_nonce'], 'pdcrequest_cancel_request')) die( 'pb_nonce');
            
            
            $request_id = $_GET['request_id'];
            
            //verify request id
            if ( ! absint( $request_id ) ) die( 'wrong request');
            
            
            $request = get_post( $request_id );
            
            //verify request
            if ( ! $request || $request->post_type != 'request') exit( 'no request');
            
            $current_user = wp_get_current_user();
            //verify author
            if ( ! $current_user || $current_user->ID != $request->post_author ) exit( 'wrong author');
            
            //verify that there is no translation
            if (pdcrequest_get_translation_for_request($request_id)) exit( 'has translation');
            
            /* ok now we can safely cancel the request */
            request_status::update_status($request_id, 'cancelled');
            
            //prepare notification
            $message = urlencode( sprintf( __('Request %s has been cancelled.', 'pdcrequest'), $request->post_title ) );
            
            Pdc_Requestmgt::redirect_to_dashboard($message);
        }
        
        
        public static function reopen_request() {
            
            //verify nonce
            if (!isset($_GET['pdcrequest_nonce']) || !wp_verify_nonce($_GET['pdcrequest_nonce'], 'pdcrequest_reopen_request')) die( 'pb_nonce');
            
            
            $request_id = $_GET['request_id'];
            
            //verify request id
            if ( ! absint( $request_id ) ) exit( 'wrong request');
            
            
            $request = get_post( $request_id );
            
            //verify request
            if ( ! $request || $request->post_type != 'request') exit( 'no request');
            
            $current_user = wp_get_current_user();
            //verify author
            if ( ! $current_user || $current_user->ID != $request->post_author ) exit( 'wrong author');
            
            //verify that there is no translation
            if (pdcrequest_get_translation_for_request($request_id)) exit( 'has translation');
            
            /* ok now we can safely cancel the request */
            request_status::update_status($request_id, 'open');
            
            //prepare notification
            $message = urlencode( sprintf( __('Request %s has been reopened.', 'pdcrequest'), $request->post_title ) );
            
            Pdc_Requestmgt::redirect_to_dashboard($message);
        }
}