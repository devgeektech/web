<?php


/**
 * deals with a language
 * list languages and render select form fragments
 *
 * @version 0.4
 * @author philippe
 */
class lang {
    /**
     *
     * @var string 
     */
    var $lang_code;
    
    /**
     *
     * @var string 
     */
    var $lang_name;
    
    /**
     *
     * @var type 
     */
    var $locale;
    
    
    /**
     *
     * @var array 
     */
    var $lang_telltales = array(
        'fr'    => array( 'à' ),
        'es'    => array( '~', 'ñ', '¿' ),
        'de'    => array( 'ß')
    );
    /**
     * 
     * @param string $lang_code
     * @param string $lang_name
     * @param string $locale
     */
    public function __construct($lang_code, $lang_name, $locale) {
        $this->code = $lang_code;
        $this->name = $lang_name;
        $this->locale = $locale;
    }
    
     
    /**
     * render a language select
     * @param string $type
     * @param array $default
     * @return string html select
     */
    public static function render_lang_select( $type = 'from', $default = array() ) {
        $langs = self::get_main_languages_list();
        
        switch ( $type ) :
            case 'to' :
                $select_id = 'to-lang';
                $label = __( 'Translate To', 'pdcrequest');
                $default_key = 'to-lang';
                break;
            case 'from' :
                $select_id = 'from-lang';
                $label = __( 'Translate From', 'pdcrequest');
                $default_key = 'from-lang';
                break;
            case 'native' :
                $select_id = 'nativelang';
                $label = '';__( 'Native Language', 'pdcrequest');
                $default_key = '';
                break;
            case 'questionlang' :
                $select_id = 'questionlang';
                $label = __( 'That I would like answered in', 'pdcrequest');
                $default_key = 'questionlang';
                break;
            case 'question_about' :
                $select_id = 'question_about';
                $label = __( 'I have a local question about', 'pdcrequest');
                $default_key = 'question_about';
                break;
            case 'document' :
                $select_id = 'doclang';
                $label = __( 'Document Language', 'pdcrequest');
                $default_key = 'doclang';
                break;
            
        endswitch;
        
        $html = '<label for="' . esc_attr( $select_id ) . '">' . esc_html($label) . '</label>';
        $html .= ' <select name="' . esc_attr( $select_id ) . '" id="' . esc_attr( $select_id ) . '" class="pdcrequest-lang" required>';
        foreach ( $langs as $key => $value ) :
            foreach( $value as $code => $name ) :
                $default_value = ( isset( $default[$default_key])) ? $default[$default_key][0] : '';
                
                if ( isset( $default[$default_key])) :
                    $selected = selected( $default[$default_key][0], $code, false );
                else :
                    $selected = '';
                endif;
                
                $html .= '<option value="' . esc_attr( $code ) . '"' . $selected . '>' . esc_html( $name) . '</option>';
            endforeach;
        endforeach;
        $html .= '</select>';
        
        return $html;
    }
    
    
    /**
     * 
     * @param string $type
     * @param string $default
     * @return string html fieldset with checkbox inputs
     */
    public static function render_lang_checkbox( $type = 'to', $default = null ) {
        $langs = self::get_main_languages_list();
        
        switch ( $type ) :
            case 'to' :
                $select_id = 'to-lang';
                break;
            case 'from' :
                $select_id = 'from-lang';
                break;
            case 'requestlang';
                $select_id = 'requestlang';
                break;
            default:
                $select_id = 'from-lang';
        endswitch;
        
        $html = '<fieldset id="' . esc_attr($select_id) . '" class="language-form">';
        foreach( $langs as $key => $value ) :
            foreach( $value as $code => $name):
                if ( is_array($default) ) :
                    $checked = ( in_array($code, $default )) ? 'checked="checked"' : '';
                else :
                    $checked = checked($code, $default);
                endif;

                $html .= '<label>';
                $html .= '<input type="checkbox" name="' . esc_attr() . '" value="' . esc_attr($code) . '"' . $checked . '>';
                $html .= esc_html($name) . '</label>';
            endforeach;
        endforeach;
        $html .= '</fieldset>';
        
        return $html;
    }
    
    
    /**
     * check if lang belongs to our known language list
     * @param string $lang
     * @return boolean true if lang exists, false otherwise
     * @uses list_langs method
     */
    public static function lang_exists( $lang_code ) {
        $lang_list = self::list_language_locales();
        
        $code = str_split($lang_code, 2 );
        
        if ( array_key_exists( $code[0], $lang_list ) ) :
            return true; //lang exists
        endif;
               
        return false;
    }
    
    
    /**
     * save languages as post_meta
     * @param int $request_id
     * @param string $fieldname
     * @param string $value
     * @return mixed int/boolean false
     */
    public static function save_lang_info( $request_id, $fieldname, $value ) {
        write_log($request_id . ', ' . $fieldname . ', ' . $value);
        $fields = array('to-lang', 'from-lang','nativelang', 'questionlang', 'question_about', 'doclang' );
        if ( ! in_array( $fieldname, $fields ) ) return;
        $update = false;
        
        switch ( $fieldname ) :
            case 'to':
            case 'to-lang' :
                $meta_key = 'to-lang';
                break;
            case 'from' :
            case 'from-lang' :
                $meta_key = 'from-lang';
                break;
            case 'native' :
                $meta_key = 'nativelang';
                break;
            case 'questionlang' :
                $meta_key = 'questionlang';
                break;
            case 'question_about' :
                $meta_key = 'question_about';
                break;
            case 'document' :
                $meta_key = 'doclang';
                break;
            default:
                $meta_key = 'from-lang';
        endswitch;
        
        //if ( self::lang_exists($value) ) :
            $update = update_post_meta( $request_id, $meta_key, $value );
            write_log( 'update='. $update);
        //endif;
        
        return $update;
    }
    
    
    public static function from_lang_name_to_code( $langname ) {
        $langs_array = self::list_langs();
        return array_search( $langname, $langs_array);
    }
    
    
    public static function render_lang_info( $from, $to ) {
        $html = '<fieldset>';
        $html .= '<legend>' . __( 'Translation', 'pdcrequest') . '</legend>';
        $html .= '<p>' . __( 'From', 'pdcrequest') . ' ' . esc_attr( pdcrequest_get_lang_name_from_code( $from ) ) . '<br>';
        $html .= __( 'To', 'pdcrequest') . ' ' . esc_attr( pdcrequest_get_lang_name_from_code( $to ) ) . '</p>';
        
        return $html;
    }
    
