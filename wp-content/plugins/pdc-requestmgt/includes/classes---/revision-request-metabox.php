<?php

/**
 * Calls the class on the post edit screen.
 */
function call_Setlr_Rev_Request_Metabox() {
    new Rev_Request_Metabox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'call_Setlr_Rev_Request_Metabox' );
    add_action( 'load-post-new.php', 'call_Setlr_Rev_Request_Metabox' );
}

/** 
 * The Class.
 */
class Rev_Request_Metabox {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            $post_types = array('setlr_rev_request');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
		add_meta_box(
			'rev_request_ids'
			,__( 'Information', 'pdcrequest' )
			,array( $this, 'render_meta_box_content' )
			,$post_type
			,'side'
			,'high'
		);
                add_meta_box(
                        'reasons_id'
                        , __( 'Reasons', 'pdcrequest')
                        , array( $this, 'render_reasons_box_content')
                        , $post_type
                        , 'side'
                        , 'high'
                );
                add_meta_box(
                        'allocate_to_users'
                        , __( 'Select Translators', 'pdcrequest')
                        , array( $this, 'render_allocate_box_content')
                        , $post_type
                        , 'side'
                        , 'high'
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
		if ( ! isset( $_POST['pdcrequest_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['pdcrequest_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'pdcrequest_inner_custom_box' ) )
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
		$data['project_id'] = absint( $_POST['project_id'] );
                $data['translation_id'] = pdcrequest_get_translation_for_request($_POST['project_id']);
                
                if ( isset($_POST['reasons']) && is_array($_POST['reasons']) ) :
                    update_post_meta( $post_id, 'setlr_reasons', $_POST['reasons'] );
                endif;

		// Update the meta field.
		update_post_meta( $post_id, 'revision_request_ids', $data );
                
                $selected_translators = ( $_POST['revision_author']) ? pdcrequest_sanitize_array( 'absint', $_POST['revision_author']) : array();
                //update chosen translators
                update_post_meta( $post_id, 'revision_translators', $selected_translators);
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'pdcrequest_inner_custom_box', 'pdcrequest_inner_custom_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$data = get_post_meta( $post->ID, 'revision_request_ids', true );
                write_log( $data );

                $statuses = array( 'customer_review' );
                $projects = Pdc_Requestmgt::get_requests_for_current_user( $statuses );
		// Display the form, using the current value.
		echo '<label for="project_id">';
		_e( 'Select your project', 'pdcrequest' );
		echo '</label> ';
                if ( !empty($projects)) :
		echo '<select name="project_id">';
                foreach ( $projects as $project ) :
                    $selected =  ( isset( $data['project_id'] ) ) ? selected( $project->ID, $data['project_id']) : '';
                    echo '<option value="' . absint( $project->ID ) . '" ' . $selected . '>' . esc_attr( $project->post_title ) . '</option>';
                endforeach;
                echo '</select>';
                else :
                    echo '<p>' . __( 'You have no projects to review', 'pdcrequest') . '</p>';
                endif;
                
	}
        
        
        public function render_reasons_box_content( $post ) {
            // Add an nonce field so we can check for it later.
            wp_nonce_field( 'pdcrequest_inner_custom_box', 'pdcrequest_inner_custom_box_nonce' );

            // Use get_post_meta to retrieve an existing value from the database.
            $data = get_post_meta( $post->ID, 'setlr_reasons', true );

            $reasons = Setlr_Revision_Request::get_reasons_list();   
                
		// Display the form, using the current value.
		echo '<h2>';
		_e( 'Check the Reasons for Revision', 'pdcrequest' );
		echo '</h2> ';
                if ( !empty($reasons)) :
		echo '<ul>';
                foreach ( $reasons as $code => $name ) :
                    $checked =  ( isset( $data ) && is_array($data ) && in_array( $code, $data )) ? 'checked="checked"' : '';
                    echo '<li><label class="setlr-checkbox"><input type="checkbox" name="reasons[]" value="' . esc_attr( $code ) . '" ' . $checked . '>' . esc_html( $name ) . '</label></li>';
                endforeach;
                echo '</ul>';
                else :
                    echo '<p class="setlr-error no-data">' . __( 'Oups! We missed the reasons list', 'pdcrequest') . '</p>';
                endif;
        }
        
        
        public function render_allocate_box_content( $post ) {
            // Add an nonce field so we can check for it later.
            wp_nonce_field( 'pdcrequest_inner_custom_box', 'pdcrequest_inner_custom_box_nonce' );
            
            // Use get_post_meta to retrieve an existing value from the database.
            $info = Setlr_Revision_Request::get_rev_request_information( $post->ID );
            write_log( $info);
            
            
            $meta = get_post_meta( $post->ID, 'revision_translators', true );
            
            if ( is_array( $meta ) ) :
                $selected = $meta;
            elseif ( is_string($meta )) :
                parse_str( $meta, $selected );
            else :
                $selected = array();
            endif; 
            
            $translation = get_post( $info['translation_id'] );
            $translator = $translation->post_author;
            echo pdcrequest_select_translators( $from, $to, $selected, $translator );
        }
}