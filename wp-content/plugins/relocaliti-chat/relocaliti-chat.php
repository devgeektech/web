<?php
/*
Plugin Name: Relocaliti Chat
Plugin URI: http://ispectors.com/plugin/relocaliti-chat
Description: Simple chat system between registered users
Version: 1.0
Author: p2chabot
Author URI: http://ispectors.com/philippe-de-chabot
Text Domain: relochat
Domain Path: /lang/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class ReloChat {
	
	/**
	 * @var ReloChat
	 * @since 1.0
	 */
	private static $instance;
	
	
	/**
	 * Main ReloChat Instance
	 *
	 * Insures that only one instance of ReloChat exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @return The ReloChat
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ReloChat ) ) {
			self::$instance = new ReloChat;
			self::$instance->setup_constants();
			self::$instance->install_db();
			//self::$instance->includes();

			register_activation_hook( __FILE__, 'relo_chat_activation' );
			add_action( 'plugins_loaded', array( self::$instance, 'load_lang' ) );
			add_action( 'init', array( self::$instance, 'init' ), 5 );
			//add_action( 'admin_init', array( self::$instance, 'admin_init' ), 5 );
			
		}

		return self::$instance;
	}


	public function init() {
		add_action( 'wp_ajax_relochat_get_updated_conversation', array( $this, 'get_updated_conversation' ) );
		add_action( 'wp_ajax_nopriv_relochat_get_updated_conversation', array( $this, 'get_updated_conversation' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_relochat-new', array( $this, 'ajax_verify_form' ) );
		add_action( 'wp_ajax_nopriv_relochat-new', array( $this, 'ajax_verify_form' ) );
		add_action( 'admin_action_relochat-new', array( $this, 'verify_form' ) );
	}
	
	
	public function enqueue_scripts() {
		if ( is_page( 'job-dashboard' ) ) :
			wp_enqueue_script( 'relochat', plugins_url( 'js/front.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_localize_script( 'relochat', 'relochat', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
			wp_enqueue_style( 'relochat', plugins_url( 'css/front.css', __FILE__ ), '1.0' );
		endif;
	}
	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 2.7
	 * @return void
	 */
	private function setup_constants() {
		global $wpdb;

		// Plugin version
		if ( ! defined( 'RC_PLUGIN_VERSION' ) )
			define( 'RC_PLUGIN_VERSION', '1.0' );

		// Plugin Folder Path
		if ( ! defined( 'RC_PLUGIN_DIR' ) )
			define( 'RC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Folder URL
		if ( ! defined( 'RC_PLUGIN_URL' ) )
			define( 'RC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Root File
		if ( ! defined( 'RC_PLUGIN_FILE' ) )
			define( 'RC_PLUGIN_FILE', __FILE__ );

		// Objects table name
		if ( ! defined( 'RC_TABLE_NAME') )
			define( 'RC_TABLE_NAME', $wpdb->prefix . 'relo_chat' );

	}
	
	
	private function install_db() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'relo_chat';
		$check_table = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
		if ( function_exists( 'write_log' ) ) write_log( 'relochat install db' );
	if ( $check_table != $table_name ) :
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(7) NOT NULL AUTO_INCREMENT, 
			time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
			text text NOT NULL, 
			from_id bigint(11) NOT NULL,
			to_id bigint(11) NOT NULL,
			PRIMARY KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		if ( function_exists( 'write_log' ) ) write_log( 'relochat db installed' );
	endif;
	}
	
	
		/**
	 * Load our language files
	 * 
	 * @access public
	 * @since 2.7
	 * @return void
	 */
	public function load_lang() {
		/** Set our unique textdomain string */
		$textdomain = 'relochat';

		/** The 'plugin_locale' filter is also used by default in load_plugin_textdomain() */
		$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

		/** Set filter for WordPress languages directory */
		$wp_lang_dir = apply_filters(
			'relochat_wp_lang_dir',
			WP_LANG_DIR . '/relochat/' . $textdomain . '-' . $locale . '.mo'
		);

		/** Translations: First, look in WordPress' "languages" folder = custom & update-secure! */
		load_textdomain( $textdomain, $wp_lang_dir );

		/** Translations: Secondly, look in plugin's "lang" folder = default */
		$plugin_dir = basename( dirname( __FILE__ ) );
		$lang_dir = apply_filters( 'relochat_lang_dir', $plugin_dir . '/lang/' );
		load_plugin_textdomain( $textdomain, FALSE, $lang_dir );
	}


	private function add_conversation( $from, $to, $text ) {
		global $wpdb;
			
			if ( $from == $to ) return false;
			//new conversation
			$insert = $wpdb->insert( 
								RC_TABLE_NAME, 
								array( 
									'text'		=> $text,
									'from_id'	=> $from,
									'to_id'		=> $to
									),
								array( '%s', '%d', '%d' )
								);
								
			return $wpdb->insert_id;
	}
	
	
	public function get_conversation( $user1_id, $user2_id, $since = null ) {
		global $wpdb;
		
		$table = $wpdb->prefix . 'relo_chat';
		if ( !is_null( $since ) && !empty( $since ) && absint( $since ) ) :
			
			//SELECT time, text, from_id, to_id FROM wp_relo_chat WHERE `time` >= STR_TO_DATE('2014-12-29 15:47:16', '%Y-%m-%d %T') ORDER BY time ASC
			$sql = "SELECT time, text, from_id, to_id FROM $table WHERE ((from_id = $user1_id AND to_id = $user2_id) OR (from_id = $user2_id AND to_id = $user1_id)) AND time > STR_TO_DATE('$since', '%Y-%m-%d %T') ORDER BY time ASC";
					
			//$sql2 = $wpdb->prepare( "SELECT * FROM $table WHERE time > STR_TO_DATE( %s, '%Y-%m-%d %T')", $since );
			
			$results = $wpdb->get_results( $sql );
			
		else:
			
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT time, text, from_id, to_id FROM $table WHERE (from_id = %d AND to_id = %d) OR (from_id = %d AND to_id = %d) ORDER BY time ASC", $user1_id, $user2_id, $user2_id, $user1_id ) );
		endif;

		return $results;
	}
		
	
	public function get_updated_conversation() {
		global $current_user;
		
		$user1_id = ( isset( $_POST['user1_id'] ) ) ? absint( $_POST['user1_id'] ) : null;
		$user2_id = ( isset( $_POST['user2_id'] ) ) ? absint( $_POST['user2_id'] ) : null;
		//convert jQuery now in microseconds to PHP timestamp in seconds
		
		$since = ( isset( $_POST['since'] ) ) ? (string) $_POST['since'] : null;
		
		$conversations = $this->get_conversation( $user1_id, $user2_id, $since );
		
		$output = '';
		if ( $conversations ) :
			
			foreach( $conversations as $conversation ) :
				if ( $conversation->from_id == $current_user->ID ) :
					$output .= '<li class="relochat-you">';
				else :
					$output .= '<li class="relochat-notyou">';
				endif;
				$output .= esc_html( $conversation->text );
				$output .= '<span class="relochat-time">' . $conversation->time . '</span>';
				$output .= '</li>';
			endforeach;
		else :
			$output = '';
		endif;
		
		echo $output;
		exit;
	}
	
	
	public function ajax_verify_form() {
		echo $this->verify_form();
		exit;
	}
	
	public function verify_form() {
		//unserialize data message=one+more+test&user1=4&user2=3&relochatnonce=4ac28d7882&_wp_http_referer=%2Frelocaliti%2Fwp-admin%2Fadmin-ajax.php&action=relochat-new
		$data = array();
		parse_str($_POST['data'], $data);
		
		
		$nonce = $data['relochatnonce'];
		//verify nonce
		if ( ! wp_verify_nonce( $nonce, 'relochat-message-new' ) ) :
			write_log( 'pb nonce' );
			die( 'Security check' ); 
		endif;
		
		//verify required values
		$user1_id = ( $data['user1'] ) ? absint( $data['user1'] ) : null;
		$user2_id = ( $data['user2'] ) ? absint( $data['user2'] ) : null;
		//keep $text raw for db as wpdb insert will escape it
		$text = ( $data['message'] ) ? $data['message'] : null;
		
		if ( ! is_null( $user1_id ) && ! is_null( $user2_id ) && ! is_null( $text ) ) :
			$chat_id = $this->add_conversation( $user1_id, $user2_id, $text );
			
			if ( $chat_id > 0 ) :
				$html = '<li class="relochat-you">';
				$html .= esc_html( $text );
				$html .= '<span class="relochat-time">' . time() . '</span>';
				$html .= '</li>';
			else :
				$html .= '<li>db pb</li>';
			endif;
			return $html;
		else :
			write_log( 'is null test failed' );
			return __( 'error: relo_chat', 'relochat' );
		endif;
	}

}