    /**
     * determines the language of a given text
     * 
     * @param type $string
     * @return array (isocode of language, confidence score )
     */
    public static function determine_lang( $string ) {
        $lang_telltales = $this->lang_telltales;
        $determined_lang = array();
        $score = 0;
        foreach ( $lang_telltales as $lang => $telltales ) :
            foreach ( $telltales as $telltale ) :
                if ( strpos( $string, $telltale, 0 ) ) :
                    $determined_lang[] = $lang;
                endif;
            endforeach;
        endforeach;
        
        $result = self::determine_score($determined_lang);
        
        return result;
    }
    
    /**
     * clalculates confidence score as percentage
     * @param array $array
     * @return array (language code, score as percentage)
     */
    public static function determine_score( $array ) {
        if ( ! empty( $array ) ) :
            $total = count( $array );
            $counted = array_count_values($array);

            $max = max( $counted );
            foreach ( $array as $key => $value ) :
                if ( $max == $value ) :
                    $lang = $key;
                endif;
            endforeach;
            $score = ($total > 0 ) ? $max / $total : 0;
        else :
            $lang = "";
            $score = 0;
        endif;
        return array( $lang, $score );
    }
    
    /**
     * list all languages and locales
     * @return array umtidimensional key (language) => value = array (locale code => language name - Country)
     */
    public static function list_language_locales() {
    $locales = array(
        'af' => array( __( 'Afrikaan', 'pdcrequest' ) => array(
                    array( 'af_ZA' => __( 'Afrikaan - South Africa', 'pdcrequest' ) )
                ) ),
        'sq' => array( __( 'Albanian', 'pdcrequest' ) => array(
                    array( 'sq_AL' => __( 'Albanian - Albania', 'pdcrequest' ) )
                ) ),
        'ar' => array( __( 'Arabic', 'pdcrequest' ) => array(
                    array( 'ar_DZ' => __( 'Arabic - Algeria', 'pdcrequest' ) ),
                    array( 'ar_BH' => __( 'Arabic - Bahrein', 'pdcrequest' ) ),
                    array( 'ar_EG' => __( 'Arabic - Egypt', 'pdcrequest' ) ),
                    array( 'ar_IQ' => __( 'Arabic - Iraq', 'pdcrequest' ) ),
                    array( 'ar_JO' => __( 'Arabic - Jordan', 'pdcrequest' ) ),
                    array( 'ar_KW' => __( 'Arabic - Kuwait', 'pdcrequest' ) ),
                    array( 'ar_LB' => __( 'Arabic - Lebanon', 'pdcrequest' ) ),
                    array( 'ar_LY' => __( 'Arabic - Lybia', 'pdcrequest' ) ),
                    array( 'ar_MA' => __( 'Arabic - Morocco', 'pdcrequest' ) ),
                    array( 'ar_OM' => __( 'Arabic - Oman', 'pdcrequest' ) ),
                    array( 'ar_QA' => __( 'Arabic - Qatar', 'pdcrequest' ) ),
                    array( 'ar_SA' => __( 'Arabic - Saudi Arabia', 'pdcrequest' ) ),
                    array( 'ar_SY' => __( 'Arabic - Syria', 'pdcrequest' ) ),
                    array( 'ar_TN' => __( 'Arabic - Tunisia', 'pdcrequest' ) ),
                    array( 'ar_AE' => __( 'Arabic - United Arab Emirates', 'pdcrequest' ) ),
                    array( 'ar_YE' => __( 'Arabic - Yemen', 'pdcrequest' ) )
                    ) ),
        'hy' => array( __( 'Armenian', 'pdcrequest' ) => array(
                    array( 'hy_AM' => __( 'Armenian - Armenia', 'pdcrequest' ) )
                ) ),
        'az' => array( __( 'Azeri', 'pdcrequest' ) => array(
                    array( 'az_AZ' => __( 'Azeri - Azerbaijan', 'pdcrequest' ) )
                ) ),
        'eu' => array( __( 'Basque', 'pdcrequest' ) => array(
                    array( 'eu_ES' => __( 'Basque - Basque', 'pdcrequest' ) )
                ) ),
        'be' => array( __( 'Belarussian', 'pdcrequest' ) => array(
                    array( 'be_BY' => __( 'Belarusian - Belarus', 'pdcrequest' ) )
                ) ),
        'bg' => array( __( 'Bulgarian', 'pdcrequest' ) => array(
                    array( 'bg_BG' => __( 'Bulgarian - Bulgaria', 'pdcrequest' ) )
                ) ),
        'ca' => array( __( 'Catalan', 'pdcrequest' ) => array(
                    array( 'ca_ES' => __( 'Catalan - Catalan', 'pdcrequest' ) )
                ) ),
        'zh' => array( __( 'Chinese', 'pdcrequest' ) => array(
                    array( 'zh_CN' => __( 'Chinese - China', 'pdcrequest' ) ),
                    array( 'zh_HK' => __( 'Chinese - Hong Kong', 'pdcrequest' ) ),
                    array( 'zh_MO' => __( 'Chinese - Macau SAR', 'pdcrequest' ) ),
                    array( 'zh_SG' => __( 'Chinese - Singapore', 'pdcrequest' ) ),
                    array( 'zh_TW' => __( 'Chinese - Taiwan', 'pdcrequest' ) ),
                    array( 'zh_CHS' => __( 'Chinese (Simplified)', 'pdcrequest' ) ),
                    array( 'zh_TW' => __( 'Chinese (Traditional)', 'pdcrequest' ) )
                    ) ),
        'hr' => array( __( 'Croatian', 'pdcrequest' ) => array(
                    array( 'hr_HR' => __( 'Croatian - Crotia', 'pdcrequest' ) )
                ) ),
        'da' => array( __( 'Danish', 'pdcrequest' ) => array(
                    array( 'da_DK' => __( 'Danish - Denmark', 'pdcrequest' ) )
                ) ),
        'div' => array( __( 'Dhivehi', 'pdcrequest' ) => array(
                    array( 'div_MV' => __( 'Dhivehi - Maldives', 'pdcrequest' ) )
                ) ),
        'nl' => array( __( 'Dutch', 'pdcrequest' ) => array(
                    array( 'nl_BE' => __( 'Dutch - Belgium', 'pdcrequest' ) ),
                    array( 'nl_NL' => __( 'Dutch - The Nederlands', 'pdcrequest' ) )
                    ) ),
        'en' => array( __( 'English', 'pdcrequest' ) => array(
                    array( 'en_AU' => __( 'English - Australia', 'pdcrequest' ) ),
                    array( 'en_BE' => __( 'English - Belize', 'pdcrequest' ) ),
                    array( 'en_CA' => __( 'English - Canada', 'pdcrequest' ) ),
                    array( 'en_CB' => __( 'English - Carabbean', 'pdcrequest' ) ),
                    array( 'en_IE' => __( 'English - Ireland', 'pdcrequest' ) ),
                    array( 'en_JM' => __( 'English - Jamaica', 'pdcrequest' ) ),
                    array( 'en_NZ' => __( 'English - New Zealand', 'pdcrequest' ) ),
                    array( 'en_PH' => __( 'English - Philippines', 'pdcrequest' ) ),
                    array( 'en_ZA' => __( 'English - South Africa', 'pdcrequest' ) ),
                    array( 'en_TT' => __( 'English - Trinidad and Tobago', 'pdcrequest' ) ),
                    array( 'en_GB' => __( 'English - United Kingdom', 'pdcrequest' ) ),
                    array( 'en_US' => __( 'English - United States', 'pdcrequest' ) ),
                    array( 'en_ZW' => __( 'English - Zimbabwe', 'pdcrequest' ) )
                    ) ),
        'et' => array( __( 'Estonian', 'pdcrequest' ) => array(
                    array( 'et_EE' => __( 'Estonian - Estonia', 'pdcrequest' ) )
                ) ),
        'fo' => array( __( 'Faroese', 'pdcrequest' ) => array(
                    array( 'fo_FO' => __( 'Faroese - Faroe Islands', 'pdcrequest' ) )
                ) ),
        'fa' => array( __( 'Farsi', 'pdcrequest' ) => array(
                    array( 'fa_IR' => __( 'Farsi - Iran', 'pdcrequest' ) )
                ) ),
        'fi' => array( __( 'Finnish', 'pdcrequest' ) => array(
                    array( 'fi_FI' => __( 'Finnish - Finland', 'pdcrequest' ) )
                ) ),
        'fr' => array( __( 'French', 'pdcrequest' ) => array(
                    array( 'fr_BE' => __( 'French - Belgium', 'pdcrequest' ) ),
                    array( 'fr_CA' => __( 'French - Canada', 'pdcrequest' ) ),
                    array( 'fr_FR' => __( 'French - France', 'pdcrequest' ) ),
                    array( 'fr_LU' => __( 'French - Luxembourg', 'pdcrequest' ) ),
                    array( 'fr_MC' => __( 'French - Monaco', 'pdcrequest' ) ),
                    array( 'fr_CH' => __( 'French - Switzerland', 'pdcrequest' ) )
                    ) ),
        'gl' => array( __( 'Galician', 'pdcrequest' ) => array(
                    array( 'gl_ES' => __( 'Galician - Galician', 'pdcrequest' ) )
                ) ),
        'ka' => array( __( 'Georgian', 'pdcrequest' ) => array(
                    array( 'ka_GE' => __( 'Georgian - Georgia', 'pdcrequest' ) )
                ) ),
        'de' => array( __( 'German', 'pdcrequest' ) => array(
                    array( 'de_AT' => __( 'German - Austria', 'pdcrequest' ) ),
                    array( 'de_DE' => __( 'German - Germany', 'pdcrequest' ) ),
                    array( 'de_LI' => __( 'German - Lichtenstein', 'pdcrequest' ) ),
                    array( 'de_LU' => __( 'German - Luxembourg', 'pdcrequest' ) ),
                    array( 'de_CH' => __( 'German - Switzerland', 'pdcrequest' ) )
                    ) ),
        'el' => array( __( 'Greek', 'pdcrequest' ) => array(
                    array( 'el_GR' => __( 'Greek - Greece', 'pdcrequest' ) )
                ) ),
        'gu' => array( __( 'Gurajati', 'pdcrequest' ) => array(
                    array( 'gu_IN' => __( 'Gurajati - India', 'pdcrequest' ) )
                ) ),
        'he' => array( __( 'Hebrew', 'pdcrequest' ) => array(
                    array( 'he_IL' => __( 'Hebrew - Israel', 'pdcrequest' ) )
                ) ),
        'hi' => array( __( 'Hindi', 'pdcrequest' ) => array(
                    array( 'hi_IN' => __( 'Hindi - India', 'pdcrequest' ) )
                ) ),
        'hu' => array( __( 'Hungarian', 'pdcrequest' ) => array(
                    array( 'hu_HU' => __( 'Hungarian - Hungary', 'pdcrequest' ) )
                ) ),
        'is' => array( __( 'Icelandic', 'pdcrequest' ) => array(
                    array( 'is_IS' => __( 'Icelandic - Iceland', 'pdcrequest' ) )
                ) ),
        'id' => array( __( 'Indonesian', 'pdcrequest' ) => array(
                    array( 'id_ID' => __( 'Indonesian - Indonesia', 'pdcrequest' ) )
                ) ),
        'it' => array( __( 'Italian', 'pdcrequest' ) => array(
                    array( 'it_IT' => __( 'Italian - Italy', 'pdcrequest' ) ),
                    array( 'it_CH' => __( 'Italian - Switzerland', 'pdcrequest' ) )
                    ) ),
        'ja' => array( __( 'Japanese', 'pdcrequest' ) => array(
                    array( 'jp_JP' => __( 'Japanese - Japan', 'pdcrequest' ) )
                ) ),
        'kn' => array( __( 'Kannada', 'pdcrequest' ) => array(
                    array( 'kn_IN' => __( 'Kannada - India', 'pdcrequest' ) )
                ) ),
        'kk' => array( __( 'Kazakh', 'pdcrequest' ) => array(
                    array( 'kk_KZ' => __( 'Kazakh - Kazakhstan', 'pdcrequest' ) )
                ) ),
        'kok' => array( __( 'Konkani', 'pdcrequest' ) => array(
                    array( 'kok_IN' => __( 'Konkani - India', 'pdcrequest' ) )
                ) ),
        'ko' => array( __( 'Korean', 'pdcrequest' ) => array(
                    array( 'ko_KR' => __( 'Korean - Korea', 'pdcrequest' ) )
                ) ),
        'ky' => array( __( 'Kyrgyz', 'pdcrequest' ) => array(
                    array( 'ky_KZ' => __( 'Kyrgyz - Kazakhstan', 'pdcrequest' ) )
                ) ),
        'lv' => array( __( 'Latvian', 'pdcrequest' ) => array(
                    array( 'lv_LV' => __( 'Latvian - Latvia', 'pdcrequest' ) )
                ) ),
        'lt' => array( __( 'Lithunian', 'pdcrequest' ) => array(
                    array( 'lt_LT' => __( 'Lithunian - Lithunia', 'pdcrequest' ) )
                ) ),
        'mk' => array( __( 'Macedonian (FYROM)', 'pdcrequest' ) => array(
                    array( 'mk_MK' => __( 'Macedonian (FYROM)', 'pdcrequest' ) )
                ) ),
        'ms' => array( __( 'Malay', 'pdcrequest' ) => array(
                    array( 'ms_BN' => __( 'Malay - Brunei', 'pdcrequest' ) ),
                    array( 'ms_MY' => __( 'Malay - Malaysia', 'pdcrequest' ) )
                    ) ),
        'mr' => array( __( 'Marathi', 'pdcrequest' ) => array(
                    array( 'mr_IN' => __( 'Marathi - India', 'pdcrequest' ) )
                ) ),
        'mn' => array( __( 'Mongolian', 'pdcrequest' ) => array(
                    array( 'mn_MN' => __( 'Mongolian - Mongolia', 'pdcrequest' ) )
                ) ),
        'nb' => array( __( 'Norwegian (Bokmal)', 'pdcrequest' ) => array(
                    array( 'nb_NO' => __( 'Norwegian (Bokmal) - Norway', 'pdcrequest' ) )
                ) ),
        'nn' => array( __( 'Norwegian (Nynorsk)', 'pdcrequest' ) => array(
                    array( 'nn_NO' => __( 'Norwegian (Nynorsk) - Norway', 'pdcrequest' ) )
                ) ),
        'pl' => array( __( 'Polish', 'pdcrequest' ) => array(
                    array( 'pl_PL' => __( 'Polish - Poland', 'pdcrequest' ) )
                ) ),
        'pt' => array( __( 'Portuguese', 'pdcrequest' ) => array(
                    array( 'pt_BR' => __( 'Portuguese - Brazil', 'pdcrequest' ) ),
                    array( 'pt_PT' => __( 'Portuguese - Portugal', 'pdcrequest' ) )
                    ) ),
        'pa' => array( __( 'Punjabi', 'pdcrequest' ) => array(
                    array( 'pa_IN' => __( 'Punjabi - India', 'pdcrequest' ) )
                ) ),
        'ro' => array( __( 'Romanian', 'pdcrequest' ) => array(
                    array( 'ro_RO' => __( 'Romanian - Romania', 'pdcrequest' ) )
                ) ),
        'ru' => array( __( 'Russian', 'pdcrequest' ) => array(
                    array( 'ru_RU' => __( 'Russian - Russia', 'pdcrequest' ) )
                ) ),
        'sa' => array( __( 'Sanskrit', 'pdcrequest' ) => array(
                    array( 'sa_IN' => __( 'Sanskrit - India', 'pdcrequest' ) )
                ) ),
        'sr' => array( __( 'Serbian', 'pdcrequest' ) => array(
                    array( 'sr_Cy_SP' => __( 'Serbian (Cyrillic) - Serbia', 'pdcrequest' ) ),
                    array( 'sr_Lt_SP' => __( 'Serbian (Latin) - Serbia', 'pdcrequest' ) )
                ) ),
        'sk' => array( __( 'Slovak', 'pdcrequest' ) => array(
                    array( 'sk_SK' => __( 'Slovak - Slovakia', 'pdcrequest' ) )
                ) ),
        'sl' => array( __( 'Slovenian', 'pdcrequest' ) => array(
                    array( 'sl_SI' => __( 'Slovenian - Slovenia', 'pdcrequest' ) )
                ) ),
        'es' => array( __( 'Spanish', 'pdcrequest' ) => array(
                    array( 'es_AR' => __( 'Spanish - Argentina', 'pdcrequest' ) ),
                    array( 'es_BO' => __( 'Spanish - Bolivia', 'pdcrequest' ) ),
                    array( 'es_CL' => __( 'Spanish - Chile', 'pdcrequest' ) ),
                    array( 'es_CO' => __( 'Spanish - Columbia', 'pdcrequest' ) ),
                    array( 'es_CR' => __( 'Spanish - Costa Rica', 'pdcrequest' ) ),
                    array( 'es_DO' => __( 'Spanish - Dominican Republic', 'pdcrequest' ) ),
                    array( 'es_EC' => __( 'Spanish - Ecuador', 'pdcrequest' ) ),
                    array( 'es_SV' => __( 'Spanish - El Salvador', 'pdcrequest' ) ),
                    array( 'es_GT' => __( 'Spanish - Guatemala', 'pdcrequest' ) ),
                    array( 'es_HN' => __( 'Spanish - Honduras', 'pdcrequest' ) ),
                    array( 'es_MX' => __( 'Spanish - Mexico', 'pdcrequest' ) ),
                    array( 'es_NI' => __( 'Spanish - Nicaragua', 'pdcrequest' ) ),
                    array( 'es_PA' => __( 'Spanish - Panama', 'pdcrequest' ) ),
                    array( 'es_PY' => __( 'Spanish - Paraguay', 'pdcrequest' ) ),
                    array( 'es_PE' => __( 'Spanish - Peru', 'pdcrequest' ) ),
                    array( 'es_PR' => __( 'Spanish - Puerto Rico', 'pdcrequest' ) ),
                    array( 'es_ES' => __( 'Spanish - Spain', 'pdcrequest' ) ),
                    array( 'es_UY' => __( 'Spanish - Uruguay', 'pdcrequest' ) ),
                    array( 'es_VE' => __( 'Spanish - Venezuela', 'pdcrequest' ) )
                    ) ),
        'sw' => array( __( 'Swahili', 'pdcrequest' ) => array(
                    array( 'sw_KE' => __( 'Swahili - Kenya', 'pdcrequest' ) )
                ) ),
        'sv' => array( __( 'Swedish', 'pdcrequest' ) => array(
                    array( 'sv_FI' => __( 'Swedish - Finland', 'pdcrequest' ) ),
                    array( 'sv_SE' => __( 'Swedish - Sweden', 'pdcrequest' ) )
                    ) ),
        'syr' => array( __( 'Syrian', 'pdcrequest' ) => array(
                    array( 'syr_SY' => __( 'Syrian - Syria', 'pdcrequest' ) )
                ) ),
        'ta' => array( __( 'Tamil', 'pdcrequest' ) => array(
                    array( 'ta_IN' => __( 'Tamil - India', 'pdcrequest' ) )
                ) ),
        'tt' => array( __( 'Tatar', 'pdcrequest' ) => array(
                    array( 'tt_RU' => __( 'Tatar - Russia', 'pdcrequest' ) )
                ) ),
        'te' => array( __( 'Telugu', 'pdcrequest' ) => array(
                    array( 'te_IN' => __( 'Telugu - India', 'pdcrequest' ) )
                ) ),
        'th' => array( __( 'Thai', 'pdcrequest' ) => array(
                    array( 'th_TH' => __( 'Thai - Thailand', 'pdcrequest' ) )
                ) ),
        'tr' => array( __( 'Turkish', 'pdcrequest' ) => array(
                    array( 'tr_TR' => __( 'Turkish - Turkey', 'pdcrequest' ) )
                ) ),
        'uk' => array( __( 'Ukrainian', 'pdcrequest' ) => array(
                    array( 'uk_UA' => __( 'Ukrainian - Ukraine', 'pdcrequest' ) )
                ) ),
        'ur' => array( __( 'Urdu', 'pdcrequest' ) => array(
                    array( 'ur_PK' => __( 'Urdu - Pakistan', 'pdcrequest' ) )
                ) ),
        'uz' => array( __( 'Uzbek', 'pdcrequest' ) => array(
                    array( 'uz_Cy_UZ' => __( 'Uzbek (Cyrillic) - Uzbekistan', 'pdcrequest' ) ),
                    array( 'uz_Lt_UZ' => __( 'Uzbek (Latin) - Uzbekistan', 'pdcrequest' ) )
                ) ),
        'vi' => array( __( 'Vietnamese', 'pdcrequest' ) => array(
                    array( 'vi_VN' => __( 'Vietnamese - Vietnam', 'pdcrequest' ) )
                ) )
    );
    
    return $locales;
    }
    
    
    /**
     * list all main languages
     * @return array lang_code => lang_name
     */
    public static function get_main_languages_list() {
        $locales = self::list_language_locales();
        
        $languages = array();
        
        foreach( $locales as $lang_code => $value ) :
            $name = array_keys($value);
            
            $languages[] = array( $lang_code => $name[0] );
        endforeach;
        
        return $languages;
    }
    
    
    /**
     * list locales for given language
     * @param string $lang_code
     * @return array
     */
    public static function get_locales_for_language( $lang_code ) {
        $complete = self::list_language_locales();
        $lang_code = (string) $lang_code;
        $locales = $complete[$lang_code];
        $keys = array_keys( $locales );
        
        return $locales[$keys[0]];
    }
    
    
    /**
     * render the locales form for ajax calls as html fieldset with radio buttons
     * @return Void 
     */
    public static function render_locales_form() {
        //check nonce
        
       // check_ajax_referer( 'pdc-update-profile', 'nonce', true );
       
        $lang_code = ( isset( $_POST['lang'] ) && self::lang_exists($_POST['lang'])) ? esc_html($_POST['lang']) : false;
        
        if( $lang_code != false ) :
            
            //get locales
            $locales = self::get_locales_for_language($lang_code);
            write_log($locales);

            if ( ! empty( $locales ) ) :
                
                //populate fieldset with radio input
                $html =  '<fieldset class="setlr-locales-form"><legend>' . __( 'Please choose a locale', 'pdcrequest') . '</legend>';

                foreach ( $locales as $locale ) :
                    foreach ( $locale as $locale_code => $locale_name ) :
                        $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="'.  esc_attr( $locale_code ) . '">' .  esc_html( $locale_name ) . '</label>';
                    endforeach;
                endforeach;

                $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="">' . __( 'not important', 'pdcrequest' ) . '</label>';
                
            else :
                //no locale for chosen lang
                $html = '';
            endif;

            echo $html;
            exit;
        else :
            
            //wrong language code
            write_log( 'profile wrong language code: ' . esc_html($_POST['lang']));
        
            echo '';
            exit;
        endif;
    }
    
    
    /**
     * create form fieldset for translators to precise their locales
     * @return string HTML fieldset with radio input with locales
     */
    static function locales_form( $user_id = null ) {
        if ( ! absint( $user_id ) ) :
            $user = wp_get_current_user();
            $user_id = $user->ID;
        endif;
        $user_locale = get_user_meta($user_id, 'setlr-locale', true );
        $user_lang = get_user_meta($user_id, 'nativelang', true );
        
        $html = '';
        if ( $user_locale && $user_lang ) :
            //get locales
            $locales = self::get_locales_for_language($user_lang );
            
            if ( ! empty( $locales ) ) :
                
                //populate fieldset with radio input
                $html =  '<fieldset class="setlr-locales-form"><legend>' . __( 'Please choose a locale', 'pdcrequest') . '</legend>';

                foreach ( $locales as $locale ) :
                    foreach ( $locale as $locale_code => $locale_name ) :
                        $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="'.  esc_attr( $locale_code ) . '" ' . checked( $locale_code, $user_locale, false ) . '>' .  esc_html( $locale_name ) . '</label>';
                    endforeach;
                endforeach;
               
                
                $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="">' . __( 'not important', 'pdcrequest' ) . '</label>';
                $html .= '</fieldset>';
            else :
                //no locale for chosen lang
                $html = '';
            endif;
        endif;
        
        return $html;
    }
    
