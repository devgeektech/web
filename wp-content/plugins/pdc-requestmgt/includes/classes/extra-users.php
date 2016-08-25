<?php

class Extra_Users {
	
    
        public function __construct() {
            
        }
        
        
	public static function show_extra_user_fields( $user ) {
		//global $q_config;
		?>
            <table class="form-table">
		<tr>
			<th><label for="requestlang"><?php _e( 'Native Language', 'pdcrequest' ); ?></label></th>
			<td>
            	<!--<select multiple="multiple" id="requestlang" name="requestlang[]">-->
                
            	<?php 
				
				echo lang::native_language_form( $user->ID );
				
                                echo lang::locales_form( $user->ID );
                                ?>
				<p class="description"><?php _e( 'Please select your native (or preferred) language.', 'pdcrequest' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="requestlang"><?php _e( 'Languages', 'pdcrequest' ); ?></label></th>
			<td>
            	<!--<select multiple="multiple" id="requestlang" name="requestlang[]">-->
                
            	<?php 
				$user_langs = get_the_author_meta( 'requestlang', $user->ID );
                                
				echo lang::languages_form( $user->ID ); ?>
                                
                               
                <!--</select>-->
				<p class="description"><?php _e( 'Please select all languages that you speak. These will be shown on requests or help applications that you make.', 'pdcrequest' ); ?></p>
			</td>
		</tr>
                
                <!-- services -->
                <tr>
			<th><label for="services"><?php _e( 'Services', 'pdcrequest' ); ?></label></th>
			<td>
            	<!--<select multiple="multiple" id="requestlang" name="requestlang[]">-->
                
            	<?php 
				$checked = '';
				$user_services = get_the_author_meta( 'setlr_services', $user->ID );
                                
				$services = pdcrequest_list_services();
				
				asort( $services );
				foreach ( $services as $service ) :
					$checked = ( in_array( $service, $user_services ) ) ? 'checked="checked"' : '';
					echo '<label class="setlr-checkbox"><input type="checkbox" name="services[]" value="' . esc_attr( $service ) . '" ' . $checked . '>' .esc_html( $service ) . '</label>';
				endforeach; ?>
                <!--</select>-->
                <p class="description"><?php _e( 'Please select services that you can offer.', 'pdcrequest' ); ?></p>
			</td>
		</tr>
                <!-- specialisations -->
                <tr>
			<th><label for="specialisations"><?php _e( 'Specialisations', 'pdcrequest' ); ?></label></th>
			<td>
            	<!--<select multiple="multiple" id="requestlang" name="requestlang[]">-->
                
            	<?php 
				$checked = '';
                                
				$user_specialisations = get_the_author_meta( 'setlr_specialisations', $user->ID );/* 'key' */
                               
				$specialisations = pdcrequest_list_specialisations(); /* 'key'=>'value' */
				
				foreach ( $specialisations as $key => $specialisation ) :
                                        if ( is_array( $user_specialisations) && !empty($user_specialisations)):
                                            
                                            $checked = ( in_array( $key, $user_specialisations ) ) ? 'checked="checked"' : '';
					else :
                                            
                                            $checked = ( $key == $user_specialisations ) ? 'checked="checked"' : '';
                                        endif;
                                        echo '<label class="setlr-checkbox"><input type="checkbox" name="specialisations[]" value="' . esc_attr( $key ) . '" ' . $checked . '>' .esc_html( $specialisation ) . '</label>';
				endforeach; ?>
                <!--</select>-->
				<p class="description"><?php _e( 'Please select all specialisations that apply to you.', 'pdcrequest' ); ?></p>
			</td>
		</tr>
                <!-- experience -->
                <tr>
			<th><label for="experience"><?php _e( 'Experience', 'pdcrequest' ); ?></label></th>
			<td>
            	<!--<select multiple="multiple" id="requestlang" name="requestlang[]">-->
                
            	<?php 
				$checked = '';
				$user_experience = get_the_author_meta( 'setlr_experience', $user->ID );
                                
				$experiences = pdcrequest_list_experiences();
				
				
				foreach ( $experiences as $experience ) :
					$checked = ( $experience == $user_experience  ) ? 'checked="checked"' : '';
					echo '<label class="setlr-radio"><input type="radio" name="experience" value="' . esc_attr( $experience ) . '" ' . $checked . '>' .esc_html( $experience ) . '</label>';
				endforeach; ?>
                <!--</select>-->
				<p class="description"><?php _e( 'Please select the most suitable experience level.', 'pdcrequest' ); ?></p>
			</td>
		</tr>
                <tr>
                    <th><label for="experience_years"><?php _e( 'Years of Experience', 'pdcrequest' ); ?></label></th>
                    <td><input type="text" name="experience_years" value="<?php echo get_the_author_meta( 'setlr_experience_years', $user->ID ); ?>"></td>
                </tr>
        </table>
       <?php
	}
	
	
	public static function update_extra_user_fields( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) ) return false;
                
                
		update_user_meta( $user_id, 'nativelang', $_POST['nativelang'] );
		update_user_meta( $user_id, 'requestlang', $_POST['requestlang'] );
                
