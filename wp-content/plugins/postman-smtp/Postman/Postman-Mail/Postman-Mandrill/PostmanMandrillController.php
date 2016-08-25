<?php
require_once 'PostmanMandrillTransport.php';

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanMandrillController implements PostmanTransportController {
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
		return 'mandrill';
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
			
			// decodes the Mandrill API Key
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
		array_push ( $transports, new PostmanMandrillTransport ( $initializationData ) );
		return $transports;
	}
	
	/**
	 */
	private function registerStylesAndScripts() {
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
		wp_register_script ( 'postman_mandrill_script', plugins_url ( 'Postman/Postman-Mail/Postman-Mandrill/postman_mandrill.js', $this->rootPluginFilenameAndPath ), array (
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
		wp_enqueue_script ( 'postman_mandrill_script' );
	}
	
	/**
	 */
	private function addSettings() {
		// the Mandrill Auth section
		add_settings_section ( PostmanMandrillTransport::MANDRILL_AUTH_SECTION, __ ( 'Authentication', Postman::TEXT_DOMAIN ), array (
				$this,
				'printMandrillAuthSectionInfo' 
		), PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS );
		
		add_settings_field ( PostmanOptions::MANDRILL_API_KEY, __ ( 'API Key', Postman::TEXT_DOMAIN ), array (
				$this,
				'mandrill_api_key_callback' 
		), PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS, PostmanMandrillTransport::MANDRILL_AUTH_SECTION );
	}
	
	/**
	 */
	public function printMandrillAuthSectionInfo() {
		/* Translators: Where (1) is the service URL and (2) is the service name and (3) is a api key URL */
		printf ( '<p id="wizard_mandrill_auth_help">%s</p>', sprintf ( __ ( 'Create an account at <a href="%1$s" target="_new">%2$s</a> and enter <a href="%3$s" target="_new">an API key</a> below.', Postman::TEXT_DOMAIN ), 'https://mandrillapp.com', 'Mandrillapp.com', 'https://mandrillapp.com/settings' ) );
	}
	
	/**
	 */
	public function mandrill_api_key_callback() {
		printf ( '<input type="password" autocomplete="off" id="mandrill_api_key" name="postman_options[mandrill_api_key]" value="%s" size="60" class="required" placeholder="%s"/>', null !== $this->options->getMandrillApiKey () ? esc_attr ( PostmanUtils::obfuscatePassword ( $this->options->getMandrillApiKey () ) ) : '', __ ( 'Required', Postman::TEXT_DOMAIN ) );
		print ' <input type="button" id="toggleMandrillApiKey" value="Show Password" class="button button-secondary" style="visibility:hidden" />';
	}
	
	/**
	 */
	public function on_postman_print_wizard_authentication_step() {
		print '<section class="wizard_mandrill">';
		$this->printMandrillAuthSectionInfo ();
		printf ( '<label for="api_key">%s</label>', __ ( 'API Key', Postman::TEXT_DOMAIN ) );
		print '<br />';
		print $this->mandrill_api_key_callback ();
		print '</section>';
	}
	
	/**
	 *
	 * @param unknown $data        	
	 */
	public function on_postman_prep_options_for_export($data) {
		// use our own options for export, not the network options
		$data [PostmanOptions::MANDRILL_API_KEY] = PostmanOptions::getInstance ()->getMandrillApiKey ();
		return $data;
	}
}