    public static function request_locales_form( $request_id ) {
        $request_lang = get_post_meta( $request_id, 'requestlang', true );
        write_log( 'request_locales_form for: ' . $request_id );
        write_log( 'request_lang: ' . $request_lang );
        $request_locale = get_post_meta( $request_id, 'setlr-locale', true );
        if ( isset( $request_lang ) && $locales = self::get_locales_for_language($request_lang)) :
            //populate fieldset with radio input
                $html =  '<fieldset class="setlr-locales-form"><legend>' . __( 'Please choose a locale', 'pdcrequest') . '</legend>';

                foreach ( $locales as $locale ) :
                    foreach ( $locale as $locale_code => $locale_name ) :
                        $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="'.  esc_attr( $locale_code ) . '" ' . checked( $locale_code, $request_locale, false ) . '>' .  esc_html( $locale_name ) . '</label>';
                    endforeach;
                endforeach;
               
                
                $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="">' . __( 'not important', 'pdcrequest' ) . '</label>';
        else :    
            $html = '';
        endif;
        
        return $html;
    }
    
    
    static function ajax_request_locales_form() {
        
            $request_lang = ( $_POST['lang'] ) ? esc_attr($_POST['lang']) : '';
            
            if ( isset( $request_lang ) && $locales = self::get_locales_for_language($request_lang)) :
                //populate fieldset with radio input
                    $html =  '<fieldset class="setlr-locales-form"><legend>' . __( 'Please choose a locale', 'pdcrequest') . '</legend>';

                    foreach ( $locales as $locale ) :
                        foreach ( $locale as $locale_code => $locale_name ) :
                            $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="'.  esc_attr( $locale_code ) . '">' .  esc_html( $locale_name ) . '</label>';
                        endforeach;
                    endforeach;


                    $html .= '<label class="setlr-radio"><input type="radio" name="locale" value="">' . __( 'not important', 'pdcrequest' ) . '</label>';
            else :    
                $html = '';
            endif;
        
        //$html = '<p>Got an Answer</p>';
        echo $html;
        exit;
    }
    /**
     * Render form for native language
     * @param int $user_id the user id
     * @return string HTML select nativelang
     */
    static function native_language_form( $user_id ) {
	$html = '';
	$native_lang = get_the_author_meta( 'nativelang', $user_id );
	$languages = self::get_main_languages_list();
        
	if ( $languages ) :
		$html .= '<select name="nativelang" class="setlr-select">';
		
                foreach( $languages as $language ) :
                    foreach ( $language as $lang_code => $lang_name ) :
			$html .= '<option value="' . esc_attr( $lang_code ) . '" ' . selected( $lang_code, $native_lang, false ) . '>' . esc_html( $lang_name ) . '</option>';
                    endforeach;
                endforeach;
		$html .= '</select>';
	endif;
	return $html;
    }
    
    
    /**
     * Render form for known languages
     * @param int $user_id the user id
     * @return string HTML label + input type=checkbox requestlang array
     */
    static function languages_form( $user_id ) {
	$html = '';
	$user_langs = get_the_author_meta( 'requestlang', $user_id );
	$languages = self::get_main_languages_list();
	
        foreach( $languages as $language ) :
            foreach ( $language as $lang_code => $lang_name ) :
		if ( is_array( $user_langs ) ) :
			$checked = ( in_array( $lang_code, $user_langs ) ) ? 'checked="checked"' : '';
		else :
			$checked = ( $lang_code == $user_langs ) ? 'checked="checked"' : '';
		endif;
		$html .= '<label class="setlr-checkbox"><input type="checkbox" name="requestlang[]" value="' . esc_attr( $lang_code ) . '" ' . $checked . '>' .esc_html( $lang_name ) . '</label>';
            endforeach;
        endforeach;
	
	return $html;
    }
    
    
    /**
     * save the locale as request's post_meta
     * @param int $request_id request id
     * @param string language code
     * @param string $locale language locale
     * @return Void
     */
    static function save_request_locale( $request_id, $lang_code, $locale ) {
        if ( self::validate_locale( $lang_code, $locale ) ) : 
            $update = update_post_meta( $request_id, 'setlr-locale', $locale );
        else :
            write_log( 'locale not validated: ' . $locale );
        endif; 
             
    }
    
    
    /**
     * determines if locale is a true locale of requestlang
     * @param string $locale
     * @return boolean
     */
    static function validate_locale( $lang_code, $locale ) {
        $locales = self::get_locales_for_language( $lang_code );
        
        foreach( array_values( $locales ) as $key => $values ):
            
            if (array_key_exists($locale, $values)) :
                return true;
            endif;
        endforeach;
        return false;
    }
    
    
    /**
     * list languages in their own language
     * @return string the language name in its own language
     */
    public static function list_langs() {
        if ( function_exists( 'qtranxf_default_language_name') ) :
            $lang_array = qtranxf_default_language_name();
        else :
            $lang_array = array(
                'en'    => 'English',
                'fr'    => 'français',
                'de'    => 'Deutsch',
                'it'    => 'Italiano',
                'es'    => 'Español'
            );
        endif;
        
        return $lang_array;
    }
    
    
}



