<?php
require_once ('PostmanSession.php');

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanNetworkMessageHandler extends PostmanAbstractMessageHandler {
	private static $instance;
	
	/**
	 */
	protected function __construct() {
		parent::__construct ( new PostmanLogger ( get_class ( $this ) ) );
		// we'll let the 'init' functions run first; some of them may end the request
		add_action ( 'network_admin_notices', Array (
				$this,
				'displayAllMessages' 
		) );
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'Created PostmanNetworkMessageHandler' );
		}
	}
	
	/**
	 */
	public static function getInstance() {
		if (PostmanNetworkMessageHandler::$instance == null) {
			PostmanNetworkMessageHandler::$instance = new PostmanNetworkMessageHandler ();
		}
		return PostmanNetworkMessageHandler::$instance;
	}
	
	/**
	 */
	protected function getPostmanSession() {
		return PostmanNetworkSession::getInstance ();
	}
}

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanMessageHandler extends PostmanAbstractMessageHandler {
	private static $instance;
	
	/**
	 *
	 * @param unknown $options        	
	 */
	protected function __construct() {
		parent::__construct ( new PostmanLogger ( get_class ( $this ) ) );
		
		// we'll let the 'init' functions run first; some of them may end the request
		add_action ( 'admin_notices', Array (
				$this,
				'displayAllMessages' 
		) );
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'Created PostmanMessageHandler' );
		}
	}
	
	/**
	 */
	public static function getInstance() {
		if (PostmanMessageHandler::$instance == null) {
			PostmanMessageHandler::$instance = new PostmanMessageHandler ();
		}
		return PostmanMessageHandler::$instance;
	}
	
	/**
	 *
	 * @return Ambigous <NULL, PostmanSession>
	 */
	protected function getPostmanSession() {
		return PostmanSession::getInstance ();
	}
}

/**
 *
 * @author jasonhendriks
 *        
 */
abstract class PostmanAbstractMessageHandler {
	
	// The Session variables that carry messages
	const ERROR_CLASS = 'error';
	const WARNING_CLASS = 'update-nag';
	const SUCCESS_CLASS = 'updated';
	protected $logger;
	
	/**
	 *
	 * @param unknown $logger        	
	 */
	protected function __construct(PostmanLogger $logger) {
		$this->logger = $logger;
	}
	
	/**
	 *
	 * @param unknown $message        	
	 */
	public function addError($message) {
		$this->storeMessage ( $message, 'error' );
	}
	/**
	 *
	 * @param unknown $message        	
	 */
	public function addWarning($message) {
		$this->storeMessage ( $message, 'warning' );
	}
	/**
	 *
	 * @param unknown $message        	
	 */
	public function addMessage($message) {
		$this->storeMessage ( $message, 'notify' );
	}
	
	/**
	 * store messages for display later
	 *
	 * @param unknown $message        	
	 * @param unknown $type        	
	 */
	private function storeMessage($message, $type) {
		$messageArray = array ();
		$oldMessageArray = $this->getPostmanSession ()->getMessage ();
		if ($oldMessageArray) {
			$messageArray = $oldMessageArray;
		}
		$weGotIt = false;
		foreach ( $messageArray as $storedMessage ) {
			if ($storedMessage ['message'] === $message) {
				$weGotIt = true;
			}
		}
		if (! $weGotIt) {
			$m = array (
					'type' => $type,
					'message' => $message 
			);
			array_push ( $messageArray, $m );
			$this->getPostmanSession ()->setMessage ( $messageArray );
		}
	}
	/**
	 * Retrieve the messages and show them
	 */
	public function displayAllMessages() {
		$messageArray = $this->getPostmanSession ()->getMessage ();
		if ($messageArray) {
			$this->getPostmanSession ()->unsetMessage ();
			foreach ( $messageArray as $m ) {
				$type = $m ['type'];
				switch ($type) {
					case 'error' :
						$className = self::ERROR_CLASS;
						break;
					case 'warning' :
						$className = self::WARNING_CLASS;
						break;
					default :
						$className = self::SUCCESS_CLASS;
						break;
				}
				$message = $m ['message'];
				$this->printMessage ( $message, $className );
			}
		}
	}
	
	/**
	 * putput message
	 *
	 * @param unknown $message        	
	 * @param unknown $className        	
	 */
	public function printMessage($message, $className) {
		printf ( '<div class="%s"><p>%s</p></div>', $className, $message );
	}
}