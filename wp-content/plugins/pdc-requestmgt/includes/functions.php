<?php
/*
function pdcrequestmgt_render_gifts( $id ) {
	$terms = get_the_terms( $id, 'gift' );
						
if ( $terms && ! is_wp_error( $terms ) ) : 

	$gift_links = array();

	$html  = '<div class="gifts">';
	
	foreach ( $terms as $term ) {
		$html .= '<div class="' . esc_attr( $term->slug ) . '"><span class="gift-value">' .  get_option( 'taxonomy_' . $term->term_id ). '</span></div>';
	}
	
	$html .= '</div>';
	echo $html;
endif;

}
*/
/**
 * Render a profile form on front for users (both customers and translators)
 */
function requestmgt_render_profile_form() {
	global $current_user;
	get_currentuserinfo();	
        
        $plugin_origin = get_user_meta( $current_user->ID, 'plugin_origin', true );
	
	$firstname = ( $current_user->first_name ) ? sanitize_text_field( $current_user->first_name ) : '';
	$lastname = ( $current_user->last_name ) ? sanitize_text_field( $current_user->last_name ) : '';
	$email = ( $current_user->user_email ) ? sanitize_text_field( $current_user->user_email ) : '';
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
        $html .= '</fieldset>';
        
        /* Logo */
        if ( (is_array( $main_role ) && in_array( 'customer', $main_role )) || 'customer' == $main_role ) :
            $html .= '<fieldset><legend>' . __( 'Logo', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_picture_form( $current_user->ID, true );
            $html .= '</fieldset>';
             /* billing address */
	$html .= '<fieldset><legend>' . __( 'Billing Address', 'pdcrequest' ) . '</legend>';
	$html .= '<p><label for="billing_company">' . __( 'Company', 'pdcrequest' );
	$html .= '<input type="text" name="billing_company" value="' . $billing_company . '"></label></p>';
        else :
            $html .= '<fieldset><legend>' . __( 'Profile Picture', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_picture_form( $current_user->ID, false );
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
	$html .= '<select name="billing_country">';
	foreach( $countries as $key => $value ) :
		$html .= '<option value="' . $key . '" ' . selected( $key, $billing_country, false ) . '>' . $value . '</option>';
	endforeach;
	$html .= '</select></label></p>';
	$html .= '</fieldset>';
        
        /* Context of website */
        if ( (is_array( $main_role ) && in_array( 'customer', $main_role )) || 'customer' == $main_role ) :
           
        
            $html .= '<fieldset><legend>' . __( 'About your website', 'pdcrequest' ) . '</legend>';
            $required = ( ! empty( $plugin_url ) ) ? 'required' : '';
            $html .= '<p><label class="' . $required . '>' . __( 'URL (website address)', 'pdcrequest');
            $html .= '<input type="text" name="user_url" value="' . $user_url . '" ' . $required . '></label></p></fieldset>';
            
        
            $html .= '<fieldset><legend>' . __( 'Select your website main language', 'pdcrequest' ) . '</legend>';
            $html .= lang::native_language_form( $current_user->ID ); 
            $html .= '</fieldset>';
            
            $html .= '<fieldset><legend>' . __( 'Select the languages you need translation to', 'pdcrequest' ) . '</legend>';
            $html .= lang::languages_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<p><label>' . __( 'Give context to our translators', 'pdcrequest' ) . '<textarea name="description">' .esc_textarea( $description ) . '</textarea></p>';
        
        /* Payment info */    
        $html .= methodStripe::toggle_cc_form( $current_user->ID );
        else :
            //translator profile
            $html .= '<fieldset><legend>' . __( 'Select your native language', 'pdcrequest' ) . '</legend>';
            $html .= lang::native_language_form( $current_user->ID ); 
            $html .= '</fieldset>';
            $html .= lang::locales_form();
            $html .= '<fieldset><legend>' . __( 'Select the languages you can translate from', 'pdcrequest' ) . '</legend>';
            $html .= lang::languages_form( $current_user->ID );
            $html .= '</fieldset>'; 
            
            $html .= '<fieldset><legend>' . __( 'Select all services you can do', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_services_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Select all specialisations that you have', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_specialisations_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Indicate your experience', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_experience_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<p><label>' . __( 'About You', 'pdcrequest' ) . '<textarea name="description">' .esc_textarea( $description ) . '</textarea></p>';
        endif;
	$html .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Update', 'pdcrequest' ) . '"></p>';
	$html .= '<input type="hidden" name="action" value="pdc_update_profile">';
	$html .= wp_nonce_field( 'pdc-update-profile', 'pdcrequestnonce', true, false );
	$html .= '</form>';
        $html .= methodStripe::collect_cc_form();
	$html .= '</div>';
	
	echo $html;
}

/*
function render_customer_profile_form() {
    global $current_user;
    get_currentuserinfo();
    $main_role = get_user_meta( $current_user->ID, 'main_role', true );
    
    if ( $main_role !== 'customer') :
        Pdc_Requestmgt::redirect_to_dashboard();
    endif;
        
    
        
    $plugin_origin = get_user_meta( $current_user->ID, 'plugin_origin', true );
	
    $firstname = ( $current_user->first_name ) ? sanitize_text_field( $current_user->first_name ) : '';
    $lastname = ( $current_user->last_name ) ? sanitize_text_field( $current_user->last_name ) : '';
    $email = ( $current_user->user_email ) ? sanitize_text_field( $current_user->user_email ) : '';
    $billing_company = ( get_user_meta( $current_user->ID, 'billing_company', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_company', true ) ) : '';
    $billing_address_1 = ( get_user_meta( $current_user->ID, 'billing_address_1', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_address_1', true ) ) : '';
    $billing_address_2 = ( get_user_meta( $current_user->ID, 'billing_address_2', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_address_2', true ) ) : '';
    $billing_postcode = ( get_user_meta( $current_user->ID, 'billing_postcode', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_postcode', true ) ) : '';
    $billing_city = ( get_user_meta( $current_user->ID, 'billing_city', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_city', true ) ) : '';
    $billing_country = ( get_user_meta( $current_user->ID, 'billing_country', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'billing_country', true ) ) : '';
    $phone = ( get_user_meta( $current_user->ID, 'phone', true ) ) ? sanitize_text_field( get_user_meta( $current_user->ID, 'phone', true ) ) : '';
    $description = ( $current_user->description ) ? $current_user->description : '';
    $user_url = ( $current_user->user_url ) ? sanitize_text_field($current_user->user_url) : '';
        
    
	
    $main_role = get_user_meta( $current_user->ID, 'main_role', true );
		
    $countries = pdcrequest_countries_select();  
    // retrieve apikeys for current user
    $apikey_array = Api_Key::show_apikey( $current_user->ID );
    
    $html = '';
    
    if ( !empty( $apikey_array)) :
        foreach ( $apikey_array as $item ) : 
            $html .= '<fieldset><legend>' . __( 'API Key for WordPress Integration', 'pdcrequest') . '</legend>';
            $html .= '<p><label>' . sprintf (__( 'API Key for %s', 'pdcrequest' ), $item['url']);
            $html .= '<input type="text" name="apikey" value="' . sanitize_text_field( $item['apikey'] ) . '"></label></p></fieldset>';
       endforeach;
    endif;
    
    $html .= '<a href="#" id="profile-toggle" class="button-primary">' . __( 'Edit Your Profile', 'pdcrequest' ) . '</a>';
    $html .= '<div id="your-profile">';
    $html .= '<form id="custome-profile-update" method="post" enctype="multipart/form-data" action="' . get_admin_url( NULL, 'admin-post.php', 'admin' ) . '">';
    $html .= '<fieldset><legend>' . __( 'Identity', 'pdcrequest' ) . '</legend>';
    $html .= '<p><label for="firstname">' . __( 'Firstname', 'pdcrequest' );
    $html .= '<input type="text" name="firstname" value="' . $firstname . '"></label></p>';
    $html .= '<p><label for="lastname">' . __( 'Lastname', 'pdcrequest' );
    $html .= '<input type="text" name="lastname" value="' . $lastname . '"></label></p>';
    $html .= '<p><label for="user_pass">' . __( 'Password', 'usermgt' ) . '<input type="password" name="user_pass" value=""></label></p>';
    $html .= '<p><label for="user_pass_confirm">' . __( 'Password Confirm', 'usermgt' ) . '<input type="password" name="user_pass_confirm" value=""></label></p>';
    $html .= '</fieldset>';
    $html .= '<fieldset><legend>' . __( 'Picture', 'pdcrequest' ) . '</legend>';
    $html .= pdcrequest_picture_form( $current_user->ID );
    $html .= '</fieldset>';
	if ( $main_role === 'customer' ) :
		$html .= '<fieldset><legend>' . __( 'Billing Address', 'pdcrequest' ) . '</legend>';
		$html .= '<p><label for="billing_company">' . __( 'Company', 'pdcrequest' );
		$html .= '<input type="text" name="billing_company" value="' . $billing_company . '"></label></p>';
	else :
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
	$html .= '<select name="billing_country">';
	foreach( $countries as $key => $value ) :
		$html .= '<option value="' . $key . '" ' . selected( $key, $billing_country, false ) . '>' . $value . '</option>';
	endforeach;
	$html .= '</select></label></p>';
	$html .= '<p><label for="email">' . __( 'Email', 'pdcrequest' );
	$html .= '<input type="email" name="email" value="' . $email . '"></label></p>';
	$html .= '<p><label for="phone">' . __( 'Phone', 'pdcrequest' );
	$html .= '<input type="tel" name="phone" value="' . $phone . '"></label></p>';
	$html .= '</fieldset>';
        
        $html .= methodStripe::toggle_cc_form();
        
        if ( $main_role === 'customer') :
            $html .= '<fieldset><legend>' . __( 'About your website', 'pdcrequest' ) . '</legend>';
            $required = ( ! empty( $plugin_url ) ) ? 'required' : '';
            $html .= '<p><label class="' . $required . '>' . __( 'URL (website address)', 'pdcrequest');
            $html .= '<input type="text" name="user_url" value="' . $user_url . '" ' . $required . '></label></p></fieldset>';
            
            
            
            
            $html .= '<fieldset><legend>' . __( 'Select your website main language', 'pdcrequest' ) . '</legend>';
            $html .= lang::native_language_form( $current_user->ID ); 
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Select the languages you need translation to', 'pdcrequest' ) . '</legend>';
            $html .= lang::languages_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<p><label>' . __( 'Give context to our translators', 'pdcrequest' ) . '<textarea name="description">' .esc_textarea( $description ) . '</textarea></p>';
        else :
            $html .= '<fieldset><legend>' . __( 'Select your native language', 'pdcrequest' ) . '</legend>';
            $html .= lang::native_language_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Select your other languages', 'pdcrequest' ) . '</legend>';
            $html .= lang::languages_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Select all services you can do', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_services_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Select all specialisations that you have', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_specialisations_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<fieldset><legend>' . __( 'Indicate your experience', 'pdcrequest' ) . '</legend>';
            $html .= pdcrequest_experience_form( $current_user->ID );
            $html .= '</fieldset>';
            $html .= '<p><label>' . __( 'Short Biography', 'pdcrequest' ) . '<textarea name="description">' .esc_textarea( $description ) . '</textarea></p>';
        endif;
	$html .= '<p class="submit"><input type="submit" name="submit" value="' . __( 'Update', 'pdcrequest' ) . '"></p>';
	$html .= '<input type="hidden" name="action" value="pdc_update_profile">';
	$html .= wp_nonce_field( 'pdc-update-profile', 'pdcrequestnonce', true, false );
	$html .= '</form>';
        $html .= methodStripe::collect_cc_form();
	$html .= '</div>';
	
	echo $html;
    
}
*/

function pdcrequest_countries_select(){
    $countries = array(
      "GB" => "United Kingdom",
      "US" => "United States",
      "AF" => "Afghanistan",
      "AL" => "Albania",
      "DZ" => "Algeria",
      "AS" => "American Samoa",
      "AD" => "Andorra",
      "AO" => "Angola",
      "AI" => "Anguilla",
      "AQ" => "Antarctica",
      "AG" => "Antigua And Barbuda",
      "AR" => "Argentina",
      "AM" => "Armenia",
      "AW" => "Aruba",
      "AU" => "Australia",
      "AT" => "Austria",
      "AZ" => "Azerbaijan",
      "BS" => "Bahamas",
      "BH" => "Bahrain",
      "BD" => "Bangladesh",
      "BB" => "Barbados",
      "BY" => "Belarus",
      "BE" => "Belgium",
      "BZ" => "Belize",
      "BJ" => "Benin",
      "BM" => "Bermuda",
      "BT" => "Bhutan",
      "BO" => "Bolivia",
      "BA" => "Bosnia And Herzegowina",
      "BW" => "Botswana",
      "BV" => "Bouvet Island",
      "BR" => "Brazil",
      "IO" => "British Indian Ocean Territory",
      "BN" => "Brunei Darussalam",
      "BG" => "Bulgaria",
      "BF" => "Burkina Faso",
      "BI" => "Burundi",
      "KH" => "Cambodia",
      "CM" => "Cameroon",
      "CA" => "Canada",
      "CV" => "Cape Verde",
      "KY" => "Cayman Islands",
      "CF" => "Central African Republic",
      "TD" => "Chad",
      "CL" => "Chile",
      "CN" => "China",
      "CX" => "Christmas Island",
      "CC" => "Cocos (Keeling) Islands",
      "CO" => "Colombia",
      "KM" => "Comoros",
      "CG" => "Congo",
      "CD" => "Congo, The Democratic Republic Of The",
      "CK" => "Cook Islands",
      "CR" => "Costa Rica",
      "CI" => "Cote D'Ivoire",
      "HR" => "Croatia (Local Name: Hrvatska)",
      "CU" => "Cuba",
      "CY" => "Cyprus",
      "CZ" => "Czech Republic",
      "DK" => "Denmark",
      "DJ" => "Djibouti",
      "DM" => "Dominica",
      "DO" => "Dominican Republic",
      "TP" => "East Timor",
      "EC" => "Ecuador",
      "EG" => "Egypt",
      "SV" => "El Salvador",
      "GQ" => "Equatorial Guinea",
      "ER" => "Eritrea",
      "EE" => "Estonia",
      "ET" => "Ethiopia",
      "FK" => "Falkland Islands (Malvinas)",
      "FO" => "Faroe Islands",
      "FJ" => "Fiji",
      "FI" => "Finland",
      "FR" => "France",
      "FX" => "France, Metropolitan",
      "GF" => "French Guiana",
      "PF" => "French Polynesia",
      "TF" => "French Southern Territories",
      "GA" => "Gabon",
      "GM" => "Gambia",
      "GE" => "Georgia",
      "DE" => "Germany",
      "GH" => "Ghana",
      "GI" => "Gibraltar",
      "GR" => "Greece",
      "GL" => "Greenland",
      "GD" => "Grenada",
      "GP" => "Guadeloupe",
      "GU" => "Guam",
      "GT" => "Guatemala",
      "GN" => "Guinea",
      "GW" => "Guinea-Bissau",
      "GY" => "Guyana",
      "HT" => "Haiti",
      "HM" => "Heard And Mc Donald Islands",
      "VA" => "Holy See (Vatican City State)",
      "HN" => "Honduras",
      "HK" => "Hong Kong",
      "HU" => "Hungary",
      "IS" => "Iceland",
      "IN" => "India",
      "ID" => "Indonesia",
      "IR" => "Iran (Islamic Republic Of)",
      "IQ" => "Iraq",
      "IE" => "Ireland",
      "IL" => "Israel",
      "IT" => "Italy",
      "JM" => "Jamaica",
      "JP" => "Japan",
      "JO" => "Jordan",
      "KZ" => "Kazakhstan",
      "KE" => "Kenya",
      "KI" => "Kiribati",
      "KP" => "Korea, Democratic People's Republic Of",
      "KR" => "Korea, Republic Of",
      "KW" => "Kuwait",
      "KG" => "Kyrgyzstan",
      "LA" => "Lao People's Democratic Republic",
      "LV" => "Latvia",
      "LB" => "Lebanon",
      "LS" => "Lesotho",
      "LR" => "Liberia",
      "LY" => "Libyan Arab Jamahiriya",
      "LI" => "Liechtenstein",
      "LT" => "Lithuania",
      "LU" => "Luxembourg",
      "MO" => "Macau",
      "MK" => "Macedonia, Former Yugoslav Republic Of",
      "MG" => "Madagascar",
      "MW" => "Malawi",
      "MY" => "Malaysia",
      "MV" => "Maldives",
      "ML" => "Mali",
      "MT" => "Malta",
      "MH" => "Marshall Islands",
      "MQ" => "Martinique",
      "MR" => "Mauritania",
      "MU" => "Mauritius",
      "YT" => "Mayotte",
      "MX" => "Mexico",
      "FM" => "Micronesia, Federated States Of",
      "MD" => "Moldova, Republic Of",
      "MC" => "Monaco",
      "MN" => "Mongolia",
      "MS" => "Montserrat",
      "MA" => "Morocco",
      "MZ" => "Mozambique",
      "MM" => "Myanmar",
      "NA" => "Namibia",
      "NR" => "Nauru",
      "NP" => "Nepal",
      "NL" => "Netherlands",
      "AN" => "Netherlands Antilles",
      "NC" => "New Caledonia",
      "NZ" => "New Zealand",
      "NI" => "Nicaragua",
      "NE" => "Niger",
      "NG" => "Nigeria",
      "NU" => "Niue",
      "NF" => "Norfolk Island",
      "MP" => "Northern Mariana Islands",
      "NO" => "Norway",
      "OM" => "Oman",
      "PK" => "Pakistan",
      "PW" => "Palau",
      "PA" => "Panama",
      "PG" => "Papua New Guinea",
      "PY" => "Paraguay",
      "PE" => "Peru",
      "PH" => "Philippines",
      "PN" => "Pitcairn",
      "PL" => "Poland",
      "PT" => "Portugal",
      "PR" => "Puerto Rico",
      "QA" => "Qatar",
      "RE" => "Reunion",
      "RO" => "Romania",
      "RU" => "Russian Federation",
      "RW" => "Rwanda",
      "KN" => "Saint Kitts And Nevis",
      "LC" => "Saint Lucia",
      "VC" => "Saint Vincent And The Grenadines",
      "WS" => "Samoa",
      "SM" => "San Marino",
      "ST" => "Sao Tome And Principe",
      "SA" => "Saudi Arabia",
      "SN" => "Senegal",
      "SC" => "Seychelles",
      "SL" => "Sierra Leone",
      "SG" => "Singapore",
      "SK" => "Slovakia (Slovak Republic)",
      "SI" => "Slovenia",
      "SB" => "Solomon Islands",
      "SO" => "Somalia",
      "ZA" => "South Africa",
      "GS" => "South Georgia, South Sandwich Islands",
      "ES" => "Spain",
      "LK" => "Sri Lanka",
      "SH" => "St. Helena",
      "PM" => "St. Pierre And Miquelon",
      "SD" => "Sudan",
      "SR" => "Suriname",
      "SJ" => "Svalbard And Jan Mayen Islands",
      "SZ" => "Swaziland",
      "SE" => "Sweden",
      "CH" => "Switzerland",
      "SY" => "Syrian Arab Republic",
      "TW" => "Taiwan",
      "TJ" => "Tajikistan",
      "TZ" => "Tanzania, United Republic Of",
      "TH" => "Thailand",
      "TG" => "Togo",
      "TK" => "Tokelau",
      "TO" => "Tonga",
      "TT" => "Trinidad And Tobago",
      "TN" => "Tunisia",
      "TR" => "Turkey",
      "TM" => "Turkmenistan",
      "TC" => "Turks And Caicos Islands",
      "TV" => "Tuvalu",
      "UG" => "Uganda",
      "UA" => "Ukraine",
      "AE" => "United Arab Emirates",
      "UM" => "United States Minor Outlying Islands",
      "UY" => "Uruguay",
      "UZ" => "Uzbekistan",
      "VU" => "Vanuatu",
      "VE" => "Venezuela",
      "VN" => "Viet Nam",
      "VG" => "Virgin Islands (British)",
      "VI" => "Virgin Islands (U.S.)",
      "WF" => "Wallis And Futuna Islands",
      "EH" => "Western Sahara",
      "YE" => "Yemen",
      "YU" => "Yugoslavia",
      "ZM" => "Zambia",
      "ZW" => "Zimbabwe"
    );
	
	return $countries;
}

function pdcrequest_list_gifts() {
	$args = array(
    'orderby'           => 'name', 
    'order'             => 'ASC',
    'hide_empty'        => false, 
    'exclude'           => array(), 
    'exclude_tree'      => array(), 
    'include'           => array(),
    'number'            => '', 
    'fields'            => 'all', 
    'slug'              => '',
    'parent'            => '',
    'hierarchical'      => true, 
    'child_of'          => 0, 
    'get'               => '', 
    'name__like'        => '',
    'description__like' => '',
    'pad_counts'        => false, 
    'offset'            => '', 
    'search'            => '', 
    'cache_domain'      => 'core'
); 

$taxonomies = 'gift';

$terms = get_terms($taxonomies, $args);
return $terms;
}


/**
 * @deprecated since version 1.0 use lang::languages_form instead
 * @param type $user_id
 * @return string
 */
function pdcrequest_languages_form( $user_id ) {
	$html = '';
	$user_langs = get_the_author_meta( 'requestlang', $user_id );
	$languages = lang::get_main_languages_list();
	
	foreach ( $languages as $lang => $language ) :
		if ( is_array( $user_langs ) ) :
			$checked = ( in_array( $language, $user_langs ) ) ? 'checked="checked"' : '';
		else :
			$checked = ( $language == $user_langs ) ? 'checked="checked"' : '';
		endif;
		$html .= '<label class="pdcrequest-checkbox"><input type="checkbox" name="requestlang[]" value="' . esc_attr( $language ) . '" ' . $checked . '>' .esc_html( $language ) . '</label>';
	endforeach;
	
	return $html;
}

/**
 * @deprecated since version 1.0 use lang::native_language_form instead
 * @param type $user_id
 * @return string
 */
function pdcrequest_native_language_form( $user_id ) {
	$html = '';
	$native_lang = get_the_author_meta( 'nativelang', $user_id );
	$languages = lang::get_main_languages_list();
       
	if ( $languages ) :
		$html .= '<select name="nativelang">';
		asort( $languages );
                foreach( $languages as $language ) :
		foreach ( $language as $lang_code => $lang_name ) :
			$html .= '<option value="' . esc_attr( $lang_code ) . '" ' . selected( $lang_code, $native_lang, false ) . '>' . esc_html( $lang_name ) . '</option>';
		endforeach;
                endforeach;
		$html .= '</select>';
	endif;
	return $html;
}

function pdcrequest_picture_form( $user_id, $is_customer = false ) {
	$picture_id = get_user_meta( $user_id, 'profile_picture', true );
	
	$html = '';
	$html .= '<div class="profile-photo">';
	
	if ( absint( $picture_id ) ) :	
		$html .= wp_get_attachment_link( $picture_id, 'thumbnail', false, true, '' );
	endif;
	
	$html .= '</div>';
	$html .= '<label for="user-photo">';
	if ( $is_customer) :
            $html .= ( absint( $picture_id ) ) ? __( 'Change Logo', 'pdcrequest' ) : __( 'Upload Logo', 'pdcrequest' );
        else :
            $html .= ( absint( $picture_id ) ) ? __( 'Change Picture', 'pdcrequest' ) : __( 'Upload Picture', 'pdcrequest' );
        endif;
	$html .= '</label>';
	$html .= '<input type="file" id="user-photo" name="user-photo" accept="image/*" capture="camera">';
	
	return $html;
}

function pdcrequest_services_form( $user_id ) {
    $html = '';
    $user_services = get_the_author_meta( 'setlr_services', $user_id );
    $services = pdcrequest_list_services();
    
    foreach( $services as $service ) :
        $checked = ( in_array( $service, $user_services ) ) ? 'checked="checked"' : '';
        $html .= '<label><input type="checkbox" name="services[]" value="' . esc_attr( $service ) . '" ' . $checked . '>' . esc_html( $service ) . '</label>';
    endforeach;
    
    return $html;
}


function pdcrequest_specialisations_form( $user_id ) {
    $html = '';
    $user_specialisations = get_the_author_meta( 'setlr_specialisations', $user_id ); //simple array
    $specialisations = pdcrequest_list_specialisations(); // array key => value
    
    foreach( $specialisations as $key   => $specialisation ) :
        $checked = ( in_array( $key, $user_specialisations ) ) ? 'checked="checked"' : '';
        $html .= '<label class="pdcrequest-checkbox"><input type="checkbox" name="specialisations[]" value="' . esc_attr( $key ) . '" ' . $checked . '>' . esc_html( $specialisation ) . '</label>';
    endforeach;
    
    return $html;
}


function pdcrequest_experience_form( $user_id ) {
    $html = '';
    $user_experience = get_the_author_meta( 'setlr_experience', $user_id );
    $experiences = pdcrequest_list_experiences();
    $user_experience_years = get_the_author_meta( 'setlr_experience_years', $user_id );
    
    foreach( $experiences as $experience ) :
        
        $html .= '<label class="pdcrequest-checkbox"><input type="radio" name="experience" value="' . esc_attr( $experience ) . '" ' . checked( $experience, $user_experience, false ) . '>' . esc_html( $experience ) . '</label>';
    endforeach;
    $html .= '<p><label for="experience_years">' . __( 'Number of years of experience', 'pdcrequest' );
    $html .= '<input type="text" name="experience_years" value="' . esc_attr( $user_experience_years ) . '"></label></p>';
    
    return $html;
}

/**
 * Translator dashboard V1
 * @param int $request_id
 * @return string
 */
function pdcrequest_translation_dashboard( $request_id ) {
    $request = get_post( $request_id, OBJECT, 'raw' );
    $translation_id = get_post_meta( $request_id, 'setlr_translation', true );
    
    if ( ! empty( $translation_id) && absint($translation_id)) :
        $translation = get_post( $translation_id, OBJECT, 'raw');
        $translation_title = ( $translation->post_title ) ? $translation->post_title : '';
        $translation_content = ( $translation->post_content ) ? $translation->post_content : '';
        $translation_excerpt = ( $translation->post_excerpt ) ? $translation->post_excerpt : '';
    endif;
    
    $html = '<form name="translation-dashboard" id="translation-dashboard" method="post" action="' . admin_url('admin-post.php') . '">';
    $html .= '<div id="original_post">';
    $html .= '<h1 class="post-title">' . esc_html( $request->post_title ) . '</h1>';
    $html .= '<div class="post-content">' . wp_kses_post( $request->post_content ) . '</div>';
    if ( ! empty( $request->post_excerpt ) ) :
        $html .= '<div class="post-excerpt">' . wp_kses_post( $request->post_excerpt ) . '</div>';
    endif;
    $html .= '</div><!-- end original post -->';
    $html .= '<div id="translation-job">';
    $html .= '<p><label>' . __( 'Title', 'pdcrequest');
    $html .= '<input type="text" name="post-title" value="' . sanitize_text_field( $translation_title ) . '"></label></p>';
    $html .= 'div><label>' . __( 'Content', 'pdcrequest');
    $html .= '<textarea name="post-content">' . esc_textarea( $translation_content ) . '</textarea></label></div>';
    if ( ! empty( $request->post_excerpt ) ) :
        $html .= '<p><label>' . __( 'Excerpt', 'pdcrequest');
        $html .= '<textarea name="post-excerpt">' . esc_textarea( $translation_excerpt ) . '</textarea></label></p>';
    endif;
    $html .= '</div>';
    $html .= '<div class="publishbox">';
    $html .= '<p><input type="submit" id="draft" name="draft" class="button button-secondary" value="' . __( 'Save Progress', 'pdcrequest') . '"></p>';
    $html .= '<p><input type="submit" id="completed" name="completed" class="button button-primary" value="' . __( 'Translation Complete', 'pdcrequest') . '"></p>';
    $html .= '</div>';
    $html .= '</form>';
    
    return $html;
}


/**
 * show notifications
 * @return string
 */
function pdcrequest_show_notification() {
    $message = get_query_var('message');
    $message_type = (get_query_var('message_type') ) ? esc_attr( get_query_var('message_type')) : '';
    
    $html = '<div class="pdcrequest-message ' . esc_attr( $message_type ) . '">';
    $html .= '<p>' . esc_html( $message) . '</p>';
    $html .= '</div>';
    
    return $html;
}


/**
 * pdcrequest apply link
 * @param int $request_id
 * @return string the complete link with action and nonce
 */
function pdcrequest_apply_link( $request_id ) {
    $action = 'pdcrequest_apply_to_request';
    $actionurl = admin_url( 'admin.php' );
    $url = add_query_arg( array('action' => $action, 'request_id' => $request_id ), $actionurl );
    return wp_nonce_url( $url);
}

function pdcrequest_apply_button( $request_id ) {
    echo '<a class="button primary-button" href="' . pdcrequest_apply_link( $request_id ) . '" title="' . __('Apply', 'pdcrequest') . '">' . __('apply', 'pdcrequest') . '</a>';
}


function pdcrequest_show_original_post( $post_id ) {
    global $post;
    $post = get_post( $post_id );
    
    echo Request_CPT::show_original_request($post->post_parent);
    wp_reset_postdata();
}


function pdcrequest_show_author( $user_id ) {
    global $user;
    $user = get_user_by( 'id', $user_id );
    $author_bio_avatar_size = apply_filters( 'setlr_author_bio_avatar_size', 56 );

    $html ='<div class="author-info">';
	
    $html .= '<div class="author-avatar">';
		
    $html .= get_avatar( $user->user_email, $author_bio_avatar_size );
		
    $html .= '</div><!-- .author-avatar -->';
	
    $html .= '<div class="author-description">';
    $html .= '<p class="author-title">';
    $html .= '<a class="author-link" href="' . esc_url( get_author_posts_url( $user->ID ) ) . '" rel="author">';
    $html .= sprintf( __( 'Requested by %s', 'setlr' ), $user->display_name );
    $html .= '</a>';
    $html .= '</p>';
    $html .= '<div>' . wp_kses_post( $user->user_description ) . '</div>';
    $html .= '</div><!-- .author-description -->';
    $html .= '</div><!-- .author-info -->';
    
    return $html;
}


function pdcrequest_show_translator_buttons() {
    
    $html = '<div class="pdcrequest-actions">';
    $html .= '<p class="secondary-action"><input name="save" type="submit" class="button secondary-button" value="' . __( 'Save Progress', 'pdcrequest') .'"></p>';
    $html .= '<p class="primary-action"><input name="finished" type="submit" class="button primary-button" value="' . __( 'Translation Finished', 'pdcrequest') .'"></p>';
    $html .= wp_nonce_field( 'translator-dashboard', 'pdcrequestnonce', true, false );
    $html .= '<input type="hidden" name="action" value="pdcrequest_update_translation">';
    $html .= '<input type="hidden" name="post_id" value="' . absint( get_the_ID() ) . '">';
    $html .= '</div>';
    
    return $html;
}


function pdcrequest_show_customer_buttons() {
    $html = '<div class="pdcrequest-actions">';
    $html .= '<p class="secondary-action"><input name="send_review" type="submit" class="button secondary-button" value="' . __( 'Send For Review', 'pdcrequest') .'"></p>';
    $html .= '<p class="secondary-action"><input name="reject" type="submit" class="button secondary-button" value="' . __( 'Reject Translation', 'pdcrequest') .'"></p>';
    $html .= '<p class="primary-action"><input name="accept" type="submit" class="button primary-button" value="' . __( 'Accept Translation', 'pdcrequest') .'"></p>';
    $html .= wp_nonce_field( 'translator-dashboard', 'pdcrequestnonce', true, false );
    $html .= '<input type="hidden" name="action" value="pdcrequest_update_translation">';
    $html .= '<input type="hidden" name="post_id" value="' . absint( get_the_ID() ) . '">';
    $html .= '</div>';
    
    return $html;
}


/**
 * shows the customer or translator action buttons as appropriate
 * @global WP_User $current_user
 * @return html
 */
function pdcrequest_show_appropriate_buttons() {
    global $current_user;
    get_currentuserinfo();

    $main_role = get_user_meta( $current_user->ID, 'main_role');
   
    if ( current_user_can('helper')) :
        $html = pdcrequest_show_translator_buttons();
    elseif ( current_user_can('customer')) :
        $html = pdcrequest_show_customer_buttons();
    else :
        $html = pdcrequest_show_translator_buttons();
        $html .= pdcrequest_show_customer_buttons();
    endif;
    
    return $html;
}


function pdcrequest_dashboard() {
    $page = get_page_by_path( 'my-dashboard');
    
    return $page->ID;
}


function pdcrequest_list_languages() {
    $languages = array(
        "de"    => "Deutsch",
        "en"    => "English",
        "zh"    => "中文",
        "ru"    => "Русский",
        "fi"    => "suomi",
        "fr"    => "Français",
        "nl"    => "Nederlands",
        "sv"    => "Svenska",
        "it"    => "Italiano",
        "ro"    => "Română",
        "hu"    => "Magyar",
        "ja"    => "日本語",
        "es"    => "Español",
        "vi"    => "Tiếng Việt",
        "ar"    => "العربية",
        "pt"    => "Português",
        "pl"    => "Polski",
        "gl"    => "galego",
        "tr"    => "Turkish",
        "et"    => "Eesti"
    );
    
    asort( $languages );
    
    return $languages;
}

/**
 * list all languages and locales
 * @return array umtidimensional key (language) => value = array (locale code => language name - Country)
 */
function pdcrequest_list_language_locales() {
    $locales = array(
        'af' => array(
                    array( 'af_ZA' => 'Afrikaan - South Africa' )
                ),
        'sq' => array(
                    array( 'sq_AL' => 'Albanian - Albania' )
                ),
        'ar' => array( 
                    array( 'ar_DZ' => 'Arabic - Algeria' ),
                    array( 'ar_BH' => 'Arabic - Bahrein' ),
                    array( 'ar_EG' => 'Arabic - Egypt' ),
                    array( 'ar_IQ' => 'Arabic - Iraq' ),
                    array( 'ar_JO' => 'Arabic - Jordan' ),
                    array( 'ar_KW' => 'Arabic - Kuwait' ),
                    array( 'ar_LB' => 'Arabic - Lebanon' ),
                    array( 'ar_LY' => 'Arabic - Lybia' ),
                    array( 'ar_MA' => 'Arabic - Morocco' ),
                    array( 'ar_OM' => 'Arabic - Oman' ),
                    array( 'ar_QA' => 'Arabic - Qatar' ),
                    array( 'ar_SA' => 'Arabic - Saudi Arabia' ),
                    array( 'ar_SY' => 'Arabic - Syria' ),
                    array( 'ar_TN' => 'Arabic - Tunisia' ),
                    array( 'ar_AE' => 'Arabic - United Arab Emirates' ),
                    array( 'ar_YE' => 'Arabic - Yemen' )
                    ),
        'hy' => array(
                    array( 'hy_AM' => 'Armenian - Armenia' )
                ),
        'az' => array(
                    array( 'az_AZ' => 'Azeri - Azerbaijan' )
                ),
        'eu' => array(
                    array( 'eu_ES' => 'Basque - Basque' )
                ),
        'be' => array(
                    array( 'be_BY' => 'Belarusian - Belarus' )
                ),
        'bg' => array(
                    array( 'bg_BG' => 'Bulgarian - Bulgaria' )
                ),
        'ca' => array(
                    array( 'ca_ES' => 'Catalan - Catalan' )
                ),
        'zh' => array(
                    array( 'zh_CN' => 'Chinese - China' ),
                    array( 'zh_HK' => 'Chinese - Hong Kong'),
                    array( 'zh_MO' => 'Chinese - Macau SAR'),
                    array( 'zh_SG' => 'Chinese - Singapore' ),
                    array( 'zh_TW' => 'Chinese - Taiwan' ),
                    array( 'zh_CHS' => 'Chinese (Simplified)' ),
                    array( 'zh_TW' => 'Chinese (Traditional)' )
                    ),
        'hr' => array(
                    array( 'hr_HR' => 'Croatian - Crotia' )
                ),
        'da' => array(
                    array( 'da_DK' => 'Danish - Denmark' )
                ),
        'div' => array(
                    array( 'div_MV' => 'Dhivehi - Maldives' )
                ),
        'nl' => array(
                    array( 'nl_BE' => 'Dutch - Belgium' ),
                    array( 'nl_NL' => 'Dutch - The Nederlands' )
                    ),
        'en' => array(
                    array( 'en_AU' => 'English - Australia' ),
                    array( 'en_BE' => 'English - Belize' ),
                    array( 'en_CA' => 'English - Canada' ),
                    array( 'en_CB' => 'English - Carabbean' ),
                    array( 'en_IE' => 'English - Ireland' ),
                    array( 'en_JM' => 'English - Jamaica' ),
                    array( 'en_NZ' => 'English - New Zealand' ),
                    array( 'en_PH' => 'English - Philippines' ),
                    array( 'en_ZA' => 'English - South Africa' ),
                    array( 'en_TT' => 'English - Trinidad and Tobago' ),
                    array( 'en_GB' => 'English - United Kingdom' ),
                    array( 'en_US' => 'English - United States' ),
                    array( 'en_ZW' => 'English - Zimbabwe' )
                    ),
        'et' => array(
                    array( 'et_EE' => 'Estonian - Estonia' )
                ),
        'fo' => array(
                    array( 'fo_FO' => 'Faroese - Faroe Islands' )
                ),
        'fa' => array(
                    array( 'fa_IR' => 'Farsi - Iran' )
                ),
        'fi' => array(
                    array( 'fi_FI' => 'Finnish - Finland' )
                ),
        'fr' => array(
                    array( 'fr_BE' => 'French - Belgium' ),
                    array( 'fr_CA' => 'French - Canada' ),
                    array( 'fr_FR' => 'French - France' ),
                    array( 'fr_LU' => 'French - Luxembourg' ),
                    array( 'fr_MC' => 'French - Monaco' ),
                    array( 'fr_CH' => 'French - Switzerland' )
                    ),
        'gl' => array(
                    array( 'gl_ES' => 'Galician - Galician' )
                ),
        'ka' => array(
                    array( 'ka_GE' => 'Georgian - Georgia' )
                ),
        'de' => array(
                    array( 'de_AT' => 'German - Austria' ),
                    array( 'de_DE' => 'German - Germany' ),
                    array( 'de_LI' => 'German - Lichtenstein' ),
                    array( 'de_LU' => 'German - Luxembourg' ),
                    array( 'de_CH' => 'German - Switzerland' )
                    ),
        'el' => array(
                    array( 'el_GR' => 'Greek - Greece' )
                ),
        'gu' => array(
                    array( 'gu_IN' => 'Gurajati - India' )
                ),
        'he' => array(
                    array( 'he_IL' => 'Hebrew - Israel' )
                ),
        'hi' => array(
                    array( 'hi_IN' => 'Hindi - India' )
                ),
        'hu' => array(
                    array( 'hu_HU' => 'Hungarian - Hungary' )
                ),
        'is' => array(
                    array( 'is_IS' => 'Icelandic - Iceland' )
                ),
        'id' => array(
                    array( 'id_ID' => 'Indonesian - Indonesia' )
                ),
        'it' => array(
                    array( 'it_IT' => 'Italian - Italy' ),
                    array( 'it_CH' => 'Italian - Switzerland' )
                    ),
        'ja' => array(
                    array( 'jp_JP' => 'Japanese - Japan' )
                ),
        'kn' => array(
                    array( 'kn_IN' => 'Kannada - India' )
                ),
        'kk' => array(
                    array( 'kk_KZ' => 'Kazakh - Kazakhstan' )
                ),
        'kok' => array(
                    array( 'kok_IN' => 'Konkani - India' )
                ),
        'ko' => array(
                    array( 'ko_KR' => 'Korean - Korea' )
                ),
        'ky' => array(
                    array( 'ky_KZ' => 'Kyrgyz - Kazakhstan' )
                ),
        'lv' => array(
                    array( 'lv_LV' => 'Latvian - Latvia' )
                ),
        'lt' => array(
                    array( 'lt_LT' => 'Lithunian - Lithunia' )
                ),
        'mk' => array(
                    array( 'mk_MK' => 'Macedonian (FYROM)' )
                ),
        'ms' => array(
                    array( 'ms_BN' => 'Malay - Brunei' ),
                    array( 'ms_MY' => 'Malay - Malaysia' )
                    ),
        'mr' => array(
                    array( 'mr_IN' => 'Marathi - India' )
                ),
        'mn' => array(
                    array( 'mn_MN' => 'Mongolian - Mongolia' )
                ),
        'nb' => array(
                    array( 'nb_NO' => 'Norwegian (Bokmal) - Norway' )
                ),
        'nn' => array(
                    array( 'nn_NO' => 'Norwegian (Nynorsk) - Norway' )
                ),
        'pl' => array(
                    array( 'pl_PL' => 'Polish - Poland' )
                ),
        'pt' => array(
                    array( 'pt_BR' => 'Portuguese - Brazil' ),
                    array( 'pt_PT' => 'Portuguese - Portugal' )
                    ),
        'pa' => array(
                    array( 'pa_IN' => 'Punjabi - India' )
                ),
        'ro' => array(
                    array( 'ro_RO' => 'Romanian - Romania' )
                ),
        'ru' => array(
                    array( 'ru_RU' => 'Russian - Russia' )
                ),
        'sa' => array(
                    array( 'sa_IN' => 'Sanskrit - India' )
                ),
        'sr' => array(
                    array( 'sr_Cy_SP' => 'Serbian (Cyrillic) - Serbia' ),
                    array( 'sr_Lt_SP' => 'Serbian (Latin) - Serbia' )
                ),
        'sk' => array(
                    array( 'sk_SK' => 'Slovak - Slovakia' )
                ),
        'sl' => array(
                    array( 'sl_SI' => 'Slovenian - Slovenia' )
                ),
        'es' => array(
                    array( 'es_AR' => 'Spanish - Argentina' ),
                    array( 'es_BO' => 'Spanish - Bolivia' ),
                    array( 'es_CL' => 'Spanish - Chile' ),
                    array( 'es_CO' => 'Spanish - Columbia' ),
                    array( 'es_CR' => 'Spanish - Costa Rica' ),
                    array( 'es_DO' => 'Spanish - Dominican Republic' ),
                    array( 'es_EC' => 'Spanish - Ecuador' ),
                    array( 'es_SV' => 'Spanish - El Salvador' ),
                    array( 'es_GT' => 'Spanish - Guatemala' ),
                    array( 'es_HN' => 'Spanish - Honduras' ),
                    array( 'es_MX' => 'Spanish - Mexico' ),
                    array( 'es_NI' => 'Spanish - Nicaragua' ),
                    array( 'es_PA' => 'Spanish - Panama' ),
                    array( 'es_PY' => 'Spanish - Paraguay' ),
                    array( 'es_PE' => 'Spanish - Peru' ),
                    array( 'es_PR' => 'Spanish - Puerto Rico' ),
                    array( 'es_ES' => 'Spanish - Spain' ),
                    array( 'es_UY' => 'Spanish - Uruguay' ),
                    array( 'es_VE' => 'Spanish - Venezuela' )
                    ),
        'sw' => array(
                    array( 'sw_KE' => 'Swahili - Kenya' )
                ),
        'sv' => array(
                    array( 'sv_FI' => 'Swedish - Finland' ),
                    array( 'sv_SE' => 'Swedish - Sweden' )
                    ),
        'syr' => array(
                    array( 'syr_SY' => 'Syrian - Syria' )
                ),
        'ta' => array(
                    array( 'ta_IN' => 'Tamil - India' )
                ),
        'tt' => array(
                    array( 'tt_RU' => 'Tatar - Russia' )
                ),
        'te' => array(
                    array( 'te_IN' => 'Telugu - India' )
                ),
        'th' => array(
                    array( 'th_TH' => 'Thai - Thailand' )
                ),
        'tr' => array(
                    array( 'tr_TR' => 'Turkish - Turkey' )
                ),
        'uk' => array(
                    array( 'uk_UA' => 'Ukrainian - Ukraine' )
                ),
        'ur' => array(
                    array( 'ur_PK' => 'Urdu - Pakistan' )
                ),
        'uz' => array(
                    array( 'uz_Cy_UZ' => 'Uzbek (Cyrillic) - Uzbekistan' ),
                    array( 'uz_Lt_UZ' => 'Uzbek (Latin) - Uzbekistan' )
                ),
        'vi' => array(
                    array( 'vi_VN' => 'Vietnamese - Vietnam' )
                ),
    );
    
    return $locales;
}


function pdcrequest_get_locales_for_lang( $lang ) {
    $lang_list = pdcrequest_list_locales();
    
    if (array_key_exists($lang, $lang_list) ) :
        return $lang_list[ $lang ];
    endif;
    
    return false;  
}


/**
 * list services
 * @return array services
 */
function pdcrequest_list_services() {
    $services = array(
        'translation',
        'translation-revision',
        'proofreading',
        'interpreting'
    );
    asort( $services );
    
    return $services;
}


/**
 * List Specialisations
 * @return array specialisations
 */
function pdcrequest_list_specialisations() {
    $specialisations = array(
        'legal'         => 'Legal & Regulatory',
        'literature'    => 'Literature',
        'education'     => 'Education',
        'humanistic'    => 'Humanistic',
        'academic'      => 'Academic',
        'advertising'   => 'Advertising, Marketing & Creative Media',
        'art'           => 'Art & Design',
        'scientific'    => 'Scientific',
        'medical'       => 'Medical',
        'engineering'   => 'Engineering',
        'expats'        => "People's everyday needs (eg for expats)",
        'business'      => 'Business',
        'economic'      => 'Economic',
        'media'         => 'Public Information Media (news, journalism, blogs)',
        'it'            => 'IT, Technology & Internet',
        'web'           => 'Web Languages (HTML, PHP, JavaScript, etc)'
    );
    asort( $specialisations );
    
    return $specialisations;
}


function pdcrequest_list_experiences() {
    $experiences = array(
        'Professional Level',
        'Amateur or Semi-Pro',
        'No Experience'
    );
    
    return $experiences;
}


/**
 * Count the number of translatable words in a post
 * @param int $post_id
 * @return int
 */
function pdcrequest_get_word_count( $post_id ) {
    
    $word_count = get_post_meta( $post_id, 'setlr_word_count', true );
    
    if ( ! absint( $word_count) || 0 == $word_count ) :
        //we need to count words
        $post = get_post( $post_id );
        $lang = get_post_meta( $post_id, 'doclang', true );
        $text_array = array( $post->post_title, $post->post_content, $post->post_excerpt );
        $complete_text = implode( ' ', $text_array );
        
        $count = new Setlr_Count( $complete_text, $lang );
        $word_count = $count->count();
        
        update_post_meta( $post_id, 'setlr_word_count', $word_count );
    endif;
    
    return $word_count;
}



/**
 * retrieves the request id for a given translation id
 * @param int $post_id the translation id
 * @return \WP_Error | int request_id
 */
function pdcrequest_get_request_for_translation( $translation_id ) {
    $post = get_post( $translation_id );
    
    if ( !isset( $post->post_parent) || empty( $post->post_parent ) ) :
        return new WP_Error('pdcrequest_no_request', __( "Sorry, we can't find the corresponding request!", 'pdcrequest' ), $translation_id );
    else :
        return $post->post_parent;
    endif;
}

/**
 * retrieves the translation id for a given request id
 * @global object $wpdb
 * @param int $request_id the request id
 * @return int || boolean false the translation id || false if none
 */
function pdcrequest_get_translation_for_request( $request_id ) {
    global $wpdb;
    $sql = "SELECT ID FROM $wpdb->posts WHERE post_parent=%d AND post_type='translation'";
    
    $translation_id = $wpdb->get_var( $wpdb->prepare( $sql, $request_id ) );
    
    if ( absint( $translation_id ) && 0 < $translation_id ) :
        return $translation_id;
    else :
        return false;
    endif;
}

