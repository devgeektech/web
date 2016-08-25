<?php
require_once 'PostmanSendGridTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanSendGridController implements PostmanTransportController {
	private $rootPluginFilenameAndPath;
	private $options;
	
	/**
	 */
	public function __construct($rootPluginFilenameAndPath) {
		$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
		$this->options = PostmanOptions::getInstance ();
		
		// register the bind status hook
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
		return 'send_grid';
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
			
			// decodes the SendGrid API Key
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
		array_push ( $transports, new PostmanSendGridTransport ($initializationData) );
		return $transports;
	}
	
	/**
	 */
	public function on_postman_enqueue_transport_script() {
		$this->enqueueScript ();
	}
	
	/**
	 */
	private function registerStylesAndScripts() {
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
		wp_register_script ( 'postman_sendgrid_script', plugins_url ( 'Postman/Postman-Mail/Postman-SendGrid/postman_sendgrid.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				'jquery_validation',
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
	}
	
	/**
	 */
	private function enqueueScript() {
		wp_enqueue_script ( 'postman_sendgrid_script' );
	}
	
	/**
	 *
	 * @param unknown $data        	
	 */
	public function on_postman_prep_options_for_export($data) {
		// use our own options for export, not the network options
		$data [PostmanOptions::SENDGRID_API_KEY] = PostmanOptions::getInstance ()->getSendGridApiKey ();
		return $data;
	}
	
	/**
	 */
	public function on_postman_print_wizard_authentication_step() {
		print '<section class="wizard_sendgrid">';
		$this->printSendGridAuthSectionInfo ();
		printf ( '<label for="api_key">%s</label>', __ ( 'API Key', Postman::TEXT_DOMAIN ) );
		print '<br />';
		print $this->sendgrid_api_key_callback ();
		print '</section>';
	}
	
	/**
	 */
	public function addSettings() {
		// the SendGrid Auth section
		add_settings_section ( PostmanSendGridTransport::SENDGRID_AUTH_SECTION, __ ( 'Authentication', Postman::TEXT_DOMAIN ), array (
				$this,
				'printSendGridAuthSectionInfo' 
		), PostmanSendGridTransport::SENDGRID_AUTH_OPTIONS );
		
		add_settings_field ( PostmanOptions::SENDGRID_API_KEY, __ ( 'API Key', Postman::TEXT_DOMAIN ), array (
				$this,
				'sendgrid_api_key_callback' 
		), PostmanSendGridTransport::SENDGRID_AUTH_OPTIONS, PostmanSendGridTransport::SENDGRID_AUTH_SECTION );
	}
	
	/**
	 */
	public function printSendGridAuthSectionInfo() {
		/* Translators: Where (1) is the service URL and (2) is the service name and (3) is a api key URL */
		printf ( '<p id="wizard_sendgrid_auth_help">%s</p>', sprintf ( __ ( 'Create an account at <a href="%1$s" target="_new">%2$s</a> and enter <a href="%3$s" target="_new">an API key</a> below.', Postman::TEXT_DOMAIN ), 'https://sendgrid.com', 'SendGrid.com', 'https://app.sendgrid.com/settings/api_keys' ) );
	}
	
	/**
	 */
	public function sendgrid_api_key_callback() {
		printf ( '<input type="password" autocomplete="off" id="sendgrid_api_key" name="postman_options[sendgrid_api_key]" value="%s" size="60" class="required" placeholder="%s"/>', null !== $this->options->getSendGridApiKey () ? esc_attr ( PostmanUtils::obfuscatePassword ( $this->options->getSendGridApiKey () ) ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
		print ' <input type="button" id="toggleSendGridApiKey" value="Show Password" class="button button-secondary" style="visibility:hidden" />';
	}
}