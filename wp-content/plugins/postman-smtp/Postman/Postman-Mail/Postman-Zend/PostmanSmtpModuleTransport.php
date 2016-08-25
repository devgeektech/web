<?php
require_once 'PostmanZendTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanSmtpModuleTransport extends PostmanAbstractZendModuleTransport implements PostmanZendModuleTransport {
	const SLUG = 'smtp';
	
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
		require_once 'PostmanZendMailTransportConfigurationFactory.php';
		if (PostmanOptions::AUTHENTICATION_TYPE_OAUTH2 == $this->getAuthenticationType ()) {
			$config = PostmanOAuth2ConfigurationFactory::createConfig ( $this );
		} else {
			$config = PostmanBasicAuthConfigurationFactory::createConfig ( $this );
		}
		return new Postman_Zend_Mail_Transport_Smtp ( $this->getHostname (), $config );
	}
	
	/**
	 * Determines whether Mail Engine locking is needed
	 *
	 * @see PostmanModuleTransport::requiresLocking()
	 */
	public function isLockingRequired() {
		return PostmanOptions::AUTHENTICATION_TYPE_OAUTH2 == $this->getAuthenticationType ();
	}
	public function getSlug() {
		return self::SLUG;
	}
	public function getName() {
		return 'SMTP';
	}
	public function getHostname() {
		return $this->accountOptions->getHostname ();
	}
	public function getPort() {
		return $this->accountOptions->getPort ();
	}
	public function getAuthenticationType() {
		return $this->accountOptions->getAuthenticationType ();
	}
	public function getCredentialsId() {
		if ($this->accountOptions->isAuthTypeOAuth2 ()) {
			return $this->accountOptions->getClientId ();
		} else {
			return $this->accountOptions->getUsername ();
		}
	}
	public function getCredentialsSecret() {
		if ($this->accountOptions->isAuthTypeOAuth2 ()) {
			return $this->accountOptions->getClientSecret ();
		} else {
			return $this->accountOptions->getPassword ();
		}
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanTransport::getMisconfigurationMessage()
	 */
	protected function validateTransportConfiguration() {
		$messages = parent::validateTransportConfiguration ();
		if (! $this->isHostConfigured ()) {
			array_push ( $messages, __ ( 'Outgoing Mail Server Hostname and Port can not be empty.', Postman::TEXT_DOMAIN ) );
			$this->setNotConfiguredAndReady ();
		}
		if (! $this->isEnvelopeFromConfigured ()) {
			array_push ( $messages, __ ( 'Envelope-From Email Address can not be empty', Postman::TEXT_DOMAIN ) . '.' );
			$this->setNotConfiguredAndReady ();
		}
		if ($this->accountOptions->isAuthTypePassword () && ! $this->isPasswordAuthenticationConfigured ( $this->accountOptions )) {
			array_push ( $messages, __ ( 'Username and password can not be empty.', Postman::TEXT_DOMAIN ) );
			$this->setNotConfiguredAndReady ();
		}
		if ($this->getAuthenticationType () == PostmanOptions::AUTHENTICATION_TYPE_OAUTH2) {
			if (! $this->isOAuth2SupportedHostConfigured ()) {
				/* translators: %1$s is the Client ID label, and %2$s is the Client Secret label (e.g. Warning: OAuth 2.0 authentication requires an OAuth 2.0-capable Outgoing Mail Server, Sender Email Address, Client ID, and Client Secret.) */
				array_push ( $messages, sprintf ( __ ( 'OAuth 2.0 authentication requires a supported OAuth 2.0-capable Outgoing Mail Server.', Postman::TEXT_DOMAIN ) ) );
				$this->setNotConfiguredAndReady ();
			}
		}
		if (empty ( $messages )) {
			$this->setReadyForOAuthGrant ();
			if ($this->isPermissionNeeded ( $this->accountOptions, $this->getOAuthToken () )) {
				/* translators: %1$s is the Client ID label, and %2$s is the Client Secret label */
				$message = sprintf ( __ ( 'You have configured OAuth 2.0 authentication, but have not received permission to use it.', Postman::TEXT_DOMAIN ), $this->getScribe ()->getClientIdLabel (), $this->getScribe ()->getClientSecretLabel () );
				$message .= sprintf ( ' <a href="%s">%s</a>.', PostmanUtils::getGrantOAuthPermissionUrl (), $this->getScribe ()->getRequestPermissionLinkText () );
				array_push ( $messages, $message );
				$this->setNotConfiguredAndReady ();
			}
		}
		return $messages;
	}
	
	/**
	 *
	 * @return boolean
	 */
	private function isOAuth2SupportedHostConfigured() {
		$hostname = $this->accountOptions->getHostname ();
		$supportedOAuthProvider = $this->isServiceProviderGoogle ( $hostname ) || $this->isServiceProviderMicrosoft ( $hostname ) || $this->isServiceProviderYahoo ( $hostname );
		return $supportedOAuthProvider;
	}
	
	/**
	 * Given a hostname, what ports should we test?
	 *
	 * May return an array of several combinations.
	 */
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
		$hosts = array (
				$this->createSocketDefinition ( $hostname, 25 ),
				$this->createSocketDefinition ( $hostname, 465 ),
				$this->createSocketDefinition ( $hostname, 587 ) 
		);
		
		return $hosts;
	}
	
	/**
	 * Creates a single socket for the Wizard to test
	 */
	protected function createSocketDefinition($hostname, $port) {
		$socket = parent::createSocketDefinition ( $hostname, $port );
		$socket ['smtp'] = true;
		return $socket;
	}
	
	/**
	 * SendGrid will never recommend it's configuration
	 *
	 * @param unknown $hostData        	
	 */
	public function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
		$port = $hostData->port;
		$hostname = $hostData->hostname;
		// because some servers, like smtp.broadband.rogers.com, report XOAUTH2 but have no OAuth2 front-end
		$supportedOAuth2Provider = $this->isServiceProviderGoogle ( $hostname ) || $this->isServiceProviderMicrosoft ( $hostname ) || $this->isServiceProviderYahoo ( $hostname );
		$score = 1;
		$recommendation = array ();
		// increment score for auth type
		if ($hostData->mitm) {
			$this->logger->debug ( 'Losing points for MITM' );
			$score -= 10000;
			$recommendation ['mitm'] = true;
		}
		if (! empty ( $originalSmtpServer ) && $hostname != $originalSmtpServer) {
			$this->logger->debug ( 'Losing points for Not The Original SMTP server' );
			$score -= 10000;
		}
		$secure = true;
		if ($hostData->startTls) {
			// STARTTLS was formalized in 2002
			// http://www.rfc-editor.org/rfc/rfc3207.txt
			$recommendation ['enc'] = PostmanOptions::SECURITY_TYPE_STARTTLS;
			$score += 30000;
		} elseif ($hostData->protocol == 'SMTPS') {
			// "The hopelessly confusing and imprecise term, SSL,
			// has often been used to indicate the SMTPS wrapper and
			// TLS to indicate the STARTTLS protocol extension."
			// http://stackoverflow.com/a/19942206/4368109
			$recommendation ['enc'] = PostmanOptions::SECURITY_TYPE_SMTPS;
			$score += 28000;
		} elseif ($hostData->protocol == 'SMTP') {
			$recommendation ['enc'] = PostmanOptions::SECURITY_TYPE_NONE;
			$score += 26000;
			$secure = false;
		}
		
		// if there is a way to send mail....
		if ($score > 10) {
			
			// determine the authentication type
			if ($hostData->auth_xoauth && $supportedOAuth2Provider && (empty ( $userAuthOverride ) || $userAuthOverride == 'oauth2')) {
				$recommendation ['auth'] = PostmanOptions::AUTHENTICATION_TYPE_OAUTH2;
				$recommendation ['display_auth'] = 'oauth2';
				$score += 500;
				if (! $secure) {
					$this->logger->debug ( 'Losing points for sending credentials in the clear' );
					$score -= 10000;
				}
			} elseif ($hostData->auth_crammd5 && (empty ( $userAuthOverride ) || $userAuthOverride == 'password')) {
				$recommendation ['auth'] = PostmanOptions::AUTHENTICATION_TYPE_CRAMMD5;
				$recommendation ['display_auth'] = 'password';
				$score += 400;
				if (! $secure) {
					$this->logger->debug ( 'Losing points for sending credentials in the clear' );
					$score -= 10000;
				}
			} elseif ($hostData->authPlain && (empty ( $userAuthOverride ) || $userAuthOverride == 'password')) {
				$recommendation ['auth'] = PostmanOptions::AUTHENTICATION_TYPE_PLAIN;
				$recommendation ['display_auth'] = 'password';
				$score += 300;
				if (! $secure) {
					$this->logger->debug ( 'Losing points for sending credentials in the clear' );
					$score -= 10000;
				}
			} elseif ($hostData->auth_login && (empty ( $userAuthOverride ) || $userAuthOverride == 'password')) {
				$recommendation ['auth'] = PostmanOptions::AUTHENTICATION_TYPE_LOGIN;
				$recommendation ['display_auth'] = 'password';
				$score += 200;
				if (! $secure) {
					$this->logger->debug ( 'Losing points for sending credentials in the clear' );
					$score -= 10000;
				}
			} else if (empty ( $userAuthOverride ) || $userAuthOverride == 'none') {
				$recommendation ['auth'] = PostmanOptions::AUTHENTICATION_TYPE_NONE;
				$recommendation ['display_auth'] = 'none';
				$score += 100;
			}
			
			// tiny weighting to prejudice the port selection, all things being equal
			if ($port == 587) {
				$score += 4;
			} elseif ($port == 25) {
				// "due to the prevalence of machines that have worms,
				// viruses, or other malicious software that generate large amounts of
				// spam, many sites now prohibit outbound traffic on the standard SMTP
				// port (port 25), funneling all mail submissions through submission
				// servers."
				// http://www.rfc-editor.org/rfc/rfc6409.txt
				$score += 3;
			} elseif ($port == 465) {
				// use of port 465 for SMTP was deprecated in 1998
				// http://www.imc.org/ietf-apps-tls/mail-archive/msg00204.html
				$score += 2;
			} else {
				$score += 1;
			}
			
			// create the recommendation message for the user
			// this can only be set if there is a valid ['auth'] and ['enc']
			$transportDescription = $this->getTransportDescription ( $recommendation ['enc'] );
			$authDesc = $this->getAuthenticationDescription ( $recommendation ['auth'] );
			$recommendation ['label'] = sprintf ( 'SMTP - %2$s:%3$d', $transportDescription, $hostData->hostnameDomainOnly, $port );
			/* translators: where %1$s is a description of the transport (eg. SMTPS-SSL), %2$s is a description of the authentication (eg. Password-CRAMMD5), %3$d is the TCP port (eg. 465), %4$d is the hostname */
			$recommendation ['message'] = sprintf ( __ ( 'Postman recommends %1$s with %2$s authentication to host %4$s on port %3$d.', Postman::TEXT_DOMAIN ), $transportDescription, $authDesc, $port, $hostname );
		}
		
		// fill-in the rest of the recommendation
		$recommendation ['transport'] = PostmanSmtpModuleTransport::SLUG;
		$recommendation ['priority'] = $score;
		$recommendation ['port'] = $port;
		$recommendation ['hostname'] = $hostname;
		$recommendation ['transport'] = self::SLUG;
		
		return $recommendation;
	}
}