function relochat_render_conversation( $user1_id, $user2_id ) {
	global $current_user;
	$relochat = new ReloChat();
	$user2 = get_user_by( 'id', $user2_id );
	$conversations = $relochat->get_conversation( $user1_id, $user2_id );
	
	$output = '<div class="relochat">';
	$output .= '<h2 class="relochat-title">' . sprintf( __( 'Conversation with %s', 'relochat' ), $user2->display_name ) . '</h2>';
	
	if ( $conversations ) :		
		$output .= '<ul class="relochat-conversation">';
		foreach ( $conversations as $conversation ) :
			if ( $conversation->from_id == $current_user->ID ) :
				$output .= '<li class="relochat-you">';
			else :
				$output .= '<li class="relochat-notyou">';
			endif;
			$output .= esc_html( $conversation->text );
			$output .= '<span class="relochat-time">' . $conversation->time . '</span>';
			$output .= '</li>';
		endforeach;
		$output .= '</ul>';
		
	else :
		$output = '<ul class="relochat-conversation relochat-empty">';
		$output .= '<li class="relochat-neutral">' . sprintf( __( 'Start a conversation with %s', 'relochat' ), $user2->display_name ) . '</li>';
		$output .= '</ul>';
	endif;
		$output .= relochat_render_conversation_form( $user1_id, $user2_id );
		$output .= '</div>';
		echo $output;
		exit;
}

