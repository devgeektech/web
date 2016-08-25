<?php
class Request_Form {
	
	public function __construct() {
		add_action( 'admin_post_pdc_new_request', array( $this, 'validate_request_form' ) );
		add_action( 'wp_ajax_pdc_new_request', array( $this, 'validate_request_form' ) );
		add_action( 'wp_ajax_nopriv_pdc_new_request', array( $this, 'validate_request_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}
	
	
	public static function create_form_new_request( $data = array() ) {
		global $current_user;
		get_currentuserinfo();
		
                $html = '';
                if ( ! empty($data ) ) :
                    //we are updating the form
                    
                    //check if current user can add request
                    if ( current_user_can( 'customer' ) || current_user_can( 'manage_options') ) :
                            $title = ( isset( $data['title'] ) ) ? sanitize_text_field( $data['title'] ) : '';
                            $content = ( isset( $data['content'] ) ) ? esc_textarea( $data['content'] ) : '';
                            $user = $current_user->ID;

                            if ( isset( $data['setlr_request_notes']) ) :
                                if ( is_array( $data['setlr_request_notes'] ) ) :
                                    $notes = $data['setlr_request_notes'][0];
                                else :
                                    $notes = $data['setlr_request_notes'];
                                endif;
                            else :
                                $notes = '';
                            endif;

                            //the form
                            $html  = '<form name="pdc_request_new" id="pdc_request_new" action="' . admin_url( 'admin.php' ) . '" method="post">';
                            if ( isset( $data[setlr_total_price] ) && $data[setlr_total_price][0] ) :
                                $total_price = pdcrequest_format_amount( 'GBP', $data[setlr_total_price][0] );
                                $html .= '<p>' . sprintf( __( 'Paid: %s', 'pdcrequest'), $total_price ) . '</p>';
                            endif;
                            $html .= '<fieldset id="choose-service"><legend>' . __( 'Choose A Service', 'pdcrequest') . '</legend>';
                            $html .= self::get_services_select( $data );
                            $html .= '</fieldset>';
                            //empty container div to receive ajax form fragment depending on service selected
                            
                                $html .=  '<div id="container ">';
                                $html .= self::get_content( $data );
                                $html .= '</div>';
                            
                            //$html .= '<p>description of gift system</p>';
                            $html .= '<div id="submit-form">';
                            
                            $html .= self::is_quote_needed($data);
                            
                            if ( isset( $data['ID']) && absint( $data['ID'] ) ) :
                                $html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Update Request', 'pdcrequest' ) . '"  /></p>';
                                $html .= '<input type="hidden" name="post_id" value="' . absint( $data['ID'] ) . '">';
                                $html .= '<input type="hidden" name="updated" value="updated">';
                            else :
                                $html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Get Quote', 'pdcrequest' ) . '"  /></p>';
                                $html .= '<input type="hidden" name="quote_needed" value="yes">';
                            endif;
                            
                            $html .= '<input type="hidden" name="action" value="pdc_new_request">';
                            $html .= '<input type="hidden" name="user" value="' . absint( $current_user->ID ) . '">';
                            $html .= wp_nonce_field( 'pdc-new-request', 'pdcrequestnonce', true, false );
                            $html .= '</div>';
                            $html .= '</form>';

                    else :
                            $html = '<p class="error">' . __( 'You do not have sufficient rights to make a request', 'pdcrequest' ) . '</p>';
                    endif;
                else :
                    //it is a new project
                    $html  = '<form name="pdc_request_new" id="pdc_request_new" action="' . admin_url( 'admin.php' ) . '" method="post">';
                            $html .= '<fieldset id="choose-service"><legend>' . __( 'Choose A Service', 'pdcrequest') . '</legend>';
                            $html .= self::get_services_select();
                            $html .= '</fieldset>';
                            //empty container div to receive ajax form fragment depending on service selected
                            if (!empty( $data)) :
                                $html .=  '<div id="container ">';
                                $html .= self::get_content();
                                $html .= '</div>';
                            else :
                                $html .= '<div id="container"></div>';
                            endif;
                            //$html .= '<p>description of gift system</p>';
                            $html .= '<div id="submit-form">';
                            
                            $onclick = 'onclick="ga(\'send\', \'event\', \'project-add\', \'submit-finished\')”';
                                
                            $html .= '<p class="submit"><input type="submit" name="submit" id="submit" ' . $onclick . ' class="button button-primary" value="' . __( 'Get Quote', 'pdcrequest' ) . '"  /></p>';
                            $html .= '<input type="hidden" name="quote_needed" value="yes">';
                            
                            $html .= '<input type="hidden" name="action" value="pdc_new_request">';
                            $html .= '<input type="hidden" name="user" value="' . absint( $current_user->ID ) . '">';
                            $html .= wp_nonce_field( 'pdc-new-request', 'pdcrequestnonce', true, false );
                            $html .= '</div>';
                            $html .= '</form>';

                endif;
		return $html;
	}
	
	
	public static function render_form_new_request( $data = array() ) {
		$request_form = new Request_Form();
                wp_enqueue_script('request_lang_locale');
		echo $request_form->create_form_new_request( $data );
		
	}


	public function enqueue_scripts() {
		wp_enqueue_script( 'new_request_validate', plugins_url( '/assets/js/newrequest-validate.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'new_request_validate', 'pdcrequest', array( 'ajaxurl' => admin_ajax() ) );
	}
	
        
        /**
         * 
         * @return Void
         */
	public static function validate_request_form() {
		if ( ! isset( $_POST['pdcrequestnonce'] ) || ! wp_verify_nonce( $_POST['pdcrequestnonce'], 'pdc-new-request' ) ) :
                        write_log( 'pdcrequestnonce did not validate');
			exit;
		endif;
		
		//check required fields
                if ( $_POST['service'] == 'translation' ) :
                    $required = array( 'title', 'content', 'user', 'to-lang', 'from-lang' );
		else :
                    $required = array( 'title', 'content', 'user', 'question_about' );
                endif;
                
		foreach ( $required as $field ) :
			if ( ! isset( $_POST[$field] ) || empty( $_POST[$field] ) ) :
				$errors[] = $field . '_missing';
			endif;
		endforeach;
		
		if ( ! empty( $errors ) ) :
                        write_log($errors);
			return array( false, $errors, $_POST );
		else :
                        
			// do something
			$post = array(
			  'post_content'   => $_POST['content'],
			  //'post_name'      => [ <string> ] // The name (slug) for your post
			  'post_title'     => wp_strip_all_tags( $_POST['title'] ),
			  'post_status'    => 'publish',
			  'post_type'      => 'request'  
			); 
                
                        /* check if update or new project */
                        if ( isset( $_POST['post_id'] ) && absint( $_POST['post_id'] ) ) :
                            //we need to update
                            $post = array_merge( $post, array( 'ID' => $_POST['post_id']));
                            $update = wp_update_post($post, true );
                            
                            // wp_update_post can = 0 if nothing in post was updated
                            if ( $update != 0 && ! is_wp_error( $update )) :
                                $request_id = $update;
                            else :
                                $request_id = absint( $_POST['post_id'] );
                            endif;
                        else :
                            //we insert the new request
                            $request_id = wp_insert_post( $post, true );
                        endif;
			
			
			if ( is_wp_error( $request_id ) ) :
                            write_log( $request_id);
                            $message = apply_filters( 'pdcrequest-insertmessage-error', __( 'Your request could not be published', 'pdcrequest' ), $wp_error );
                            $message_type = 'error';
                            $redirect_page = get_page_by_path('request-translation');
                        else :
                                if ( $_POST['service'] == 'translation' ) :
                                    lang::save_lang_info( $request_id, 'to-lang', $_POST['to-lang'] );
                                    lang::save_lang_info( $request_id, 'from-lang', $_POST['from-lang'] );
                                else :
                                    lang::save_lang_info( $request_id, 'question_about', $_POST['question_about'] );
                                    lang::save_lang_info( $request_id, 'questionlang', $_POST['questionlang'] );
                                    update_post_meta( $request_id, 'question_country', $_POST['question_country'] );
                                endif;
                                request_status::update_status( $request_id );
                                request_status::update_status_history($request_id, 'open');
                                
                                
                                // we need a payment status
                                /* @todo: verify request type  && word count to check if need to update price */
                                $payment_status = Payment_Status::get_payment_status( $request_id );
                                if ( ! isset( $payment_status ) || $payment_status == false ) :
                                    Payment_Status::update_status($request_id, 'pending' );
                                endif;
                               
                                
                                if ( isset( $_POST['locale'])) :
                                    lang::save_request_locale( $request_id, $_POST['to-lang'], $_POST['locale'] );
                                endif;
                                
                                //update service
                                $services = pdcrequest_list_services();
                                if ( isset( $_POST['service'] ) && self::validate_value( $_POST['service'], $services )) :
                                    update_post_meta( $request_id, 'setlr_service', $_POST['service'] );
                                endif;
                                
                                
                                //update specialisation
                                $specialisations = pdcrequest_list_specialisations();
                                if ( isset( $_POST['specialisation'] ) && self::validate_value( $_POST['specialisation'], $specialisations ) ) :
                                    update_post_meta( $request_id, 'setlr_specialisation', $_POST['specialisation'] );
                                endif;
                                
                                if ( isset( $_POST['word_count'] ) && absint( $_POST['word_count'] ) ) :
                                    update_post_meta( $request_id, 'setlr_word_count', $_POST['word_count'] );
                                endif;
                                
                                //update age groups
                                $age_groups = pdcrequest_list_age_groups();
                                if ( isset( $_POST['age_group'] ) && self::validate_value( $_POST['age_group'], $age_groups ) ) :
                                    update_post_meta( $request_id, 'setlr_age_group', $_POST['age_group'] );
                                endif;
                                
                                 
                                //update genders
                                $genders = pdcrequest_list_genders();
                                if ( isset( $_POST['gender'] ) && self::validate_value( $_POST['gender'], $genders ) ) :
                                    update_post_meta( $request_id, 'setlr_gender', $_POST['gender'] );
                                endif;
                                
                                if ( isset( $_POST['setlr_request_notes'] ) ) :
                                    update_post_meta( $request_id, 'setlr_request_notes', $_POST['setlr_request_notes'] );
                                endif;
                                
                                if ( isset( $_POST['updated'] ) && $_POST['updated'] == 'updated' ) :
                                    $message = apply_filters( 'pdcrequest-insertmessage', sprintf(__( 'Your project %s has been updated', 'pdcrequest' ), $post['post_title'] ) );
                                else :
                                    $message = apply_filters( 'pdcrequest-insertmessage', sprintf(__( 'Your project %s has been published.', 'pdcrequest' ), $post['post_title'] ) );
                                endif;
                                $message_type = 'success';
                                $redirect_page = get_page_by_path( 'my-dashboard' );
                                
                                if ( isset( $_POST['quote_needed'] ) && 'yes' === $_POST['quote_needed'] ) :
                                    $redirect_page = get_page_by_path( 'project-quote' );
                                    $message = apply_filters( 'pdcrequest-insertmessage', sprintf( __('Your project %s is almost ready. You need to proceed to payment'), $post['post_title'] ) );
                                endif;
                                
			
			endif;
		endif;
                
                
                $redirect = get_permalink( $redirect_page );
                $dashboard_with_message = add_query_arg( array('message' => urlencode( $message ), 'message_type' => $message_type, 'request_id' => urlencode( $request_id )), $redirect);
                
                write_log( 'we redirect to ' . $redirect );
                wp_redirect( wp_nonce_url( $dashboard_with_message, 'request-form', 'setlrnonce' ) );
		
		
	}
        
        /**
         * get available services as select and option
         * @return string the HTML select with all options
         * @todo open with real services and not just translation
         */
        public static function get_services_select( $data = array() ) {
            //$services = pdcrequest_list_services();
            $services = array( 
                                'translation'   => __( 'Translation', 'pdcrequest' ),
                                'question'      => __( 'Local Question', 'pdcrequest' )
                );
            
            $html = '<select name="service" id="setlr-service">';
            
            foreach ( $services as $code => $name ) :
                $selected = ( isset( $data['setlr_service']) ) ? selected( $data['setlr_service'][0], $code, false ) : '';
                
                $html .= '<option value="' . esc_attr( $code ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
            endforeach;
            $html .= '</select>';
            
            return $html;
        }
        
        
        /**
         * get the targeting info form fragment
         * this is enclosed in a fieldset
         * @param array data the values of previous form
         * @return string the HTML form fragment
         */
        public static function get_targeting_info_form( $data = array() ) {
            $html = '';
            /* Interests & Specialisations */
            $interests = pdcrequest_list_specialisations();
            
            
            if ( $interests ) :
                $html .= '<p><label for="specialisation">' . __( 'Interests & Specialisations', 'pdcrequest' ) . '</label><br>';
                $html .= '<select name="specialisation">';
                $html .= '<option value="' . esc_attr( '' ) . '">' . esc_html( __( 'Not Important', 'pdcrequest' ) ) . '</option>';
                foreach ( $interests as $code => $name ) :
                    //mark selected
                    if ( isset( $data['setlr_specialisation'] ) ) :
                        if ( is_Array( $data['setlr_specialisation'] ) ) :
                            $selected = ( isset( $data['setlr_specialisation'][0] ) ) ? selected( $data['setlr_specialisation'][0], $code, false ) : '';
                        else :
                            $selected = ( isset( $data['setlr_specialisation']) ) ? selected( $data['setlr_specialisation'], $code, false ) : '';
                        endif;
                    else :
                        $selected = '';
                    endif;
                    
                    $html .= '<option value="' . esc_attr( $code ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
                endforeach;
                
                $html .= '</select></p>';
            endif;
            
            /* Age Group */
            $age_groups = pdcrequest_list_age_groups();
            
            if ( $age_groups ) :
                $html .= '<p><label for="age_group">' . __( 'Age Group', 'pdcrequest' ) . '</label><br>';
                $html .= '<select name="age_group">';
                $html .= '<option value="' . esc_attr( '' ) . '">' . esc_html( __( 'Not Important', 'pdcrequest' ) ) . '</option>';
                foreach ( $age_groups as $code => $name ) :
                    //mark selected
                    if ( isset( $data['setlr_age_group'] ) ) :
                        if ( is_Array( $data['setlr_age_group'] ) ) :
                            $selected = ( isset( $data['setlr_age_group'][0] ) ) ? selected( $data['setlr_age_group'][0], $code, false ) : '';
                        else :
                            $selected = ( isset( $data['setlr_age_group']) ) ? selected( $data['setlr_age_group'], $code, false ) : '';
                        endif;
                    else :
                        $selected = '';
                    endif;
                    
                    $html .= '<option value="' . esc_attr( $code ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
                endforeach;
                
                $html .= '</select></p>';
            endif;
            
            /* Gender */
            $genders = pdcrequest_list_genders();
            
            if ( $genders ) :
                $html .= '<p><label for="gender">' . __( 'Gender', 'pdcrequest' ) . '</label><br>';
                $html .= '<select name="gender">';
                $html .= '<option value="' . esc_attr( '' ) . '">' . esc_html( __( 'Not Important', 'pdcrequest' ) ) . '</option>';
                foreach ( $genders as $code => $name ) :
                    //mark selected
                    if ( isset( $data['setlr_gender'] ) ) :
                        if ( is_Array( $data['setlr_gender'] ) ) :
                            $selected = ( isset( $data['setlr_gender'][0] ) ) ? selected( $data['setlr_gender'][0], $code, false ) : '';
                        else :
                            $selected = ( isset( $data['setlr_gender']) ) ? selected( $data['setlr_gender'], $code, false ) : '';
                        endif;
                    else :
                        $selected = '';
                    endif;
                    
                    $html .= '<option value="' . esc_attr( $code ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
                endforeach;
                
                $html .= '</select></p>';
            endif;
            
            return $html;
        }
        
        
        public static function create_project_update_form( $project_id ) {
            /* 1) let get the data */
            $request = get_post( $project_id );
            $metadata = get_metadata( 'post', $project_id, '', false );
            
            /* 2) let's put it all in an array */
            
            $data = array(
                'ID'        => $request->ID,
                'title'     => $request->post_title,
                'content'   => $request->post_content,
            );
            
            $data = array_merge($data, $metadata );
            
            /* 3) return the create form with data filled in */
            return self::create_form_new_request( $data );
        }
        
        
        public static function validate_value( $value, $array_values ) {
            //$array_values [value] => name
            $array_keys = array_keys( $array_values );
            
            //check case $value is string
            if (is_string($value)) :
                
                if ( in_array( $value, $array_keys ) ) :
                    //value is in array of possible values
                    return true;
                endif;
            elseif ( is_array($value ) ) :
                foreach( $value as $code ) :
                    //we need to check for all values
                    if ( ! in_array( $code, $array_keys ) ) :
                        //if any values is suspect then do not validate
                        return false;
                    endif;
                    return true;
                endforeach;
            endif;
            
            //if we have neither a string nor an array, something wrong do not validate
            return false;
        }
        
        
        public static function view_project( $project_id ) {
            /* 1) let get the data */
            $request = get_post( $project_id );
            $metadata = get_metadata( 'post', $project_id, '', false );
            
            /* 2) let's put it all in a single array */
            
            $data = array(
                'ID'            => $request->ID,
                'title'         => $request->post_title,
                'content'       => $request->post_content,
                'post_date'     => $request->post_date,
                'post_date_gmt' => $request->post_date_gmt
            );
            
            $data = array_merge( $data, $metadata );
            
            //print_r( $data );
            /* 3) return the view with data filled in */
            
            // dates
            $html = '<p><span class="setlr-date date-posted">' . sprintf( __( 'Date Posted: %s', 'pdcrequest'), format_datetime( $data['post_date'] ) ) . '</span>';
            $html .= ( isset( $data['date_completed'] ) ) ? '<span class="setlr-date date-finished">' . sprintf( __( 'Date Completed %s', 'pdcrequest' ), format_datetime( $data['date_completed'] ) ) . '</span>' : '';
            $html .= '</p>';
            $html .= '<p class="setlr-price">' . Setlr_Pricing::get_price_for( $request->ID ) . '</p>';
            
            // service & languages
            if ( isset( $data['setlr_service'] ) ) :
                $html .= '<p>' . sprintf( __( 'Service: %s', 'pdcrequest'), $data['setlr_service'][0] );
                switch ( $data['setlr_service'][0] ) :
                    case 'translation' :
                        $html .= ' ' . sprintf( __( 'From: %s'), pdcrequest_get_lang_name_from_code( $data['from-lang'][0] ) ) . ' ' . sprintf( __( 'To: %s', 'pdcrequest'), pdcrequest_get_lang_name_from_code( $data['to-lang'][0] ) );
                    if ( isset( $data['setlr-locale']) ) :
                        $html .= ' (' . $data['setlr-locale'][0] . ')';
                    endif;
                    $html .= '</p>';
                   
                        break;
                    case 'question' :
                        $html .= ' ' .sprintf( __( 'Language: %s', 'pdcrequest' ), $data['question_about'][0] ) . '</p>';
                        $html .= ' ' .sprintf( __( 'Language: %s', 'pdcrequest' ), $data['questionlang'][0] ) . '</p>';
                        $html .= ' ' .sprintf( __( 'Language: %s', 'pdcrequest' ), $data['question_country'][0] ) . '</p>';
                        break;
                endswitch;
            endif;
            
            //targeting info
            if ( isset($data['setlr_specialisation']) || isset($data['setlr_age_group']) || isset($data['setlr_gender']) ) :
                $html .= '<h2>' . __( 'Targeting Information', 'pdcrequest' ) . '</h2>';
                $html .= '<ul>';
                if ( isset($data['setlr_specialisation']) ) :
                    $html .= '<li>' . sprintf( __( 'Interests & Specialisations: %s', 'pdcrequest' ), $data['setlr_specialisation'][0] ) . '</li>';
                endif;
                if ( isset($data['setlr_age_group']) ) :
                    $html .= '<li>' . sprintf( __( 'Age Group: %s', 'pdcrequest' ), $data['setlr_age_group'][0] ) . '</li>';
                endif;
                if ( isset($data['setlr_gender']) ) :
                    $html .= '<li>' . sprintf( __( 'Gender: %s', 'pdcrequest' ), $data['setlr_gender'][0] ) . '</li>';
                endif;
                $html .= '</ul>';
            endif;
            
            // project content
            $html .= '<div><h2>' . __( "Project's Content", 'pdcrequest' ) . '</h2>';
            $html .= '<div class="entry-content">' . wpautop( wp_kses_post( $data['content'] ) ) .'</div></div>';
            
            // project notes
            if ( isset($data['setlr_request_notes']) ) :
                $html .= '<div><h2>' . __( "Project's Notes", 'pdcrequest' ) . '</h2>';
                $html .= '<div>' . $data['setlr_request_notes'][0] . '</div></div>';
            endif;
           
            $html .= '<p>' . pdcrequest_link_to_dashboard( __( 'Return to My Dashboard', 'pdcrequest') ) . '</p>';
            return $html;
            
        }
        
        
        public static function ajax_request_form() {
            
            $type = sanitize_text_field( $_POST['type'] );
            
            $data = ( isset( $_POST['data'] ) ) ? $_POST['data'] : array();
            
            
            $title = '';
            $content = '';
            $notes = '';
            
            switch ( $type ) :
                case 'translation' :
            
                        $html = '<fieldset id="setlr-select-langs">';
                        $html .= '<legend>' . __( 'Select the languages', 'pdcrequest') . '</legend>';
                        $html .= lang::render_lang_select('from', $data );
                        $html .= lang::render_lang_select('to', $data );
                        
                        if ( isset( $data['setlr-locale'])) :
                            $html .= lang::request_locales_form(get_the_id());
                        endif;
                        $html .= '</fieldset>';
                        $html .= '<fieldset><legend>' . __( 'Targeting Information', 'pdcrequest') . '</legend>';
                        $html .= self::get_targeting_info_form( $data );
                        $html .= '</fieldset>';
                        $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
                        $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
			$html .= '<input type="text" id="form_title" name="title" value="' . $title . '" required></label></p>';
                        $html .= '</fieldset>';
                        $html .= '<fieldset class="setlr-request">';
                        $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
			$html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
			$html .= '<textarea id="form_content" spellcheck="false" name="content" required>' . $content . '</textarea></label></p>';
                        $html .= '</fieldset>';
                        //$html .= '<div id="request_word_count" class="pdcrequest-word-count">';
                        //$html .= '<p>' . __('Word Count:', 'pdcrequest');
                        //$html .= '<input type="text" id="pdcrequest-word-count-total" name="word_count" style="width:100px;" disabled>';
                        $html .= '<input type="hidden" name="word_count" value="">';
                        $html .= '</p>';
                        $html .= '</div>';
                        $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
                        $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
			$html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $notes ) . '</textarea></p>';
                        $html .= '</fieldset>';
                    break;
                case 'question' :
                        //$html = '<fieldset><legend>' . __('I have a question about', 'pdcrequest') . '</legend>';
                        $html = '<p>' . lang::render_lang_select('question_about', $data ) . '</p>';
                        //$html .= '</fieldset>';
                        //$html .= '<fieldset><legend>' . __('That I would like answered in', 'pdcrequest') . '</legend>';
                        $html .= '<p>' . lang::render_lang_select('questionlang', $data ) . '</p>';
                        //$html .= '</fieldset>';
                        $html .= '<p><label for="question_country">' . __( 'Is it specific to a country?', 'pdcrequest') . '</label>';
                        $html .= '<select name="question_country">';
                        $html .= '<option value="' . esc_attr( "" ) . '">' . esc_html( 'choose a  country' ) . '</option>';
                        foreach ( pdcrequest_countries_select() as $code => $name ) :
                            $html .= '<option value="' . esc_attr( $code ) . '">' . esc_html( $name ) . '</option>';
                        endforeach;
                        $html .= '</select></p>';
                        $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
                        $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
			$html .= '<input type="text" id="form_title" name="title" value="' . $title . '" required></label></p>';
                        $html .= '</fieldset>';
                        $html .= '<fieldset class="setlr-request">';
                        $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
			$html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
			$html .= '<textarea spellcheck="false" id="form_content" name="content" required>' . $content . '</textarea></label></p>';
                        $html .= '</fieldset>';
                        $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
                        $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
			$html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $notes ) . '</textarea></p>';
                        $html .= '</fieldset>';
                    break;
            endswitch;
            
            echo $html;
            exit();
        }
        
        public static function get_content( $data ) {
            
            $type = ( isset( $data['setlr_service'][0] ) ) ? esc_attr( $data['setlr_service'][0] ) : '';
            
            switch ( $type ) :
                
                case 'question' :
                    //$html = '<fieldset><legend>' . __('I have a question about', 'pdcrequest') . '</legend>';
                    $html = '<p>' . lang::render_lang_select('question_about', $data ) . '</p>';    
                    //$html .= '</fieldset>';
                    //$html = '<fieldset><legend>' . __('That I would like to be answered in', 'pdcrequest') . '</legend>';
                    $html .= '<p>' . lang::render_lang_select('questionlang', $data ) . '</p>';
                    //$html .= '</fieldset>';
                    $html .= '<fieldset><legend>' . __( 'Is it specific to a country?', 'pdcrequest') . '</legend>';
                    $html .= '<p><select name="question_country">';
                    $html .= '<option value="">' . __( 'no', 'pdcrequest') . '</option>';
                        foreach( pdcrequest_countries_select() as $code => $name ) :
                            $html .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, $data['question_country'][0], false ) . '>' . esc_html( $name ) . '</option>';
                        endforeach;
                    $html .= '</select></p>';
                    $html .= '</fieldset>';
                    $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
                    $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
                    $html .= '<input type="text" id="form_title" name="title" value="' . $data['title'] . '" required></label></p>';
                    $html .= '</fieldset>';
                    $html .= '<fieldset class="setlr-request">';
                    $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
                    $html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
                    $html .= '<textarea spellcheck="false" id="form_content" name="content" required>' . $data['content'] . '</textarea></label></p>';
                    $html .= '</fieldset>';
                    $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
                    $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
                    $html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $data['setlr_request_notes'][0] ) . '</textarea></p>';
                    $html .= '</fieldset>';
                    break;
                case 'translation' :
                default:
                    $html = '<fieldset id="setlr-select-langs">';
                    $html .= '<legend>' . __( 'Select the languages', 'pdcrequest') . '</legend>';
                    $html .= lang::render_lang_select('from', $data );
                    $html .= ' ' . lang::render_lang_select('to', $data );
                        
                    if ( isset( $data['setlr-locale'])) :
                        $html .= ' ' . lang::request_locales_form(get_the_id());
                    endif;
                    $html .= '</fieldset>';
                    $html .= '<fieldset><legend>' . __( 'Targeting Information', 'pdcrequest') . '</legend>';
                    $html .= self::get_targeting_info_form( $data );
                    $html .= '</fieldset>';
                    $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
                    $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
                    $html .= '<input type="text" id="form_title" name="title" value="' . $data['title'] . '" required></label></p>';
                    $html .= '</fieldset>';
                    $html .= '<fieldset class="setlr-request">';
                    $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
                    $html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
                    $html .= '<textarea id="form_content" spellcheck="false" name="content" required>' . $data['content'] . '</textarea></label></p>';
                    $html .= '</fieldset>';
                        //$html .= '<div id="request_word_count" class="pdcrequest-word-count">';
                        //$html .= '<p>' . __('Word Count:', 'pdcrequest');
                        //$html .= '<input type="text" id="pdcrequest-word-count-total" name="word_count" style="width:100px;" disabled>';
                    $html .= '<input type="hidden" name="word_count" value="">';
                    $html .= '</p>';
                    $html .= '</div>';
                    $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
                    $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
                    $html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $data['setlr_request_notes'][0] ) . '</textarea></p>';
                    $html .= '</fieldset>';
                        
                
                    
                    break;
            endswitch;
            
            return $html;
        }
        
        public static function is_quote_needed( $data ) {
            //[setlr_service] => Array ( [0] => question )
            if ( $data['setlr_service'][0] === 'question') :
                $html = '';
            else :
                $html = '<input type="hidden" name="quote_needed" value="yes">';
            endif;
            
            
            return $html;
            
        }
}