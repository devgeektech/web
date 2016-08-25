<?php
require_once 'PostmanZendTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanSendmailModuleTransport extends PostmanAbstractZendModuleTransport implements PostmanZendModuleTransport {
	const SLUG = 'sendmail';
	public function isConfiguredAndReady() {
		return true;
	}
	public function isReadyToSendMail() {
		return true;
	}
	public function getEnvelopeFromEmailAddress() {
		return $this->getFromEmailAddress ();
	}
	public function isEmailValidationSupported() {
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::createMailEngine()
	 */
	public function createMailEngine() {
		require_once 'PostmanZendMailEngine.php';
		return new PostmanZendMailEngine ( $this );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanZendModuleTransport::createZendMailTransport()
	 */
	public function createZendMailTransport($fakeHostname, $fakeConfig) {
		return new Postman_Zend_Mail_Transport_Sendmail (); // this should be PHP mail
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanAbstractModuleTransport::getDeliveryDetails()
	 */
	public function getDeliveryDetails() {
		/* translators: where (1) is the secure icon and (2) is the transport name */
		return sprintf ( __ ( 'Postman will send mail via <b>%1$s %2$s</b>.', Postman::TEXT_DOMAIN ), '🔓', 'Sendmail' );
	}
	
	/**
	 * A short-hand way of showing the complete delivery method
	 *
	 * @param PostmanModuleTransport $transport        	
	 * @return string
	 */
	public function getPublicTransportUri() {
		$name = $this->getSlug ();
		$host = $this->getHostname ();
		$port = $this->getPort ();
		$protocol = $this->getProtocol ();
		return 'sendmail://';
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanAbstractZendModuleTransport::validateTransportConfiguration()
	 */
	protected function validateTransportConfiguration() {
		return parent::validateTransportConfiguration ();
	}
	
	/**
	 * Determines whether Mail Engine locking is needed
	 *
	 * @see PostmanModuleTransport::requiresLocking()
	 */
	public function isLockingRequired() {
		return false;
	}
	public function getSlug() {
		return self::SLUG;
	}
	public function getName() {
		return __ ( 'Sendmail/PHP Mail', Postman::TEXT_DOMAIN );
	}
	public function getHostname() {
		return 'localhost';
	}
	public function getPort() {
		return 25;
	}
	public function getSecurityType() {
		return PostmanOptions::SECURITY_TYPE_NONE;
	}
	public function getAuthenticationType() {
		return PostmanOptions::AUTHENTICATION_TYPE_NONE;
	}
	public function getCredentialsId() {
		$options = PostmanOptions::getInstance ();
		if ($options->isAuthTypeOAuth2 ()) {
			return $options->getClientId ();
		} else {
			return $options->getUsername ();
		}
	}
	public function getCredentialsSecret() {
		$options = PostmanOptions::getInstance ();
		if ($options->isAuthTypeOAuth2 ()) {
			return $options->getClientSecret ();
		} else {
			return $options->getPassword ();
		}
	}
	public function isServiceProviderGoogle($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'gmail.com' );
	}
	public function isServiceProviderMicrosoft($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'live.com' );
	}
	public function isServiceProviderYahoo($hostname) {
		return strpos ( $hostname, 'yahoo' );
	}
	public function isOAuthUsed($authType) {
		return false;
	}
	public final function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
		return null;
	}
	
	/**
	 * Does not participate in the Wizard process;
	 *
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::getSocketsForSetupWizardToProbe()
	 */
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
		return array ();
	}
}
