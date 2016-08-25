<?php
require_once ('PostmanRegisterConfigurationSettings.php');
class PostmanConfigurationController {
	const CONFIGURATION_SLUG = 'postman';
	const CONFIGURATION_WIZARD_SLUG = 'postman/setup_wizard';
	const DIAGNOSTICS_SUBPAGE_SLUG = 'diagnostics';
	const POSTMAN_MENU_SLUG = 'postman';
	const SAVE_NETWORK_SETTINGS_SLUG = 'jason123';
	
	// logging
	private $logger;
	private $options;
	private $settingsRegistry;
	
	// Holds the values to be used in the fields callbacks
	private $rootPluginFilenameAndPath;
	
	/**
	 */
	public function getNetworkAdminHomeUrl() {
		return 'admin.php?page=' . PostmanConfigurationController::CONFIGURATION_SLUG;
	}
	
	/**
	 */
	public function getAdminHomeUrl() {
		return 'options-general.php?page=' . PostmanConfigurationController::CONFIGURATION_SLUG;
	}
	
	/**
	 * Constructor
	 *
	 * @param unknown $rootPluginFilenameAndPath        	
	 */
	public function __construct($rootPluginFilenameAndPath) {
		assert ( ! empty ( $rootPluginFilenameAndPath ) );
		assert ( PostmanUtils::isUserAdmin () );
		assert ( is_admin () );
		
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
		$this->options = PostmanOptions::getInstance ();
		$this->settingsRegistry = new PostmanSettingsRegistry ();
		
		PostmanUtils::registerAdminMenu ( $this, 'add_admin_configuration_menu' );
		// since this is a top-level menu, it must register first
		PostmanUtils::registerNetworkAdminMenu ( $this, 'add_network_admin_configuration_menu', 0 );
		PostmanUtils::registerAdminMenu ( $this, 'add_setup_wizard_menu' );
		PostmanUtils::registerNetworkAdminMenu ( $this, 'add_network_setup_wizard_menu' );
		
		// register the post handlers
		PostmanUtils::registerAdminPostAction ( PostmanConfigurationController::SAVE_NETWORK_SETTINGS_SLUG, $this, 'update_my_settings' );
		
		// hook on the init event
		add_action ( 'init', array (
				$this,
				'on_init' 
		) );
		
		// initialize the scripts, stylesheets and form fields
		add_action ( 'admin_init', array (
				$this,
				'on_admin_init' 
		) );
		
		add_action ( 'postman_get_home_url', array (
				$this,
				'on_postman_get_home_url' 
		) );
		
		add_action ( 'postman_get_setup_wizard_url', array (
				$this,
				'on_postman_get_setup_wizard_url' 
		) );
	}
	public function on_postman_get_home_url() {
		if (PostmanUtils::isPageNetworkAdmin ()) {
			return $this->getNetworkAdminHomeUrl ();
		} else {
			return $this->getAdminHomeUrl ();
		}
	}
	public function on_postman_get_setup_wizard_url() {
		if (PostmanUtils::isPageNetworkAdmin ()) {
			return 'admin.php?page=' . PostmanConfigurationController::CONFIGURATION_WIZARD_SLUG;
		} else {
			return 'tools.php?page=' . PostmanConfigurationController::CONFIGURATION_WIZARD_SLUG;
		}
	}
	public function update_my_settings() {
		// the request is logged as part of the first subsite because it is submitted on admin-post.php
		$this->logger->info ( "Handling a request on behalf of the network administrator" );
		if (PostmanUtils::isUserNetworkAdmin ()) {
			$sanitizer = new PostmanInputSanitizer ();
			$data = $sanitizer->sanitize ( $_REQUEST ['postman_options'] );
			update_site_option ( PostmanOptions::POSTMAN_OPTIONS, $data );
		} else {
			$this->logger->error ( "User is not network administrator" );
		}
		$messageHandler = PostmanNetworkMessageHandler::getInstance ();
		$messageHandler->addMessage ( _x ( 'Settings saved.', 'The plugin successfully saved new settings.', Postman::TEXT_DOMAIN ) );
		// this redirects us to /wordpress/wp-admin/network/admin.php?page=postman
		PostmanUtils::redirect ( 'network/' . $this->getNetworkAdminHomeUrl () );
	}
	
	/**
	 * Functions to execute on the init event
	 *
	 * "Typically used by plugins to initialize. The current user is already authenticated by this time."
	 * ref: http://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_a_Typical_Request
	 */
	public function on_init() {
		// register Ajax handlers
		new PostmanGetHostnameByEmailAjaxController ();
		new PostmanManageConfigurationAjaxHandler ();
		new PostmanImportConfigurationAjaxController ( $this->options );
		require_once 'PostmanGetDiagnosticsViaAjax.php';
		new PostmanGetDiagnosticsViaAjax ();
	}
	
	/**
	 * Fires on the admin_init method
	 */
	public function on_admin_init() {
		//
		$this->registerStylesAndScripts ();
		$this->settingsRegistry->on_admin_init ();
	}
	
