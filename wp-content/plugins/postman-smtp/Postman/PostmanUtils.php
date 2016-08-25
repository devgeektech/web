<?php
require_once 'PostmanLogger.php';
require_once 'PostmanState.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanUtils {
	private static $logger;
	private static $emailValidator;
	
	//
	const POSTMAN_SETTINGS_PAGE_STUB = 'postman';
	const REQUEST_OAUTH2_GRANT_SLUG = 'postman/requestOauthGrant';
	const POSTMAN_EMAIL_LOG_PAGE_STUB = 'postman_email_log';
	
	// redirections back to THIS SITE should always be relative because of IIS bug
	const POSTMAN_EMAIL_LOG_PAGE_RELATIVE_URL = 'tools.php?page=postman_email_log';
	
	// custom admin post page
	const ADMIN_POST_OAUTH2_GRANT_URL_PART = 'admin-post.php?action=postman/requestOauthGrant';
	
	//
	const NO_ECHO = false;
	
	/**
	 * Initialize the Logger
	 */
	public static function staticInit() {
		PostmanUtils::$logger = new PostmanLogger ( 'PostmanUtils' );
	}
	
	/**
	 * Returns an escaped URL
	 */
	public static function getGrantOAuthPermissionUrl() {
		return get_admin_url () . self::ADMIN_POST_OAUTH2_GRANT_URL_PART;
	}
	
	/**
	 * Returns an escaped URL
	 */
	public static function getEmailLogPageUrl() {
		$url = apply_filters ( 'postman_get_email_log_url', null );
		return $url;
	}
	
	/**
	 * Returns an escaped URL
	 */
	public static function getSettingsPageUrl() {
		$url = apply_filters ( 'postman_get_home_url', null );
		return $url;
	}
	
	//
	public static function isCurrentPagePostmanAdmin($page = 'postman') {
		$result = (isset ( $_REQUEST ['page'] ) && substr ( $_REQUEST ['page'], 0, strlen ( $page ) ) == $page);
		return $result;
	}
	/**
	 * from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
	 *
	 * @param unknown $haystack        	
	 * @param unknown $needle        	
	 * @return boolean
	 */
	public static function startsWith($haystack, $needle) {
		$length = strlen ( $needle );
		return (substr ( $haystack, 0, $length ) === $needle);
	}
	/**
	 * from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
	 *
	 * @param unknown $haystack        	
	 * @param unknown $needle        	
	 * @return boolean
	 */
	public static function endsWith($haystack, $needle) {
		$length = strlen ( $needle );
		if ($length == 0) {
			return true;
		}
		return (substr ( $haystack, - $length ) === $needle);
	}
	public static function obfuscatePassword($password) {
		return str_repeat ( '*', strlen ( $password ) );
	}
	/**
	 * Detect if the host is NOT a domain name
	 *
	 * @param unknown $ipAddress        	
	 * @return number
	 */
	public static function isHostAddressNotADomainName($host) {
		// IPv4 / IPv6 test from http://stackoverflow.com/a/17871737/4368109
		$ipv6Detected = preg_match ( '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/', $host );
		$ipv4Detected = preg_match ( '/((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])/', $host );
		return $ipv4Detected || $ipv6Detected;
		// from http://stackoverflow.com/questions/106179/regular-expression-to-match-dns-hostname-or-ip-address
		// return preg_match ( '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9‌​]{2}|2[0-4][0-9]|25[0-5])$/', $ipAddress );
	}
	/**
	 * Makes the outgoing HTTP requests
	 * Inside WordPress we can use wp_remote_post().
	 * Outside WordPress, not so much.
	 *
	 * @param unknown $url        	
	 * @param unknown $args        	
	 * @return the HTML body
	 */
	static function remotePostGetBodyOnly($url, $body, array $headers = array()) {
		$response = PostmanUtils::remotePost ( $url, $body, $headers );
		$theBody = wp_remote_retrieve_body ( $response );
		return $theBody;
	}
	
	/**
	 * Makes the outgoing HTTP requests
	 * Inside WordPress we can use wp_remote_post().
	 * Outside WordPress, not so much.
	 *
	 * @param unknown $url        	
	 * @param unknown $args        	
	 * @return the HTTP response
	 */
	static function remotePost($url, $body = array(), array $headers = array()) {
		$args = array (
				'sslverify' => true,
				'timeout' => PostmanOptions::getInstance ()->getConnectionTimeout (),
				'headers' => $headers,
				'body' => $body 
		);
		if (PostmanUtils::$logger->isTrace ()) {
			PostmanUtils::$logger->trace ( sprintf ( 'Posting to %s', $url ) );
			PostmanUtils::$logger->trace ( 'Post header:' );
			PostmanUtils::$logger->trace ( $headers );
			PostmanUtils::$logger->trace ( 'Posting body:' );
			PostmanUtils::$logger->trace ( $body );
		}
		$response = wp_remote_post ( $url, $args );
		
		// pre-process the response
		if (is_wp_error ( $response )) {
			PostmanUtils::$logger->error ( $response->get_error_message () );
			throw new Exception ( 'Error executing wp_remote_post: ' . $response->get_error_message () );
		} else {
			return $response;
		}
	}
	/**
	 * A facade function that handles redirects.
	 * Inside WordPress we can use wp_redirect(). Outside WordPress, not so much. **Load it before postman-core.php**
	 *
	 * @param unknown $url        	
	 */
	static function redirect($url) {
		// redirections back to THIS SITE should always be relative because of IIS bug
		if (PostmanUtils::$logger->isTrace ()) {
			PostmanUtils::$logger->trace ( sprintf ( "Redirecting to '%s'", $url ) );
		}
		wp_redirect ( $url );
		exit ();
	}
	static function parseBoolean($var) {
		return filter_var ( $var, FILTER_VALIDATE_BOOLEAN );
	}
	static function logMemoryUse($startingMemory, $description) {
		$memoryUse = memory_get_usage () - $startingMemory;
		if ($memoryUse > 0) {
			// this check was for a bug someone reported where memory_get_usage returns 0
			PostmanUtils::$logger->trace ( sprintf ( $description . ' memory used: %s', PostmanUtils::roundBytes ( $memoryUse ) ) );
		}
	}
	
	/**
	 * Rounds the bytes returned from memory_get_usage to smaller amounts used IEC binary prefixes
	 * See http://en.wikipedia.org/wiki/Binary_prefix
	 *
	 * @param unknown $size        	
	 * @return string
	 */
	static function roundBytes($size) {
		assert ( $size > 0 );
		$unit = array (
				'B',
				'KiB',
				'MiB',
				'GiB',
				'TiB',
				'PiB' 
		);
		return @round ( $size / pow ( 1024, ($i = floor ( log ( $size, 1024 ) )) ), 2 ) . ' ' . $unit [$i];
	}
	
	/**
	 * Unblock threads waiting on lock()
	 */
	static function unlock() {
		if (PostmanState::getInstance ()->isFileLockingEnabled ()) {
			PostmanUtils::deleteLockFile ();
		}
	}
	
	/**
	 * Processes will block on this method until unlock() is called
	 * Inspired by http://cubicspot.blogspot.ca/2010/10/forget-flock-and-system-v-semaphores.html
	 *
	 * @throws Exception
	 */
	static function lock() {
		if (PostmanState::getInstance ()->isFileLockingEnabled ()) {
			$attempts = 0;
			while ( true ) {
				// create the semaphore
				$lock = PostmanUtils::createLockFile ();
				if ($lock) {
					// if we got the lock, return
					return;
				} else {
					$attempts ++;
					if ($attempts >= 10) {
						throw new Exception ( sprintf ( 'Could not create lockfile %s', '/tmp' . '/.postman.lock' ) );
					}
					sleep ( 1 );
				}
			}
		}
	}
	static function deleteLockFile($tempDirectory = null) {
		$path = PostmanUtils::calculateTemporaryLockPath ( $tempDirectory );
		$success = @unlink ( $path );
		if (PostmanUtils::$logger->isTrace ()) {
			PostmanUtils::$logger->trace ( sprintf ( 'Deleting file %s : %s', $path, $success ) );
		}
		return $success;
	}
	static function createLockFile($tempDirectory = null) {
		$path = PostmanUtils::calculateTemporaryLockPath ( $tempDirectory );
		$success = @fopen ( $path, 'xb' );
		if (PostmanUtils::$logger->isTrace ()) {
			PostmanUtils::$logger->trace ( sprintf ( 'Creating file %s : %s', $path, $success ) );
		}
		return $success;
	}
	
	/**
	 * Creates the pathname of the lockfile
	 *
	 * @param unknown $tempDirectory        	
	 * @return string
	 */
	private static function calculateTemporaryLockPath($tempDirectory) {
		if (empty ( $tempDirectory )) {
			$options = PostmanOptions::getInstance ();
			$tempDirectory = $options->getTempDirectory ();
		}
		$fullPath = sprintf ( '%s/.postman_%s.lock', $tempDirectory, self::generateUniqueLockKey () );
		return $fullPath;
	}
	
	/**
	 *
	 * @return string
	 */
	private static function generateUniqueLockKey() {
		if (PostmanUtils::isMultisite () && PostmanNetworkOptions::getInstance ()->isSubsiteAccountSettingsUnderNetworkControl ()) {
			// for shared configuration, use the network_site_url to generate the key because it is common
			$key = hash ( 'crc32', network_site_url ( '/' ) );
		} else {
			// for single sites, use the site_url to generate the key because it is unique for all
			$key = hash ( 'crc32', site_url ( '/' ) );
		}
		return $key;
	}
	
	/**
	 * From http://stackoverflow.com/a/381275/4368109
	 *
	 * @param unknown $text        	
	 * @return boolean
	 */
	public static function isEmpty($text) {
		// Function for basic field validation (present and neither empty nor only white space
		return (! isset ( $text ) || trim ( $text ) === '');
	}
	
	/**
	 * Warning! This can only be called on hook 'init' or later
	 */
	public static function isUserAdmin() {
		return current_user_can ( Postman::MANAGE_POSTMAN_CAPABILITY_NAME );
	}
	
	/**
	 * Warning! This can only be called on hook 'init' or later
	 */
	public static function isUserNetworkAdmin() {
		return current_user_can ( 'manage_network' ) && self::isUserAdmin ();
	}
	
	/**
	 * 
	 */
	public static function isPageAdmin() {
		/**
		 * is_admin() will return false when trying to access wp-login.php.
		 * is_admin() will return true when trying to make an ajax request.
		 * is_admin() will return true for calls to load-scripts.php and load-styles.php.
		 * is_admin() is not intended to be used for security checks. It will return true
		 * whenever the current URL is for a page on the admin side of WordPress. It does
		 * not check if the user is logged in, nor if the user even has access to the page
		 * being requested. It is a convenience function for plugins and themes to use for
		 * various purposes, but it is not suitable for validating secured requests.
		 *
		 * Good to know.
		 */
		return is_admin ();
	}
	
	/**
	 * Check if the user can manage Postman's network settings
	 * Warning! This can only be called on hook 'init' or later
	 *
	 * @return boolean
	 */
	public static function isPageNetworkAdmin() {
		return is_network_admin ();
	}
	
	/**
	 * Validate an e-mail address
	 *
	 * @param unknown $email        	
	 * @return number
	 */
	static function validateEmail($email) {
		if (PostmanOptions::getInstance ()->isEmailValidationDisabled ()) {
			return true;
		}
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Exception.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Registry.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/Exception.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/Interface.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/Abstract.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/Ip.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/Hostname.php';
		require_once 'Postman-Mail/Postman-Zend/Zend-1.12.10/Validate/EmailAddress.php';
		if (! isset ( PostmanUtils::$emailValidator )) {
			PostmanUtils::$emailValidator = new Postman_Zend_Validate_EmailAddress ();
		}
		return PostmanUtils::$emailValidator->isValid ( $email );
	}
	
	/**
	 * From http://stackoverflow.com/questions/13430120/str-getcsv-alternative-for-older-php-version-gives-me-an-empty-array-at-the-e
	 *
	 * @param unknown $string        	
	 * @return multitype:
	 */
	static function postman_strgetcsv_impl($string) {
		$fh = fopen ( 'php://temp', 'r+' );
		fwrite ( $fh, $string );
		rewind ( $fh );
		
		$row = fgetcsv ( $fh );
		
		fclose ( $fh );
		return $row;
	}
	
	/**
	 *
	 * @return Ambigous <string, unknown>
	 */
	static function postmanGetServerName() {
		if (! empty ( $_SERVER ['SERVER_NAME'] )) {
			$serverName = $_SERVER ['SERVER_NAME'];
		} else if (! empty ( $_SERVER ['HTTP_HOST'] )) {
			$serverName = $_SERVER ['HTTP_HOST'];
		} else {
			$serverName = 'localhost.localdomain';
		}
		return $serverName;
	}
	
	/**
	 * Does this hostname belong to Google?
	 *
	 * @param unknown $hostname        	
	 * @return boolean
	 */
	static function isGoogle($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'gmail.com' ) || PostmanUtils::endsWith ( $hostname, 'googleapis.com' );
	}
	
	/**
	 *
	 * @param unknown $actionName        	
	 * @param unknown $callbackName        	
	 */
	public static function registerAdminMenu($controller, $function_to_add, $priority = 10, $accepted_args = 1) {
		$logger = PostmanUtils::$logger;
		$hook = 'admin_menu';
		if ($logger->isTrace ()) {
			$logger->trace ( 'Registering admin menu ' . $function_to_add );
		}
		add_action ( $hook, array (
				$controller,
				$function_to_add 
		), $priority, $accepted_args );
	}
	
	/**
	 *
	 * @param unknown $actionName        	
	 * @param unknown $callbackName        	
	 */
	public static function registerNetworkAdminMenu($controller, $function_to_add, $priority = 10, $accepted_args = 1) {
		$logger = PostmanUtils::$logger;
		$hook = 'network_admin_menu';
		if ($logger->isTrace ()) {
			$logger->trace ( 'Registering network admin menu ' . $function_to_add );
		}
		add_action ( $hook, array (
				$controller,
				$function_to_add 
		), $priority, $accepted_args );
	}
	
	/**
	 *
	 * @param unknown $actionName        	
	 * @param unknown $callbackName        	
	 */
	public static function registerAjaxHandler($actionName, $class, $callbackName) {
		if (is_admin ()) {
			$fullname = 'wp_ajax_' . $actionName;
			// $this->logger->debug ( 'Registering ' . 'wp_ajax_' . $fullname . ' Ajax handler' );
			add_action ( $fullname, array (
					$class,
					$callbackName 
			) );
		}
	}
	
	/**
	 *
	 * @param unknown $parameterName        	
	 * @return mixed
	 */
	public static function getBooleanRequestParameter($parameterName) {
		return filter_var ( $this->getRequestParameter ( $parameterName ), FILTER_VALIDATE_BOOLEAN );
	}
	
	/**
	 *
	 * @param unknown $parameterName        	
	 * @return unknown
	 */
	public static function getRequestParameter($parameterName) {
		$logger = PostmanUtils::$logger;
		if (isset ( $_POST [$parameterName] )) {
			$value = $_POST [$parameterName];
			if ($logger->isTrace ()) {
				$logger->trace ( sprintf ( 'Found parameter "%s"', $parameterName ) );
				$logger->trace ( $value );
			}
			return $value;
		}
	}
	
	/**
	 *
	 * @return true if this WordPress installation is in multisite mode
	 */
	public static function isMultisite() {
		return function_exists ( 'is_multisite' ) && is_multisite ();
	}
	
	/**
	 * Registers actions posted by am HTML FORM with the WordPress 'action' parameter
	 *
	 * @param unknown $actionName        	
	 * @param unknown $callbankName        	
	 */
	public static function registerAdminPostAction($actionName, $controller, $callbankName) {
		$logger = PostmanUtils::$logger;
		if ($logger->isTrace ()) {
			$logger->trace ( 'Registering admin post action ' . $actionName );
		}
		add_action ( 'admin_post_' . $actionName, array (
				$controller,
				$callbankName 
		) );
	}
}
PostmanUtils::staticInit ();
