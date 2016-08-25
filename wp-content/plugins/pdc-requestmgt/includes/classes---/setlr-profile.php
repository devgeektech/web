<?php

class Setlr_Profile {
    
    
    public function __construct() {
        
    }
    
    
    /**
     * Render a profile form on front for users (both customers and translators)
     * @return Void
     */
    public static function render_profile_form() {
        global $current_user;
        get_currentuserinfo();	
            
        if ( is_user_logged_in() ) :
            $plugin_origin = get_user_meta( $current_user->ID, 'plugin_origin', true );

            $firstname = ( $current_user->first_name ) ? sanitize_text_field( $current_user->first_name ) : '';
            $lastname = ( $current_user->last_name ) ? sanitize_text_field( $current_user->last_name ) : '';
            $email = ( $current_user->user_email ) ? sanitize_text_field( $current_user->user_email ) : '';
            $nickname = ( $current_user->nickname ) ? sanitize_text_field( $current_user->nickname ) : '';
            $billing_company = ( get_user_meta( $current_user->ID, 'billing_company', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_company', true ) ) : '';
            $billing_address_1 = ( get_user_meta( $current_user->ID, 'billing_address_1', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_address_1', true ) ) : '';
            $billing_address_2 = ( get_user_meta( $current_user->ID, 'billing_address_2', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_address_2', true ) ) : '';
            $billing_postcode = ( get_user_meta( $current_user->ID, 'billing_postcode', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_postcode', true ) ) : '';
            $billing_city = ( get_user_meta( $current_user->ID, 'billing_city', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_city', true ) ) : '';
            $billing_country = ( get_user_meta( $current_user->ID, 'billing_country', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_country', true ) ) : '';
            $phone = ( get_user_meta( $current_user->ID, 'phone', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'phone', true ) ) : '';
            $description = ( $current_user->description ) ? $current_user->description : '';
            $user_url = ( $current_user->user_url ) ? sanitize_text_field($current_user->user_url) : '';
            
            $apikey_array = Api_Key::show_apikey( $current_user->ID );

            $main_role = get_user_meta( $current_user->ID, 'main_role', true );

            $countries = pdcrequest_countries_select();

            $html = '';

            if ( !empty( $apikey_array)) :
                foreach ( $apikey_array as $item ) : 
                    $html .= '<fieldset><legend>' . __( 'API Key for WordPress Integration', 'pdcrequest') . '</legend>';
                    $html .= '<p><label>' . sprintf (__( 'API Key for %s', 'pdcrequest' ), $item['url']);
                    $html .= '<input type="text" name="apikey" value="' . sanitize_text_field( $item['apikey'] ) . '"></label>';
                    $html .= '<label>' . __('Copy the key above in your plugin', 'pdcrequest') . '</label></p></fieldset>';
               endforeach;
            endif;

            $html .= '<a href="#" id="profile-toggle" class="button-primary">' . __( 'Edit Your Profile', 'pdcrequest' ) . '</a>';
            $html .= '<div id="your-profile">';
            $html .= '<form id="profile-update" method="post" enctype="multipart/form-data" action="' . get_admin_url( NULL, 'admin-post.php', 'admin' ) . '">';

            /* Identity firstname, lastname, password, email, tel of user */
            $html .= '<fieldset><legend>' . __( 'Identity', 'pdcrequest' ) . '</legend>';
            $html .= '<p><label for="firstname">' . __( 'Firstname', 'pdcrequest' );
            $html .= '<input type="text" name="firstname" value="' . $firstname . '"></label></p>';
            $html .= '<p><label for="lastname">' . __( 'Lastname', 'pdcrequest' );
            $html .= '<input type="text" name="lastname" value="' . $lastname . '"></label></p>';
            $html .= '<p><label for="user_pass">' . __( 'Password', 'usermgt' ) . '<input type="password" name="user_pass" value=""></label></p>';
            $html .= '<p><label for="user_pass_confirm">' . __( 'Password Confirm', 'usermgt' ) . '<input type="password" name="user_pass_confirm" value=""></label></p>';
            $html .= '<p><label for="email">' . __( 'Email', 'pdcrequest' );
            $html .= '<input type="email" name="email" value="' . $email . '"></label></p>';
            $html .= '<p><label for="phone">' . __( 'Phone', 'pdcrequest' );
            $html .= '<input type="tel" name="phone" value="' . $phone . '"></label></p>';
            $html .= '<p><label for="nickname">' . __( 'Nickname', 'pdcrequest');
            $html .= '<input type="text" name="nickname" value="' . $nickname . '"></label></p>';
            $html .= '</fieldset>';

            /* Logo */
            if ( (is_array( $main_role ) && in_array( 'customer', $main_role )) || 'customer' == $main_role ) :
                $html .= '<fieldset><legend>' . __( 'Logo', 'pdcrequest' ) . '</legend>';
                $html .= self::picture_form( $current_user->ID, true );
                $html .= '</fieldset>';
                 /* billing address */
            $html .= '<fieldset><legend>' . __( 'Billing Address', 'pdcrequest' ) . '</legend>';
            $html .= '<p><label for="billing_company">' . __( 'Company', 'pdcrequest' );
            $html .= '<input type="text" name="billing_company" value="' . $billing_company . '"></label></p>';
            else :
                $html .= '<fieldset><legend>' . __( 'Profile Picture', 'pdcrequest' ) . '</legend>';
                $html .= self::picture_form( $current_user->ID, false );
                $html .= '</fieldset>';
                 /* billing address */
            $html .= '<fieldset><legend>' . __( 'Living Address', 'pdcrequest' ) . '</legend>';
            endif;

            $html .= '<p><label for="billing_address_1">' . __( 'Address', 'pdcrequest' );
            $html .= '<input type="text" name="billing_address_1" value="' . $billing_address_1 . '"></label></p>';
            $html .= '<p><label for="billing_addres_2">' . __( 'Address Complement', 'pdcrequest' );
            $html .= '<input type="text" name="billing_address_2" value="' . $billing_address_2 . '"></label></p>';
            $html .= '<p><label for="billing_postcode">' . __( 'Zipcode/Postcode', 'pdcrequest' );
            $html .= '<input type="text" name="billing_postcode" value="' . $billing_postcode . '"></label></p>';
            $html .= '<p><label for="billing_city">' . __( 'City', 'pdcrequest' );
            $html .= '<input type="text" name="billing_city" value="' . $billing_city . '"></label></p>';
            $html .= '<p><label for="billing_country">' . __( 'Country', 'pdcrequest' );
            $html .= ' <select name="billing_country">';
            foreach( $countries as $key => $value ) :
                    $html .= '<option value="' . $key . '" ' . selected( $key, $billing_country, false ) . '>' . $value . '</option>';
            endforeach;
            $html .= '</select></label></p>';
            $html .= '</fieldset>';

            /* Context of website */
            if ( (is_array( $main_role ) && in_array( 'customer', $main_role )) || 'customer' == $main_role || current_user_can( 'customer' ) ) :


                $html .= '<fieldset><legend>' . __( 'About your website', 'pdcrequest' ) . '</legend>';
                $required = ( ! empty( $plugin_url ) ) ? 'required' : '';
                $html .= '<p><label class="' . $required . '>' . __( 'URL (website address)', 'pdcrequest');
                $html .= '<input type="text" name="user_url" value="' . $user_url . '" ' . $required . '></label></p></fieldset>';


                $html .= '<fieldset><legend>' . __( 'Select your website default language', 'pdcrequest' ) . '</legend>';
                $html .= lang::native_language_form( $current_user->ID ); 
                $html .= '</fieldset>';

                //$html .= '<fieldset><legend>' . __( 'Select the languages you need translation to', 'pdcrequest' ) . '</legend>';
                //$html .= lang::languages_form( $current_user->ID );
                $html .= '</fieldset>';
                
                $html .= '<p><label>' . __( 'Give context to our translators', 'pdcrequest' ) . '<textarea name="description">' . $description . '</textarea></p>';

            /* Payment info */    
            //$html .= methodStripe::toggle_cc_form( $current_user->ID );
            else :
                $html .= Paypal_Payment::show_account_form( $current_user->ID );
                //translator profile
                $html .= '<fieldset>';
                $html .= self::gender_form( $current_user->ID );
                $html .= self::date_of_birth( $current_user->ID );
                $html .= '</fieldset>';
                $html .= '<fieldset class="setlr-language-form"><legend>' . __( 'Select your default language', 'pdcrequest' ) . '</legend>';
                $html .= lang::native_language_form( $current_user->ID ); 
                $html .= '</fieldset>';
                $html .= lang::locales_form();
                $html .= '<fieldset><legend>' . __( 'Select the languages you can translate from', 'pdcrequest' ) . '</legend>';
                $html .= lang::languages_form( $current_user->ID );
                $html .= '</fieldset>'; 

                $html .= '<fieldset id="services"><legend>' . __( 'Select all services you can do', 'pdcrequest' ) . '</legend>';
                $html .= self::services_form( $current_user->ID );
                $html .= '</fieldset>';
                $html .= '<fieldset><legend>' . __( 'Select all specialisations that you have', 'pdcrequest' ) . '</legend>';
                $html .= self::specialisations_form( $current_user->ID );
                $html .= '</fieldset>';
                $html .= '<fieldset><legend>' . __( 'Indicate your experience', 'pdcrequest' ) . '</legend>';
                $html .= self::experience_form( $current_user->ID );
                $html .= '</fieldset>';
                $html .= '<p><label>' . __( 'About You', 'pdcrequest' ) . '<textarea name="description">' .esc_textarea( $description ) . '</textarea></p>';
                $html .= '<div id="container-countries">';
                $user_services = get_user_meta( $current_user->ID, 'setlr_services', true);
                
                if ( in_array( 'question', $user_services ) ) :
                    $html .= self::get_question_countries_form( $current_user->ID );
                endif;
                $html .= '</div>';
            endif;
            $html .= '<a href="#" id="setlr-cancel" class="button-secondary">' . __( 'Cancel', 'pdcrequest' ) . '</a>';
            $html .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Update', 'pdcrequest' ) . '"></p>';
            $html .= '<input type="hidden" name="action" value="pdc_update_profile">';
            $html .= wp_nonce_field( 'pdc-update-profile', 'pdcrequestnonce', true, false );
            $html .= '</form>';
            //$html .= methodStripe::collect_cc_form();
            $html .= '</div>';
        else :
            $html = '';
        endif;
            
        echo $html;
    }
    
    
    public static function get_question_countries_form( $user_id ) {
        
        $html = '<fieldset><legend>' . __( 'Indicate which countries you can answer local questions', 'pdcrequest') . '</legend>';
        $html .= '<p><label for="question_countries1"> ' . __( 'Select a country', 'pdcrequest') . '</label>';
        $html .= '<select name="question_countries1">';
        $html .= '<option value="">' . esc_html( __( 'Select A Country', 'pdcrequest') ) . '</option>';
        foreach ( pdcrequest_countries_select() as $code => $name ) :
            $html .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, get_user_meta( $user_id, 'setlr_question_countries1', true ), false ) . '>' . esc_html( $name ) . '</option>';
        endforeach;
        $html .= '</select></p>';
        $html .= '<p><label for="question_countries2"> ' . __( 'Select a country', 'pdcrequest') . '</label>';
        $html .= '<select name="question_countries2">';
        $html .= '<option value="">' . esc_html( __( 'Select A Country', 'pdcrequest') ) . '</option>';
        foreach ( pdcrequest_countries_select() as $code => $name ) :
            $html .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, get_user_meta( $user_id, 'setlr_question_countries2', true ), false ) . '>' . esc_html( $name ) . '</option>';
        endforeach;
        $html .= '</select></p>';
        $html .= '<p><label for="question_countries3"> ' . __( 'Select a country', 'pdcrequest') . '</label>';
        $html .= '<select name="question_countries3">';
        $html .= '<option value="">' . esc_html( __( 'Select A Country', 'pdcrequest') ) . '</option>';
        foreach ( pdcrequest_countries_select() as $code => $name ) : 
            $html .= '<option value="' . esc_attr( $code ) . '" ' . selected( $code, get_user_meta( $user_id, 'setlr_question_countries3', true ), false ) . '>' . esc_html( $name ) . '</option>';
        endforeach;
        $html .= '</select></p>';
        $html .= '</fieldset>';
        
        return $html;
    }
    
    
    public static function question_countries_form() {
        $user_id = ( isset( $_POST['user_id'] ) && absint( $_POST['user_id'])) ? absint( $_POST['user_id']) : 0;
        
        $html = self::get_question_countries_form($user_id);
                
        echo $html;
        exit();
    }
    
    
    public static function services_form( $user_id ) {
        $html = '';
        $user_services = get_user_meta( $user_id, 'setlr_services', true );
       
        if ( current_user_can( 'helper') ) :
            $services = pdcrequest_list_helper_services();
        else :
            $services = pdcrequest_list_services();
        endif;
        
        foreach( $services as $code => $service ) :
            
            if ( empty( $user_services) || $user_services[0] === 0) :
                $checked = '';
            
            elseif ( is_array( $user_services) && in_array( $code, $user_services ) ) :
                
                $checked = 'checked="checked"';
            elseif ( $code == $user_services ) :
                
                $checked = 'checked="checked"';
            else :
                
                $checked = '';
            endif;
            $html .= '<label class="setlr-checkbox"><input type="checkbox" name="services[]" value="' . esc_attr( $code ) . '" ' . $checked . '>' . esc_html( $service ) . '</label>';
        endforeach;

        return $html;
    }


    public static function specialisations_form( $user_id ) {
        $html = '';
        $user_specialisations = get_the_author_meta( 'setlr_specialisations', $user_id ); //simple array
        $specialisations = pdcrequest_list_specialisations(); // array key => value

        foreach( $specialisations as $key   => $specialisation ) :
            if ( is_array( $user_specialisations) && in_array( $key, $user_specialisations ) ) :
                $checked = 'checked="checked"';
            elseif ( $key == $user_specialisations ) :
                $checked = 'checked="checked"';
            else :
                $checked = '';
            endif;
            $html .= '<label class="setlr-checkbox"><input type="checkbox" name="specialisations[]" value="' . esc_attr( $key ) . '" ' . $checked . '>' . esc_html( $specialisation ) . '</label>';
        endforeach;

        return $html;
    }


    public static function experience_form( $user_id ) {
        $html = '';
        $user_experience = get_the_author_meta( 'setlr_experience', $user_id );
        $experiences = pdcrequest_list_experiences();
        $user_experience_years = get_the_author_meta( 'setlr_experience_years', $user_id );

        foreach( $experiences as $experience ) :

            $html .= '<label class="setlr-radio"><input type="radio" name="experience" value="' . esc_attr( $experience ) . '" ' . checked( $experience, $user_experience, false ) . '>' . esc_html( $experience ) . '</label>';
        endforeach;
        $html .= '<p><label for="experience_years">' . __( 'Number of years of experience', 'pdcrequest' );
        $html .= '<input type="text" name="experience_years" value="' . esc_attr( $user_experience_years ) . '"></label></p>';

        return $html;
    }
    
    public static function picture_form( $user_id, $is_customer = false ) {
	$picture_id = get_user_meta( $user_id, 'profile_picture', true );
	
	$html = '';
	$html .= '<div class="profile-photo">';
	
	if ( absint( $picture_id ) ) :	
		$html .= wp_get_attachment_link( $picture_id, 'thumbnail', false, true, '' );
        else :
            $html .= get_avatar( $user_id );
	endif;
	
	$html .= '</div>';
	$html .= '<label for="user-photo">';
	if ( $is_customer) :
            $html .= ( absint( $picture_id ) ) ? __( 'Change Logo ', 'pdcrequest' ) : __( 'Upload Logo', 'pdcrequest' );
        else :
            $html .= ( absint( $picture_id ) ) ? __( 'Change Picture ', 'pdcrequest' ) : __( 'Upload Picture', 'pdcrequest' );
        endif;
	$html .= '</label>';
	$html .= '<input type="file" id="user-photo" name="user-photo" accept="image/*" capture="camera">';
	
	return $html;
    }
    
    public static function gender_form( $user_id ) {
        $user = get_user_by( 'id', $user_id );
        
        $user_gender = get_user_meta( $user_id, 'setlr-user-gender', true );
        $genders = array(   'male'      => __( 'Male', 'pdcrequest'), 
                            'female'    => __( 'Female', 'pdcrequest'),
                            ''       => __( 'I prefer not to say', 'pdcrequest') 
                        );
        if ( current_user_can('customer') ) :
            $html = '';
        else :
            
            $html = '<fieldset><legend>' . __( 'My Gender', 'pdcrequest' ) . '</legend>';
            foreach( $genders as $gender_value => $gender_name ) :
            $html .= '<label class="setlr-radio"><input type="radio" name="gender" value="' . esc_attr( $gender_value ) . '" ' . checked($user_gender, $gender_value, false ) . '>';
            $html .= esc_html( $gender_name ) . '</label>';    
            endforeach;
        endif;
        
        return $html;
    }
    
    
    public static function date_of_birth( $user_id ) {
        $user_dob = get_user_meta( $user_id, 'setlr-user-dob', true );
        $user_day = '';
        $user_month = '';
        $user_year = '';
        if ( $user_dob ) :
            list( $user_day, $user_month, $user_year ) = explode( '/', $user_dob );
        endif;
        
       if ( current_user_can('customer') ) :
            $html = '';
        else :
            $html = '<p><label>' . __( 'Date of Birth (d/m/YYYY)', 'pdcrequest' );
            $html .= '<input class="setlr-day" type="number" placeholder="dd" maxlength="2" min="1" max="31" step="1" name="user-day" value="' . esc_attr( $user_day ) . '">';
            $html .= '<input class="setlr-month" type="number" placeholder="mm" maxlength="2" min="1" max="12" step="1" name="user-month" value="' . esc_attr( $user_month ) . '">';
            $html .= '<input class="setlr-year" type="number" placeholder="yyyy" maxlength="4" min="1900" max="2015" step="1" name="user-year" value="' . esc_attr( $user_year ) . '">';
            $html .= '</p>';
        endif;
        
        return $html;
    }
    
        /** 
	 * Validate Profile
	 *
	 * gets profile data from form on my-dashboard and update user meta
	 *
	 * @return Void
	 */
	public static function validate_profile() {
		global $current_user;
		get_currentuserinfo();
		if ( isset( $_POST['formdata'] ) ) {
			parse_str( $_POST['formdata'], $data );
			if ( !isset( $data['pdcrequestnonce'] ) || ! wp_verify_nonce( $data['pdcrequestnonce'], 'pdc-update-profile' ) ) {
				
				die( __( 'Busted!', 'pdcrequest' ) );
			}
			$file = $_FILES;
		} else {
			if ( !isset( $_POST['pdcrequestnonce'] ) || ! wp_verify_nonce( $_POST['pdcrequestnonce'], 'pdc-update-profile' ) ) {
				
				die( __( 'Busted Again!', 'pdcrequest' ) );
			}
			$data = $_POST;
			$file = $_FILES;
			
		}
		
		//do_action( 'pdc_save_updated_profile' );
		$redirect = self::update_profile( $current_user->ID, $data, $file );
		if ( isset( $_POST['formdata'] ) ) {
                    echo __( 'Done!', 'pdcrequest' );
                    exit;
		} else {
                        if ( $redirect == '' ) :
                            wp_safe_redirect( home_url( '/my-dashboard' ) );
                        else :
                            wp_safe_redirect( home_url( $redirect));
                        endif;
		}
	}
	
        
        
	public static function update_profile( $user_id, $data, $file = array() ) {
                write_log($data);
                $redirect = '';
		if ( isset( $data['firstname'] ) ) :
			$firstname = trim( $data['firstname'] ); 
			update_user_meta( $user_id, 'first_name', $firstname );
		endif;
		if ( isset( $data['lastname'] ) ) :
			$lastname = trim( $data['lastname'] );
			update_user_meta( $user_id, 'last_name', $lastname );
		endif;
		//user_pass
		if ( isset( $data['user_pass'] ) && trim( $data['user_pass'] ) !== '' &&  isset( $data['user_pass_confirm'] ) && $data['user_pass'] === $data['user_pass_confirm'] ) :
			wp_set_password( $data['user_pass'], $user_id );
                        $redirect = 'wp-login.php';
		endif;
                if ( isset( $data['nickname'])) :
                        $nickname = trim( $data['nickname']);
                        update_user_meta($user_id, 'nickname', $nickname );
                endif;
		if ( isset( $data['billing_company'] ) ) :
			$billing_company = trim( $data['billing_company'] );
			update_user_meta( $user_id, 'billing_company', $billing_company );
		endif;
		if ( isset( $data['billing_address_1'] ) ) :
			$billing_address_1 = trim( $data['billing_address_1'] );
			update_user_meta( $user_id, 'billing_address_1', $billing_address_1 );
		endif;
		if ( isset( $data['billing_address_2'] ) ) :
			$billing_address_2 = trim( $data['billing_address_2'] );
			update_user_meta( $user_id, 'billing_address_2', $billing_address_2 );
		endif;
		if ( isset( $data['billing_postcode'] ) ) :
			$billing_postcode = trim( $data['billing_postcode'] );
			update_user_meta( $user_id, 'billing_postcode', $billing_postcode );
		endif;
		if ( isset( $data['billing_city'] ) ) :
			$billing_city = trim( $data['billing_city'] );
			update_user_meta( $user_id, 'billing_city', $billing_city );
		endif;
		if ( isset( $data['billing_country'] ) ) :
			$billing_country = trim( $data['billing_country'] );
			update_user_meta( $user_id, 'billing_country', $billing_country );
		endif;
		if ( isset( $data['phone'] ) ) :
			$phone = trim( $data['phone'] );
			update_user_meta( $user_id, 'phone', $phone );
		endif;
		
		if ( isset( $data['requestlang'] ) ) update_user_meta( $user_id, 'requestlang', $data['requestlang'] );
		if ( isset( $data['nativelang'] ) ) update_user_meta( $user_id, 'nativelang', $data['nativelang'] );
		if ( isset( $data['locale'] ) ) update_user_meta( $user_id, 'setlr-locale', $data['locale'] );
		if ( isset( $data['description'] ) ) :
			$description = esc_textarea( $data['description'] );
			update_user_meta( $user_id, 'description', $description );
		endif;
		
		if ( isset( $file['user-photo']['name'] ) ) :
			
			// These files need to be included as dependencies when on the front end.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
			
			$document_id = media_handle_upload( 'user-photo', 0 );
			
			
			
			if (  !is_wp_error( $document_id ) ) {
				
				update_user_meta( $user_id, 'profile_picture', $document_id );
				
			} else {
				
				//send error info to user TODO
			}
		endif;
                
                //save PayPal email for helpers
                if ( isset( $_POST['paypal_email'] ) && is_email( $_POST['paypal_email'])) :
                    Paypal_Payment::save_paypal_email($user_id, $_POST['paypal_email'] );
                endif;
                
                if ( isset( $_POST['gender'])) :
                    $genders = array( 'male', 'female');
                
                    if ( in_array($_POST['gender'], $genders ) ) :
                        update_user_meta( $user_id, 'setlr-user-gender', $_POST['gender'] );
                    endif;
                endif;
                
                
                //date of birth
                $day = ( isset( $_POST['user-day'] ) && absint($_POST['user-day'])) ? absint( $_POST['user-day']) : '';
                $month = ( isset( $_POST['user-month'] ) && absint($_POST['user-month'])) ? absint( $_POST['user-month']) : '';
                $year = ( isset( $_POST['user-year'] ) && absint($_POST['user-year'])) ? absint( $_POST['user-year']) : '';
                
                if ( ! empty($day) && ! empty($month) && !empty($year) ) :
                    update_user_meta($user_id, 'setlr-user-dob', $day . '/'. $month . '/' . $year );
                endif;
                
                    
                if ( isset( $_POST['services'])) :
                    
                    
                    
                    if ( $_POST['services'] ) :
                        update_user_meta( $user_id, 'setlr_services', $_POST['services'] );
                    endif;
                endif;
                
                if ( isset( $_POST['specialisations'])) :
                    
                    $specialisations = Extra_Users::verify_specialisations( $_POST['specialisations'] );
                    if ( $specialisations ) :
                        update_user_meta( $user_id, 'setlr_specialisations', $specialisations );
                    endif;
                endif;
                
                if ( isset( $_POST['experience'])) :
                    $experiences = Extra_Users::verify_experience( $_POST['experience'] );
                    if ( $experiences ) :
                        update_user_meta( $user_id, 'setlr_experience', $experiences );
                    endif;
                endif;
                
                if ( isset( $_POST['experience_years'] ) && absint( $_POST['experience_years'] ) ) :
                    update_user_meta( $user_id, 'setlr_experience_years', $_POST['experience_years'] );
                endif;
                
                
                if ( isset( $_POST['question_countries1'] ) ) :
                    update_user_meta( $user_id, 'setlr_question_countries1', $_POST['question_countries1'] );
                endif;
                
                if ( isset( $_POST['question_countries2'] ) ) :
                    update_user_meta( $user_id, 'setlr_question_countries2', $_POST['question_countries2'] );
                endif;
                
                if ( isset( $_POST['question_countries3'] ) ) :
                    update_user_meta( $user_id, 'setlr_question_countries3', $_POST['question_countries3'] );
                endif;
                
                return $redirect;
	}
}