	/**
	 * Register and add settings
	 */
	private function registerStylesAndScripts() {
		if ($this->logger->isTrace ()) {
			$this->logger->trace ( 'registerStylesAndScripts()' );
		}
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
		wp_register_script ( 'postman_manual_config_script', plugins_url ( 'Postman/Postman-Configuration/postman_manual_config.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				'jquery_validation',
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
		wp_register_script ( 'postman_wizard_script', plugins_url ( 'Postman/Postman-Configuration/postman_wizard.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				'jquery_validation',
				'jquery_steps_script',
				PostmanViewController::POSTMAN_SCRIPT,
				'sprintf' 
		), $pluginData ['version'] );
		wp_register_script ( 'postman_diagnostics_script', plugins_url ( 'Postman/Postman-Configuration/postman_diagnostics.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
	}
	
	/**
	 */
	private function addLocalizeScriptsToPage() {
		$warning = __ ( 'Warning', Postman::TEXT_DOMAIN );
		/* translators: where %s is the name of the SMTP server */
		wp_localize_script ( 'postman_wizard_script', 'postman_smtp_mitm', sprintf ( '%s: %s', $warning, __ ( 'connected to %1$s instead of %2$s.', Postman::TEXT_DOMAIN ) ) );
		/* translators: where %d is a port number */
		wp_localize_script ( 'postman_wizard_script', 'postman_wizard_bad_redirect_url', __ ( 'You are about to configure OAuth 2.0 with an IP address instead of a domain name. This is not permitted. Either assign a real domain name to your site or add a fake one in your local host file.', Postman::TEXT_DOMAIN ) );
		
		// user input
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_input_sender_email', '#input_' . PostmanOptions::MESSAGE_SENDER_EMAIL );
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_input_sender_name', '#input_' . PostmanOptions::MESSAGE_SENDER_NAME );
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_port_element_name', '#input_' . PostmanOptions::PORT );
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_hostname_element_name', '#input_' . PostmanOptions::HOSTNAME );
		
		// the enc input
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_enc_for_password_el', '#input_enc_type_password' );
		// these are the ids for the <option>s in the encryption <select>
		
		// the password inputs
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_input_basic_username', '#input_' . PostmanOptions::BASIC_AUTH_USERNAME );
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_input_basic_password', '#input_' . PostmanOptions::BASIC_AUTH_PASSWORD );
		
		// the auth input
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_redirect_url_el', '#input_oauth_redirect_url' );
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_input_auth_type', '#input_' . PostmanOptions::AUTHENTICATION_TYPE );
		
		// the transport modules scripts
		$data = '';
		apply_filters ( 'postman_enqueue_transport_script', $data );
		
		// we need data from port test
		PostmanConnectivityTestController::addLocalizeScriptForPortTest ();
	}
	
	/**
	 * Register the Configuration screen
	 */
	public function add_admin_configuration_menu() {
		$this->setupConfigurationPage ( false );
	}
	
	/**
	 * Register the Configuration screen
	 */
	public function add_network_admin_configuration_menu() {
		$this->setupConfigurationPage ( true );
	}
	
	/**
	 * Register the Configuration screen
	 */
	public function add_setup_wizard_menu() {
		$this->setupWizardPage ( false );
	}
	
	/**
	 * Register the Configuration screen
	 */
	public function add_network_setup_wizard_menu() {
		$this->setupWizardPage ( true );
	}
	
	/**
	 * Register the Configuration screen
	 */
	private function setupConfigurationPage($networkMode) {
		$menuName = __ ( 'Postman SMTP', Postman::TEXT_DOMAIN );
		$pageTitle = sprintf ( '%s - %s', __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ), __ ( 'Settings', Postman::TEXT_DOMAIN ) );
		$uniqueId = PostmanConfigurationController::CONFIGURATION_SLUG;
		$pageOptions = array (
				$this,
				'outputManualConfigurationContent' 
		);
		if ($networkMode) {
			$page = add_menu_page ( $pageTitle, $menuName, 'jason', $uniqueId, $pageOptions, 'dashicons-admin-tools' );
		} else {
			$page = add_options_page ( $pageTitle, $menuName, Postman::MANAGE_POSTMAN_CAPABILITY_NAME, $uniqueId, $pageOptions );
		}
		// When the plugin options page is loaded, also load the stylesheet
		add_action ( 'admin_print_styles-' . $page, array (
				$this,
				'enqueueConfigurationResources' 
		) );
	}
	
	/**
	 */
	function enqueueConfigurationResources() {
		wp_enqueue_style ( PostmanViewController::POSTMAN_STYLE );
		if (! isset ( $_REQUEST ['subpage'] )) {
			wp_enqueue_style ( 'jquery_ui_style' );
			$this->addLocalizeScriptsToPage ();
			wp_enqueue_script ( 'postman_manual_config_script' );
			wp_enqueue_script ( 'jquery-ui-tabs' );
			$disableTabs = array ();
			if (! PostmanUtils::isPageNetworkAdmin () && PostmanNetworkOptions::getInstance ()->isSubsiteAccountSettingsUnderNetworkControl ()) {
				$disableTabs ['account'] = true;
			}
			if (! PostmanUtils::isPageNetworkAdmin () && PostmanNetworkOptions::getInstance ()->isSubsiteMessageSettingsUnderNetworkControl ()) {
				$disableTabs ['message'] = true;
			}
			wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postmanDisableTab', $disableTabs );
		}
		if (isset ( $_REQUEST ['subpage'] ) && $_REQUEST ['subpage'] == 'diagnostics') {
			wp_enqueue_script ( 'postman_diagnostics_script' );
		}
	}
	
	/**
	 * Register the Setup Wizard screen
	 */
	public function setupWizardPage($networkMode) {
		$menuName = __ ( 'Setup Wizard', Postman::TEXT_DOMAIN );
		$pageTitle = sprintf ( '%s - %s', __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ), $menuName );
		$uniqueId = self::CONFIGURATION_WIZARD_SLUG;
		$pageOptions = array (
				$this,
				'outputWizardContent' 
		);
		if ($networkMode) {
			$page = add_submenu_page ( PostmanConfigurationController::CONFIGURATION_WIZARD_SLUG, $pageTitle, $menuName, Postman::MANAGE_POSTMAN_CAPABILITY_NAME, $uniqueId, $pageOptions );
		} else {
			$page = add_management_page ( $pageTitle, __ ( 'Email Setup Wizard', Postman::TEXT_DOMAIN ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, $uniqueId, $pageOptions );
		}
		// When the plugin options page is loaded, also load the stylesheet
		add_action ( 'admin_print_styles-' . $page, array (
				$this,
				'enqueueWizardResources' 
		) );
	}
	
	/**
	 */
	function enqueueWizardResources() {
		$this->addLocalizeScriptsToPage ();
		$this->importableConfiguration = new PostmanImportableConfiguration ();
		$startPage = 1;
		if ($this->importableConfiguration->isImportAvailable ()) {
			$startPage = 0;
		}
		wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_setup_wizard', array (
				'start_page' => $startPage 
		) );
		wp_enqueue_style ( 'jquery_steps_style' );
		wp_enqueue_style ( PostmanViewController::POSTMAN_STYLE );
		wp_enqueue_script ( 'postman_wizard_script' );
		$shortLocale = substr ( get_locale (), 0, 2 );
		if ($shortLocale != 'en') {
			$url = plugins_url ( sprintf ( 'script/jquery-validate/localization/messages_%s.js', $shortLocale ), $this->rootPluginFilenameAndPath );
			wp_enqueue_script ( sprintf ( 'jquery-validation-locale-%s', $shortLocale ), $url );
		}
	}
	
	/**
	 */
	public function outputManualConfigurationContent() {
		if (! isset ( $_REQUEST ['subpage'] )) {
			$this->outputConfigurationTabs ();
		} else if ($_REQUEST ['subpage'] == 'reset' || $_REQUEST ['subpage'] == 'network/reset') {
			$this->outputPurgeDataContent ();
		} else if ($_REQUEST ['subpage'] == 'diagnostics') {
			$this->outputDiagnosticsContent ();
		} else {
			$this->outputConfigurationTabs ();
		}
	}
	private function outputConfigurationTabs() {
		print '<div class="wrap">';
		
		printf ( '<h2>%s</h2>', __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ) );
		print '<div id="config_tabs"><ul>';
		print sprintf ( '<li><a href="#dashboard">%s</a></li>', __ ( 'Dashboard', Postman::TEXT_DOMAIN ) );
		print sprintf ( '<li><a href="#account_config">%s</a></li>', __ ( 'Account', Postman::TEXT_DOMAIN ) );
		print sprintf ( '<li><a href="#message_config">%s</a></li>', __ ( 'Message', Postman::TEXT_DOMAIN ) );
		print sprintf ( '<li><a href="#logging_config">%s</a></li>', __ ( 'Logging', Postman::TEXT_DOMAIN ) );
		print sprintf ( '<li><a href="#advanced_options_config">%s</a></li>', __ ( 'Advanced', Postman::TEXT_DOMAIN ) );
		if (PostmanUtils::isPageNetworkAdmin ()) {
			print sprintf ( '<li><a href="#multisite_config">%s</a></li>', __ ( 'Multisite', Postman::TEXT_DOMAIN ) );
		}
		print '</ul>';
		if (PostmanUtils::isPageNetworkAdmin ()) {
			// from http://wordpress.stackexchange.com/questions/16474/how-to-add-field-for-new-site-wide-option-on-network-settings-screen
			printf ( '<form method="post" action="%s">', get_admin_url () . 'admin-post.php' );
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanConfigurationController::SAVE_NETWORK_SETTINGS_SLUG );
			wp_nonce_field ( PostmanConfigurationController::SAVE_NETWORK_SETTINGS_SLUG );
		} else {
			printf ( '<form method="post" action="%s">', get_admin_url () . 'options.php' );
			// This prints out all hidden setting fields
			settings_fields ( PostmanSettingsRegistry::SETTINGS_GROUP_NAME );
		}
		print '<section id="dashboard">';
		$this->outputDashboardTab ();
		print '</section>';
		print '<section id="account_config">';
		if (sizeof ( PostmanTransportRegistry::getInstance ()->getTransports () ) > 1) {
			do_settings_sections ( 'transport_options' );
		} else {
			printf ( '<input id="input_%2$s" type="hidden" name="%1$s[%2$s]" value="%3$s"/>', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSPORT_TYPE, PostmanSmtpModuleTransport::SLUG );
		}
		print '<div id="smtp_config" class="transport_setting">';
		do_settings_sections ( PostmanZendController::SMTP_OPTIONS );
		print '</div>';
		print '<div id="password_settings" class="authentication_setting non-oauth2">';
		do_settings_sections ( PostmanZendController::BASIC_AUTH_OPTIONS );
		print '</div>';
		print '<div id="oauth_settings" class="authentication_setting non-basic">';
		do_settings_sections ( PostmanZendController::OAUTH_AUTH_OPTIONS );
		print '</div>';
		print '<div id="mandrill_settings" class="authentication_setting non-basic non-oauth2">';
		do_settings_sections ( PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS );
		print '</div>';
		print '<div id="sendgrid_settings" class="authentication_setting non-basic non-oauth2">';
		do_settings_sections ( PostmanSendGridTransport::SENDGRID_AUTH_OPTIONS );
		print '</div>';
		submit_button ();
		print '</section>';
		print '<section id="message_config">';
		do_settings_sections ( PostmanSettingsRegistry::MESSAGE_SENDER_OPTIONS );
		do_settings_sections ( PostmanSettingsRegistry::MESSAGE_FROM_OPTIONS );
		do_settings_sections ( PostmanSettingsRegistry::EMAIL_VALIDATION_OPTIONS );
		do_settings_sections ( PostmanSettingsRegistry::MESSAGE_OPTIONS );
		do_settings_sections ( PostmanSettingsRegistry::MESSAGE_HEADERS_OPTIONS );
		submit_button ();
		print '</section>';
		print '<section id="logging_config">';
		do_settings_sections ( PostmanSettingsRegistry::LOGGING_OPTIONS );
		submit_button ();
		print '</section>';
		
