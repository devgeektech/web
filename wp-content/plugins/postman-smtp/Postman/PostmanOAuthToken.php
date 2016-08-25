<?php
abstract class PostmanAbstractOAuthToken {
	const OPTIONS_NAME = 'postman_auth_token';
	//
	const REFRESH_TOKEN = 'refresh_token';
	const EXPIRY_TIME = 'auth_token_expires';
	const ACCESS_TOKEN = 'access_token';
	const VENDOR_NAME = 'vendor_name';
	//
	protected $options;
	
	// private constructor
	protected function __construct() {
		$this->load ();
	}
	
	/**
	 * Is there a valid access token and refresh token
	 */
	public function isValid() {
		$accessToken = $this->getAccessToken ();
		$refreshToken = $this->getRefreshToken ();
		return ! (empty ( $accessToken ) || empty ( $refreshToken ));
	}
	
	/**
	 * Load the Postman OAuth token properties to the database
	 */
	private function load() {
		$this->options = get_option ( PostmanOAuthToken::OPTIONS_NAME );
	}
	
	/**
	 * Save the Postman OAuth token properties to the database
	 */
	public function save() {
		update_option ( PostmanOAuthToken::OPTIONS_NAME, $this->options );
	}
	public function getVendorName() {
		if (isset ( $this->options [PostmanOAuthToken::VENDOR_NAME] ))
			return $this->options [PostmanOAuthToken::VENDOR_NAME];
	}
	public function getExpiryTime() {
		if (isset ( $this->options [PostmanOAuthToken::EXPIRY_TIME] ))
			return $this->options [PostmanOAuthToken::EXPIRY_TIME];
	}
	public function getAccessToken() {
		if (isset ( $this->options [PostmanOAuthToken::ACCESS_TOKEN] ))
			return $this->options [PostmanOAuthToken::ACCESS_TOKEN];
	}
	public function getRefreshToken() {
		if (isset ( $this->options [PostmanOAuthToken::REFRESH_TOKEN] ))
			return $this->options [PostmanOAuthToken::REFRESH_TOKEN];
	}
	public function setVendorName($name) {
		$this->options [PostmanOAuthToken::VENDOR_NAME] = sanitize_text_field ( $name );
	}
	public function setExpiryTime($time) {
		$this->options [PostmanOAuthToken::EXPIRY_TIME] = sanitize_text_field ( $time );
	}
	public function setAccessToken($token) {
		$this->options [PostmanOAuthToken::ACCESS_TOKEN] = sanitize_text_field ( $token );
	}
	public function setRefreshToken($token) {
		$this->options [PostmanOAuthToken::REFRESH_TOKEN] = sanitize_text_field ( $token );
	}
}

/**
 * http://stackoverflow.com/questions/23880928/use-oauth-refresh-token-to-obtain-new-access-token-google-api
 * http://pastebin.com/jA9sBNTk
 *
 * Make sure these emails are permitted (see http://en.wikipedia.org/wiki/E-mail_address#Internationalization):
 */
class PostmanOAuthToken extends PostmanAbstractOAuthToken {
	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanOAuthToken ();
		}
		return $inst;
	}
	//
	public function save() {
		update_option ( PostmanOAuthToken::OPTIONS_NAME, $this->options );
	}
	public function load() {
		$this->options = get_option ( PostmanOAuthToken::OPTIONS_NAME );
	}
}

/**
 * http://stackoverflow.com/questions/23880928/use-oauth-refresh-token-to-obtain-new-access-token-google-api
 * http://pastebin.com/jA9sBNTk
 *
 * Make sure these emails are permitted (see http://en.wikipedia.org/wiki/E-mail_address#Internationalization):
 */
class PostmanNetworkOAuthToken extends PostmanAbstractOAuthToken {
	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanNetworkOAuthToken ();
		}
		return $inst;
	}
	//
	public function save() {
		update_option ( PostmanNetworkOAuthToken::OPTIONS_NAME, $this->options );
	}
	public function load() {
		$this->options = get_site_option ( PostmanNetworkOAuthToken::OPTIONS_NAME );
	}
}
	
	