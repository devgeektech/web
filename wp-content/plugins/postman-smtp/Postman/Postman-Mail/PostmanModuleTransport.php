<?php
/**
 * Keep the interface_exists check here for Postman Gmail API Extension users!
 * 
 * @author jasonhendriks
 */
if (! interface_exists ( 'PostmanTransport' )) {
	interface PostmanTransport {
		public function isServiceProviderGoogle($hostname);
		public function isServiceProviderMicrosoft($hostname);
		public function isServiceProviderYahoo($hostname);
		// @deprecated
		public function isOAuthUsed($authType);
		public function isTranscriptSupported();
		public function getSlug();
		public function getName();
		// @deprecated
		public function createPostmanMailAuthenticator(PostmanOptions $options, PostmanOAuthToken $authToken);
		public function createZendMailTransport($fakeHostname, $fakeConfig);
		public function isConfigured(PostmanOptionsInterface $options, PostmanOAuthToken $token);
		public function isReady(PostmanOptionsInterface $options, PostmanOAuthToken $token);
		// @deprecated
		public function getMisconfigurationMessage(PostmanConfigTextHelper $scribe, PostmanOptionsInterface $options, PostmanOAuthToken $token);
		// @deprecated
		public function getConfigurationRecommendation($hostData);
		// @deprecated
		public function getHostsToTest($hostname);
	}
}
interface PostmanModuleTransport extends PostmanTransport {
	const RAW_MESSAGE_FOLLOWS = '

--Raw message follows--

';
	public function getDeliveryDetails();
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess);
	public function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer);
	public function isLockingRequired();
	public function createMailEngine();
	public function isWizardSupported();
	public function isConfiguredAndReady();
	public function isReadyToSendMail();
	public function getFromEmailAddress();
	public function isSenderEmailOverridePrevented();
	public function isSenderNameOverridePrevented();
	public function getFromName();
	public function getProtocol();
	public function isEmailValidationSupported();
	public function getPort();
	public function getEnvelopeFromEmailAddress();
	public function isStealthModeEnabled();
}

/**
 *
 * @author jasonhendriks
 *        
 */
abstract class PostmanAbstractModuleTransport implements PostmanModuleTransport {
	private $configurationMessages;
	private $configuredAndReady;
	
	/**
	 * These internal variables are exposed for the subclasses to use
	 *
	 * @var unknown
	 */
	protected $logger;
	protected $accountOptions;
	protected $messageOptions;
	protected $scribe;
	
	/**
	 */
	public function __construct($initializationData) {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$this->init ( $initializationData );
	}
	
	/**
	 * Initialize the Module
	 *
	 * Perform validation and create configuration error messages.
	 * The module is not in a configured-and-ready state until initialization
	 */
	protected function init($initializationData) {
		$this->accountOptions = $initializationData ['account_options'];
		$this->messageOptions = $initializationData ['message_options'];
		// create the scribe
		$hostname = $this->getHostname ();
		$this->scribe = $this->createScribe ( $hostname );
		
		// validate the transport and generate error messages
		$this->configurationMessages = $this->validateTransportConfiguration ();
	}
	
