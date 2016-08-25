<?php

/** class to show languages pairs
 *
 * shortcode: [pdcrequest_all_lang_pairs]
 */

class langpairs {
	
	/** render lang_pairs as html unordered list
	 * 
	 * @return Void
	 */
	public static function get_langpairs() {
		
		if ( false === ( $langpairs = get_transient( 'setlr_langpairs' ) ) ) :
			
     		// this code runs when there is no valid transient set
			$langpairs = self::store_langpairs();
		endif;	
		
		
		$html = '<ul class="langpairs">';
		foreach ( $langpairs as $langpair ) :
			//var_dump( $langpair );
			$html .= $langpair;
		endforeach;
		$html .= '</ul>';
                
                return $html;
	}
	
	
	/** set transient
	 *
	 * @uses transient_api
	 *
	 * @return array
	 */
	public static function store_langpairs() {
		global $wpdb;
		/* loop through all user meta and make a lang pair for each langs spoken to main lang */
		
		// nativelang
		
		// requestlang
		
		$args = array(
			'fields'	=> 'ID',
			'meta_key'	=> 'main_role',
			'meta_value'	=> 'helper',
			'meta_compare'	=> '='
		);
		$users = get_users( $args );
		
		//print_r( $users );
		$langpairs = array();
		foreach ( $users as $user ) :
			$tolang = get_user_meta( $user, 'nativelang', true );//en
			/* get local lang name */
			$tolang = self::get_lang_local_name( $tolang );
			
			$fromlang = get_user_meta( $user, 'requestlang', false );
			
			
			foreach ( $fromlang as $lang ) :
				foreach( $lang as $item ) :
					$langpair = $item . '-' . $tolang;
					if ( ! in_array( $langpair, $langpairs ) ) :
						$langpairs[] = $langpair;
					endif;
				endforeach;
			endforeach;
		endforeach;
		asort( $langpairs );
		
		foreach ( $langpairs as $langpair ) :
			$lang = explode( '-', $langpair );
			$langs[] = '<li class="langpair"><span class="' . esc_attr( self::get_lang_code( $lang[0] ) ) . '">' . $lang[0] . '</span><span class="' . esc_attr( self::get_lang_code( $lang[1] ) ) . '">' . $lang[1] . '</span></li>';
		endforeach;
		set_transient( 'setlr_langpairs', $langs, 1 * WEEK_IN_SECONDS );
		return $langpairs;
	}
	
	
	/** get local lang name from given lang code
	 *
	 * @param string $lang_code
	 * @return string
	 */
	public static function get_lang_local_name( $lang_code ) {
            
                $main_lang = strlen( $lang_code );
            
		switch ( $main_lang ) :
			case 'ar' :
				$lang = "العربية";
				break;
			case 'de' :
				$lang = 'Deutsch';
			case 'en' :
				$lang = 'English';
				break;
			case 'es' :
				$lang = '';
				break;
			case 'fr' :
				$lang = 'français';
				break;
			case 'it' :
				$lang = 'Italiano';
				break;
			case 'pt' :
				$lang = 'Português';
				break;
			case 'ru' :
				$lang = 'Русский';
				break;
		endswitch;
		
		return $lang;
	}
	
	
	/** get lang code from given lang name
	 *
	 * @param string $lang_name
	 * @return string
	 */
	public static function get_lang_code( $lang_name ) {
		switch ( $lang_name ) :
			case 'English' :
				$lang = 'en';
				break;
			case 'français' :
				$lang = 'fr';
				break;
			case 'Português' :
				$lang = 'pt';
				break;
			case 'Deutsch' :
				$lang = 'de';
				break;
			case 'Español' :
				$lang = 'es';
				break;
			case 'Italiano' :
				$lang = 'it';
				break;
			case 'Русский' :
				$lang = 'ru';
				break;
			case "العربية" :
				$lang = 'ar';
				break;
		endswitch;
		
		return $lang;
	}
        
        
        public static function get_lang_pair_for_request( $request_id ) {
            $from = (get_post_meta( $request_id, 'doclang', true )) ? get_post_meta( $request_id, 'doclang', true ) : get_post_meta( $request_id, 'from-lang', true );
            $to = (get_post_meta( $request_id, 'requestlang', true )) ? get_post_meta( $request_id, 'requestlang', true ) : get_post_meta( $request_id, 'to-lang', true );
            $to_locale = get_post_meta( $request_id, 'setlr-locale', true );
            $question_lang = get_post_meta( $request_id, 'questionlang', true );
            
            if ( $question_lang ) :
                //write_log( 'get_lang_pair_for_request=' . $question_lang );
                return $question_lang;
            else :
                
            
                if ( $to_locale ) :
                    return array( 'from_lang' => $from, 'to_lang' => $to_locale );
                endif;
            
                return array( 'from_lang' => $from, 'to_lang' => $to);
            endif;
        }
        
        
        public static function render_lang_pair_for_request( $request_id, $separator = ' --> ' ) {
            $lang_pair = self::get_lang_pair_for_request($request_id);
            
            //write_log( $lang_pair);
            $html = '';
            if ( ! is_array( $lang_pair) ) :
                
                $html .= '<span class="to-lang lang-'. esc_attr( $lang_pair ) . '">' . esc_html( $lang_pair ) . '</span>';
            else :
                if ( empty( $lang_pair['from_lang'] ) && empty($lang_pair['to_lang']) ) :
                    $html = '';
                else :
                    $html = '<span class="from-lang lang-'. esc_attr( $lang_pair['from_lang'] ) . '">' . esc_html( $lang_pair['from_lang'] ) . '</span>';
                    $html .= '<span class="setlr-lang-separator">' . esc_html( $separator ) . '</span>';
                    $html .= '<span class="to-lang lang-'. esc_attr( $lang_pair['to_lang'] ) . '">' . esc_html( $lang_pair['to_lang'] ) . '</span>';
                endif;
            endif;
            
            return $html;
        }
}