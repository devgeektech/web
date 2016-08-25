<?php
/**
 * WP_Job_Manager_Geocode
 *
 * Obtains Geolocation data for posted jobs from Google.
 */
class Geocode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'pdcgeocode_update_location_data', array( $this, 'update_location_data' ), 20, 2 );
		add_action( 'pdcgeocode_location_edited', array( $this, 'change_location_data' ), 20, 2 );
		
		add_action( 'admin_action_pdc_update_profile', array( $this, 'update_location_data' ), 20, 2 );
		add_action( 'wp_ajax_pdc_update_profile', array( $this, 'update_location_data' ), 20, 2 );
		add_action( 'wp_ajax_nopriv_pdc_update_profile', array( $this, 'update_location_data' ), 20, 2 );
	}

	/**
	 * Update location data - when adding profile address
	 */
	public function update_location_data( $user_id, $values ) {
		$location = ( $values['billing_address_1'] ) ? $values['billing_address_1'] : '';
		$location .= ( $values['billing_postcode'] ) ? $values['billing_postcode'] : '';
		$location .= ( $values['billing_city'] ) ? $values['billing_city'] : '';
		$location .= ( $values['billing_country'] ) ? $values['billing_country'] : '';

		if ( ! empty( $location ) ) :
			$address_data = self::get_location_data( $location );
			self::save_location_data( $user_id, $address_data );
		endif;
		
	}

	/**
	 * Change user location data upon editing
	 * @param  int $user_id
	 * @param  string $new_location
	 */
	public function change_location_data( $user_id, $new_location ) {
		
			$address_data = self::get_location_data( $new_location );
			self::clear_location_data( $user_id );
			self::save_location_data( $user_id, $address_data );
		
	}

	/**
	 * Checks if a job has location data or not
	 * @param  int  $user_id
	 * @return boolean
	 */
	public static function has_location_data( $user_id ) {
		return get_post_meta( $user_id, 'geolocated', true ) == 1;
	}

	/**
	 * Called manually to generate location data and save to a user
	 * @param  int $user_id
	 * @param  string $location
	 */
	public static function generate_location_data( $user_id, $location ) {
		$address_data = self::get_location_data( $location );
		self::save_location_data( $user_id, $address_data );
	}

	/**
	 * Delete a job's location data
	 * @param  int $user_id
	 */
	public static function clear_location_data( $user_id ) {
		delete_user_meta( $user_id, 'geolocated' );
		delete_user_meta( $user_id, 'geolocation_lat' );
		delete_user_meta( $user_id, 'geolocation_long' );
		delete_user_meta( $user_id, 'geolocation_address' );
	}

	/**
	 * Save any returned data to user meta
	 * @param  int $user_id
	 * @param  array $address_data
	 */
	public static function save_location_data( $user_id, $address_data ) {
		if ( ! is_wp_error( $address_data ) && $address_data ) {
			foreach ( $address_data as $key => $value ) {
				if ( $value ) {
					update_user_meta( $user_id, 'geolocation_' . $key, $value );
				}
			}
			update_user_meta( $user_id, 'geolocated', 1 );
		}
	}

	/**
	 * Get Location Data from Google
	 *
	 * Based on code by Eyal Fitoussi.
	 *
	 * @param string $raw_address
	 * @return array location data
	 */
	public static function get_location_data( $raw_address ) {
		

		$formated_address = self::format_address_for_geolocation( $raw_address );
		if ( empty( $formated_address ) ) {
			return false;
		}

		$transient_name              = 'geocode_' . md5( $formated_address );
		$geocoded_address            = get_transient( $transient_name );
		$jm_geocode_over_query_limit = get_transient( 'geocode_over_query_limit' );

		// Query limit reached - don't geocode for a while
		if ( $jm_geocode_over_query_limit && false === $geocoded_address ) {
			return false;
		}

		try {
			if ( false === $geocoded_address || empty( $geocoded_address->results[0] ) ) {
				$result = wp_remote_get(
					apply_filters( 'pdcgeocode_geolocation_endpoint', "http://maps.googleapis.com/maps/api/geocode/json?address=" . $formated_address . "&sensor=false&region=" . apply_filters( 'pdcgeocode_geolocation_region_cctld', '', $formated_address ), $formated_address ),
					array(
						'timeout'     => 5,
					    'redirection' => 1,
					    'httpversion' => '1.1',
					    'user-agent'  => 'WordPress/Pdc-Geocode-' . PDC_GEOCODE_VERSION . '; ' . get_bloginfo( 'url' ),
					    'sslverify'   => false
				    )
				);
				$result           = wp_remote_retrieve_body( $result );
				$geocoded_address = json_decode( $result );

				if ( $geocoded_address->status ) {
					switch ( $geocoded_address->status ) {
						case 'ZERO_RESULTS' :
							throw new Exception( __( "No results found", 'wp-job-manager' ) );
						break;
						case 'OVER_QUERY_LIMIT' :
							set_transient( 'geocode_over_query_limit', 1, HOUR_IN_SECONDS );
							throw new Exception( __( "Query limit reached", 'pdcgeocode' ) );
						break;
						case 'OK' :
							if ( ! empty( $geocoded_address->results[0] ) ) {
								set_transient( $transient_name, $geocoded_address, 24 * HOUR_IN_SECONDS * 365 );
							} else {
								throw new Exception( __( "Geocoding error", 'pdcgeocode' ) );
							}
						break;
						default :
							throw new Exception( __( "Geocoding error", 'pdcgeocode' ) );
						break;
					}
				} else {
					throw new Exception( __( "Geocoding error", 'pdcgeocode' ) );
				}
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'error', $e->getMessage() );
		}

		$address                      = array();
		$address['lat']               = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lat );
		$address['long']              = sanitize_text_field( $geocoded_address->results[0]->geometry->location->lng );
		$address['formatted_address'] = sanitize_text_field( $geocoded_address->results[0]->formatted_address );

		return $address;
	}
	
	
	/**
	 * Format address for Google Geocoding API
	 *
	 * Based on code by Eyal Fitoussi.
	 *
	 * @param string $raw_address
	 * @return string $formated_address
	 */

	public static function format_address_for_geolocation( $raw_address ) {
		$invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "" );
		$formated_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );
		
		return $formated_address;
	}
}

new Geocode();