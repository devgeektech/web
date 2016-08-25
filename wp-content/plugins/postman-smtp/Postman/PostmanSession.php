<?php
class PostmanNetworkSession extends PostmanAbstractSession {
	
	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanNetworkSession ();
			$inst->logger = new PostmanLogger ( get_class ( $inst ) );
		}
		return $inst;
	}
	protected function get($key) {
		return get_site_transient ( $key );
	}
	protected function set($key, $value, $expiry) {
		return set_site_transient ( $key, $value, $expiry );
	}
	protected function delete($key) {
		return delete_site_transient ( $key );
	}
}

class PostmanSession extends PostmanAbstractSession {
	
	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanSession ();
			$inst->logger = new PostmanLogger ( get_class ( $inst ) );
		}
		return $inst;
	}
		protected function get($key) {
		return get_site_transient ( $key );
	}
	protected function set($key, $value, $expiry) {
		return set_site_transient ( $key, $value, $expiry );
	}
	protected function delete($key) {
		return delete_site_transient ( $key );
	}
}

/**
 * Persist session state to the database
 *
 * I heard plugins are forbidden from writing to the http session
 * on some hosts, such as WPEngine, so this class write session
 * state to the database instead.
 *
 * What's better about this is I don't have to prefix all my
 * variables with , in fear of colliding with another
 * plugin's similiarily named variables.
 *
 * @author jasonhendriks
 *        
 */
abstract class PostmanAbstractSession {
	// length of time to keep items around
	const MINUTES_IN_SECONDS = 60;
	
	//
	const OAUTH_IN_PROGRESS = 'oauth_in_progress';
	const ACTION = 'action';
	const ERROR_MESSAGE = 'error_message';
	protected $logger;
	
	protected function get($key) {
		return get_transient ( $key );
	}
	protected function set($key, $value, $expiry) {
		return set_transient ( $key, $value, $expiry );
	}
	protected function delete($key) {
		return delete_transient ( $key );
	}
	
	/**
	 * OAuth is in progress $state is the randomly generated
	 * transaction ID
	 *
	 * @param unknown $state        	
	 */
	public function isSetOauthInProgress() {
		return $this->get ( self::OAUTH_IN_PROGRESS ) != false;
	}
	public function setOauthInProgress($state) {
		$this->set ( self::OAUTH_IN_PROGRESS, $state, 3 * self::MINUTES_IN_SECONDS );
	}
	public function getOauthInProgress() {
		return $this->get ( self::OAUTH_IN_PROGRESS );
	}
	public function unsetOauthInProgress() {
		$this->delete ( self::OAUTH_IN_PROGRESS );
	}
	
	/**
	 * Sometimes I need to keep track of what I'm doing between requests
	 *
	 * @param unknown $action        	
	 */
	public function isSetAction() {
		return $this->get ( self::ACTION ) != false;
	}
	public function setAction($action) {
		$this->set ( self::ACTION, $action, 30 * self::MINUTES_IN_SECONDS );
	}
	public function getAction() {
		return $this->get ( self::ACTION );
	}
	public function unsetAction() {
		$this->delete ( self::ACTION );
	}
	
	/**
	 * Sometimes I need to keep track of what I'm doing between requests
	 *
	 * @param unknown $message        	
	 */
	public function isSetErrorMessage() {
		return $this->get ( self::ERROR_MESSAGE ) != false;
	}
	public function setMessage($message) {
		$this->set ( self::ERROR_MESSAGE, $message, 30 * self::MINUTES_IN_SECONDS );
	}
	public function getMessage() {
		return $this->get ( self::ERROR_MESSAGE );
	}
	public function unsetMessage() {
		$this->delete ( self::ERROR_MESSAGE );
	}
}
