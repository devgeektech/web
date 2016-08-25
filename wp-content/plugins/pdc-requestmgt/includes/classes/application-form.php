<?php
class Application_Form {
	
	public function __construct() {
		add_action( 'admin_action_pdc_new_application', array( $this, 'validate_form' ) );
		add_action( 'wp_ajax_pdc_new_application', array( $this, 'validate_form' ) );
		add_action( 'wp_ajax_nopriv_pdc_new_application', array( $this, 'validate_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}
	
	
	public function create_form_new_application( $data = array() ) {
		global $current_user, $post;
		get_currentuserinfo();
		
		// verify if logged in if not redirect to login/register
		if ( ! is_user_logged_in() ) :
			if (function_exists( 'write_log' ) ) write_log( 'auth_redirect called' );
			auth_redirect();
			exit;
		endif;
		
		// check if user has already applied
		$applications = Application_CPT::check_if_applied( $post->ID );
		
		if ( $applications ) :
			$html = '<p>' . __( 'You have already applied!', 'pdcrequest' ) . '</p>';
		else :
		
			//if (function_exists( 'write_log' ) ) write_log( 'checking user can' );
			//check if current user can add request
			if ( current_user_can( 'pdc_application_post' ) ) :
				$title = ( isset( $data['title'] ) ) ? sanitize_text_field( $data['title'] ) : '';
				$content = ( isset( $data['content'] ) ) ? esc_textarea( $data['content'] ) : '';
				$request_id = ( isset( $data['request_id'] ) && absint( $data['request_id'] ) ) ? absint( $data['request_id'] ) : '';
				$user = $current_user->ID;
				
				//for debugging
				if ( has_action( 'admin_action_pdc_new_application', array( $this, 'validate_form' ) ) ) :
					write_log( 'admin action is hooked and validate_form is set' );
				else :
					write_log( 'admin action not hooked or validate_form not set' );
				endif;
				
				//the form
				$html  = '<form name="pdc_application_new" id="pdc_application_new" action="' . admin_url( 'admin.php' ) . '" method="post">';
				$html .= '<label for="title">' . __( 'Title', 'pdcrequest' ) . '</label>';
				$html .= '<input type="text" name="title" value="' . $title . '" required>';
				$html .= '<label for="content">' . __( 'Content', 'pdcrequest' ) . '</label>';
				$html .= '<textarea name="content" required>' . $content . '</textarea>';
				$html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Post Application', 'pdcrequest' ) . '"  /></p>';
				$html .= '<input type="hidden" name="action" value="pdc_new_application">';
				$html .= '<input type="hidden" name="user" value="' . absint( $user ) . '">';
				$html .= '<input type="hidden" name="request_id" value="' . $request_id . '">';
				$html .= wp_nonce_field( 'pdc-new-application', 'pdcrequestnonce', true, false );
				$html .= '</form>';
			else :
				$html = '<p class="error">' . __( 'You do not have sufficient rights to make an application', 'pdcrequest' ) . '</p>';
			endif;
		endif;
		return $html;
	}
	
	
	public static function render_form_new_application( $data = array() ) {
		$application_form = new Application_Form();
		echo $application_form->create_form_new_application( $data );
		exit;
	}


	public function enqueue_scripts() {
		wp_enqueue_script( 'new_application_validate', plugins_url( 'pdc-requestmgt/assets/js/newapplication-validate.js' ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'new_application_validate', 'pdcrequest', array( 'ajaxurl' => admin_ajax() ) );
	}
	
	public static function validate_form() {
		
		if (function_exists( 'write_log' ) ) write_log( 'validate form' );
		
		
		parse_str( $_POST['formdata'], $data );
		
		if (function_exists( 'write_log' ) ) write_log( $data );
		
		if ( ! isset( $data['pdcrequestnonce'] ) ) :
			if (function_exists( 'write_log' ) ) write_log( 'no nonce' );
			echo 'error: no nonce';
			exit;
		elseif ( ! wp_verify_nonce( $data['pdcrequestnonce'], 'pdc-new-application' ) ) :
			if (function_exists( 'write_log' ) ) write_log( 'nonce not verified' );
			echo 'error: nonce not verified';
			exit;
		
		else :
			if (function_exists( 'write_log' ) ) write_log( 'nonce ok' );
		endif;
		
		//check required fields
		$required = array( 'title', 'content', 'user', 'request_id' );
		
		foreach ( $required as $field ) :
			if ( ! isset( $data[$field] ) || empty( $data[$field] ) ) :
				$errors[] = $field . '_missing';
			endif;
		endforeach;
		
		if ( ! absint( $data['request_id'] ) ) :
			$errors[] = 'request_id_missing';
		endif;
		
		if ( ! empty( $errors ) ) :
			return array( false, $errors, $data );
		else :
			// do something
			$post = array(
			  'post_content'   => $data['content'],
			  //'post_name'      => [ <string> ] // The name (slug) for your post
			  'post_title'     => wp_strip_all_tags( $data['title'] ),
			  'post_status'    => 'publish',
			  'post_type'      => 'application',
			  //'post_author'    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
			  'post_parent'    => $data['request_id'] // Sets the parent of the new post, if any. Default 0.
			  //'post_category'  => [ array(<category id>, ...) ] // Default empty.
			  //'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
			 // 'tax_input'      => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
			  //'page_template'  => [ <string> ] // Requires name of template file, eg template.php. Default empty.
			);  
			wp_insert_post( $post, $wp_error );
			
			if ( ! is_wp_error( $wp_error ) ) :
				$message = apply_filters( 'pdcrequest-insertmessage', __( 'Your application is published', 'pdcrequest' ) );
			else :
				$message = apply_filters( 'pdcrequest-insertmessage-error', __( 'Your application could not be published', 'pdcrequest' ), $wp_error );
			endif;
		endif;
		echo '<div class="message">' . esc_html__( $message ) . '</div>';
		//wp_redirect( esc_url( home_url( '/post-a-request' ) ) );
		exit;
	}
	
}