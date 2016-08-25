<?php
require_once 'PostmanModuleTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
interface PostmanTransportController {
}

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanTransportRegistry {
	private $transports;
	private $logger;
	private $options;
	private $transportInitializationData;
	private $defaultTransport;
	
	/**
	 */
	private function __construct() {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$this->init ();
		
		// after all the plugins have loaded, dynamically load the transport modules
		add_filter ( 'plugins_loaded', array (
				$this,
				'on_plugins_loaded' 
		) );
	}
	
	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new PostmanTransportRegistry ();
		}
		return $inst;
	}
	
	/**
	 * Loads third party plugins via Postman API
	 */
	public function on_plugins_loaded() {
		$this->registerTransportsViaApi ();
	}
	
	/**
	 */
	private function registerTransportsViaApi() {
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'begin registerTransportsViaApi()' );
		}
		$thirdPartyTransports = array ();
		$thirdPartyTransports = apply_filters ( 'postman_register_transport', $thirdPartyTransports, $this->transportInitializationData );
		foreach ( $thirdPartyTransports as $transport ) {
			$this->registerTransport ( $transport );
		}
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'end registerTransportsViaApi()' );
		}
	}
	
	/**
	 * Direct registration of transport
	 *
	 * @param PostmanModuleTransport $instance        	
	 */
	public function registerTransport(PostmanModuleTransport $transport) {
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'Registering transport: ' . get_class ( $transport ) );
		}
		if ($transport->getSlug () == 'default') {
			$this->defaultTransport = $transport;
		}
		$this->transports [$transport->getSlug ()] = $transport;
	}
	
	/**
	 */
	private function init() {
		if (PostmanUtils::isMultisite ()) {
			if (PostmanNetworkOptions::getInstance ()->isSubsiteAccountSettingsUnderNetworkControl ()) {
				$this->options = PostmanNetworkOptions::getInstance ();
				$data ['account_options'] = PostmanNetworkOptions::getInstance ();
				$data ['account_settings_from_network'] = true;
			} else {
				$this->options = PostmanOptions::getInstance ();
				$data ['account_options'] = PostmanOptions::getInstance ();
				$data ['account_settings_from_network'] = false;
			}
			if (PostmanNetworkOptions::getInstance ()->isSubsiteMessageSettingsUnderNetworkControl ()) {
				$data ['message_options'] = PostmanNetworkOptions::getInstance ();
				$data ['message_settings_from_network'] = true;
			} else {
				$data ['message_options'] = PostmanOptions::getInstance ();
				$data ['message_settings_from_network'] = false;
			}
		} else {
			$this->options = PostmanOptions::getInstance ();
			$data ['account_options'] = PostmanOptions::getInstance ();
			$data ['account_settings_from_network'] = false;
			$data ['message_options'] = PostmanOptions::getInstance ();
			$data ['message_settings_from_network'] = false;
		}
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( sprintf ( 'Getting account settings from network: %s', $data ['account_settings_from_network'] ? 'Yes' : 'No' ) );
			$this->logger->debug ( sprintf ( 'Getting message settings from network: %s', $data ['message_settings_from_network'] ? 'Yes' : 'No' ) );
			$this->logger->debug ( 'selected transport is ' . $this->options->getTransportType () );
		}
		$this->transportInitializationData = $data;
	}
	
	/**
	 *
	 * @return PostmanModuleTransport
	 */
	public function getTransports() {
		return $this->transports;
	}
	
	/**
	 * Retrieve a Transport by slug
	 * Look up a specific Transport use:
	 * A) when retrieving the transport saved in the database
	 * B) when querying what a theoretical scenario involving this transport is like
	 * (ie.for ajax in config screen)
	 *
	 * @param unknown $slug        	
	 */
	public function getTransport($slug) {
		$transports = $this->getTransports ();
		if (isset ( $transports [$slug] )) {
			return $transports [$slug];
		}
	}
	
	/**
	 * A short-hand way of showing the complete delivery method
	 *
	 * @param PostmanModuleTransport $transport        	
	 * @return string
	 */
	public function getPublicTransportUri(PostmanModuleTransport $transport) {
		return $transport->getPublicTransportUri ();
	}
	
	/**
	 * Determine if a specific transport is registered in the directory.
	 *
	 * @param unknown $slug        	
	 */
	public function isRegistered($slug) {
		$transports = $this->getTransports ();
		return isset ( $transports [$slug] );
	}
	
	/**
	 * Retrieve the transport Postman is currently configured with.
	 *
	 * @return PostmanDummyTransport|PostmanModuleTransport
	 * @deprecated
	 *
	 */
	public function getCurrentTransport() {
		$selectedTransport = $this->options->getTransportType ();
		$transports = $this->getTransports ();
		if (! isset ( $transports [$selectedTransport] )) {
			return $this->defaultTransport;
		} else {
			return $transports [$selectedTransport];
		}
	}
	
	/**
	 *
	 * @param PostmanOptions $options        	
	 * @param PostmanOAuthToken $token        	
	 * @return boolean
	 */
	public function getActiveTransport() {
		$selectedTransport = $this->options->getTransportType ();
		$transports = $this->getTransports ();
		if (isset ( $transports [$selectedTransport] )) {
			$transport = $transports [$selectedTransport];
			if ($transport->getSlug () == $selectedTransport && $transport->isConfiguredAndReady ()) {
				return $transport;
			}
		}
		return $this->defaultTransport;
	}
	
	/**
	 * Retrieve the transport Postman is currently configured with.
	 *
	 * @return PostmanDummyTransport|PostmanModuleTransport
	 */
	public function getSelectedTransport() {
		$selectedTransport = $this->options->getTransportType ();
		$transports = $this->getTransports ();
		if (isset ( $transports [$selectedTransport] )) {
			return $transports [$selectedTransport];
		} else {
			return $this->defaultTransport;
		}
	}
	
	/**
	 * Polls all the installed transports to get a complete list of sockets to probe for connectivity
	 *
	 * @param unknown $hostname        	
	 * @param unknown $isGmail        	
	 * @return multitype:
	 */
	public function getSocketsForSetupWizardToProbe($hostname = 'localhost', $smtpServerGuess = null) {
		$hosts = array ();
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( sprintf ( 'Getting sockets for Port Test given hostname %s and smtpServerGuess %s', $hostname, $smtpServerGuess ) );
		}
		foreach ( $this->getTransports () as $transport ) {
			$socketsToTest = $transport->getSocketsForSetupWizardToProbe ( $hostname, $smtpServerGuess );
			if ($this->logger->isTrace ()) {
				$this->logger->trace ( 'sockets to test:' );
				$this->logger->trace ( $socketsToTest );
			}
			$hosts = array_merge ( $hosts, $socketsToTest );
			if ($this->logger->isDebug ()) {
				$this->logger->debug ( sprintf ( 'Transport %s returns %d sockets ', $transport->getName (), sizeof ( $socketsToTest ) ) );
			}
		}
		return $hosts;
	}
	
	/**
	 * If the host port is a possible configuration option, recommend it
	 *
	 * $hostData includes ['host'] and ['port']
	 *
	 * response should include ['success'], ['message'], ['priority']
	 *
	 * @param unknown $hostData        	
	 */
	public function getRecommendation(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
		$scrubbedUserAuthOverride = $this->scrubUserOverride ( $hostData, $userAuthOverride );
		$transport = $this->getTransport ( $hostData->transport );
		$recommendation = $transport->getConfigurationBid ( $hostData, $scrubbedUserAuthOverride, $originalSmtpServer );
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( sprintf ( 'Transport %s bid %s', $transport->getName (), $recommendation ['priority'] ) );
		}
		return $recommendation;
	}
	
	/**
	 *
	 * @param PostmanWizardSocket $hostData        	
	 * @param unknown $userAuthOverride        	
	 * @return NULL
	 */
	private function scrubUserOverride(PostmanWizardSocket $hostData, $userAuthOverride) {
		$this->logger->trace ( 'before scrubbing userAuthOverride: ' . $userAuthOverride );
		
		// validate userAuthOverride
		if (! ($userAuthOverride == 'oauth2' || $userAuthOverride == 'password' || $userAuthOverride == 'none')) {
			$userAuthOverride = null;
		}
		
		// validate the userAuthOverride
		if (! $hostData->auth_xoauth) {
			if ($userAuthOverride == 'oauth2') {
				$userAuthOverride = null;
			}
		}
		if (! $hostData->auth_crammd5 && ! $hostData->authPlain && ! $hostData->auth_login) {
			if ($userAuthOverride == 'password') {
				$userAuthOverride = null;
			}
		}
		if (! $hostData->auth_none) {
			if ($userAuthOverride == 'none') {
				$userAuthOverride = null;
			}
		}
		$this->logger->trace ( 'after scrubbing userAuthOverride: ' . $userAuthOverride );
		return $userAuthOverride;
	}
	
	/**
	 */
	public function getReadyMessage() {
		if (! PostmanUtils::isPageNetworkAdmin () && PostmanNetworkOptions::getInstance ()->isSubsiteAccountSettingsUnderNetworkControl ()) {
			if ($this->getCurrentTransport ()->isConfiguredAndReady ()) {
				$message ['content'] = __ ( 'Postman has been configured by your network administrator.', Postman::TEXT_DOMAIN );
				$message ['type'] = 'notify';
			} else {
				$message ['content'] = __ ( 'Postman is not configured. Ask your network administrator to set it up.', Postman::TEXT_DOMAIN );
				$message ['type'] = 'error';
			}
		} else {
			$bindResult = apply_filters ( 'postman_wp_mail_bind_status', null );
			$bound = $bindResult ['bound'];
			if (! PostmanPreRequisitesCheck::isReady () || ! $bound) {
				$message ['content'] = __ ( 'Postman is unable to run. Email delivery is being handled by WordPress (or another plugin).', Postman::TEXT_DOMAIN );
				$message ['type'] = 'error';
			} else if ($this->options->getTransportType() != null && $this->getCurrentTransport ()->isConfiguredAndReady ()) {
				if (PostmanOptions::getInstance ()->getRunMode () != PostmanOptions::RUN_MODE_PRODUCTION) {
					$message ['content'] = __ ( 'Postman is in <em>non-Production</em> mode and is dumping all emails.', Postman::TEXT_DOMAIN );
					$message ['type'] = 'warning';
				} else {
					$message ['content'] = __ ( 'Postman is configured.', Postman::TEXT_DOMAIN );
					$message ['type'] = 'notify';
				}
			} else {
				$message ['content'] = __ ( 'Postman is <em>not</em> configured and is mimicking out-of-the-box WordPress email delivery.', Postman::TEXT_DOMAIN );
				$message ['type'] = 'error';
			}
		}
		return $message;
	}
}

PostmanTransportRegistry::getInstance ();