		//
		print '<section id="advanced_options_config">';
		do_settings_sections ( PostmanSettingsRegistry::NETWORK_OPTIONS );
		do_settings_sections ( PostmanSettingsRegistry::ADVANCED_OPTIONS );
		submit_button ();
		print '</section>';
		
		//
		if (PostmanUtils::isPageNetworkAdmin ()) {
			print '<section id="multisite_config">';
			do_settings_sections ( PostmanSettingsRegistry::MULTISITE_OPTIONS );
			submit_button ();
			print '</section>';
		}
		
		// done
		print '</form>';
		print '</div>';
		print '</div>';
	}
	public function outputDashboardTab() {
		$this->displayTopNavigation ();
		$this->outputDefaultContent ();
	}
	
	/**
	 * Options page callback
	 */
	private function outputDefaultContent() {
		{
			$status = PostmanTransportRegistry::getInstance ()->getReadyMessage ();
			$statusMessage = $status ['content'];
			if ($status ['type'] == 'notify') {
				printf ( '<p><span style="color:green;padding:2px 0; font-size:1.1em">%s</span></p>', $statusMessage );
			} else if ($status ['type'] == 'warning') {
				printf ( '<p><span style="background-color:yellow">%s</span></p>', $statusMessage );
			} else {
				printf ( '<p><span style="color:red; padding:2px 0; font-size:1.1em">%s</span></p>', $statusMessage );
			}
			$this->printDeliveryDetails ();
			/* translators: where %d is the number of emails delivered */
			print '<p style="margin:10px 10px"><span>';
			printf ( _n ( 'Postman has delivered <span style="color:green">%d</span> email.', 'Postman has delivered <span style="color:green">%d</span> emails.', PostmanState::getInstance ()->getSuccessfulDeliveries (), Postman::TEXT_DOMAIN ), PostmanState::getInstance ()->getSuccessfulDeliveries () );
			if ($this->options->isMailLoggingEnabled ()) {
				print ' ';
				printf ( __ ( 'The last %d email attempts are recorded <a href="%s">in the log</a>.', Postman::TEXT_DOMAIN ), PostmanOptions::getInstance ()->getMailLoggingMaxEntries (), PostmanUtils::getEmailLogPageUrl () );
			}
			print '</span></p>';
		}
		
		if ($this->options->isNew ()) {
			printf ( '<h3 style="padding-top:10px">%s</h3>', __ ( 'Thank-you for choosing Postman!', Postman::TEXT_DOMAIN ) );
			/* translators: where %s is the URL of the Setup Wizard */
			printf ( '<p><span>%s</span></p>', sprintf ( __ ( 'Let\'s get started! All users are strongly encouraged to <a href="%s">run the Setup Wizard</a>.', Postman::TEXT_DOMAIN ), $this->on_postman_get_setup_wizard_url () ) );
			printf ( '<a class="button button-primary button-hero" href="%s">%s</a>', $this->on_postman_get_setup_wizard_url (), __ ( 'Start the Wizard', Postman::TEXT_DOMAIN ) );
		} else {
			if (PostmanState::getInstance ()->isTimeToReviewPostman () && ! PostmanOptions::getInstance ()->isNew ()) {
				print '</br><hr width="70%"></br>';
				/* translators: where %s is the URL to the WordPress.org review and ratings page */
				printf ( '%s</span></p>', sprintf ( __ ( 'Please consider <a href="%s">leaving a review</a> to help spread the word! :D', Postman::TEXT_DOMAIN ), 'https://wordpress.org/support/view/plugin-reviews/postman-smtp?filter=5' ) );
			}
			printf ( '<p><span>%s :-)</span></p>', sprintf ( __ ( 'Postman needs translators! Please take a moment to <a href="%s">translate a few sentences on-line</a>', Postman::TEXT_DOMAIN ), 'https://translate.wordpress.org/projects/wp-plugins/postman-smtp/stable' ) );
		}
	}
	
	/**
	 */
	public function outputDiagnosticsContent() {
		// test features
		print '<div class="wrap">';
		
		PostmanViewController::outputChildPageHeader ( __ ( 'Diagnostic Test', Postman::TEXT_DOMAIN ) );
		
		printf ( '<h4>%s</h4>', __ ( 'Are you having issues with Postman?', Postman::TEXT_DOMAIN ) );
		/* translators: where %1$s and %2$s are the URLs to the Troubleshooting and Support Forums on WordPress.org */
		printf ( '<p style="margin:0 10px">%s</p>', sprintf ( __ ( 'Please check the <a href="%1$s">troubleshooting and error messages</a> page and the <a href="%2$s">support forum</a>.', Postman::TEXT_DOMAIN ), 'https://wordpress.org/plugins/postman-smtp/other_notes/', 'https://wordpress.org/support/plugin/postman-smtp' ) );
		printf ( '<h4>%s</h4>', __ ( 'Diagnostic Test', Postman::TEXT_DOMAIN ) );
		printf ( '<p style="margin:0 10px">%s</p><br/>', sprintf ( __ ( 'If you write for help, please include the following:', Postman::TEXT_DOMAIN ), 'https://wordpress.org/plugins/postman-smtp/other_notes/', 'https://wordpress.org/support/plugin/postman-smtp' ) );
		printf ( '<textarea readonly="readonly" id="diagnostic-text" cols="80" rows="15">%s</textarea>', _x ( 'Checking..', 'The "please wait" message', Postman::TEXT_DOMAIN ) );
		print '</div>';
	}
	
	/**
	 */
	private function outputPurgeDataContent() {
		// construct Wizard
		print '<div class="wrap">';
		
		$importTitle = __ ( 'Import', Postman::TEXT_DOMAIN );
		$exportTile = __ ( 'Export', Postman::TEXT_DOMAIN );
		$resetTitle = __ ( 'Reset', Postman::TEXT_DOMAIN );
		
		//
		PostmanViewController::outputChildPageHeader ( sprintf ( '%s/%s/%s', $importTitle, $exportTile, $resetTitle ) );
		
		$options = $this->options;
		print '<section id="export_settings">';
		printf ( '<h3><span>%s<span></h3>', $exportTile );
		printf ( '<p><span>%s</span></p>', __ ( 'Copy this data into another instance of Postman to duplicate the configuration.', Postman::TEXT_DOMAIN ) );
		$data = '';
		if (! PostmanPreRequisitesCheck::checkZlibEncode ()) {
			$extraDeleteButtonAttributes = sprintf ( 'disabled="true"' );
			$data = '';
		} else {
			$extraDeleteButtonAttributes = '';
			if (! $options->isNew ()) {
				$data = $options->export ();
			}
		}
		printf ( '<textarea cols="80" rows="5" readonly="true" name="settings" %s>%s</textarea>', $extraDeleteButtonAttributes, $data );
		print '</section>';
		print '<section id="import_settings">';
		printf ( '<h3><span>%s<span></h3>', $importTitle );
		print '<form method="POST" action="' . get_admin_url () . 'admin-post.php">';
		if (PostmanUtils::isPageNetworkAdmin ()) {
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanAdminController::IMPORT_NETWORK_SETTINGS_SLUG );
			wp_nonce_field ( PostmanAdminController::IMPORT_NETWORK_SETTINGS_SLUG );
		} else {
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanAdminController::IMPORT_SETTINGS_SLUG );
			wp_nonce_field ( PostmanAdminController::IMPORT_SETTINGS_SLUG );
		}
		print '<p>';
		printf ( '<span>%s</span>', __ ( 'Paste data from another instance of Postman here to duplicate the configuration.', Postman::TEXT_DOMAIN ) );
		if (PostmanTransportRegistry::getInstance ()->getSelectedTransport ()->isOAuthUsed ( PostmanOptions::getInstance ()->getAuthenticationType () )) {
			$warning = __ ( 'Warning', Postman::TEXT_DOMAIN );
			$errorMessage = __ ( 'Using the same OAuth 2.0 Client ID and Client Secret from this site at the same time as another site will cause failures.', Postman::TEXT_DOMAIN );
			printf ( ' <span><b>%s</b>: %s</span>', $warning, $errorMessage );
		}
		print '</p>';
		printf ( '<textarea cols="80" rows="5" name="settings" %s></textarea>', $extraDeleteButtonAttributes );
		submit_button ( __ ( 'Import', Postman::TEXT_DOMAIN ), 'primary', 'import', true, $extraDeleteButtonAttributes );
		print '</form>';
		print '</section>';
		print '<section id="delete_settings">';
		printf ( '<h3><span>%s<span></h3>', $resetTitle );
		print '<form method="POST" action="' . get_admin_url () . 'admin-post.php">';
		if (PostmanUtils::isPageNetworkAdmin ()) {
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanAdminController::PURGE_NETWORK_DATA_SLUG );
			wp_nonce_field ( PostmanAdminController::PURGE_NETWORK_DATA_SLUG );
		} else {
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanAdminController::PURGE_DATA_SLUG );
			wp_nonce_field ( PostmanAdminController::PURGE_DATA_SLUG );
		}
		printf ( '<p><span>%s</span></p><p><span>%s</span></p>', __ ( 'This will purge all of Postman\'s settings, including account credentials and the email log.', Postman::TEXT_DOMAIN ), __ ( 'Are you sure?', Postman::TEXT_DOMAIN ) );
		$extraDeleteButtonAttributes = 'style="background-color:red;color:white"';
		if ($this->options->isNew ()) {
			$extraDeleteButtonAttributes .= ' disabled="true"';
		}
		submit_button ( $resetTitle, 'delete', 'submit', true, $extraDeleteButtonAttributes );
		print '</form>';
		print '</section>';
		print '</div>';
	}
	
	/**
	 */
	private function printDeliveryDetails() {
		$currentTransport = PostmanTransportRegistry::getInstance ()->getActiveTransport ();
		$deliveryDetails = $currentTransport->getDeliveryDetails ( $this->options );
		printf ( '<p style="margin:0 10px"><span>%s</span></p>', $deliveryDetails );
	}
	
	/**
	 */
	private function displayTopNavigation() {
		$bindResult = apply_filters ( 'postman_wp_mail_bind_status', null );
		$bound = $bindResult ['bound'];
		screen_icon ();
		print '<div id="postman-main-menu" class="welcome-panel">';
		print '<div class="welcome-panel-content">';
		print '<div class="welcome-panel-column-container">';
		print '<div class="welcome-panel-column">';
		printf ( '<h4>%s</h4>', __ ( 'Configuration', Postman::TEXT_DOMAIN ) );
		print '<ul>';
		$url = apply_filters ( 'postman_get_setup_wizard_url', null );
		printf ( '<li><a href="%s" class="welcome-icon setup-wizard">%s</a></li>', $url, __ ( 'Setup Wizard', Postman::TEXT_DOMAIN ) );
		// Grant OAuth 2.0 permission with Google
		PostmanTransportRegistry::getInstance ()->getSelectedTransport ()->printActionMenuItem ();
		
		// import-export-reset menu item
		if (! $this->options->isNew () || true) {
			$purgeLinkPattern = '<li><a href="%1$s" class="welcome-icon oauth-authorize">%2$s</a></li>';
		} else {
			$purgeLinkPattern = '<li>%2$s</li>';
		}
		$importTitle = __ ( 'Import', Postman::TEXT_DOMAIN );
		$exportTile = __ ( 'Export', Postman::TEXT_DOMAIN );
		$resetTitle = __ ( 'Reset', Postman::TEXT_DOMAIN );
		$importExportReset = sprintf ( '%s/%s/%s', $importTitle, $exportTile, $resetTitle );
		printf ( $purgeLinkPattern, PostmanUtils::getSettingsPageUrl () . '&subpage=network/reset', sprintf ( '%s', $importExportReset ) );
		
		print '</ul>';
		print '</div>';
		print '<div class="welcome-panel-column">';
		printf ( '<h4>%s</h4>', _x ( 'Delivery', 'Main Menu', Postman::TEXT_DOMAIN ) );
		print '<ul>';
		
		if ($bound) {
			$url = apply_filters ( 'postman_get_send_test_email_url', null );
			printf ( '<li><a href="%s" class="welcome-icon send_test_email">%s</a></li>', $url, __ ( 'Send a Test Email', Postman::TEXT_DOMAIN ) );
		} else {
			printf ( '<li><div class="welcome-icon send_test_email">%s</div></li>', __ ( 'Send a Test Email', Postman::TEXT_DOMAIN ) );
		}
		$url = apply_filters ( 'postman_get_email_log_url', null );
		printf ( '<li><a href="%s">%s</a></li>', $url, __ ( 'Email Log', 'postman-smtp' ) );
		
		print '</ul>';
		print '</div>';
		print '<div class="welcome-panel-column welcome-panel-last">';
		printf ( '<h4>%s</h4>', _x ( 'Troubleshooting', 'Main Menu', Postman::TEXT_DOMAIN ) );
		print '<ul>';
		$url = apply_filters ( 'postman_get_connectivity_test_url', null );
		printf ( '<li><a href="%s" class="welcome-icon run-port-test">%s</a></li>', $url, __ ( 'SMTP Server Connectivity Test', Postman::TEXT_DOMAIN ) );
		$url = sprintf ( '%s&subpage=%s', PostmanUtils::getSettingsPageUrl (), PostmanConfigurationController::DIAGNOSTICS_SUBPAGE_SLUG );
		printf ( '<li><a href="%s" class="welcome-icon run-port-test">%s</a></li>', $url, sprintf ( '%s/%s', __ ( 'Diagnostic Test', Postman::TEXT_DOMAIN ), __ ( 'Online Support', Postman::TEXT_DOMAIN ) ) );
		print '</ul></div></div></div></div>';
	}
	
	/**
	 */
	public function outputWizardContent() {
		// Set default values for input fields
		$this->options->setMessageSenderEmailIfEmpty ( wp_get_current_user ()->user_email );
		$this->options->setMessageSenderNameIfEmpty ( wp_get_current_user ()->display_name );
		
		// construct Wizard
		print '<div class="wrap">';
		
		PostmanViewController::outputChildPageHeader ( __ ( 'Email Setup Wizard', Postman::TEXT_DOMAIN ) );
		
		if (PostmanUtils::isPageNetworkAdmin ()) {
			// from http://wordpress.stackexchange.com/questions/16474/how-to-add-field-for-new-site-wide-option-on-network-settings-screen
			printf ( '<form id="postman_wizard" method="post" action="%s">', admin_url ( 'admin-post.php' ) );
			printf ( '<input type="hidden" name="action" value="%s" />', PostmanConfigurationController::SAVE_NETWORK_SETTINGS_SLUG );
			wp_nonce_field ( PostmanConfigurationController::SAVE_NETWORK_SETTINGS_SLUG );
		} else {
			printf ( '<form id="postman_wizard" method="post" action="%s">', get_admin_url () . 'options.php' );
			// This prints out all hidden setting fields
			settings_fields ( PostmanSettingsRegistry::SETTINGS_GROUP_NAME );
		}
		
		// account tab
		
		// message tab
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PREVENT_MESSAGE_SENDER_EMAIL_OVERRIDE, $this->options->isPluginSenderEmailEnforced () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PREVENT_MESSAGE_SENDER_NAME_OVERRIDE, $this->options->isPluginSenderNameEnforced () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::REPLY_TO, $this->options->getReplyTo () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_TO_RECIPIENTS, $this->options->getForcedToRecipients () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_CC_RECIPIENTS, $this->options->getForcedCcRecipients () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::FORCED_BCC_RECIPIENTS, $this->options->getForcedBccRecipients () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::ADDITIONAL_HEADERS, $this->options->getAdditionalHeaders () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::DISABLE_EMAIL_VALIDAITON, $this->options->isEmailValidationDisabled () );
		
		// logging tab
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::MAIL_LOG_ENABLED_OPTION, $this->options->getMailLoggingEnabled () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::MAIL_LOG_MAX_ENTRIES, $this->options->getMailLoggingMaxEntries () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSCRIPT_SIZE, $this->options->getTranscriptSize () );
		
		// advanced tab
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::CONNECTION_TIMEOUT, $this->options->getConnectionTimeout () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::READ_TIMEOUT, $this->options->getReadTimeout () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::LOG_LEVEL, $this->options->getLogLevel () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::RUN_MODE, $this->options->getRunMode () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::STEALTH_MODE, $this->options->isStealthModeEnabled () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TEMPORARY_DIRECTORY, $this->options->getTempDirectory () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TEMPORARY_DIRECTORY, $this->options->getTempDirectory () );
		
		// multisite
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::SUBSITES_ACCOUNT_UNDER_NETWORK_CONTROL, $this->options->isSubsiteAccountSettingsUnderNetworkControl () );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]" value="%3$s" />', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::SUBSITES_MESSAGE_UNDER_NETWORK_CONTROL, $this->options->isSubsiteMessageSettingsUnderNetworkControl () );
		
		// Wizard Step 0
		printf ( '<h5>%s</h5>', _x ( 'Import Configuration', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		printf ( '<legend>%s</legend>', _x ( 'Import configuration from another plugin?', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'If you had a working configuration with another Plugin, the Setup Wizard can begin with those settings.', Postman::TEXT_DOMAIN ) );
		print '<table class="input_auth_type">';
		printf ( '<tr><td><input type="radio" id="import_none" name="input_plugin" value="%s" checked="checked"></input></td><td><label> %s</label></td></tr>', 'none', __ ( 'None', Postman::TEXT_DOMAIN ) );
		
		if ($this->importableConfiguration->isImportAvailable ()) {
			foreach ( $this->importableConfiguration->getAvailableOptions () as $options ) {
				printf ( '<tr><td><input type="radio" name="input_plugin" value="%s"/></td><td><label> %s</label></td></tr>', $options->getPluginSlug (), $options->getPluginName () );
			}
		}
		print '</table>';
		print '</fieldset>';
		
		// Wizard Step 1
		printf ( '<h5>%s</h5>', _x ( 'Sender Details', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		printf ( '<legend>%s</legend>', _x ( 'Who is the mail coming from?', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'Enter the email address and name you\'d like to send mail as.', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'Please note that to prevent abuse, many email services will <em>not</em> let you send from an email address other than the one you authenticate with.', Postman::TEXT_DOMAIN ) );
		printf ( '<label for="postman_options[sender_email]">%s</label>', __ ( 'Email Address', Postman::TEXT_DOMAIN ) );
		print $this->settingsRegistry->from_email_callback ();
		print '<br/>';
		printf ( '<label for="postman_options[sender_name]">%s</label>', __ ( 'Name', Postman::TEXT_DOMAIN ) );
		print $this->settingsRegistry->sender_name_callback ();
		print '</fieldset>';
		
		// Wizard Step 2
		printf ( '<h5>%s</h5>', __ ( 'Outgoing Mail Server Hostname', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		$data = '';
		apply_filters ( 'postman_print_wizard_mail_server_hostname', $data );
		print '</fieldset>';
		
		// Wizard Step 3
		printf ( '<h5>%s</h5>', __ ( 'SMTP Server Connectivity Test', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		printf ( '<legend>%s</legend>', __ ( 'How will the connection to the mail server be established?', Postman::TEXT_DOMAIN ) );
		printf ( '<p>%s</p>', __ ( 'Your connection settings depend on what your email service provider offers, and what your WordPress host allows.', Postman::TEXT_DOMAIN ) );
		printf ( '<p id="connectivity_test_status">%s: <span id="port_test_status">%s</span></p>', __ ( 'SMTP Server Connectivity Test', Postman::TEXT_DOMAIN ), _x ( 'Ready', 'TCP Port Test Status', Postman::TEXT_DOMAIN ) );
		printf ( '<p class="ajax-loader" style="display:none"><img src="%s"/></p>', plugins_url ( 'postman-smtp/style/ajax-loader.gif' ) );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::TRANSPORT_TYPE );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::PORT );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::SECURITY_TYPE );
		printf ( '<input type="hidden" id="input_%2$s" name="%1$s[%2$s]">', PostmanOptions::POSTMAN_OPTIONS, PostmanOptions::AUTHENTICATION_TYPE );
		print '<p id="wizard_recommendation"></p>';
		/* Translators: Where %1$s is the socket identifier and %2$s is the authentication type */
		printf ( '<p class="user_override" style="display:none"><label><span>%s:</span></label> <table id="user_socket_override" class="user_override"></table></p>', _x ( 'Socket', 'A socket is the network term for host and port together', Postman::TEXT_DOMAIN ) );
		printf ( '<p class="user_override" style="display:none"><label><span>%s:</span></label> <table id="user_auth_override" class="user_override"></table></p>', __ ( 'Authentication', Postman::TEXT_DOMAIN ) );
		print ('<p><span id="smtp_mitm" style="display:none; background-color:yellow"></span></p>') ;
		$warning = __ ( 'Warning', Postman::TEXT_DOMAIN );
		$clearCredentialsWarning = __ ( 'This configuration option will send your authorization credentials in the clear.', Postman::TEXT_DOMAIN );
		printf ( '<p id="smtp_not_secure" style="display:none"><span style="background-color:yellow">%s: %s</span></p>', $warning, $clearCredentialsWarning );
		print '</fieldset>';
		
		// Wizard Step 4
		printf ( '<h5>%s</h5>', __ ( 'Authentication', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		printf ( '<legend>%s</legend>', __ ( 'How will you prove your identity to the mail server?', Postman::TEXT_DOMAIN ) );
		$data = '';
		apply_filters ( 'postman_print_wizard_authentication_step', $data );
		print '</fieldset>';
		
		// Wizard Step 5
		printf ( '<h5>%s</h5>', _x ( 'Finish', 'The final step of the Wizard', Postman::TEXT_DOMAIN ) );
		print '<fieldset>';
		printf ( '<legend>%s</legend>', _x ( 'You\'re Done!', 'Wizard Step Title', Postman::TEXT_DOMAIN ) );
		print '<section>';
		printf ( '<p>%s</p>', __ ( 'Click Finish to save these settings, then:', Postman::TEXT_DOMAIN ) );
		print '<ul style="margin-left: 20px">';
		printf ( '<li class="wizard-auth-oauth2">%s</li>', __ ( 'Grant OAuth 2.0 permission with the Email Provider for Postman to send email and', Postman::TEXT_DOMAIN ) );
		printf ( '<li>%s</li>', __ ( 'Send yourself a Test Email to make sure everything is working!', Postman::TEXT_DOMAIN ) );
		print '</ul>';
		print '</section>';
		print '</fieldset>';
		print '</form>';
		print '</div>';
	}
}

/**
 *
 * @author jasonhendriks
 *        
 */
class PostmanGetHostnameByEmailAjaxController extends PostmanAbstractAjaxHandler {
	const IS_GOOGLE_PARAMETER = 'is_google';
	function __construct() {
		parent::__construct ();
		PostmanUtils::registerAjaxHandler ( 'postman_check_email', $this, 'getAjaxHostnameByEmail' );
	}
	/**
	 * This Ajax function retrieves the smtp hostname for a give e-mail address
	 */
	function getAjaxHostnameByEmail() {
		$goDaddyHostDetected = $this->getBooleanRequestParameter ( 'go_daddy' );
		$email = $this->getRequestParameter ( 'email' );
		$d = new PostmanSmtpDiscovery ( $email );
		$smtp = $d->getSmtpServer ();
		$this->logger->debug ( 'given email ' . $email . ', smtp server is ' . $smtp );
		$this->logger->trace ( $d );
		if ($goDaddyHostDetected && ! $d->isGoogle) {
			// override with the GoDaddy SMTP server
			$smtp = 'relay-hosting.secureserver.net';
			$this->logger->debug ( 'detected GoDaddy SMTP server, smtp server is ' . $smtp );
		}
		$response = array (
				'hostname' => $smtp,
				self::IS_GOOGLE_PARAMETER => $d->isGoogle,
				'is_go_daddy' => $d->isGoDaddy,
				'is_well_known' => $d->isWellKnownDomain 
		);
		$this->logger->trace ( $response );
		wp_send_json_success ( $response );
	}
}
class PostmanManageConfigurationAjaxHandler extends PostmanAbstractAjaxHandler {
	function __construct() {
		parent::__construct ();
		PostmanUtils::registerAjaxHandler ( 'manual_config', $this, 'getManualConfigurationViaAjax' );
		PostmanUtils::registerAjaxHandler ( 'get_wizard_configuration_options', $this, 'getWizardConfigurationViaAjax' );
	}
	
	/**
	 * Handle a Advanced Configuration request with Ajax
	 *
	 * @throws Exception
	 */
	function getManualConfigurationViaAjax() {
		if ($this->logger->isDebug ()) {
			$this->logger->debug ( 'getManualConfigurationViaAjax()' );
		}
		$queryTransportType = $this->getTransportTypeFromRequest ();
		$queryAuthType = $this->getAuthenticationTypeFromRequest ();
		$queryHostname = $this->getHostnameFromRequest ();
		
		// the outgoing server hostname is only required for the SMTP Transport
		// the Gmail API transport doesn't use an SMTP server
		$transport = PostmanTransportRegistry::getInstance ()->getTransport ( $queryTransportType );
		if (! $transport) {
			throw new Exception ( 'Unable to find transport ' . $queryTransportType );
		}
		
		// create the response
		$response = $transport->populateConfiguration ( $queryHostname );
		$response ['referer'] = 'manual_config';
		
		// set the display_auth to oauth2 if the transport needs it
		if ($transport->isOAuthUsed ( $queryAuthType )) {
			$response ['display_auth'] = 'oauth2';
			$this->logger->debug ( 'ajaxRedirectUrl answer display_auth:' . $response ['display_auth'] );
		}
		$this->logger->trace ( $response );
		wp_send_json_success ( $response );
	}
	
	/**
	 * Once the Port Tests have run, the results are analyzed.
	 * The Transport place bids on the sockets and highest bid becomes the recommended
	 * The UI response is built so the user may choose a different socket with different options.
	 */
	function getWizardConfigurationViaAjax() {
		$this->logger->debug ( 'in getWizardConfiguration' );
		$originalSmtpServer = $this->getRequestParameter ( 'original_smtp_server' );
		$queryHostData = $this->getHostDataFromRequest ();
		$sockets = array ();
		foreach ( $queryHostData as $id => $datum ) {
			array_push ( $sockets, new PostmanWizardSocket ( $datum ) );
		}
		$this->logger->error ( $sockets );
		$userPortOverride = $this->getUserPortOverride ();
		$userAuthOverride = $this->getUserAuthOverride ();
		
		// determine a configuration recommendation
		$winningRecommendation = $this->getWinningRecommendation ( $sockets, $userPortOverride, $userAuthOverride, $originalSmtpServer );
		if ($this->logger->isTrace ()) {
			$this->logger->trace ( 'winning recommendation:' );
			$this->logger->trace ( $winningRecommendation );
		}
		
		// create the reponse
		$response = array ();
		$configuration = array ();
		$response ['referer'] = 'wizard';
		if (isset ( $userPortOverride ) || isset ( $userAuthOverride )) {
			$configuration ['user_override'] = true;
		}
		
		if (isset ( $winningRecommendation )) {
			
			// create an appropriate (theoretical) transport
			$transport = PostmanTransportRegistry::getInstance ()->getTransport ( $winningRecommendation ['transport'] );
			
			// create user override menu
			$overrideMenu = $this->createOverrideMenus ( $sockets, $winningRecommendation, $userPortOverride, $userAuthOverride );
			if ($this->logger->isTrace ()) {
				$this->logger->trace ( 'override menu:' );
				$this->logger->trace ( $overrideMenu );
			}
			
			$queryHostName = $winningRecommendation ['hostname'];
			if ($this->logger->isDebug ()) {
				$this->logger->debug ( 'Getting scribe for ' . $queryHostName );
			}
			$generalConfig1 = $transport->populateConfiguration ( $queryHostName );
			$generalConfig2 = $transport->populateConfigurationFromRecommendation ( $winningRecommendation );
			$configuration = array_merge ( $configuration, $generalConfig1, $generalConfig2 );
			$response ['override_menu'] = $overrideMenu;
			$response ['configuration'] = $configuration;
			if ($this->logger->isTrace ()) {
				$this->logger->trace ( 'configuration:' );
				$this->logger->trace ( $configuration );
				$this->logger->trace ( 'response:' );
				$this->logger->trace ( $response );
			}
			wp_send_json_success ( $response );
		} else {
			$url = apply_filters ( 'postman_get_connectivity_test_url', null );
			/* translators: where %s is the URL to the Connectivity Test page */
			$configuration ['message'] = sprintf ( __ ( 'Postman can\'t find any way to send mail on your system. Run a <a href="%s">connectivity test</a>.', Postman::TEXT_DOMAIN ), $url );
			$response ['configuration'] = $configuration;
			if ($this->logger->isTrace ()) {
				$this->logger->trace ( 'configuration:' );
				$this->logger->trace ( $configuration );
			}
			wp_send_json_error ( $response );
		}
	}
	
	/**
	 * // for each successful host/port combination
	 * // ask a transport if they support it, and if they do at what priority is it
	 * // configure for the highest priority you find
	 *
	 * @param unknown $queryHostData        	
	 * @return unknown
	 */
	private function getWinningRecommendation($sockets, $userSocketOverride, $userAuthOverride, $originalSmtpServer) {
		foreach ( $sockets as $socket ) {
			$winningRecommendation = $this->getWin ( $socket, $userSocketOverride, $userAuthOverride, $originalSmtpServer );
			$this->logger->error ( $socket->label );
		}
		return $winningRecommendation;
	}
	
	/**
	 *
	 * @param PostmanSocket $socket        	
	 * @param unknown $userSocketOverride        	
	 * @param unknown $userAuthOverride        	
	 * @param unknown $originalSmtpServer        	
	 * @return Ambigous <NULL, unknown, string>
	 */
	private function getWin(PostmanWizardSocket $socket, $userSocketOverride, $userAuthOverride, $originalSmtpServer) {
		static $recommendationPriority = - 1;
		static $winningRecommendation = null;
		$available = $socket->success;
		if ($available) {
			$this->logger->debug ( sprintf ( 'Asking for judgement on %s:%s', $socket->hostname, $socket->port ) );
			$recommendation = PostmanTransportRegistry::getInstance ()->getRecommendation ( $socket, $userAuthOverride, $originalSmtpServer );
			$recommendationId = sprintf ( '%s_%s', $socket->hostname, $socket->port );
			$recommendation ['id'] = $recommendationId;
			$this->logger->debug ( sprintf ( 'Got a recommendation: [%d] %s', $recommendation ['priority'], $recommendationId ) );
			if (isset ( $userSocketOverride )) {
				if ($recommendationId == $userSocketOverride) {
					$winningRecommendation = $recommendation;
					$this->logger->debug ( sprintf ( 'User chosen socket %s is the winner', $recommendationId ) );
				}
			} elseif ($recommendation && $recommendation ['priority'] > $recommendationPriority) {
				$recommendationPriority = $recommendation ['priority'];
				$winningRecommendation = $recommendation;
			}
			$socket->label = $recommendation ['label'];
		}
		return $winningRecommendation;
	}
	
	/**
	 *
	 * @param unknown $queryHostData        	
	 * @return multitype:
	 */
	private function createOverrideMenus($sockets, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		$overrideMenu = array ();
		foreach ( $sockets as $socket ) {
			$overrideItem = $this->createOverrideMenu ( $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride );
			if ($overrideItem != null) {
				$overrideMenu [$socket->id] = $overrideItem;
			}
		}
		
		// sort
		krsort ( $overrideMenu );
		$sortedMenu = array ();
		foreach ( $overrideMenu as $menu ) {
			array_push ( $sortedMenu, $menu );
		}
		
		return $sortedMenu;
	}
	
	/**
	 *
	 * @param PostmanWizardSocket $socket        	
	 * @param unknown $winningRecommendation        	
	 * @param unknown $userSocketOverride        	
	 * @param unknown $userAuthOverride        	
	 */
	private function createOverrideMenu(PostmanWizardSocket $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		if ($socket->success) {
			$transport = PostmanTransportRegistry::getInstance ()->getTransport ( $socket->transport );
			$this->logger->debug ( sprintf ( 'Transport %s is building the override menu for socket', $transport->getSlug () ) );
			$overrideItem = $transport->createOverrideMenu ( $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride );
			return $overrideItem;
		}
		return null;
	}
	
	/**
	 */
	private function getTransportTypeFromRequest() {
		return $this->getRequestParameter ( 'transport' );
	}
	
	/**
	 */
	private function getHostnameFromRequest() {
		return $this->getRequestParameter ( 'hostname' );
	}
	
	/**
	 */
	private function getAuthenticationTypeFromRequest() {
		return $this->getRequestParameter ( 'auth_type' );
	}
	
	/**
	 */
	private function getHostDataFromRequest() {
		return $this->getRequestParameter ( 'host_data' );
	}
	
	/**
	 */
	private function getUserPortOverride() {
		return $this->getRequestParameter ( 'user_port_override' );
	}
	
	/**
	 */
	private function getUserAuthOverride() {
		return $this->getRequestParameter ( 'user_auth_override' );
	}
}
class PostmanImportConfigurationAjaxController extends PostmanAbstractAjaxHandler {
	private $options;
	/**
	 * Constructor
	 *
	 * @param PostmanOptions $options        	
	 */
	function __construct(PostmanOptions $options) {
		parent::__construct ();
		$this->options = $options;
		PostmanUtils::registerAjaxHandler ( 'import_configuration', $this, 'getConfigurationFromExternalPluginViaAjax' );
	}
	
	/**
	 * This function extracts configuration details form a competing SMTP plugin
	 * and pushes them into the Postman configuration screen.
	 */
	function getConfigurationFromExternalPluginViaAjax() {
		$importableConfiguration = new PostmanImportableConfiguration ();
		$plugin = $this->getRequestParameter ( 'plugin' );
		$this->logger->debug ( 'Looking for config=' . $plugin );
		foreach ( $importableConfiguration->getAvailableOptions () as $this->options ) {
			if ($this->options->getPluginSlug () == $plugin) {
				$this->logger->debug ( 'Sending configuration response' );
				$response = array (
						PostmanOptions::MESSAGE_SENDER_EMAIL => $this->options->getMessageSenderEmail (),
						PostmanOptions::MESSAGE_SENDER_NAME => $this->options->getMessageSenderName (),
						PostmanOptions::HOSTNAME => $this->options->getHostname (),
						PostmanOptions::PORT => $this->options->getPort (),
						PostmanOptions::AUTHENTICATION_TYPE => $this->options->getAuthenticationType (),
						PostmanOptions::SECURITY_TYPE => $this->options->getEncryptionType (),
						PostmanOptions::BASIC_AUTH_USERNAME => $this->options->getUsername (),
						PostmanOptions::BASIC_AUTH_PASSWORD => $this->options->getPassword (),
						'success' => true 
				);
				break;
			}
		}
		if (! isset ( $response )) {
			$response = array (
					'success' => false 
			);
		}
		wp_send_json ( $response );
	}
}