	/**
	 * SendGrid API doesn't care what the hostname or guessed SMTP Server is; it runs it's port test no matter what
	 */
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
		$hosts = array (
				self::createSocketDefinition ( $this->getHostname (), $this->getPort () ) 
		);
		return $hosts;
	}
	
	/**
	 * Creates a single socket for the Wizard to test
	 */
	protected function createSocketDefinition($hostname, $port) {
		$socket = array ();
		$socket ['host'] = $hostname;
		$socket ['port'] = $port;
		$socket ['id'] = sprintf ( '%s-%s', $this->getSlug (), $port );
		$socket ['transport_id'] = $this->getSlug ();
		$socket ['transport_name'] = $this->getName ();
		$socket ['smtp'] = false;
		return $socket;
	}
	
	/**
	 *
	 * @param unknown $data        	
	 */
	public function prepareOptionsForExport($data) {
		// use our own options for export, not the network options
		return $data;
	}
	
	/**
	 */
	public function printActionMenuItem() {
		printf ( '<li><div class="welcome-icon send_test_email">%s</div></li>', $this->getScribe ()->getRequestPermissionLinkText () );
	}
	
	/**
	 *
	 * @param unknown $queryHostname        	
	 */
	protected function createScribe($hostname) {
		$scribe = new PostmanNonOAuthScribe ( $hostname );
		return $scribe;
	}
	
	/**
	 * This method is for internal use
	 */
	protected function validateTransportConfiguration() {
		$this->configuredAndReady = true;
		$messages = array ();
		return $messages;
	}
	
	/**
	 */
	protected function setNotConfiguredAndReady() {
		$this->configuredAndReady = false;
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
		return sprintf ( '%s://%s:%s', $protocol, $host, $port );
	}
	
	/**
	 * The Message From Address
	 */
	public function getFromEmailAddress() {
		return $this->messageOptions->getMessageSenderEmail ();
	}
	
	/**
	 * The Message From Name
	 */
	public function getFromName() {
		return $this->messageOptions->getMessageSenderName ();
	}
	public function getEnvelopeFromEmailAddress() {
		return $this->messageOptions->getEnvelopeSender ();
	}
	public function isSenderEmailOverridePrevented() {
		return $this->messageOptions->isSenderEmailOverridePrevented ();
	}
	public function isSenderNameOverridePrevented() {
		return $this->messageOptions->isSenderNameOverridePrevented ();
	}
	public function isEmailValidationSupported() {
		return ! $this->messageOptions->isEmailValidationDisabled ();
	}
	public function isStealthModeEnabled() {
		return $this->accountOptions->isStealthModeEnabled ();
	}
	
	/**
	 * Make sure the Senders are configured
	 *
	 * @param PostmanOptions $options        	
	 * @return boolean
	 */
	protected function isSenderConfigured() {
		$messageFrom = $this->messageOptions->getMessageSenderEmail ();
		return ! empty ( $messageFrom );
	}
	
	/**
	 * Get the configuration error messages
	 */
	public function getConfigurationMessages() {
		return $this->configurationMessages;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::isConfiguredAndReady()
	 */
	public function isConfiguredAndReady() {
		return $this->configuredAndReady;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::isReadyToSendMail()
	 */
	public function isReadyToSendMail() {
		return $this->isConfiguredAndReady ();
	}
	
	/**
	 * Determines whether Mail Engine locking is needed
	 *
	 * @see PostmanModuleTransport::requiresLocking()
	 */
	public function isLockingRequired() {
		return false;
	}
	public function isOAuthUsed($authType) {
		return $authType == PostmanOptions::AUTHENTICATION_TYPE_OAUTH2;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::isWizardSupported()
	 */
	public function isWizardSupported() {
		return false;
	}
	
	/**
	 *
	 * @return PostmanNonOAuthScribe
	 */
	public function getScribe() {
		return $this->scribe;
	}
	
	/**
	 *
	 * @param unknown $hostname        	
	 * @param unknown $response        	
	 */
	public function populateConfiguration($hostname) {
		$configuration = array ();
		return $configuration;
	}
	/**
	 *
	 * @param unknown $winningRecommendation        	
	 * @param unknown $response        	
	 */
	public function populateConfigurationFromRecommendation($winningRecommendation) {
		$configuration = array ();
		$configuration ['message'] = $winningRecommendation ['message'];
		$configuration [PostmanOptions::TRANSPORT_TYPE] = $winningRecommendation ['transport'];
		return $configuration;
	}
	
	/**
	 */
	public function createOverrideMenu(PostmanWizardSocket $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		$overrideItem = array ();
		$overrideItem ['secure'] = $socket->secure;
		$overrideItem ['mitm'] = $socket->mitm;
		$overrideItem ['hostname_domain_only'] = $socket->hostnameDomainOnly;
		$overrideItem ['reported_hostname_domain_only'] = $socket->reportedHostnameDomainOnly;
		$overrideItem ['value'] = $socket->id;
		$overrideItem ['description'] = $socket->label;
		$overrideItem ['selected'] = ($winningRecommendation ['id'] == $overrideItem ['value']);
		return $overrideItem;
	}
	
	/*
	 * ******************************************************************
	 * Not deprecated, but I wish they didn't live here on the superclass
	 */
	public function isServiceProviderGoogle($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'gmail.com' ) || PostmanUtils::endsWith ( $hostname, 'googleapis.com' );
	}
	public function isServiceProviderMicrosoft($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'live.com' );
	}
	public function isServiceProviderYahoo($hostname) {
		return strpos ( $hostname, 'yahoo' );
	}
	
	/*
	 * ********************************
	 * Unused, deprecated methods follow
	 * *********************************
	 */
	
	/**
	 *
	 * @deprecated (non-PHPdoc)
	 * @see PostmanTransport::createZendMailTransport()
	 */
	public function createZendMailTransport($hostname, $config) {
	}
	
	/**
	 *
	 * @deprecated (non-PHPdoc)
	 * @see PostmanTransport::isTranscriptSupported()
	 */
	public function isTranscriptSupported() {
		return false;
	}
	
	/**
	 * Only here because I can't remove it from the Interface
	 */
	public final function getMisconfigurationMessage(PostmanConfigTextHelper $scribe, PostmanOptionsInterface $options, PostmanOAuthToken $token) {
	}
	public final function isReady(PostmanOptionsInterface $options, PostmanOAuthToken $token) {
		return ! ($this->isConfiguredAndReady ());
	}
	public final function isConfigured(PostmanOptionsInterface $options, PostmanOAuthToken $token) {
		return ! ($this->isConfiguredAndReady ());
	}
	/**
	 *
	 * @deprecated (non-PHPdoc)
	 * @see PostmanTransport::getConfigurationRecommendation()
	 */
	public final function getConfigurationRecommendation($hostData) {
	}
	/**
	 *
	 * @deprecated (non-PHPdoc)
	 * @see PostmanTransport::getHostsToTest()
	 */
	public final function getHostsToTest($hostname) {
	}
	protected final function isHostConfigured() {
		$hostname = $this->accountOptions->getHostname ();
		$port = $this->accountOptions->getPort ();
		return ! (empty ( $hostname ) || empty ( $port ));
	}
	/**
	 *
	 * @deprecated (non-PHPdoc)
	 * @see PostmanTransport::createPostmanMailAuthenticator()
	 */
	public final function createPostmanMailAuthenticator(PostmanOptions $options, PostmanOAuthToken $authToken) {
	}
}
