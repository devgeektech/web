<?php
/*
Plugin Name: PdcGeocode
Plugin URI: http://ispectors.com.com/plugins/pdcgeocode/
Description: Show Google map of helpers
Version: 0.3.0
Author: pdc
Author URI: http://ispectors.com/philippe-de-chabot/
Text Domain: pdcgeocode
Domain Path: /languages

*/

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	_e( "Hi there!  I'm just a plugin, not much I can do when called directly.", 'auctionmgt' );
	exit;
}

define( 'GOOGLE_API_KEY', 'AIzaSyA84Xesk30tooenzbBZGrO6NnZuE7nC_Ow' );
define( 'PDC_GEOCODE_VERSION', '0.3.0' );



if ( ! class_exists( "Pdc_Geocode" ) ) :

	class Pdc_Geocode {
		var $addpage;
		var $lang;

		public function __construct() {
			
			$this->auctionmgt_db_version = "1.0";
			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'init_admin' ) );
                        
			require 'includes/classes/map-widget.php';
			//add_action( 'widgets_init', array( $this, 'init_widgets' ) );
			add_action( 'widgets_init', function(){
                                register_widget( 'PdcGeocode_Map_Widget' );
                                write_log( 'register_map_widget');
                                
                           });
			


			register_activation_hook( __FILE__, array( &$this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		}

		/** activate()
		 * activates the plugin for each blog on a multisite installation or single installation
		 * @return void
		 */
		function activate() {
			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// check if it is a network activation - if so, run the activation function for each blog id
				if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
					$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->_activate();
					}
					switch_to_blog( $old_blog );
					return;
				}
			}
			$this->_activate();
		}

		/** deactivate()
		 * deactivates the plugin for each blog on a multisite installation or single installation
		 * @return void
		 */
		function deactivate() {
			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// check if it is a network activation - if so, run the activation function for each blog id
				if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
					$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->_deactivate();
					}
					switch_to_blog( $old_blog );
					return;
				}
			}
			$this->_deactivate();
		}

		/** _activates()
		 * runs database creation script
		 * runs install function
		 * schedule daily event
		 * @return void
		 */
		function _activate() {
			//UNIX timestamp format. WP cron uses UTC/GMT time uses time() 
		}

		/** _deactivates()
		 * clear scheduled daily event
		 * @return void
		 */
		function _deactivate() {
		}

		function admin_enqueue_scripts() {
			/* plugin options page settings */

		}


		/** init()
		 * sets up plugin textdomain for internationalization and localization
		 * @return void
		 */
		function init() {
			global $wpdb;
			load_plugin_textdomain( 'pdcgeocode', WP_PLUGIN_DIR . '/pdcgeocode/languages', basename( dirname( __FILE__ ) ) . '/languages/' );

			require_once( WP_PLUGIN_DIR . '/pdcgeocode/includes/classes/class-geocode.php' );
			
			include_once( WP_PLUGIN_DIR . '/pdcgeocode/includes/public/shortcodes.php' );
			include_once( WP_PLUGIN_DIR . '/pdcgeocode/includes/public/functions.php' );
			
		
			/* ajax actions */
			//add_action( 'wp_ajax_auctionmgt_verify_contact', array( $this, 'verify_contact' ) );
			//add_action( 'wp_ajax_nopriv_auctionmgt_verify_contact', array( $this, 'verify_contact' ) );
			/* actions */
			//add_action( 'admin_action_import_xml_file', array( 'auctionmgt_csv', 'parse_xml' ) );
			add_action( 'admin_action_pdcgeocode_update_users', array( $this, 'geotag_all_users' ) );
			add_action( 'wp_ajax_get_users_for_map', array( $this, 'render_map_users' ), 10, 1 );
			add_action( 'wp_ajax_nopriv_get_users_for_map', array( $this, 'render_map_users' ), 10, 1 );
			
			//add_action( 'wp_enqueue_scripts', array( $this, 'register_front_styles' ) );
			
			//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_styles' ), 20 );
			
                        
			/* recurring tasks */


			/* filters */

			
			/* shortcodes */
			add_shortcode( 'setlr_map', array( $this, 'register_shortcode' ) );
                        
			//add_action( 'wp', array( $this, 'detect_shortcode' ) );
                        
                        
                        write_log( 'pdcgeocode init');
		}
		
		
		function init_admin() {
			//$this->define_settings();
			//add_action( 'widgets_init', array( $this, 'register_map_widget' ) );
                        //write_log( 'pdcgeocode init');
		}


		public function admin_menu() {
			// This page will be under "Settings"
			
			add_options_page( __( 'PdC Geocode Settings', 'pdcgeocode' ),
					__( 'PdC Geocode Settings', 'pdcgeocode' ),
					'manage_options',
					'pdcgeocode_setting',
					array( $this, 'settings_page' )
			);
		}
			

		public function settings_page() {
			require_once( WP_PLUGIN_DIR . '/pdcgeocode/includes/admin/settings.php' );
			//echo 'auctionmgt setting';
		}

		
		
		public function enqueue_scripts_styles() {
			/* register plugin js scripts */
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'googlemapsapi', 'https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_API_KEY. '&signed_in=true&callback=initMap','', NULL, true );
			wp_enqueue_script( 'pdcgeocode', plugins_url() . '/pdcgeocode/assets/js/pdcgeocode.js', array( 'jquery', 'googlemapsapi' ), '0.3', true );
			wp_localize_script( 'pdcgeocode', 'pdcgeocode', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );	
		}	
		
	// register Foo_Widget widget
        function register_map_widget() {
            register_widget( 'Map_Widget' );
            
        }
	

	public function define_settings() {
        register_setting(
            'geocode_settings_group', // Option group
            'geocode_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        
	}
			
	
	function register_front_styles() {
		// wp_register_style( $handle, $src, $deps, $ver, $media );
		wp_register_style( 'pdcgeocode_style', plugins_url( 'assets/css/style.css', __FILE__ ), array(), NULL, 'screen' );
	}
	
	function enqueue_front_styles() {
		wp_enqueue_style( 'pdcgeocode_style' );		
	}
	
	function init_widgets() {
	}
	
	function geotag_all_users() {
		$args = array( 'fields' => 'ID' );
		$user_ids = get_users( $args );
		foreach( $user_ids as $user_id ) :
			if ( ! get_user_meta( $user_id, 'geolocated', true ) ) :
				// we have not geotagged this user
				$address1 = get_user_meta( $user_id, 'billing_address_1', true );
				$postcode = get_user_meta( $user_id, 'billing_postcode', true );
				$city = get_user_meta( $user_id, 'billing_city', true );
				$country = get_user_meta( $user_id, 'billing_country', true );
				
				$location = ( $address1 ) ? $address1 : '';
				$location .= ( $postcode ) ? $postcode: '';
				$location .= ( $city) ? $city : '';
				$location .= ( $country ) ? $country : '';
				
				
				if ( $location ) :
					$formated_address = Geocode::generate_location_data( $user_id, $location );
					//update_user_meta( $user_id, 'geolocation_address', $formated_address );
				endif;
			endif;
		endforeach;
		
			$redirect_to = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : admin_url( 'options-general.php?page=pdcgeocode_setting' );
			wp_safe_redirect( $redirect_to );
			exit;	
	}
	
	static public function map_users( $zoom_level = null ) {
		$user_array = array();
		$args = array( 
                        'fields' => 'all_with_meta',
                        'meta_query' => array(
                            
                            array(
                                    'key'     => 'main_role',
                                    'value'   => 'helper',
                                    'compare' => '='
                            )
                            
                    ));
		$user_query = new WP_User_Query( $args );
		
		if ( !empty( $user_query->results ) ) :
			// we have users to show, let's loop
			foreach( $user_query->results as $user ) :
				if ( 1 == $user->geolocated ) :
					$user_array[] = array( $user->display_name, $user->geolocation_lat, $user->geolocation_long );
				endif;
			endforeach;
			return $user_array;
		else :
			return false;
		endif;
	}
	
	static public function render_map_users( $zoom_level = null ) {
		wp_send_json( self::map_users( $zoom_level ) );
	}
        
        
        public function register_shortcode( $atts = array() ) {
            
            return '<div id="pdcgeocode_map" class="setlr-map" style="width:100%; height:100%; min-height:600px;"></div>';
        }
	
}
endif;

global $pdcgeocode;
if ( class_exists( "Pdc_Geocode" ) && ! $pdcgeocode ) {
	$pdcgeocode = new Pdc_Geocode();
}	

