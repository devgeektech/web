<?php


/**
 * Description of Setlr_Request_Profile
 *
 * @author philippe
 */
class Setlr_Request_Profile {
    
    
    public function __construct() {
        
    }
    
    public function create_request( $data = array() ) {
        
        
        $html  = '<form name="pdc_request_new" id="pdc_request_new" action="' . admin_url( 'admin.php' ) . '" method="post">';
        $html .= '<fieldset id="choose-service"><legend>' . __( 'Choose A Service', 'pdcrequest') . '</legend>';
        $html .= Request_Form::get_services_select();
        $html .= '</fieldset>';
        //empty container div to receive ajax form fragment depending on service selected
        if (!empty( $data)) :
            $html .=  '<div id="container">';
            $html .= $this->get_content($data);
            $html .= '</div>';
        else :
            $html .= '<div id="container"></div>';
        endif;
                            
        $html .= '<div id="submit-form">';
        $html .= '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Get Quote', 'pdcrequest' ) . '"  /></p>';
        $html .= '<input type="hidden" name="quote_needed" value="yes">';
        $html .= '<input type="hidden" name="action" value="pdc_new_request_profile">';
        $html .= wp_nonce_field( 'pdc-new-request', 'pdcrequestnonce', true, false );
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }
    
    
    public function validate_request( $data ) {
        if ( ! isset( $data['pdcrequestnonce'] ) || ! wp_verify_nonce( $data['pdcrequestnonce'], 'pdc-new-request' ) ) :
            exit;
        endif;
        
        //check required fields
        if ( $data['service'] == 'translation' ) :
            $required = array( 'title', 'content', 'to-lang', 'from-lang' );
        else :
            $required = array( 'title', 'content', 'question_about' );
        endif;
                
        foreach ( $required as $field ) :
            if ( ! isset( $data[$field] ) || empty( $data[$field] ) ) :
		$errors[] = $field . '_missing';
            endif;
	endforeach;
        
        if ( ! empty( $errors ) ) :
            
            return array( false, $errors, $data );
	else :
            
            return $this->show_quote( $data );
        endif;
        
    }
    
    
    public function show_quote( $data ) {
        //let's make a meaningful quote
        $quote_num = Setlr_Pricing::get_quote_request_total($data);
        
        return $quote_num;
    }
    
    
    public function get_content( $data ) {
            
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
                    $html .= '<input type="hidden" name="setlr_word_count" value="">';
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
        
        
        public function validate_customer( $data ) {
            
            $required = array( 'firstname', 'lastname', 'email', 'user_pass', 'user_pass_confirm', 'billing_country' );
            $error = array();
            foreach ( $required as $field ) :
                if ( ! isset( $data[$field] ) || empty( $data[$field] ) ) :
                    $error[] = $field;
                endif;
            endforeach;
            
            // let's validate the email
            $email = $data['email'];
            if ( ! is_email( $email ) ) $error[] = 'email_invalid';
            
            if ( $data['user_pass'] != $data['user_pass_confirm'] ) $error[] = 'non_matching_passwords';
                
            if ( ! empty( $error) ) :
                //redirect to form
                write_log( $error );
                return $error;
            endif;
            
            $nicename =explode( '@', $email );
            
            $nickname = ( isset( $data['nickname'] ) ) ? $data['nickname'] : $nicename[0];
            write_log( 'nickname=' . $nickname );
            //let's create the customer
            $args = array(
                'user_login'    => $email,
                'user_nicename' => $nicename[0],
                'user_email'    => $email,
                'user_pass'     => $data['user_pass'],
                'nickname'      => $nickname,
                'first_name'    => $data['firstname'],
                'last_name'     => $data['lastname']
            );
            $user_id = wp_insert_user( $args );
            write_log( $user_id);
            
            if ( is_wp_error( $user_id) || $user_id == 0 ) :
                //something went wrong
                return $user_id;
            endif;
            // send email with password
            
            //we keep inserting user data
            update_user_meta( $user_id, 'phone', $data['phone'] );
            update_user_meta( $user_id, 'billing_company', $data['billing_company'] );
            update_user_meta( $user_id, 'billing_address_1', $data['billing_address_1'] );
            update_user_meta( $user_id, 'billing_address_2', $data['billing_address_2'] );
            update_user_meta( $user_id, 'billing_postcode', $data['billing_postcode'] );
            update_user_meta( $user_id, 'billing_city', $data['billing_city'] );
            update_user_meta( $user_id, 'billing_country', $data['billing_country'] );
            
            //now we insert request
            $args = array(
                'post_title'    => $data['title'],
                'post_content'  => $data['content'],
                'post_author'   => $user_id,
                'post_type'     => 'request'
            );
            
            $post_id = wp_insert_post( $args );
            
            if ( is_wp_error( $post_id) || $post_id == 0 ) :
                //something went wrong
                return $post_id;
            endif;
            
            // let's insert the rest of the data
            switch ( $data['service'] ) :
                case 'question' :
                    lang::save_lang_info( $post_id, 'question_about', $data['question_about'] );
                    lang::save_lang_info( $post_id, 'questionlang', $data['questionlang'] );
                    update_post_meta( $post_id, 'question_country', $data['question_country'] );
                    if ( isset( $data['locale'])) :
                        lang::save_request_locale( $post_id, $data['questionlang'], $data['locale'] );
                    endif;
                    break;
                case 'translation' :
                default :
                    update_post_meta( $post_id, 'requestlang', $data['to-lang'] );
                    update_post_meta( $post_id, 'doclang', $data['from-lang'] );
                    if ( isset( $data['locale'])) :
                        lang::save_request_locale( $post_id, $data['to-lang'], $data['locale'] );
                    endif;
                    break;
            endswitch;
            request_status::update_status( $post_id );
            request_status::update_status_history($post_id, 'open');
            //update service
            $services = pdcrequest_list_services();
            if ( isset( $data['service'] ) && Request_Form::validate_value( $data['service'], $services )) :
                update_post_meta( $post_id, 'setlr_service', $data['service'] );
            endif;
            
            //update specialisation
            $specialisations = pdcrequest_list_specialisations();
            if ( isset( $data['specialisation'] ) && Request_Form::validate_value( $data['specialisation'], $specialisations ) ) :
                update_post_meta( $post_id, 'setlr_specialisation', $data['specialisation'] );
            endif;
            
            //update age groups
            $age_groups = pdcrequest_list_age_groups();
            if ( isset( $data['age_group'] ) && Request_Form::validate_value( $data['age_group'], $age_groups ) ) :
                update_post_meta( $post_id, 'setlr_age_group', $data['age_group'] );
            endif;
                                
            //update genders
            $genders = pdcrequest_list_genders();
            if ( isset( $data['gender'] ) && Request_Form::validate_value( $data['gender'], $genders ) ) :
                update_post_meta( $post_id, 'setlr_gender', $data['gender'] );
            
            endif;
            if ( isset( $data['word_count'] ) && absint( $data['word_count'] ) ) :
                update_post_meta( $post_id, 'setlr_word_count', $data['word_count'] );
            endif;
            
            if ( isset( $data['setlr_request_notes'] ) ) :
                update_post_meta( $post_id, 'setlr_request_notes', $data['setlr_request_notes'] );
            endif;
            
            return array( 'customer_id' => $user_id, 'project_id' => $post_id);
        }
}
