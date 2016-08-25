<?php

/**
 * This class creates the Custom Post Type for Email Logs and handles writing these posts.
 *
 * @author jasonhendriks
 */
class PostmanEmailLogService {
	
	/*
	 * Private content is published only for your eyes, or the eyes of only those with authorization
	 * permission levels to see private content. Normal users and visitors will not be aware of
	 * private content. It will not appear in the article lists. If a visitor were to guess the URL
	 * for your private post, they would still not be able to see your content. You will only see
	 * the private content when you are logged into your WordPress blog.
	 */
	const POSTMAN_CUSTOM_POST_STATUS_PRIVATE = 'private';
	
	// member variables
	private $logger;
	private $inst;
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
	}
	
	/**
	 * singleton instance
	 */
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanEmailLogService ();
		}
		return $inst;
	}
	
	/**
	 * Logs successful email attempts
	 *
	 * @param PostmanMessage $message        	
	 * @param unknown $transcript        	
	 * @param PostmanModuleTransport $transport        	
	 */
	public function writeSuccessLog($resendData, $transcript, PostmanModuleTransport $transport) {
		if (PostmanOptions::getInstance ()->isMailLoggingEnabled ()) {
			$statusMessage = '';
			$status = true;
			$subject = $resendData ['subject'];
			if (empty ( $subject )) {
				$statusMessage = sprintf ( '%s: %s', __ ( 'Warning', Postman::TEXT_DOMAIN ), __ ( 'An empty subject line can result in delivery failure.', Postman::TEXT_DOMAIN ) );
				$status = 'WARN';
			}
			$resendData = $this->addMoreToResendData ( $resendData, $transcript, $statusMessage, $status, $transport );
			$this->writeToEmailLog ( $resendData );
		}
	}
	
	/**
	 * Logs failed email attempts, requires more metadata so the email can be resent in the future
	 *
	 * @param PostmanMessage $message        	
	 * @param unknown $transcript        	
	 * @param PostmanModuleTransport $transport        	
	 * @param unknown $statusMessage        	
	 * @param unknown $originalTo        	
	 * @param unknown $originalSubject        	
	 * @param unknown $originalMessage        	
	 * @param unknown $originalHeaders        	
	 */
	public function writeFailureLog($resendData, $transcript, PostmanModuleTransport $transport, $statusMessage) {
		if (PostmanOptions::getInstance ()->isMailLoggingEnabled ()) {
			$resendData = $this->addMoreToResendData ( $resendData, $transcript, $statusMessage, false, $transport );
			$this->writeToEmailLog ( $resendData );
		}
	}
	
	/**
	 * Writes an email sending attempt to the Email Log
	 *
	 * From http://wordpress.stackexchange.com/questions/8569/wp-insert-post-php-function-and-custom-fields
	 */
	private function writeToEmailLog($resendData) {
		// nothing here is sanitized as WordPress should take care of
		// making database writes safe
		$my_post = array (
				'post_type' => PostmanEmailLogPostType::POSTMAN_CUSTOM_POST_TYPE_SLUG,
				'post_title' => $resendData ['subject'],
				'post_content' => base64_encode ( json_encode ( $resendData ) ),
				'post_excerpt' => $resendData ['status_message'],
				'post_status' => PostmanEmailLogService::POSTMAN_CUSTOM_POST_STATUS_PRIVATE 
		);
		
		// Insert the post into the database (WordPress gives us the Post ID)
		$post_id = wp_insert_post ( $my_post );
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( sprintf ( 'Saved message #%s to the database', $post_id ) );
		}
		
		// truncate the log (remove older entries)
		$purger = new PostmanEmailLogPurger ();
		$purger->truncateLogItems ( PostmanOptions::getInstance ()->getMailLoggingMaxEntries () );
	}
	
	/**
	 * Creates a Log object for use by writeToEmailLog()
	 *
	 * @param PostmanMessage $message        	
	 * @param unknown $transcript        	
	 * @param unknown $statusMessage        	
	 * @param unknown $success        	
	 * @param PostmanModuleTransport $transport        	
	 * @return PostmanEmailLog
	 */
	private function addMoreToResendData($resendData, $transcript, $statusMessage, $success, PostmanModuleTransport $transport) {
		$resendData ['success'] = $success;
		$resendData ['status_message'] = $statusMessage;
		$resendData ['transport_uri'] = PostmanTransportRegistry::getInstance ()->getPublicTransportUri ( $transport );
		$resendData ['transcript'] = $transcript;
		return $resendData;
	}
	
	/**
	 * Creates a readable "TO" entry based on the recipient header
	 *
	 * @param array $addresses        	
	 * @return string
	 */
	private static function flattenEmails(array $addresses) {
		$flat = '';
		$count = 0;
		foreach ( $addresses as $address ) {
			if ($count >= 3) {
				$flat .= sprintf ( __ ( '.. +%d more', Postman::TEXT_DOMAIN ), sizeof ( $addresses ) - $count );
				break;
			}
			if ($count > 0) {
				$flat .= ', ';
			}
			$flat .= $address->format ();
			$count ++;
		}
		return $flat;
	}
}

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanEmailLogPurger {
	private $posts;
	private $logger;
	
	/**
	 *
	 * @return unknown
	 */
	function __construct() {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$args = array (
				'posts_per_page' => 1000,
				'offset' => 0,
				'category' => '',
				'category_name' => '',
				'orderby' => 'date',
				'order' => 'DESC',
				'include' => '',
				'exclude' => '',
				'meta_key' => '',
				'meta_value' => '',
				'post_type' => PostmanEmailLogPostType::POSTMAN_CUSTOM_POST_TYPE_SLUG,
				'post_mime_type' => '',
				'post_parent' => '',
				'post_status' => 'private',
				'suppress_filters' => true 
		);
		$this->posts = get_posts ( $args );
	}
	
	/**
	 *
	 * @param array $posts        	
	 * @param unknown $postid        	
	 */
	function verifyLogItemExistsAndRemove($postid) {
		$force_delete = true;
		foreach ( $this->posts as $post ) {
			if ($post->ID == $postid) {
				if ($this->logger->isDebug ()) {
					$this->logger->debug ( 'deleting log item ' . $postid );
				}
				wp_delete_post ( $postid, $force_delete );
				return;
			}
		}
		$this->logger->warn ( 'could not find Postman Log Item #' . $postid );
	}
	function removeAll() {
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( sprintf ( 'deleting %d log items ', sizeof ( $this->posts ) ) );
		}
		$force_delete = true;
		foreach ( $this->posts as $post ) {
			wp_delete_post ( $post->ID, $force_delete );
		}
	}
	
	/**
	 *
	 * @param unknown $size        	
	 */
	function truncateLogItems($size) {
		$index = count ( $this->posts );
		$force_delete = true;
		while ( $index > $size ) {
			$postid = $this->posts [-- $index]->ID;
			if ($this->logger->isDebug ()) {
				$this->logger->debug ( 'deleting log item ' . $postid );
			}
			wp_delete_post ( $postid, $force_delete );
		}
	}
}
