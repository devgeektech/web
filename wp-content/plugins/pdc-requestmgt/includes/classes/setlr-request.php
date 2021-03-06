<?php

/**
 * Description of Setlr_Request
 *
 * @author philippe
 */
class Setlr_Request {
    
    
    public function __construct() {
        
    }
	
	
    public static function create_form_new_request( $data = array() ) {
	global $current_user;
	get_currentuserinfo();
		
        $html = '';
        
        if ( isset( $data['setlr_request_notes']) ) :
            if ( is_array( $data['setlr_request_notes'] ) ) :
                $notes = $data['setlr_request_notes'][0];
            else :
                $notes = $data['setlr_request_notes'];
            endif;
        else :
            $notes = '';
        endif;

        $handle= 'request_form_fragment';
        $list = 'registered';
        $list2 = 'enqueued';
        if ( wp_script_is( $handle, $list ) ) :
            write_log( 'request_form_fragment is registered');
        endif;
        
        if ( wp_script_is( $handle, $list2 ) ) :
            write_log( 'request_form_fragment is enqueued');
        endif;
        //the form
        $html  = '<form name="pdc_request" enctype="multipart/form-data" id="pdc_request" action="' . admin_url( 'admin.php' ) . '" method="post">';
        
        if ( isset( $data['setlr_total_price'] ) && $data['setlr_total_price'][0] ) :
            $total_price = pdcrequest_format_amount( 'GBP', $data['setlr_total_price'][0] );
            $html .= '<p>' . sprintf( __( 'Paid: %s', 'pdcrequest'), $total_price ) . '</p>';
        endif;
        
        // choose service
        $html .= '<fieldset id="choose-service"><legend>' . __( 'Choose A Service', 'pdcrequest') . '</legend>';
        $html .= self::get_services_select( $data );
        $html .= '</fieldset>';
            
        //empty container div to receive ajax form fragment depending on service selected
        $html .=  '<div id="container">';
        $html .= self::get_content( $data );
        $html .= '</div>';
                            
                            
        $html .= '<div id="submit-form">';
        $html .= self::is_quote_needed( $data );
                            
        if ( isset( $data['ID'] ) && absint( $data['ID'] ) ) :
            $html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Update Request', 'pdcrequest' ) . '"  /></p>';
            $html .= '<input type="hidden" name="post_id" value="' . absint( $data['ID'] ) . '">';
            $html .= '<input type="hidden" name="updated" value="updated">';
        else :
            $onclick = 'onclick="ga(' . 'send' .', ' . 'event' . ', ' . 'project-add' . ', ' . 'submit-finished' . ')"';
            
            $html .= '<p class="submit"><input type="submit" name="submit" id="submit" '. $onclick . ' class="button button-primary" value="' . __( 'Get Quote', 'pdcrequest' ) . '" /></p>';
            $html .= '<input type="hidden" name="quote_needed" value="yes">';
        endif;
                            
        $html .= '<input type="hidden" name="action" value="pdc_request">';
        $html .= '<input type="hidden" name="user" value="' . absint( $current_user->ID ) . '">';
        $html .= wp_nonce_field( 'pdc-new-request', 'pdcrequestnonce', true, false );
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }
    
    
    public static function render_form_new_request( $data = array() ) {
        $request_form = new Setlr_Request();
        wp_enqueue_script('request_lang_locale');
        
        echo $request_form->create_form_new_request( $data );
        exit();
    }
    
    
    public static function enqueue_scripts() {
        
    }
    
    
    /**
     * 
     * @return Void
     */
    public static function validate_request_form() {
        write_log( 'Setlr_Request validate_request_form');
        
        $data = $_POST;
        write_log( $_POST );
        
        /*
        if ( ! isset( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'pdc-new-request' ) ) :
            write_log( 'nonce did not validate');
        
            if ( ! isset( $data['pdcrequestnonce']) || ! wp_verify_nonce( $data['pdcrequestnonce'], 'pdc-new-request' ) ) :
                write_log( 'pdcrequestnonce did not validate');
                exit();
            endif;
	endif;
	*/
        if ( isset( $_POST['form'] ) ) :
            parse_str( $_POST['form'], $data ) ;
        endif;
       
	//check required fields
        if ( $data['service'] == 'translation' ) :
            $required = array( 'content', 'to-lang', 'from-lang' );
	else :
            $required = array( 'content', 'question_about' );
        endif;
                
	foreach ( $required as $field ) :
            if ( ! isset( $data[$field] ) || empty( $data[$field] ) ) :
		$errors[] = $field . '_missing';
            endif;
	endforeach;
		
        if ( empty( $errors ) ) :
            return true;
        else :
            return $errors;
        endif;
        
        write_log( 'Setlr_Request validate_request_form ended');
		
    }
        