function relochat_render_conversation_form( $user1_id, $user2_id ) {
	$output  = '<form class="relochat-form" method="post" action="' . admin_url( 'admin.php' ) . '">';
	$output .= '<label for="message">' . __( 'your message', 'relochat' ) . '</label>';
	$output .= '<textarea id="message" name="message"></textarea>';
	$output .= '<input type="hidden" id="user1" name="user1" value="' . absint( $user1_id ) . '">';
	$output .= '<input type="hidden" id="user2" name="user2" value="' . absint( $user2_id ) . '">';
	$output .= '<p class="submit"><input type="submit" class="button-secondary" value="' . __( 'Submit', 'relochat' ) . '">' . '</p>';
	$output .= wp_nonce_field( 'relochat-message-new', 'relochatnonce', true, false );
	$output .= '<input type="hidden" name="action" value="relochat-new">';
	$output .= '</form>';
	
	return $output;
}


function relochat_render_all( $user1_id, $user2_id ) {
	relochat_render_conversation( $user1_id, $user2_id );
}


/**
 * The main function responsible for returning The Highlander Ninja_Forms
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $nf = Ninja_Forms(); ?>
 *
 * @since 2.7
 * @return object The Highlander Ninja_Forms Instance
 */
function Relochat() {
	return ReloChat::instance();
}

Relochat();