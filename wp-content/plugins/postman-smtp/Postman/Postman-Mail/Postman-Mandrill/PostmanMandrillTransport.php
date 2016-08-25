<?php

/**
 * Postman Mandrill module
 *
 * @author jasonhendriks
 *        
 */
class PostmanMandrillTransport extends PostmanAbstractModuleTransport implements PostmanModuleTransport {
	const SLUG = 'mandrill_api';
	const PORT = 443;
	const HOST = 'mandrillapp.com';
	const PRIORITY = 9000;
	const MANDRILL_AUTH_OPTIONS = 'postman_mandrill_auth_options';
	const MANDRILL_AUTH_SECTION = 'postman_mandrill_auth_section';
	
	/**
	 *
	 * @param unknown $data        	
	 */
	public function prepareOptionsForExport($data) {
		// use our own options for export, not the network options
		$data = parent::prepareOptionsForExport ( $data );
		$data [PostmanOptions::MANDRILL_API_KEY] = PostmanOptions::getInstance ()->getMandrillApiKey ();
		return $data;
	}
	public function getProtocol() {
		return 'https';
	}
	
	// this should be standard across all transports
	public function getSlug() {
		return self::SLUG;
	}
	public function getName() {
		return __ ( 'Mandrill API', Postman::TEXT_DOMAIN );
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getHostname() {
		return self::HOST;
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getPort() {
		return self::PORT;
	}
	/**
	 * v1.7.0
	 *
	 * @return string
	 */
	public function getTransportType() {
		return 'mandrill_api';
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getAuthenticationType() {
		return '';
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getSecurityType() {
		return self::PROTOCOL;
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getCredentialsId() {
		return $this->accountOptions->getClientId ();
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getCredentialsSecret() {
		return $this->accountOptions->getClientSecret ();
	}
	public function isServiceProviderGoogle($hostname) {
		return false;
	}
	public function isServiceProviderMicrosoft($hostname) {
		return false;
	}
	public function isServiceProviderYahoo($hostname) {
		return false;
	}
	public function isOAuthUsed($authType) {
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::createMailEngine()
	 */
	public function createMailEngine() {
		$apiKey = $this->accountOptions->getMandrillApiKey ();
		require_once 'PostmanMandrillMailEngine.php';
		$engine = new PostmanMandrillMailEngine ( $this, $apiKey );
		return $engine;
	}
	
	/**
	 * This short description of the Transport State shows on the Summary screens
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::getDeliveryDetails()
	 */
	public function getDeliveryDetails() {
		/* translators: where (1) is the secure icon and (2) is the transport name */
		return sprintf ( __ ( 'Postman will send mail via the <b>%1$s %2$s</b>.', Postman::TEXT_DOMAIN ), '🔐', $this->getName () );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanAbstractModuleTransport::validateTransportConfiguration()
	 */
	protected function validateTransportConfiguration() {
		$messages = parent::validateTransportConfiguration ();
		$apiKey = $this->accountOptions->getMandrillApiKey ();
		if (empty ( $apiKey )) {
			array_push ( $messages, __ ( 'API Key can not be empty', Postman::TEXT_DOMAIN ) . '.' );
			$this->setNotConfiguredAndReady ();
		}
		if (! $this->isSenderConfigured ()) {
			array_push ( $messages, __ ( 'Message From Address can not be empty', Postman::TEXT_DOMAIN ) . '.' );
			$this->setNotConfiguredAndReady ();
		}
		return $messages;
	}
	
	/**
	 * Mandrill API doesn't care what the hostname or guessed SMTP Server is; it runs it's port test no matter what
	 */
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
		$hosts = array (
				self::createSocketDefinition ( $this->getHostname (), $this->getPort () ) 
		);
		return $hosts;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::getConfigurationBid()
	 */
	public function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
		$recommendation = array ();
		$recommendation ['priority'] = 0;
		$recommendation ['transport'] = self::SLUG;
		$recommendation ['hostname'] = null; // scribe looks this
		$recommendation ['label'] = $this->getName ();
		if ($hostData->hostname == self::HOST && $hostData->port == self::PORT) {
			$recommendation ['priority'] = self::PRIORITY;
			/* translators: where variables are (1) transport name (2) host and (3) port */
			$recommendation ['message'] = sprintf ( __ ( ('Postman recommends the %1$s to host %2$s on port %3$d.') ), $this->getName (), self::HOST, self::PORT );
		}
		return $recommendation;
	}
	
	/**
	 */
	public function createOverrideMenu(PostmanWizardSocket $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		$overrideItem = parent::createOverrideMenu ( $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride );
		// push the authentication options into the $overrideItem structure
		$overrideItem ['auth_items'] = array (
				array (
						'selected' => true,
						'name' => __ ( 'API Key', Postman::TEXT_DOMAIN ),
						'value' => 'api_key' 
				) 
		);
		return $overrideItem;
	}
	
}
