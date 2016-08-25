<?php
require_once 'PostmanDefaultModuleTransport.php';
require_once 'PostmanSendmailModuleTransport.php';
require_once 'PostmanSmtpModuleTransport.php';
require_once 'PostmanGmailApiModuleTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanZendController implements PostmanTransportController {
	const POSTMAN_ZEND_SCRIPT_NAME = 'postman_smtp_script';
	const SMTP_OPTIONS = 'postman_smtp_options';
	const SMTP_SECTION = 'postman_smtp_section';
	const BASIC_AUTH_OPTIONS = 'postman_basic_auth_options';
	const BASIC_AUTH_SECTION = 'postman_basic_auth_section';
	const OAUTH_AUTH_OPTIONS = 'postman_oauth_options';
	const OAUTH_SECTION = 'postman_oauth_section';
	
	//
	private $logger;
	private $rootPluginFilenameAndPath;
	private $options;
	private $oauthScribe;
	
	/**
	 */
	public function __construct($rootPluginFilenameAndPath) {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
		$this->options = PostmanOptions::getInstance ();
		
		// register the transport
		add_filter ( 'postman_register_transport', array (
				$this,
				'on_postman_register_transport' 
		), 10, 2 );
		
		// add a hook on the plugins_loaded event
		add_action ( 'admin_init', array (
				$this,
				'on_admin_init' 
		) );
	}
	
	/**
	 */
	public function getSlug() {
		return 'zend';
	}
	
	/**
	 * Functions to execute on the admin_init event
	 *
	 * "Runs at the beginning of every admin page before the page is rendered."
	 * ref: http://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
	 */
	public function on_admin_init() {
		// only administrators should be able to trigger this
		if (PostmanUtils::isUserAdmin ()) {
			$transport = PostmanTransportRegistry::getInstance ()->getSelectedTransport ();
			$this->oauthScribe = $transport->getScribe ();
			$this->addSettings ();
			$this->registerStylesAndScripts ();
			
			// register the enqueue script hook
			add_filter ( 'postman_enqueue_transport_script', array (
					$this,
					'on_postman_enqueue_transport_script' 
			) );
			
			// register the wizard filter
			add_filter ( 'postman_print_wizard_authentication_step', array (
					$this,
					'on_postman_print_wizard_authentication_step' 
			) );
			
			// register the wizard filter
			add_filter ( 'postman_print_wizard_mail_server_hostname', array (
					$this,
					'on_postman_print_wizard_mail_server_hostname' 
			) );
			
			// register the export massager
			add_filter ( 'postman_prep_options_for_export', array (
					$this,
					'on_postman_prep_options_for_export' 
			) );
		}
	}
	
	/**
	 *
	 * @return PostmanMandrillTransport
	 */
	public function on_postman_register_transport($transports, $initializationData) {
		array_push ( $transports, new PostmanDefaultModuleTransport ($initializationData) );
		array_push ( $transports, new PostmanSendmailModuleTransport ($initializationData) );
		array_push ( $transports, new PostmanSmtpModuleTransport ($initializationData) );
		array_push ( $transports, new PostmanGmailApiModuleTransport ($initializationData) );
		return $transports;
	}
	
	/**
	 */
	private function registerStylesAndScripts() {
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
		wp_register_script ( PostmanZendController::POSTMAN_ZEND_SCRIPT_NAME, plugins_url ( 'Postman/Postman-Mail/Postman-Zend/postman_smtp.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				'jquery_validation',
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
		wp_register_script ( 'postman_gmail_script', plugins_url ( 'Postman/Postman-Mail/Postman-Zend/postman_gmail.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				'jquery_validation',
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
	}
	
	/**
	 */
	public function on_postman_enqueue_transport_script() {
		$this->enqueueScript ();
	}
	
	/**
	 */
	private function enqueueScript() {
		wp_enqueue_script ( PostmanZendController::POSTMAN_ZEND_SCRIPT_NAME );
		wp_enqueue_script ( 'postman_gmail_script' );
	}
	
	/**
	 *
	 * @param unknown $data        	
	 */
	public function on_postman_prep_options_for_export($data) {
		// use our own options for export, not the network options
		$data [PostmanOptions::BASIC_AUTH_PASSWORD] = PostmanOptions::getInstance ()->getPassword ();
		return $data;
	}
	
	/*
	 * What follows in the code responsible for creating the Admin Settings page
	 */
	
	/**
	 */
	private function addSettings() {
		
		// Sanitize
		add_settings_section ( PostmanZendController::SMTP_SECTION, __ ( 'Transport Settings', Postman::TEXT_DOMAIN ), array (
				$this,
				'printSmtpSectionInfo' 
		), PostmanZendController::SMTP_OPTIONS );
		
		add_settings_field ( PostmanOptions::HOSTNAME, __ ( 'Outgoing Mail Server Hostname', Postman::TEXT_DOMAIN ), array (
				$this,
				'hostname_callback' 
		), PostmanZendController::SMTP_OPTIONS, PostmanZendController::SMTP_SECTION );
		
		add_settings_field ( PostmanOptions::PORT, __ ( 'Outgoing Mail Server Port', Postman::TEXT_DOMAIN ), array (
				$this,
				'port_callback' 
		), PostmanZendController::SMTP_OPTIONS, PostmanZendController::SMTP_SECTION );
		
		add_settings_field ( PostmanOptions::ENVELOPE_SENDER, __ ( 'Envelope-From Email Address', Postman::TEXT_DOMAIN ), array (
				$this,
				'sender_email_callback' 
		), PostmanZendController::SMTP_OPTIONS, PostmanZendController::SMTP_SECTION );
		
		add_settings_field ( PostmanOptions::SECURITY_TYPE, _x ( 'Security', 'Configuration Input Field', Postman::TEXT_DOMAIN ), array (
				$this,
				'encryption_type_callback' 
		), PostmanZendController::SMTP_OPTIONS, PostmanZendController::SMTP_SECTION );
		
		add_settings_field ( PostmanOptions::AUTHENTICATION_TYPE, __ ( 'Authentication', Postman::TEXT_DOMAIN ), array (
				$this,
				'authentication_type_callback' 
		), PostmanZendController::SMTP_OPTIONS, PostmanZendController::SMTP_SECTION );
		
		add_settings_section ( PostmanZendController::BASIC_AUTH_SECTION, __ ( 'Authentication', Postman::TEXT_DOMAIN ), array (
				$this,
				'printBasicAuthSectionInfo' 
		), PostmanZendController::BASIC_AUTH_OPTIONS );
		
		add_settings_field ( PostmanOptions::BASIC_AUTH_USERNAME, __ ( 'Username', Postman::TEXT_DOMAIN ), array (
				$this,
				'basic_auth_username_callback' 
		), PostmanZendController::BASIC_AUTH_OPTIONS, PostmanZendController::BASIC_AUTH_SECTION );
		
		add_settings_field ( PostmanOptions::BASIC_AUTH_PASSWORD, __ ( 'Password', Postman::TEXT_DOMAIN ), array (
				$this,
				'basic_auth_password_callback' 
		), PostmanZendController::BASIC_AUTH_OPTIONS, PostmanZendController::BASIC_AUTH_SECTION );
		
		// the OAuth section
		add_settings_section ( PostmanZendController::OAUTH_SECTION, __ ( 'Authentication', Postman::TEXT_DOMAIN ), array (
				$this,
				'printOAuthSectionInfo' 
		), PostmanZendController::OAUTH_AUTH_OPTIONS );
		
		add_settings_field ( 'callback_domain', sprintf ( '<span id="callback_domain">%s</span>', $this->oauthScribe->getCallbackDomainLabel () ), array (
				$this,
				'callback_domain_callback' 
		), PostmanZendController::OAUTH_AUTH_OPTIONS, PostmanZendController::OAUTH_SECTION );
		
		add_settings_field ( 'redirect_url', sprintf ( '<span id="redirect_url">%s</span>', $this->oauthScribe->getCallbackUrlLabel () ), array (
				$this,
				'redirect_url_callback' 
		), PostmanZendController::OAUTH_AUTH_OPTIONS, PostmanZendController::OAUTH_SECTION );
		
		add_settings_field ( PostmanOptions::CLIENT_ID, $this->oauthScribe->getClientIdLabel (), array (
				$this,
				'oauth_client_id_callback' 
		), PostmanZendController::OAUTH_AUTH_OPTIONS, PostmanZendController::OAUTH_SECTION );
		
		add_settings_field ( PostmanOptions::CLIENT_SECRET, $this->oauthScribe->getClientSecretLabel (), array (
				$this,
				'oauth_client_secret_callback' 
		), PostmanZendController::OAUTH_AUTH_OPTIONS, PostmanZendController::OAUTH_SECTION );
	}
	
	/**
	 * Print the Section text
	 */
	public function printSmtpSectionInfo() {
		print __ ( 'Configure the communication with the mail server.', Postman::TEXT_DOMAIN );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function hostname_callback() {
		printf ( '<input type="text" id="input_hostname" name="postman_options[hostname]" value="%s" size="40" class="required" placeholder="%s"/>', null !== $this->options->getHostname () ? esc_attr ( $this->options->getHostname () ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function port_callback($args) {
		printf ( '<input type="text" size="8" maxlength="5" id="input_port" name="postman_options[port]" value="%s" %s placeholder="%s"/>', null !== $this->options->getPort () ? esc_attr ( $this->options->getPort () ) : '', isset ( $args ['style'] ) ? $args ['style'] : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function encryption_type_callback() {
		$encType = $this->options->getEncryptionType ();
		print '<select id="input_enc_type" class="input_encryption_type" name="postman_options[enc_type]">';
		printf ( '<option class="input_enc_type_none" value="%s" %s>%s</option>', PostmanOptions::SECURITY_TYPE_NONE, $encType == PostmanOptions::SECURITY_TYPE_NONE ? 'selected="selected"' : '', __ ( 'None', Postman::TEXT_DOMAIN ) );
		printf ( '<option class="input_enc_type_ssl" value="%s" %s>%s</option>', PostmanOptions::SECURITY_TYPE_SMTPS, $encType == PostmanOptions::SECURITY_TYPE_SMTPS ? 'selected="selected"' : '', 'SMTPS' );
		printf ( '<option class="input_enc_type_tls" value="%s" %s>%s</option>', PostmanOptions::SECURITY_TYPE_STARTTLS, $encType == PostmanOptions::SECURITY_TYPE_STARTTLS ? 'selected="selected"' : '', 'STARTTLS' );
		print '</select>';
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function authentication_type_callback() {
		$authType = $this->options->getAuthenticationType ();
		printf ( '<select id="input_%2$s" class="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::AUTHENTICATION_TYPE );
		printf ( '<option class="input_auth_type_none" value="%s" %s>%s</option>', PostmanOptions::AUTHENTICATION_TYPE_NONE, $authType == PostmanOptions::AUTHENTICATION_TYPE_NONE ? 'selected="selected"' : '', 'None' );
		printf ( '<option class="input_auth_type_plain" value="%s" %s>%s</option>', PostmanOptions::AUTHENTICATION_TYPE_PLAIN, $authType == PostmanOptions::AUTHENTICATION_TYPE_PLAIN ? 'selected="selected"' : '', 'Plain' );
		printf ( '<option class="input_auth_type_login" value="%s" %s>%s</option>', PostmanOptions::AUTHENTICATION_TYPE_LOGIN, $authType == PostmanOptions::AUTHENTICATION_TYPE_LOGIN ? 'selected="selected"' : '', 'Login' );
		printf ( '<option class="input_auth_type_crammd5" value="%s" %s>%s</option>', PostmanOptions::AUTHENTICATION_TYPE_CRAMMD5, $authType == PostmanOptions::AUTHENTICATION_TYPE_CRAMMD5 ? 'selected="selected"' : '', 'CRAM-MD5' );
		printf ( '<option class="input_auth_type_oauth2" value="%s" %s>%s</option>', PostmanOptions::AUTHENTICATION_TYPE_OAUTH2, $authType == PostmanOptions::AUTHENTICATION_TYPE_OAUTH2 ? 'selected="selected"' : '', 'OAuth 2.0' );
		print '</select>';
	}
	
	/**
	 * Print the Section text
	 */
	public function printBasicAuthSectionInfo() {
		print __ ( 'Enter the account credentials.', Postman::TEXT_DOMAIN );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function basic_auth_username_callback() {
		$inputValue = (null !== $this->options->getUsername () ? esc_attr ( $this->options->getUsername () ) : '');
		$inputDescription = __ ( 'The Username is usually the same as the Envelope-From Email Address.', Postman::TEXT_DOMAIN );
		print ('<input tabindex="99" id="fake_user_name" name="fake_user[name]" style="position:absolute; top:-500px;" type="text" value="Safari Autofill Me">') ;
		printf ( '<input type="text" id="input_basic_auth_username" name="postman_options[basic_auth_username]" value="%s" size="40" class="required" placeholder="%s"/><br/><span class="postman_input_description">%s</span>', $inputValue, __ ( 'Required', Postman::TEXT_DOMAIN ), $inputDescription );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function basic_auth_password_callback() {
		print ('<input tabindex="99" id="fake_password" name="fake[password]" style="position:absolute; top:-500px;" type="password" value="Safari Autofill Me">') ;
		printf ( '<input type="password" id="input_basic_auth_password" name="postman_options[basic_auth_password]" value="%s" size="40" class="required" placeholder="%s"/>', null !== $this->options->getPassword () ? esc_attr ( PostmanUtils::obfuscatePassword ( $this->options->getPassword () ) ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
		print ' <input type="button" id="togglePasswordField" value="Show Password" class="button button-secondary" style="visibility:hidden" />';
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function oauth_client_id_callback() {
		printf ( '<input type="text" onClick="this.setSelectionRange(0, this.value.length)" id="oauth_client_id" name="postman_options[oauth_client_id]" value="%s" size="60" class="required" placeholder="%s"/>', null !== $this->options->getClientId () ? esc_attr ( $this->options->getClientId () ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function oauth_client_secret_callback() {
		printf ( '<input type="text" onClick="this.setSelectionRange(0, this.value.length)" autocomplete="off" id="oauth_client_secret" name="postman_options[oauth_client_secret]" value="%s" size="60" class="required" placeholder="%s"/>', null !== $this->options->getClientSecret () ? esc_attr ( $this->options->getClientSecret () ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
	}
	
	/**
	 * Print the Section text
	 */
	public function printOAuthSectionInfo() {
		printf ( '<p id="wizard_oauth2_help">%s</p>', $this->oauthScribe->getOAuthHelp () );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function callback_domain_callback() {
		printf ( '<input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly="readonly" id="input_oauth_callback_domain" value="%s" size="60"/>', $this->getCallbackDomain () );
	}
	
	/**
	 */
	private function getCallbackDomain() {
		try {
			return $this->oauthScribe->getCallbackDomain ();
		} catch ( Exception $e ) {
			return __ ( 'Error computing your domain root - please enter it manually', Postman::TEXT_DOMAIN );
		}
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function redirect_url_callback() {
		printf ( '<input type="text" onClick="this.setSelectionRange(0, this.value.length)" readonly="readonly" id="input_oauth_redirect_url" value="%s" size="60"/>', $this->oauthScribe->getCallbackUrl () );
	}
	
	/**
	 * Get the settings option array and print one of its values
	 */
	public function sender_email_callback() {
		$inputValue = (null !== $this->options->getEnvelopeSender () ? esc_attr ( $this->options->getEnvelopeSender () ) : '');
		$requiredLabel = __ ( 'Required', Postman::TEXT_DOMAIN );
		$envelopeFromMessage = __ ( 'This address, like the <b>return address</b> printed on an envelope, identifies the account owner to the SMTP server.', Postman::TEXT_DOMAIN );
		$spfMessage = sprintf ( __ ( 'For reliable delivery, this domain must specify an <a target="_new" href="%s">SPF record</a> permitting the use of the SMTP server named above.', Postman::TEXT_DOMAIN ), 'https://www.mail-tester.com/spf/' );
		printf ( '<input type="email" id="input_envelope_sender_email" name="postman_options[envelope_sender]" value="%s" size="40" class="required" placeholder="%s"/> <br/><span class="postman_input_description">%s %s</span>', $inputValue, $requiredLabel, $envelopeFromMessage, $spfMessage );
	}
	
	/**
	 */
	public function on_postman_print_wizard_mail_server_hostname() {
		printf ( '<legend>%s</legend>', _x ( 'Which host will relay the mail?', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'This is the Outgoing (SMTP) Mail Server, or Mail Submission Agent (MSA), which Postman delegates mail delivery to. This server is specific to your email account, and if you don\'t know what to use, ask your email service provider.', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'Note that many WordPress hosts, such as GoDaddy, Bluehost and Dreamhost, require that you use their mail accounts with their mail servers, and prevent you from using others.', Postman::TEXT_DOMAIN ) );
		printf ( '<label for="hostname">%s</label>', __ ( 'Outgoing Mail Server Hostname', Postman::TEXT_DOMAIN ) );
		print $this->hostname_callback ();
		printf ( '<p class="ajax-loader" style="display:none"><img src="%s"/></p>', plugins_url ( 'postman-smtp/style/ajax-loader.gif' ) );
		$warning = __ ( 'Warning', Postman::TEXT_DOMAIN );
		/* Translators: Where (%s) is the name of the web host */
		$nonGodaddyDomainMessage = sprintf ( __ ( 'Your email address <b>requires</b> access to a remote SMTP server blocked by %s.', Postman::TEXT_DOMAIN ), 'GoDaddy' );
		$nonGodaddyDomainMessage .= sprintf ( ' %s', __ ( 'If you have access to cPanel, enable the Remote Mail Exchanger.', Postman::TEXT_DOMAIN ) );
		printf ( '<p id="godaddy_block"><span style="background-color:yellow"><b>%s</b>: %s</span></p>', $warning, $nonGodaddyDomainMessage );
		/* Translators: Where (%1$s) is the SPF-info URL and (%2$s) is the name of the web host */
		$godaddyCustomDomainMessage = sprintf ( __ ( 'If you own this domain, make sure it has an <a href="%1$s">SPF record authorizing %2$s</a> as a relay, or you will have delivery problems.', Postman::TEXT_DOMAIN ), 'http://www.mail-tester.com/spf/godaddy', 'GoDaddy' );
		printf ( '<p id="godaddy_spf_required"><span style="background-color:yellow"><b>%s</b>: %s</span></p>', $warning, $godaddyCustomDomainMessage );
	}
	
	/**
	 */
	public function on_postman_print_wizard_authentication_step() {
		print '<section class="wizard-auth-oauth2">';
		print '<p id="wizard_oauth2_help"></p>';
		printf ( '<label id="callback_domain" for="callback_domain">%s</label>', $this->oauthScribe->getCallbackDomainLabel () );
		print '<br />';
		print $this->callback_domain_callback ();
		print '<br />';
		printf ( '<label id="redirect_url" for="redirect_uri">%s</label>', $this->oauthScribe->getCallbackUrlLabel () );
		print '<br />';
		print $this->redirect_url_callback ();
		print '<br />';
		printf ( '<label id="client_id" for="client_id">%s</label>', $this->oauthScribe->getClientIdLabel () );
		print '<br />';
		print $this->oauth_client_id_callback ();
		print '<br />';
		printf ( '<label id="client_secret" for="client_secret">%s</label>', $this->oauthScribe->getClientSecretLabel () );
		print '<br />';
		print $this->oauth_client_secret_callback ();
		print '<br />';
		print '</section>';
		
		print '<section class="wizard-auth-basic">';
		printf ( '<p class="port-explanation-ssl">%s</p>', __ ( 'Enter the account credentials.', Postman::TEXT_DOMAIN ) );
		printf ( '<label for="username">%s</label>', __ ( 'Username', Postman::TEXT_DOMAIN ) );
		print '<br />';
		print $this->basic_auth_username_callback ();
		print '<br />';
		printf ( '<label for="password">%s</label>', __ ( 'Password', Postman::TEXT_DOMAIN ) );
		print '<br />';
		print $this->basic_auth_password_callback ();
		print '</section>';
	}
}