    public static function insert_post( $data ) {
        // do something
	$post = array(
            'post_content'   => $data['content'],
            //'post_name'      => [ <string> ] // The name (slug) for your post
            'post_title'     => wp_strip_all_tags( $data['title'] ),
            'post_status'    => 'publish',
            'post_type'      => 'request'  
	); 
        
        //let's add the post author as we atre not sure it is the default user
        if ( isset( $data['user_id'] ) && absint( $data['user_id'] ) ) :
            $post = array_merge( $post, array( 'post_author' => $data['user_id'] ) );
        endif;
        
        /* check if update or new project */
        if ( isset( $data['post_id'] ) && absint( $data['post_id'] ) ) :
            //we need to update
            $post = array_merge( $post, array( 'ID' => $data['post_id']));
            $update = wp_update_post($post, true );
                            
            // wp_update_post can = 0 if nothing in post was updated
            if ( $update != 0 && ! is_wp_error( $update )) :
                $request_id = $update;
            else :
                $request_id = absint( $data['post_id'] );
            endif;
        else :
            //we insert the new request
            $request_id = wp_insert_post( $post, true );
        endif;
        
        /* check if valid update or WP_Error object */
        if ( is_wp_error( $request_id ) ) :
            write_log( $request_id);
            $message = apply_filters( 'pdcrequest-insertmessage-error', __( 'Your request could not be published', 'pdcrequest' ), $wp_error );
            $message_type = 'error';
            $redirect_page = get_page_by_path('request-translation');
        else :
            if ( $data['service'] == 'translation' ) :
                lang::save_lang_info( $request_id, 'to-lang', $data['to-lang'] );
                lang::save_lang_info( $request_id, 'from-lang', $data['from-lang'] );
            else :
                lang::save_lang_info( $request_id, 'question_about', $data['question_about'] );
                lang::save_lang_info( $request_id, 'questionlang', $data['questionlang'] );
                update_post_meta( $request_id, 'question_country', $data['question_country'] );
            endif;
            
            request_status::update_status( $request_id );
            request_status::update_status_history($request_id, 'open');
                                
                                
            // we need a payment status
            /* @todo: verify request type  && word count to check if need to update price */
            $payment_status = Payment_Status::get_payment_status( $request_id );
            if ( ! isset( $payment_status ) || $payment_status == false ) :
                Payment_Status::update_status($request_id, 'pending' );
            endif;
                               
                                
            if ( isset( $data['locale'])) :
                lang::save_request_locale( $request_id, $data['to-lang'], $data['locale'] );
            endif;
                                
            //update service
            $services = pdcrequest_list_services();
            if ( isset( $data['service'] ) && self::validate_value( $data['service'], $services )) :
                update_post_meta( $request_id, 'setlr_service', $data['service'] );
            endif;
                                
                                
            //update specialisation
            $specialisations = pdcrequest_list_specialisations();
            if ( isset( $data['specialisation'] ) && self::validate_value( $data['specialisation'], $specialisations ) ) :
                update_post_meta( $request_id, 'setlr_specialisation', $data['specialisation'] );
            endif;
                                
            // update word count
            if ( isset( $data['word_count'] ) && absint( $data['word_count'] ) ) :
                update_post_meta( $request_id, 'setlr_word_count', $data['word_count'] );
            endif;
                                
            //update age groups
            $age_groups = pdcrequest_list_age_groups();
            if ( isset( $data['age_group'] ) && self::validate_value( $data['age_group'], $age_groups ) ) :
                update_post_meta( $request_id, 'setlr_age_group', $data['age_group'] );
            endif;
                                
            //update genders
            $genders = pdcrequest_list_genders();
            if ( isset( $data['gender'] ) && self::validate_value( $data['gender'], $genders ) ) :
                update_post_meta( $request_id, 'setlr_gender', $data['gender'] );
            endif;
            
            // update notes
            if ( isset( $data['setlr_request_notes'] ) ) :
                update_post_meta( $request_id, 'setlr_request_notes', $data['setlr_request_notes'] );
            endif;
                                
            if ( isset( $data['updated'] ) && $data['updated'] == 'updated' ) :
                $message = apply_filters( 'pdcrequest-insertmessage', sprintf(__( 'Your project %s has been updated', 'pdcrequest' ), $post['post_title'] ) );
            else :
                $message = apply_filters( 'pdcrequest-insertmessage', sprintf(__( 'Your project %s has been published.', 'pdcrequest' ), $post['post_title'] ) );
            endif;
            
            $message_type = 'success';
            $redirect_page = get_page_by_path( 'my-dashboard' );
                                
            if ( isset( $data['quote_needed'] ) && 'yes' === $data['quote_needed'] ) :
                $redirect_page = get_page_by_path( 'project-quote' );
                $message = apply_filters( 'pdcrequest-insertmessage', sprintf( __('Your project %s is almost ready. You need to proceed to payment'), $post['post_title'] ) );
            endif;
            
        endif; // end check if valid
        
        return array( $request_id, $redirect_page, $message, $message_type );
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
        /*
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
        */
        /* Gender */
        /*
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
        */
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
                //$html .= '</p>'; 
                //$html .= '</div>';
                $html .= '<input type="hidden" name="word_count" value="">';
                
                        
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
        write_log( 'Setlr_Request get_content');
        write_log( $data );
        $type = ( isset( $data['setlr_service'][0] ) ) ? esc_attr( $data['setlr_service'][0] ) : '';
        $type = ( empty($type) && isset( $data['service'] ) ) ? esc_attr( $data['service'] ) : '';
        $content = (isset($data['content'])) ? $data['content'] : '';
        
        switch ( $type ) :
                
            case 'question' :
                
                //$html = '<fieldset><legend>' . __('I have a question about', 'pdcrequest') . '</legend>';
                $html = '<p>' . lang::render_lang_select('question_about', $data ) . '</p>';    
                //$html .= '</fieldset>';
                
                //$html = '<fieldset><legend>' . __('That I would like to be answered in', 'pdcrequest') . '</legend>';
                $html .= '<p>' . lang::render_lang_select('questionlang', $data ) . '</p>';
                //$html .= '</fieldset>';
                
                $html .= '<fieldset class="setlr-request">';
                $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
                $html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
                $html .= '<textarea spellcheck="false" id="form_content" name="content" required>' . esc_textarea( $content ) . '</textarea></label></p>';
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
                    
				$url = get_bloginfo('url');
                $html .= '<fieldset class="setlr-request">';
                $html .= '<legend>' . __( 'Fill in your text', 'pdcrequest') . '</legend>';
                $html .= '<p><label for="content">' . __( 'Content', 'pdcrequest' );
                $html .= '<input type="file" name="source_file" class="upload_file_to_text"> <b>We currently support:</b> ';
                $html .= '<img class="file-icons" style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/pdf.png">';
                $html .= '<img class="file-icons"style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/txt.png">';
                $html .= '<img class="file-icons" style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/rtf.png">';
                $html .= '<img class="file-icons" style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/odt.png">';
                $html .= '<img class="file-icons" style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/doc.png">';
                $html .= '<img class="file-icons" style="height:32px" src="'.$url.'/wp-content/plugins/pdc-requestmgt/assets/images/docx.png">';
				
				
                $html .= '<input type="hidden" value="" name="source_file_path" id="source_file_path">';
                $html .= '<textarea id="form_content" class="form_content_from_file" spellcheck="false" name="content" required>' . esc_textarea( $content ) . '</textarea></label></p>';
                $html .= '</fieldset>';
                        
                //$html .= '<div id="request_word_count" class="pdcrequest-word-count">';
                //$html .= '<p>' . __('Word Count:', 'pdcrequest');
                //$html .= '<input type="text" id="pdcrequest-word-count-total" name="word_count" style="width:100px;" disabled>';
                //$html .= '</p>';
                //$html .= '</div>';
                $html .= '<input type="hidden" nam="word_count" value="">';
				$url = get_bloginfo('url');
				$upload_url = get_bloginfo('url')."/wp-content/plugins/pdc-requestmgt/uploads";
                // $html .= "<script type='text/javascript'src='$url/plugins/pdc-requestmgt/assets/js/customjs.js'></script>"; 
                $html .= "<script>
				jQuery('.upload_file_to_text').on('change',function(){  
					var formData = new FormData();
					formData.append('file', this.files[0]);
					var request = new XMLHttpRequest();
					request.open('POST', '$url/wp-content/plugins/pdc-requestmgt/send_file.php');
					request.onload = function(oEvent) {
						if (request.status == 200) {
							jQuery('.progress-div').hide();
							jQuery('.register_controls input[type=submit]').prop('disabled', false);
							var obj = JSON.parse(request.response);
							jQuery('.form_content_from_file').text(obj.result);
							 
						}
					};
					request.send(formData);
				});
				</script>";
				$html .= "<script>
				jQuery(document).ready(function(){ 
					jQuery('#submit').on('click',function(){ 
						var form = jQuery('form')[0];
						var formData = new FormData(form);
			 
						jQuery.ajax({
							url: '$url/wp-content/plugins/pdc-requestmgt/upload_file.php',
							data: formData,
							cache: false,
							contentType: false,
							processData: false,
							type: 'POST',
							success: function(data){
									var obj = JSON.parse(data);
									var full_path='$upload_url/'+obj.source_file_path;
							        jQuery('#source_file_path').val(full_path);
							}
						});
					});
				});
				</script>"; 
                
                        
                break;
        endswitch;
            
        return $html;
    }
    
