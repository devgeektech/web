<?php




/**
 * list countries for use in form select
 * @return array countries [code] => country name
 */
function pdcrequest_countries_select() {
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



/**
 * @deprecated since version 1.0 use lang::languages_form instead
 * @param type $user_id
 * @return string
 */
 /*
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
*/

/**
 * @deprecated since version 1.0 use lang::native_language_form instead
 * @param type $user_id
 * @return string
 */
 /*
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
*/






/**
 * show notifications
 * @return string
 */
function pdcrequest_show_notification() {
    $message = urldecode(get_query_var('message'));
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


function pdcrequest_show_translator_buttons( $service = null ) {
    
    $html = '<div class="pdcrequest-actions">';
    $html .= '<p class="secondary-action"><input name="save" type="submit" class="button secondary-button" value="' . __( 'Save Progress', 'pdcrequest') .'"></p>';
    $html .= '<p class="primary-action"><input name="finished" type="submit" class="button primary-button" value="' . sprintf( __( '%s Finished', 'pdcrequest'), $service ) .'"></p>';
    $html .= wp_nonce_field( 'translator-dashboard', 'pdcrequestnonce', true, false );
    $html .= '<input type="hidden" name="action" value="pdcrequest_update_translation">';
    $html .= '<input type="hidden" name="post_id" value="' . absint( get_the_ID() ) . '">';
    $html .= '</div>';
    
    return $html;
}


function pdcrequest_show_customer_buttons( $service = null ) {
    $html = '<div class="pdcrequest-actions">';
    $html .= '<p class="secondary-action"><input name="send_review" type="submit" class="button secondary-button" value="' . __( 'Send For Review', 'pdcrequest') .'"></p>';
    $html .= '<p class="secondary-action"><input name="reject" type="submit" class="button secondary-button" value="' . sprintf( __( 'Reject %s', 'pdcrequest'), $service ) .'"></p>';
    $html .= '<p class="primary-action"><input name="accept" type="submit" class="button primary-button" value="' . sprintf( __( 'Accept %s', 'pdcrequest'), $service ) .'"></p>';
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
function pdcrequest_show_appropriate_buttons( $service = null, $status = null ) {
    global $current_user;
    get_currentuserinfo();

    if ( current_user_can('helper') && in_array( $status, array( 'pending', 'in_progress' ) ) ) :
        $html = pdcrequest_show_translator_buttons( $service );
    elseif ( current_user_can('customer')) :
        $html = pdcrequest_show_customer_buttons( $service );
    else :
        $html = pdcrequest_show_translator_buttons( $service );
        $html .= pdcrequest_show_customer_buttons( $service );
    endif;
    
    return $html;
}

/**
 * get the ID of the dashboard for use in URL
 * 
 * @return int
 */
function pdcrequest_dashboard() {
    $page = get_page_by_path( 'my-dashboard');
    
    return $page->ID;
}


/**
 * get the ID of the review form page for use in URL
 * 
 * @return int
 */
function pdcrequest_send_review_page() {
    $page = get_page_by_path( 'translation-revision-form');
    
    $page_id = ( $page instanceof WP_Post ) ? $page->ID : false;
    return $page_id;
}


function pdcrequest_send_rejection_page() {
    $page = get_page_by_path( 'translation-rejection-form');
    
    $page_id = ( $page instanceof WP_Post ) ? $page->ID : false;
    return $page_id;
}




/**
 * list services
 * @return array services
 */
function pdcrequest_list_services() {
    $services = array(
        'translation'           => __( 'Translation', 'pdcrequest'),
        'translation-revision'  => __( 'Translation-Revision', 'pdcrequest'),
        'proofreading'          => __( 'Proofreading', 'pdcrequest'),
        'interpreting'          => __( 'Interpreting', 'pdcrequest'),
        'transcreation'         => __( 'Transcreation / Copywriting', 'pdcrequest'),
        'question'              => __( 'Answering local questions', 'pdcrequest')
    );
    asort( $services );
    
    return $services;
}



/**
 * list services
 * @return array services
 */
function pdcrequest_list_helper_services() {
    $services = array(
        'translation'           => __( 'My Translation', 'pdcrequest'),
        'translation-revision'  => __( 'Translation-Revision', 'pdcrequest'),
        'proofreading'          => __( 'Proofreading', 'pdcrequest'),
        'interpreting'          => __( 'Interpreting', 'pdcrequest'),
        'transcreation'         => __( 'Transcreation / Copywriting', 'pdcrequest'),
        'question'              => __( 'Answering local questions', 'pdcrequest')
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
        'legal'         => __( 'Legal & Regulatory', 'pdcrequest'),
        'literature'    => __( 'Literature', 'pdcrequest'),
        'education'     => __( 'Education', 'pdcrequest'),
        'humanistic'    => __( 'Humanistic', 'pdcrequest'),
        'academic'      => __( 'Academic', 'pdcrequest'),
        'advertising'   => __( 'Advertising, Marketing & Creative Media', 'pdcrequest'),
        'art'           => __( 'Art & Design', 'pdcrequest'),
        'scientific'    => __( 'Scientific', 'pdcrequest'),
        'medical'       => __( 'Medical, Healthcare, Wellness & Therapies', 'pdcrequest'),
        'engineering'   => __( 'Engineering', 'pdcrequest'),
        'expats'        => __( "People's everyday needs (eg for expats)", 'pdcrequest'),
        'business'      => __( 'Business', 'pdcrequest'),
        'economic'      => __( 'Economic', 'pdcrequest'),
        'media'         => __( 'Public Information Media (news, journalism, blogs)', 'pdcrequest'),
        'it'            => __( 'IT, Technology & Internet', 'pdcrequest'),
        'web'           => __( 'Web Languages (HTML, PHP, JavaScript, etc)', 'pdcrequest'),
        'engineering'   => __( 'Engineering & Mechanics', 'pdcrequest'),
        'farming'       => __( 'Farming, Agriculture & Horticulture', 'pdcrequest'),
        'leisure'       => __( 'Leisure & Hobbies', 'pdcrequest'),
        'music'         => __( 'Music', 'pdcrequest'),
        'politics'      => __( 'Politics & Current Affairs', 'pdcrequest'),
        'retail'        => __( 'Retail', 'pdcrequest'),
        'sports'        => __( 'Sports & Fitness', 'pdcrequest'),
        'travel'        => __( 'Travel & Tourism', 'pdcrequest'),
        'trades'        => __( 'Trades', 'pdcrequest'),
        'telecom'       => __( 'Telecoms & Communication', 'pdcrequest')
        
    );
    asort( $specialisations );
    
    return $specialisations;
}


function pdcrequest_list_experiences() {
    $experiences = array(
        'Professional Level',
        'Amateur or Semi-Pro'
    );
    
    return $experiences;
}


function pdcrequest_list_age_groups() {
    $age_groups = array(
        'children'      =>  __( 'Children', 'pdcrequest' ),
        'teen'          =>  __( 'Teenagers', 'pdcrequest' ),
        'young_adults'  =>  __('Young Adults', 'pdcrequest' ),
        'adults'        =>  __('Adults', 'pdcrequest'),
        'senior'       =>  __( 'Seniors', 'pdcrequest')
    );
    
    return $age_groups;
}


function pdcrequest_list_genders() {
    $genders = array(
        'female'    => __( 'Female', 'pdcrequest' ),
        'male'      => __( 'Male', 'pdcrequest' )
    );
    
    return $genders;
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


function pdcrequest_show_request( $post_id ) {
    global $current_user;
    
    //determine views according to current user capabilities
    $post = get_post( $post_id );
    // do not show anything to customers who are not the request author
    if ( current_user_can( 'customer' ) && $current_user->ID != $post->post_author ) return;
    
    //show the update_form to post_author if status = open
    $status = get_post_meta( $post_id, 'setlr_status', true );
    if ( $current_user->ID == $post->post_author ) :
        if ( $status == 'open' ) :
            
            //we have the project author, let's show an update project form
            return Setlr_Request::show_update_request_form( $post_id );
        else :
            
            return Setlr_Request::view_project( $post_id );
        endif;
    endif;
    
    if ( current_user_can('helper')  ) :
        $payment_status = Payment_Status::get_payment_status($post->ID);
        
        if ( 'paid' != $payment_status ) :
            //do not show errors just blank
           return; 
        else :
            //we have a potential translator
            return Setlr_Request::view_project( $post_id );
        endif;
    endif;
}

function format_datetime( $datetime, $lang = null ) {
    
    $date = new DateTime( $datetime );
    return $date->format( 'd-m-Y' );
}


function pdcrequest_get_country_name_from_code( $code ) {
    $countries = pdcrequest_countries_select();
    
    
    if ( array_key_exists ( $code, $countries ) ) :
        $name = $countries[$code];
    else :
        $name = '';
    endif;
    return $name;
}

function pdcrequest_get_lang_name_from_code( $code ) {
    $locales_list = lang::list_language_locales();
    if ( strpos($code, '_') !== false ) :
        //we have a locale
        list($lang_code, $locale) = explode('_', $code );
        
        $langs = $locales_list[$lang_code];
        $lang = array_keys($langs);
        $locales = '';
        foreach ( $langs as $key => $values ):
            
            foreach ( $values as $key => $locales ) :
                
                foreach( $locales as $locale_code => $locale_name ) :
                    
                    if ( $code === $locale_code ) :
                        $result = $locale_name;
                    endif;
            
                endforeach;
            endforeach;
        endforeach;
        
        return $result;
    else :
        //we have a language
        $langs = $locales_list[$code];
        $lang = array_keys($langs);
        
        return $lang[0];
    endif;

}

    /**
     * list status codes and plain language
     * @return array
     */
    function pdcrequest_list_full_statuses( $arg = null ) {
        switch( $arg ) :
            
            case 'translation' :
                $statuses = array(
                        'pending'           => __( 'pending', 'pdcrequest'),
                        'in_progress'       => __( 'in progress', 'pdcrequest'),
                        'customer_review'   => __( 'customer review', 'pdcrequest'),
                        'in_revision'       => __( 'in revision', 'pdcrequest'),
                        'finished'          => __( 'finished', 'pdcrequest'),
                        'accepted'          => __( 'accepted', 'pdcrequest' ),
                        'rejected'          => __( 'rejected', 'pdcrequest' )
                );
                break;
            case 'project' :
                $statuses = array(
                        'open'              => __( 'open', 'pdcrequest' ),
                        'pending'           => __( 'pending', 'pdcrequest'),
                        'customer_review'   => __( 'customer review', 'pdcrequest'),
                        'in_revision'       => __( 'in revision', 'pdcrequest'),
                        'cancelled'          => __( 'cancelled', 'pdcrequest'),
                        'closed'            => __( 'closed', 'pdcrequest' ),
                        'rejected'          => __( 'rejected', 'pdcrequest' )
                );
                break;
            default :
                $statuses = array(
                        'open'              => __( 'open', 'pdcrequest' ),
                        'pending'           => __( 'pending', 'pdcrequest'),
                        'in_progress'       => __( 'in progress', 'pdcrequest'),
                        'customer_review'   => __( 'customer review', 'pdcrequest'),
                        'in_revision'       => __( 'in revision', 'pdcrequest'),
                        'finished'          => __( 'finished', 'pdcrequest'),
                        'cancelled'          => __( 'cancelled', 'pdcrequest'),
                        'accepted'          => __( 'accepted', 'pdcrequest' ),
                        'closed'            => __( 'closed', 'pdcrequest' ),
                        'rejected'          => __( 'rejected', 'pdcrequest' )
                );
        endswitch;
                
        return $statuses;
    }
    
    /**
     * translate status code into language
     * @param string $status
     * @return mixed string if status exists | boolean false otherwise
     */
    function pdcrequest_translate_status( $status ) {
        $statuses = pdcrequest_list_full_statuses();
        if ( array_key_exists( $status, $statuses )) :
            return $statuses[$status];
        endif;
        return false;
    }
    
    
    /**
     * list all available payment statuses with corresponding human readable text
     * @return array
     */
    function pdcrequest_list_full_payment_statuses() {
        $statuses = array( 
                'pending'           => __( 'pending', 'pdcrequest'), 
                'paid'              => __( 'paid', 'pdcrequest'), 
                'refund_pending'    => __( 'refund pending', 'pdcrequest'), 
                'refunded'          => __( 'refunded', 'pdcrequest') 
            );
        
        return $statuses;
    }
    /**
     * translate status code into language
     * @param string $status
     * @return mixed string if status exists | boolean false otherwise
     */
    function pdcrequest_translate_payment_status( $status ) {
        $statuses = pdcrequest_list_full_payment_statuses();
        if ( array_key_exists( $status, $statuses )) :
            return $statuses[$status];
        endif;
        
        return false;
    }
    
    
    function pdcrequest_get_translators( $from, $to ) {
        global $wpdb;
        switch ( strpos( $to, '_' ) ) :
            case false :
                $key = 'nativelang';
                break;
            default :
                $key = 'setlr-locale';
                break;
        endswitch;
       
        /* Using WP_User_Query does not work due to serialized meta requestlang */
        $user_meta = $wpdb->prefix . 'usermeta';
        $sql = "SELECT mt1.user_id "
                . "FROM $user_meta mt1 "
                . "INNER JOIN $user_meta mt2 ON mt2.user_id = mt1.user_id "
                . "INNER JOIN $user_meta mt3 ON mt3.user_id = mt1.user_id "
                . "WHERE mt1.meta_key = 'requestlang' AND mt1.meta_value LIKE '%". $from . "%' "
                . "AND mt2.meta_key = '" . $key . "' AND mt2.meta_value = '" . $to . "' "
                . "AND mt3.meta_key = 'wp_capabilities' AND mt3.meta_value LIKE '%helper%' ";
        
        $user_query = $wpdb->get_results( $sql );
        
        return $user_query;
    }

    
    /**
     * outputs a list of potential translators to do the revision
     * @param string $from language code of the original text
     * @param string $to language code or locale of desired translation
     * @param array $selected translator ids that have been previously selected
     * @param int $exclude id of original translator to be excluded so as not to do his own revision
     * @return string HTML block with checkbox input fields
     */
    function pdcrequest_select_translators( $from, $to, $selected = array(), $exclude = '' ) {
        $translators = pdcrequest_get_translators( $from, $to );
        
        //do exclude if any
        if ( ! empty( $exclude ) && absint( $exclude ) && $key = array_search( $exclude, $translators ) !== false ) :
            unset( $translators[$key]);
        endif;
        $html = '';
        
        foreach( $translators as $translator ) :
            $user_id = absint( $translator->user_id );
            $checked = '';
            $user = get_user_by('id', $user_id );
            if ( is_array( $selected) && ! empty($selected )) :
                $checked = (in_array( $user_id, $selected ) ) ? 'checked="checked"' : '';
            endif;
            $html .= '<label class="setlr-checkbox"><input type="checkbox" name="revision_author[]" value="' . absint( $user->ID ) . '"';
            $html .= $checked . ' >';
            $html .= esc_html( $user->display_name );
            $html .= '</label>';
        endforeach;
        
        
        return $html;
    }

    
    
function pdcrequest_show_pending_number( $menu ) {
    
    $type = "setlr_rev_request";
    $status = "pending";
    $num_posts = wp_count_posts( $type, 'readable' );
    $pending_count = 0;
    if ( !empty($num_posts->$status) )
        $pending_count = $num_posts->$status;

    // build string to match in $menu array
    if ($type == 'post') {
        $menu_str = 'edit.php';
    // support custom post types
    } else {
        $menu_str = 'edit.php?post_type=' . $type;
    }

    // loop through $menu items, find match, add indicator
    foreach( $menu as $menu_key => $menu_data ) {
        if( $menu_str != $menu_data[2] )
            continue;
        $menu[$menu_key][0] .= " <span class='update-plugins count-$pending_count'><span class='plugin-count'>" . number_format_i18n($pending_count) . '</span></span>';
    }
    return $menu;
}


function pdcrequest_sanitize_array( $type, $array ) {
    $result = array();
    switch ( $type ) :
        case 'absint' :
            foreach( $array as $item ) :
                if ( absint( $item ) ) :
                    $result[] = absint( $item );
                endif;
            endforeach;
            break;
        case 'string' :
            foreach ( $array as $item ) :
                if ( is_string( $item ) ) :
                    $result[] = sanitize_text_field( $item );
                endif;
            endforeach;
            break;
        case 'float' :
            foreach ( $array as $item ) :
                if ( is_numeric( $item ) ) :
                    $result[] = (float) $item;
                endif;
            endforeach;
            break;
    endswitch;
    
    return $result;
}


function pdcrequest_format_date( $id ) {
    $post = get_post( $id );
    
    $before = '<p class="setlr-date">';
    $after = '</p>';
    if ( $post ) :
        $date = date_create($post->post_date);
        
        return $before . date_format($date, 'M d, Y') . $after;
    endif;
    return false;
}

function pdcrequest_format_amount( $currency = 'GBP', $amount ) {
    return Setlr_Payment::get_currency_symbol($currency) . number_format ( $amount , 2 );
}




function pdcrequest_author_photo( $post_id ) {
    $post = get_post( $post_id );
    $html = '';
    if ( $post instanceof WP_Post ) :
        $user_id = $post->post_author;
    endif;
    
    if ( isset( $user_id ) && absint( $user_id ) ) :
        $picture_id = get_user_meta( $user_id, 'profile_picture', true );

        if ( absint( $picture_id ) ) :	
            $html .= wp_get_attachment_link( $picture_id, array( 56, 56 ), false, true, '' );
        else :
            /* if no picture get avatar */
            $author_bio_avatar_size = apply_filters( 'setlr_author_bio_avatar_size', 56 );
            $html .= get_avatar( get_the_author_meta( 'user_email', $user_id ), $author_bio_avatar_size );
        endif;
    endif;
    
    echo $html;
}

function pdcrequest_link_to_dashboard( $name, $class = null ) {
    $dashboard = get_page_by_path( 'my-dashboard');
    $dashboard_url = get_permalink( $dashboard->ID );
    
    $class_name = ( isset( $class) && !is_null( $class ) ) ? 'class="' . esc_attr( $class ) . '"' : '';
    
    return '<a href="' . $dashboard_url . '"' . $class_name . ' title="">' . esc_html( $name ) . '</a>';
}

function pdcrequest_show_nickname( $user_id ) {
    return get_user_meta( $user_id, 'nickname', true );
}