                $services = self::verify_services( $_POST['services'] );
                if ( $services ) :
                    update_user_meta( $user_id, 'setlr_services', $services );
                endif;
                
                
                $specialisations = self::verify_specialisations( $_POST['specialisations'] );
                if ( $specialisations ) :
                    update_user_meta( $user_id, 'setlr_specialisations', $specialisations );
                endif;
                
                $experiences = self::verify_experience( $_POST['experience'] );
                if ( $experiences ) :
                    update_user_meta( $user_id, 'setlr_experience', $experiences );
                endif;
                
                if ( absint( $_POST['experience_years'])) :
                    update_user_meta( $user_id, 'setlr_experience_years', $_POST['experience_years'] );
                endif;
                
                if ( isset( $_POST['locale'])) :
                    update_user_meta( $user_id, 'setlr-locale', $_POST['locale'] );
                endif;
               
                
	}
	
	/**
         * get requestlang for a given user id
         * @param int $user_id the user id
         * @return string the lang code of requestlang
         * @deprecated since version 0.9.5
         */
	public function get_languages_for( $user_id ) {
		$languages = get_the_author_meta( 'requestlang', $user_id );
		write_log( 'DEPRECATED: extra_users->get_languages_for('. $user_id.')');
		return $languages;
	}
        
        
        /**
         * get native language code for given user
         * @param int $user_id
         * @return string
         */
        public static function get_native_language_for( $user_id ) {
            return get_user_meta( $user_id, 'nativelang', true );
        }
        
        
        public static function get_working_languages_for( $user_id ) {
            return get_user_meta( $user_id, 'requestlang', true );
        }
	
        
        public static function get_working_lang_codes_for( $user_id ) {
            $langnames = self::get_working_languages_for($user_id);
            
            $codes = array();
           
            foreach ( $langnames as $name ) :
                $length = strlen($name);
                
                if ( $length == 2 ) :
                    //we have already a code
                    $codes[] = $name;
                    
                else :
                    $codes[] = lang::from_lang_name_to_code($name);
                   
                endif;
            endforeach;
           
            return $codes;
        }
        
        
        /**
         * verify services by white list
         * @param array $services
         * @return array
         */
        public static function verify_services( $services = array() ) {
            $user_services = array();
            $setlr_services = pdcrequest_list_services();
            foreach ( $services as $code => $service ) :
                if ( in_array( $code, array_keys($setlr_services) ) ) :
                    $user_services[] = $code;
                endif;
            endforeach;
            
            //asort( $user_services );
            
            return $user_services;
        }
        
        /**
         *  verify specialisations by white list
         * @param array $specialisations
         * @return array 
         */
        public static function verify_specialisations( $specialisations = array() ) {
            $user_specialisations = array();
            $setlr_specialisations = pdcrequest_list_specialisations();
            
            foreach ( $specialisations as $specialisation ) :
                if ( in_array( $specialisation, array_keys( $setlr_specialisations ) ) ) :
                    $user_specialisations[] = $specialisation;
                else :
                   //@todo: error message
                endif;
            endforeach;
            
            asort( $user_specialisations );
            
            return $user_specialisations;
        }
        
        
        public static function verify_experience( $experience ) {
            $user_experience = array();
            $setlr_experiences = pdcrequest_list_experiences();
            
            if ( in_array( $experience, $setlr_experiences ) ) :
                $user_experience = $experience;
            endif;
            
            return $user_experience;
        }
        
        public static function admin_get_languages_for( $user_id ) {
		
                $native = self::get_native_language_for( $user_id );
                
		$working_langs = self::get_working_lang_codes_for($user_id);
				
		if ( $native || $working_langs ) :
			$html = '<ul class="setlr-languages">';
			
			foreach( $working_langs as $language ) :
				$html .= '<li>' . esc_html( $language ) . ' --> ' . esc_html($native) . '</li>';
			endforeach;
			$html .= '</ul>';
		else :
			$html = '<p class="setlr-languages setlr-empty">' . '-' . '</p>';
		endif;
		
		return $html;	
	}
        
        
        /**
         * add columns to users list in WP admin
         * @param array $column the list of columns
         * @return array the new list of columns
         */
        public static function admin_add_columns_to_users_list( $column ) {
            $column['languages'] = __( 'Languages', 'pdcrequest');
            $column['locale'] = __( 'Locale', 'pdcrequest');
            unset( $column['posts']);
            
            return $column;
        }
        
        
        public static function admin_modify_user_table_row( $value, $column_name, $user_id ) {
            $user = get_userdata($user_id);
            
            switch ($column_name) :
                case 'languages' :
                    if ( user_can( $user, 'helper' )) :
                        return self::admin_get_languages_for( $user_id );
                    else :
                         return '';
                    endif;
                    break;
                case 'locale' :
                    if ( user_can( $user, 'helper' )) :
                        return self::get_locale_for_user( $user_id );
                    else :
                         return '';
                    endif;
                    break;
                default:
                    return $value;
            endswitch;
              
        }
        
        
        public static function get_locale_for_user( $user_id ) {
            $locale = get_user_meta( $user_id, 'setlr-locale', true );
            
            return $locale;
        }
        
        
        /**
         * 
         */
        public static function ajax_short_profile() {
            //die on false calls
            $referer = check_ajax_referer( 'short-profile', 'nonce', true );
            
            
            $user_id = ( absint( $_POST['user_id'] ) ) ? absint( $_POST['user_id'] ) : 232;
            
            if ( !empty( $user_id ) ) :
                // WP_User_Query arguments
                $html = self::show_public_profile( $user_id, null );
            else :
                $html = '<span class="setlr-error no-user-data">&nbsp;</span>';
            endif;
            
            echo $html;
            die;
        }
        
        
        /**
         * retrieves the profile picture or the gravatar of a given user
         * @param WP_User $user
         * @return string the html img tag || empty string if no picture can be found
         */
        private static function get_photo_for( WP_User $user ) {
            $profile_id = get_user_meta( $user->ID, 'profile_picture', true );
            
            $size_array = array( 96, 96 );
            $attr = array(
                        'class'	=> "photo",
                        'alt'   => sprintf( __( 'photo of %s', 'pdcrequest' ), $user->display_name ),
                );
            $img = '';
            if ( absint( $profile_id ) ) :
                $img = wp_get_attachment_image( $profile_id, $size_array, false, $attr ); 
            else :
                $img = get_avatar( $user->ID, 96, '', sprintf( __( 'photo of %s', 'pdcrequest' ), $user->display_name ), null );
            endif;
            
            return $img;
        }
        
        /**
         * get user nickname
         * @param int $user_id
         * @return string
         * @todo show picture or gravatar of user instead
         */
        public static function get_user_for_table( $user_id ) {
            
            $html = '<a href="#" class="setlr-user" data-user_id="'. esc_attr( $user_id ) . '">' . get_the_author_meta( 'nickname', $user_id ) . '</a>';
            
            return $html;
        }
        
        
        public static function get_native_locale_for( $user_id ) {
            $locale = get_user_meta( $user_id, 'setlr-locale', true );
            
            return $locale;
        }
        
        
        public static function show_public_profile( $user_id, $separator = null ) {
            if (is_null( $separator) || empty($separator)) :
                $separator = ' ' . __('to', 'pdcrequest') . ' '; 
            endif;
            //get a WP_User object
            $args = array (
                        'include'        => array( $user_id ),
                        'fields'         => 'all_with_meta',
                );

                // The User Query
                $user_query = new WP_User_Query( $args );

            if ( ! empty( $user_query->results ) ) :
                // The User Loop
                
                    foreach ( $user_query->results as $user ) :
                        $meta = get_user_meta( $user->ID);
                            
                        $nativelang = $meta['nativelang'][0];
                        $requestlangs = get_user_meta( $user->ID, 'requestlang', true );
                        
                        
                        $locale = $meta['setlr-locale'][0];
                        /* hCard according to http://microformats.org/wiki/hcard */
                        $html = '<div id="hcard-' . urlencode( $user->nickname ) . '" class="vcard">';
                        
                        $html .= self::get_photo_for($user);
                        $html .= '<span class="fn n">';
                        $html .= '<span class="given-name">' . esc_html( $user->nickname ) . '</span>';
                        //$html .= '<span class="additional-name"></span>';
                        //$html .= ' <span class="family-name">' . esc_html( $user->last_name ) . '</span>';
                        $html .= '</span>';
                        $html .= '<div class="adr">';
                        $html .= '<span class="city">' . esc_html( $user->billing_city ) . '</span>';
                        $html .= '<span class="country-name">' . esc_html( pdcrequest_get_country_name_from_code( $user->billing_country ) ) . '</span>';
                        $html .= '</div><!-- end adr -->';
                        $html .= '<ul>';
                        
                        //we need to double loop
                        foreach( $requestlangs as $lang ) :
                            
                            //foreach( $requestlang as $lang ) :
                                
                                $html .= '<li class="user-lang-pair">' . esc_html( pdcrequest_get_lang_name_from_code($lang) ) . wp_kses_post( $separator ) . esc_html( pdcrequest_get_lang_name_from_code($nativelang) ) . '</li>';
                            //endforeach;
                        endforeach;
                        $html .= '</ul><!-- end lang pairs -->';
                        $html .= '<p class="user-locale">' . esc_html( pdcrequest_get_lang_name_from_code( $locale ) ) . '</p>';
                        $html .= '<div>' . wp_kses_post( $user->description ) . '</div>';
                        $html .= '</div><!-- end hcard -->';    
                    endforeach;
            endif;
            
            return $html;
        }
        
        
        
        public function show_profile_form( $data = array() ) {
            
            $firstname = ( isset( $data['firstname']) ) ? $data['firstname'] : '';
            $lastname = ( isset( $data['lastname']) ) ? $data['lastname'] : '';
            $email = ( isset( $data['email']) ) ? $data['email'] : '';
            $phone = ( isset( $data['phone']) ) ? $data['phone'] : '';
            $nickname = ( isset( $data['nickname']) ) ? $data['nickname'] : '';
            $billing_company = ( isset( $data['billing_company']) ) ? $data['billing_company'] : '';
            $billing_address_1 = ( isset( $data['billing_address_1']) ) ? $data['billing_address_1'] : '';
            $billing_address_2 = ( isset( $data['billing_address_2']) ) ? $data['billing_address_2'] : '';
            $billing_postcode = ( isset( $data['billing_postcode']) ) ? $data['billing_postcode'] : '';
            $billing_city = ( isset( $data['billing_city']) ) ? $data['billing_city'] : '';
            $billing_country = ( isset( $data['billing_country']) ) ? $data['billing_country'] : '';
           
            
            /* Identity firstname, lastname, password, email, tel of user */
            $html = '<fieldset><legend>' . __( 'Identity', 'pdcrequest' ) . '</legend>';
            $html .= '<p><label for="firstname">' . __( 'Firstname', 'pdcrequest' );
            $html .= '<input type="text" name="firstname" value="' . sanititize_text_field( $firstname ) . '"></label></p>';
            $html .= '<p><label for="lastname">' . __( 'Lastname', 'pdcrequest' );
            $html .= '<input type="text" name="lastname" value="' . sanititize_text_field( $lastname ) . '"></label></p>';
            $html .= '<p><label for="user_pass">' . __( 'Password', 'usermgt' ) . '<input type="password" name="user_pass" value=""></label></p>';
            $html .= '<p><label for="user_pass_confirm">' . __( 'Password Confirm', 'usermgt' ) . '<input type="password" name="user_pass_confirm" value=""></label></p>';
            $html .= '<p><label for="email">' . __( 'Email', 'pdcrequest' );
            $html .= '<input type="email" name="email" value="' . sanititize_text_field( $email ) . '"></label></p>';
            $html .= '<p><label for="phone">' . __( 'Phone', 'pdcrequest' );
            $html .= '<input type="tel" name="phone" value="' . sanititize_text_field( $email ) . '"></label></p>';
            $html .= '<p><label for="nickname">' . __( 'Nickname', 'pdcrequest');
            $html .= '<input type="text" name="nickname" value="' . sanititize_text_field( $nickname ) . '"></label></p>';
            $html .= '</fieldset>';
            
           
                 /* billing address */
            $html .= '<fieldset><legend>' . __( 'Billing Address', 'pdcrequest' ) . '</legend>';
            $html .= '<p><label for="billing_company">' . __( 'Company', 'pdcrequest' );
            $html .= '<input type="text" name="billing_company" value="' . sanititize_text_field( $billing_company ) . '"></label></p>';
            

            $html .= '<p><label for="billing_address_1">' . __( 'Address', 'pdcrequest' );
            $html .= '<input type="text" name="billing_address_1" value="' . sanititize_text_field( $billing_address_1 ) . '"></label></p>';
            $html .= '<p><label for="billing_addres_2">' . __( 'Address Complement', 'pdcrequest' );
            $html .= '<input type="text" name="billing_address_2" value="' . sanititize_text_field( $billing_address_2 ) . '"></label></p>';
            $html .= '<p><label for="billing_postcode">' . __( 'Zipcode/Postcode', 'pdcrequest' );
            $html .= '<input type="text" name="billing_postcode" value="' . sanititize_text_field( $billing_postcode ) . '"></label></p>';
            $html .= '<p><label for="billing_city">' . __( 'City', 'pdcrequest' );
            $html .= '<input type="text" name="billing_city" value="' . sanititize_text_field( $billing_city ) . '"></label></p>';
            $html .= '<p><label for="billing_country">' . __( 'Country', 'pdcrequest' );
            $html .= ' <select name="billing_country">';
            
            $countries = pdcrequest_countries_select();
            foreach( $countries as $key => $value ) :
                    
                    $html .= '<option value="' . $key . '" ' . selected( $key, $billing_country, false ) . '>' . $value . '</option>';
            endforeach;
            $html .= '</select></label></p>';
            $html .= '</fieldset>';
            
            $html .= '<div><p><input id="submit2" type="submit" value="' . __( 'Submit', 'pdcrequest') . '" name="submit"></p></div>';
            
            return $html;
        
        }
}

	

        
        
	