    public static function is_quote_needed( $data ) {
        //[setlr_service] => Array ( [0] => question )
        if ( isset( $data['setlr_service'][0] ) && $data['setlr_service'][0] === 'question' ) :
            $html = '';
        elseif ( isset( $data['service'] ) && $data['service'] === 'question') :
            $html = '';
        else :
            $html = '<input type="hidden" name="quote_needed" value="yes">';
        endif;
            
        return $html;    
    }
    
    
    public static function ajax_get_quote() {
        write_log( 'Setlr_Request ajax_get_quote' );
        write_log( $_POST );
        /* 1) let's validate request part */
        $validate = self::validate_request_form( $_POST );
        
        if (is_bool($validate) && $validate == true ) :
            /* 2) get the quote */
            $total = Setlr_Pricing::get_quote_request_total( $_POST );
            
            /* 3) output the quote */
            if ( is_float( $total ) ) :
                echo '<p>' . __( 'Here is our best quote for your project', 'pdcrequest') . '</p>';
                echo '<p class="aligncenter"><strong>' . pdcrequest_format_amount( 'GBP', $total ) . '</strong></p>';
                echo '<p>' . __( 'If interested please continue filling this form', 'pdcrequest') . '</p>';
                echo '<input type="hidden" name="total" id="total" value="' . $total . '">';
                parse_str( $_POST['form'], $data );
                
                $part_2 = self::show_form_continued( $data );
                
                if ( !empty( $part_2 ) ) :
                    echo $part_2;
                    
                    $profile = new Setlr_Profile_Form();
                    $user_profile = $profile->show_registration_form();
                    
                    if ( !absint( $user_profile ) ) :
                        echo $user_profile;
                    else : 
                        write_log( 'Setlr_Request ajax_get_quote -->display_actions_fragment');
                        write_log( 'user_profile='.$user_profile);
                        write_log( 'total='. $total);
                        echo self::display_actions_fragment( $user_profile, $total );
                    endif;
                endif;
            else :
                echo '<p>' . __( 'There was an error in parsing your project', 'pdcrequest' ) . '</p>';
            endif;
            exit();
        else :
            //go back to form and output errors
            write_log( $validate );
            echo '<p class="setlr-error">' . __( 'There was an error in parsing your project.', 'pdcrequest' ) . '</p>';
            exit();
        endif;
        
        
    }
    
    
    public function asides() {
                    
                $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
                $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
                $html .= '<input type="text" id="form_title" name="title" value="' . $data['title'] . '" required></label></p>';
                $html .= '</fieldset>';
                
                
                $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
                $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
                $html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $data['setlr_request_notes'][0] ) . '</textarea></p>';
                $html .= '</fieldset>';
    }
    
    
    public static function show_form_continued( $data ) {
        write_log( 'Setlr_Request show_form_continued');
       
        $html = '';
        switch ( $data['service'] ) :
            case 'translation' :
                $html .= '<fieldset><legend>' . __( 'Targeting Information', 'pdcrequest') . '</legend>';
                $html .= self::get_targeting_info_form( $data );
                $html .= '</fieldset>';
                break;
            case 'question' :
                $html .= '<fieldset><legend>' . __( 'Is it specific to a country?', 'pdcrequest') . '</legend>';
                $html .= '<p><select name="question_country">';
                $html .= '<option value="">' . __( 'no', 'pdcrequest') . '</option>';
                foreach( pdcrequest_countries_select() as $code => $name ) :
                    $html .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, $data['question_country'][0], false ) . '>' . esc_html( $name ) . '</option>';
                endforeach;
                $html .= '</select></p>';
                $html .= '</fieldset>';
                break;
            default :
                $html .= 'error no data-service';
                break;
        endswitch;
        $title = (isset($data['title'])) ? $data['title'] : '';
        
        $html .= '<fieldset><legend>' . __( 'Give Your Project A Name', 'pdcrequest') . '</legend>';
        $html .= '<p><label for="title">' . __( 'Project Name', 'pdcrequest' );
        $html .= '<input type="text" id="form_title" name="title" value="' . esc_attr( $title ) . '" required></label></p>';
        $html .= '</fieldset>';
                
        $notes = ( isset( $data['setlr_request_notes'] ) ) ? $data['setlr_request_notes'][0] : '';
        
        $html .= '<fieldset><legend>' . __( 'Helpful Notes & Context', 'pdcrequest' ) . '</legend>';
        $html .= '<p><label for="setlr_request_notes">' . __( 'Add any helpful notes, like links to a webpage, links to screenshots or other online documents that would be useful to the helper for understanding and context', 'pdcrequest') . '</label><br>';
        $html .= '<textarea spellcheck="false" name="setlr_request_notes">' . esc_textarea( $notes ) . '</textarea></p>';
        $html .= '</fieldset>';
        
        return $html;
    }
      
    
    public static function ajax_full_validate() {
            write_log( 'Setlr_Request ajax_full_validate');
            write_log( $_POST );
            
            $form = $_POST['form'];
            parse_str($form, $data);
            write_log( $data );
            $nonce = $_POST['nonce'];
            
            $data = array_merge( $data, array( 'nonce' => $nonce ) );
            if ( !empty( $data ) ) :
               self::validate_full_request($data);
            endif;
            
        }
        
        
    public static function validate_full_request( $data ) {
        write_log( 'Setlr_Request validate_full_request');
        /*
        $nonce = ( isset( $data['nonce'] ) ) ? $data['nonce'] : '';
        if ( $nonce === '' ) :
            $nonce = ( isset( $data['pdcrequestnonce'] ) ) ? $data['pdcrequestnonce'] : '';
        endif;
        
        if ( $nonce === '' || !wp_verify_nonce( $nonce, 'pdc-new-request' ) ) :
            write_log( 'pdcrequestnonce did not validate');
            write_log( $nonce );
            exit();
	endif;
        */
        if (isset( $data['user_id'] ) && absint( $data['user_id'] ) ) :
            //we have a user
            //check the form
            //check required fields
            if ( isset( $data['service'] ) ) :
                switch ( $data['service'] ) :
                    case 'translation' :
                        $required = array( 'content', 'to-lang', 'from-lang' );
                        break;
                    case 'question' :
                        $required = array( 'content', 'question_about' );
                        break;
                endswitch;   
            else :
                write_log( 'Setlr_Request validate_full_request : data->service missing' );
            endif;
            
            if ( !empty( $required ) ) :
                foreach ( $required as $field ) :
                    if ( ! isset( $data[$field] ) || empty( $data[$field] ) ) :
                        $errors[] = $field . '_missing';
                    endif;
                endforeach;
            else :
                write_log( 'Setlr_Request validate_full_request : required missing ');
            endif;
            
            if ( isset( $data['content'] ) && isset( $data['title'] ) ) :
                list( $request_id, $redirect_page, $message, $message_type ) = self::insert_post($data);
            endif;
    
		   add_post_meta($request_id, 'source_file_path', $data['source_file_path']);
		   
            if ( absint( $request_id ) ) :
                $page = $page = get_page_by_path( 'payment' );
                if ( $page ) :
                    $total = Setlr_Pricing::get_quote_request_total( $data );
                    $page_id = $page->ID;
                    write_log( 'page_id' . $page_id );
                    write_log( 'user_id' . $data['user_id'] );
                    write_log( 'total' . $total );
                    write_log( 'request_id' . $request_id );
                    if ( absint( $data['user_id'] ) && is_float( $total ) && absint( $request_id ) ) :
                        write_log( 'redirect called' );
                        write_log( 'message= ' . $message );
                        $payment_data = array( $data['user_id'], $total, 'GBP', $request_id );
                        write_log( $payment_data );
                        Setlr_Payment::render_payment_form( $data['user_id'], $total, 'GBP', $request_id );
                       // Pdc_Requestmgt::redirect_to_page( $page_id, $message, $payment_data );
                        exit();
                    endif;
                endif;
                    
            endif;
        else :
            echo 'missing user_id';
            write_log( 'Setlr_Request validate_full_request : pb with missing user_id' );
            exit();
        endif;
    }
    
    public static function display_actions_fragment( $user_id, $total = 0 ) {
        write_log( 'Setlr_Request display_actions_fragment' );
        $display_name = pdcrequest_show_nickname( $user_id );
        $html = '<p>' . sprintf( __( 'You are signed in as %s', 'pdcrequest'), $display_name ) . '</p>';
        $html .= '<input type="hidden" name="user_id" value="' . absint( $user_id ) . '">';
        $html .= '<p class="submit"><input type="submit" name="submit" id="submit2" value="' . sprintf( __('Proceed to payment of %s', 'pdcrequest'), pdcrequest_format_amount( 'GBP', $total ) ) . '"></p>';
        $html .= '<input type="hidden" name="action" value="pdcrequest_validate_full_request">';
        $html .= wp_nonce_field('full_request', 'pdcrequestnonce', true, false );
        
        return $html;
    }
    
    
    public static function show_update_request_form( $project_id, $data = array() ) {
        global $current_user;
        wp_enqueue_script('request_lang_locale');
        write_log( 'show_update_request_form' );
        $html  = '<form name="pdc_update_request" id="pdc_update_request" action="' . admin_url( 'admin.php' ) . '" method="post">';
        
        list( $post_data, $metadata ) = self::get_project_data( $project_id );
        $data = array(
            'ID'        => $post_data->ID,
            'content'   => $post_data->post_content,
            'title'     => $post_data->post_title,
            'author_id' => $post_data->post_author,
            'date'      => $post_data->post_date,
        );
        write_log( 'metadata');
        write_log( $metadata);
        $extra = array();
        if ( isset( $metadata['to-lang'] ) && !empty( $metadata['to-lang'][0] ) ) :
            $extra['to-lang'] = $metadata['to-lang'][0];
        endif;
        if ( isset( $metadata['from-lang'] ) && !empty( $metadata['from-lang'][0] ) ) :
            $extra['from-lang'] = $metadata['from-lang'][0];
        endif;
        
        if ( isset( $metadata['question_about'] ) && !empty( $metadata['question_about'][0] ) ) :
            $extra['question_about'] = $metadata['question_about'][0];
        endif;
        if ( isset( $metadata['questionlang'] ) && !empty( $metadata['questionlang'][0] ) ) :
            $extra['questionlang'] = $metadata['questionlang'][0];
        endif;
        
        if ( isset( $metadata['setlr-locale'] ) && !empty( $metadata['setlr-locale'] ) ) :
            $extra['setlr-locale'] = $metadata['setlr-locale'][0];
        endif;
        if ( isset( $metadata['setlr_status'] ) && !empty( $metadata['setlr_status'][0] ) ) :
            $extra['setlr_status'] = $metadata['setlr_status'][0];
        endif;
        if ( isset( $metadata['payment_status'] ) && !empty( $metadata['payment_status'][0] ) ) :
            $extra['payment_status'] = $metadata['payment_status'][0];
        endif;
        if ( isset( $metadata['setlr_service'] ) && !empty( $metadata['setlr_service'][0] ) ) :
            $extra['setlr_service'] = $metadata['setlr_service'][0];
        endif;
        if ( isset( $metadata['setlr_request_notes'] ) && !empty( $metadata['setlr_request_notes'][0] ) ) :
            $extra['setlr_request_notes'] = $metadata['setlr_request_notes'][0];
        endif;
        if ( isset( $metadata['setlr_word_count'] ) && !empty( $metadata['setlr_word_count'][0] ) ) :
            $extra['setlr_word_count'] = $metadata['setlr_word_count'][0];
        endif;
        if ( isset( $metadata['setlr_specialisation'] ) && !empty( $metadata['setlr_specialisation'][0] ) ) :
            $extra['setlr_specialisation'] = $metadata['setlr_specialisation'][0];
        endif;
        $data = array_merge( $data, $extra );
        
        write_log( $data);
        
        if ( isset( $data['setlr_total_price'] ) && $data['setlr_total_price'] ) :
            $total_price = pdcrequest_format_amount( 'GBP', $data['setlr_total_price'] );
            $html .= '<p>' . sprintf( __( 'Paid: %s', 'pdcrequest'), $total_price ) . '</p>';
        endif;
        
        // choose service
        $html .= '<fieldset id="choose-service"><legend>' . __( 'Choose A Service', 'pdcrequest') . '</legend>';
        $html .= self::get_services_select( $data['setlr_service'] );
        $html .= '</fieldset>';
            
        //empty container div to receive ajax form fragment depending on service selected
        $html .=  '<div id="container">';
        $html .= self::get_content( $data );
        $html .= self::get_targeting_info_form( $data );
        $html .= '</div>';
                            
                            
        $html .= '<div id="submit-form">';
        
                            
        if ( isset( $data['ID'] ) && absint( $data['ID'] ) ) :
            $html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Update Request', 'pdcrequest' ) . '"  /></p>';
            $html .= '<input type="hidden" name="post_id" value="' . absint( $data['ID'] ) . '">';
            $html .= '<input type="hidden" name="updated" value="updated">';
            $html .= '<input type="hidden" name="title" value="' . $post_data->post_title . '">';
        endif;
                            
        $html .= '<input type="hidden" name="action" value="pdc_update_request">';
        $html .= '<input type="hidden" name="user" value="' . absint( $current_user->ID ) . '">';
        $html .= wp_nonce_field( 'update-request', 'nonce', true, false );
        $html .= '</div>';
        $html .= '</form>';
        
        return $html;
    }
    
    
    public static function validate_updated_request() {
        //validate nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update-request' ) ) :
            write_log( 'nonce did not validate');
            die( 'nonce pb');
        endif;
        
        // validate user
        if ( !isset( $_POST['user'] ) && ! absint( $_POST['user'] ) ) :
            die( 'no user reference' );
        endif;
        
        //validate ID
        if ( !isset( $_POST['post_id'] ) && ! absint( $_POST['post_id'] ) ) :
            die( 'no ID reference' );
        endif;
        
        //validate service
        if ( !isset( $_POST['service'] ) || ! in_array( $_POST['service'], array_keys( pdcrequest_list_services() ) ) ) :
            die( 'service not set');
        endif;
        
        switch ( $_POST['service'] ) :
            case 'translation':
                //validate langs
                lang::save_lang_info($_POST['post_id'], 'to-lang', $_POST['to-lang']);
                lang::save_lang_info($_POST['post_id'], 'from-lang', $_POST['from-lang'] );
                if ( isset( $_POST['locale'])) :
                    lang::save_request_locale( $_POST['post_id'], $_POST['to-lang'], $_POST['locale'] );
                endif;
                break;
            case 'question':
                //validate langs
                lang::save_lang_info($_POST['post_id'], 'to-lang', $_POST['to-lang']);
                lang::save_lang_info($_POST['post_id'], 'from-lang', $_POST['from-lang'] );
                if ( isset( $_POST['locale'])) :
                    //$request_id, $lang_code, $locale
                    lang::save_request_locale( $_POST['post_id'], $_POST['to-lang'], $_POST['locale'] );
                endif;
                break;
        endswitch;
        
        //validate new title and content
        if ( !isset( $_POST['title'] ) || !isset( $_POST['content'] ) || empty( $_POST['title'] ) || empty( $_POST['content']) ) :
            die( 'title or content missing');
        endif;
        
        $extra_price = self::verify_price( $_POST['post_id'], $_POST['service'], $_POST['content'], $_POST['from-lang'] );
        
        // save all info
            $post = array(
                'post_content'      => $_POST['content'],
                //'post_name'       => [ <string> ] // The name (slug) for your post
                'post_title'        => wp_strip_all_tags( $_POST['title'] ),
                'post_status'       => 'publish',
                'post_type'         => 'request',
                'ID'                => $_POST['post_id']
            ); 
            
            //update specialisation
            $specialisations = pdcrequest_list_specialisations();
            if ( isset( $_POST['specialisation'] ) && self::validate_value( $_POST['specialisation'], $specialisations ) ) :
                $data['setlr_specialisation'] = $_POST['specialisation'];
            endif;

            // update word count
            if ( isset( $_POST['word_count'] ) && absint( $_POST['word_count'] ) ) :
                $data['setlr_word_count'] = absint( $_POST['word_count']);
            endif;

            //update age groups
            $age_groups = pdcrequest_list_age_groups();
            if ( isset( $_POST['age_group'] ) && self::validate_value( $_POST['age_group'], $age_groups ) ) :
                $data['setlr_age_group'] = $_POST['age_group'];
            endif;

            //update genders
            $genders = pdcrequest_list_genders();
            if ( isset( $_POST['gender'] ) && self::validate_value( $_POST['gender'], $genders ) ) :
                $data['setlr_gender'] = $_POST['gender'];
            endif;

            // update notes
            if ( isset( $_POST['setlr_request_notes'] ) ) :
                $data['setlr_request_notes'] = $_POST['setlr_request_notes'];
            endif;
            
            $meta = array_merge( $post, $data );
            $name = 'updated_' . absint( $_POST['post_id'] );
            $transient = set_transient( $name, $meta, 1 * HOUR_IN_SECONDS );
            
        if ( $extra_price > 0 ) :
            // show quote form
            $page = get_page_by_path('project-quote');
            $message = sprintf( __( 'Your updates need a price increase of %s', 'pdcrequest' ), number_format( $extra_price, 2 ) );
            $project_data = array( 'project_id' => $_POST['post_id'], 'customer_id' => $_POST['user'], 'transient' => $name, 'amount' => $extra_price );
            
            Pdc_Requestmgt::redirect_to_page( $page->ID, $message, $project_data );
        else :
            
            $post_id = absint( $data['post_id'] );
        
            $update = wp_update_post( $post, true );
            
            foreach( $data as $key => $value ) :
                update_post_meta( $post_id, $key, $value );
            endforeach;
                
            $message = __( 'Your project has been updated.' );
            Pdc_Requestmgt::redirect_to_dashboard( $message );
        endif; 
    }
    
    
    public static function get_project_data( $project_id ) {
        $post = get_post( $project_id );
        $metadata = get_post_meta( $project_id );
        
        return array( $post, $metadata );
    }
    
    public static function verify_price( $post_id, $service, $content, $lang ) {
        $quote = new Setlr_Pricing( $post_id );
        $old_price = $quote->get_price();
        write_log( 'old=' . $old_price );
        $new_price = $quote->get_new_price( $service, $content, $lang );
        write_log( 'new=' . $new_price );
        
        return $new_price - $old_price;
    }
    
    
    public static function update_from_transient( $transient_name ) {
        if ( false === ( $data = get_transient( $transient_name ) ) ) :
            //no data to update
        
        else :
            write_log( 'update_from_transient' );
            write_log( $data );
            
            $post = array(
                'post_content'      => $data['content'],
                //'post_name'       => [ <string> ] // The name (slug) for your post
                'post_title'        => wp_strip_all_tags( $data['title'] ),
                'post_status'       => 'publish',
                'post_type'         => 'request',
                'ID'                => $data['post_id']
            );  
            $update = wp_update_post( $post, true );
            $post_id = absint( $data['post_id'] );
            
            unset( $data['content']);
            unset( $data['title']);
            unset( $data['post_id']);
            
            foreach( $data as $key => $value ) :
                update_post_meta( $post_id, $key, $value );
            endforeach;
        endif;
            
         
    }
    
    /**
     * update project with total amount paid and currency
     * @param int $project_id
     * @param float $total
     * @param string $currency
     */
    public static function update_project_payment( $project_id, $total, $currency ) {
        $price = get_post_meta( $project_id, 'setlr_total_price', true);
        if ( isset( $price ) && $price > 0 ) :
            // we are updating amount paid
            $amount = $price + $total;
            update_post_meta( $project_id, 'setlr_total_price', $amount );
        else :
            // initial payment
            update_post_meta( $project_id, 'setlr_total_price', $total );
        endif;
        // update currency
        update_post_meta( $project_id, 'setlr_currency', $currency );
        
    }
}
