<?php

/**
 * Sends mail with the SendGrid API
 * https://sendgrid.com/docs/API_Reference/Web_API/mail.html
 *
 * @author jasonhendriks
 *        
 */
class PostmanSendGridMailEngine implements PostmanMailEngine {
	
	// logger for all concrete classes - populate with setLogger($logger)
	protected $logger;
	
	// the result
	private $transcript;
	
	//
	private $transport;
	private $email;
	private $apiKey;
	
	/**
	 *
	 * @param unknown $senderEmail        	
	 * @param unknown $accessToken        	
	 */
	function __construct(PostmanSendGridTransport $transport, $apiKey) {
		assert ( ! empty ( $apiKey ) );
		$this->transport = $transport;
		$this->apiKey = $apiKey;
		
		// create the logger
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		
		// create the Message
		$this->email = '';
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanSmtpEngine::send()
	 */
	public function send(PostmanMessage $message) {
		
		// add the From Header
		$sender = $message->getFromAddress ();
		{
			$senderEmail = $sender->getEmail ();
			$senderName = $sender->getName ();
			assert ( ! empty ( $senderEmail ) );
			$this->email .= sprintf ( 'from=%s', urlencode ( $senderEmail ) );
			if (! empty ( $senderName )) {
				$this->email .= sprintf ( '&fromname=%s', urlencode ( $senderEmail ) );
			}
			// now log it
			$sender->log ( $this->logger, 'From' );
		}
		
		// add the Postman signature - append it to whatever the user may have set
		if (! $this->transport->isStealthModeEnabled ()) {
			$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
			$message->getHeaders ()['X-Mailer'] = sprintf ( 'Postman SMTP %s for WordPress (%s)', $pluginData ['version'], 'https://wordpress.org/plugins/postman-smtp/' );
		}
		
		// if the caller set a Content-Type header, use it
		$contentType = $message->getContentType ();
		if (! empty ( $contentType )) {
			$this->logger->debug ( 'Adding content-type ' . $contentType );
			$message->getHeaders ()['Content-Type'] = $contentType;
		}
		
		// add the headers - see http://framework.zend.com/manual/1.12/en/zend.mail.additional-headers.html
		foreach ( ( array ) $message->getHeaders () as $header ) {
			$this->logger->debug ( sprintf ( 'Adding user header %s=%s', $header ['name'], $header ['content'] ) );
			$this->email .= sprintf ( '&headers=%s', urlencode ( json_encode ( $message->getHeaders () ) ) );
		}
		
		// add the to recipients
		foreach ( ( array ) $message->getToRecipients () as $recipient ) {
			$recipient->log ( $this->logger, 'To' );
			$this->email .= sprintf ( '&to[]=%s', urlencode ( $recipient->getEmail () ) );
			$this->email .= sprintf ( '&toname[]=%s', urlencode ( $recipient->getName () ) );
		}
		
		// add the cc recipients
		foreach ( ( array ) $message->getCcRecipients () as $recipient ) {
			$recipient->log ( $this->logger, 'Cc' );
			$this->email .= sprintf ( '&cc[]=%s', urlencode ( $recipient->getEmail () ) );
			$this->email .= sprintf ( '&ccname[]=%s', urlencode ( $recipient->getName () ) );
		}
		
		// add the bcc recipients
		foreach ( ( array ) $message->getBccRecipients () as $recipient ) {
			$recipient->log ( $this->logger, 'Bcc' );
			$this->email .= sprintf ( '&bcc[]=%s', urlencode ( $recipient->getEmail () ) );
			$this->email .= sprintf ( '&bccname[]=%s', urlencode ( $recipient->getName () ) );
		}
		
		// add the reply-to
		$replyTo = $message->getReplyTo ();
		// $replyTo is null or a PostmanEmailAddress object
		if (isset ( $replyTo )) {
			$this->email .= sprintf ( '&replyto=%s', urlencode ( $replyTo->format () ) );
		}
		
		// add the date
		$date = $message->getDate ();
		if (! empty ( $date )) {
			$this->email .= sprintf ( '&date=%s', urlencode ( $message->getDate () ) );
		}
		
		// add the subject
		if (null !== $message->getSubject ()) {
			$this->email .= sprintf ( '&subject=%s', urlencode ( $message->getSubject () ) );
		}
		
		// add the message content
		{
			$textPart = $message->getBodyTextPart ();
			if (! empty ( $textPart )) {
				$this->logger->debug ( 'Adding body as text' );
				$this->email .= sprintf ( '&text=%s', urlencode ( $textPart ) );
			}
			$htmlPart = $message->getBodyHtmlPart ();
			if (! empty ( $htmlPart )) {
				$this->logger->debug ( 'Adding body as html' );
				$this->email .= sprintf ( '&html=%s', urlencode ( $htmlPart ) );
			}
		}
		
		// add attachments
		$this->logger->debug ( "Adding attachments" );
		$this->addAttachmentsToMail ( $message );
		
		$headers = array (
				'Content-type: application/json;charset=\"utf-8\"',
				'Authorization' => 'Bearer ' . $this->apiKey 
		);
		
		$result = PostmanUtils::remotePostGetBodyOnly ( 'https://api.sendgrid.com/api/mail.send.json', $this->email, $headers );
		
		// send the message
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( "Sending mail" );
		}
		
		if ($this->logger->isInfo ()) {
			$this->logger->info ( $result );
		}
		
		$resultObject = json_decode ( $result );
		
		if (is_array ( $resultObject )) {
			$resultObject = $resultObject;
		}
		
		$this->transcript = print_r ( $result, true );
		$this->transcript .= PostmanModuleTransport::RAW_MESSAGE_FOLLOWS;
		$this->transcript .= print_r ( $this->email, true );
		
		// other statuses are sent and queued
		if (isset ( $resultObject->errors )) {
			throw new Exception ( 'Error sending message: ' . $resultObject->errors [0] );
		}
	}
	
	/**
	 *
	 * @param unknown $key        	
	 * @param unknown $value        	
	 * @param string $append        	
	 */
	private function addHeader($key, $value, $append = false) {
		$this->logger->debug ( 'Adding header: ' . $key . ' = ' . $value );
		$header = &$this->email ['headers'];
		if ($append && ! empty ( $header [$key] )) {
			$header [$key] = $header [$key] . ', ' . $value;
		} else {
			$header [$key] = $value;
		}
	}
	
	/**
	 * Add attachments to the message
	 *
	 * @param Postman_Zend_Mail $mail        	
	 */
	private function addAttachmentsToMail(PostmanMessage $message) {
		$attachments = $message->getAttachments ();
		if (! is_array ( $attachments )) {
			// WordPress may a single filename or a newline-delimited string list of multiple filenames
			$attArray = explode ( PHP_EOL, $attachments );
		} else {
			$attArray = $attachments;
		}
		// otherwise WordPress sends an array
		foreach ( $attArray as $file ) {
			if (! empty ( $file )) {
				$this->logger->debug ( "Adding attachment: " . $file );
				$key = 'files[' . urlencode ( basename ( $file ) ) . ']';
				// SendGrid says must be less than 7MB
				$this->email .= sprintf ( '&%s=%s', $key, urlencode ( file_get_contents ( $file ) ) );
			}
		}
	}
	
	// return the SMTP session transcript
	public function getTranscript() {
		return $this->transcript;
	}
}
