<?php
if (! class_exists ( 'PostmanViewController' )) {
	class PostmanViewController {
		private $logger;
		private $rootPluginFilenameAndPath;
		private $options;
		private $authorizationToken;
		private $oauthScribe;
		private $importableConfiguration;
		private $adminController;
		
		// style sheets and scripts
		const POSTMAN_STYLE = 'postman_style';
		const JQUERY_SCRIPT = 'jquery';
		const POSTMAN_SCRIPT = 'postman_script';
		
		//
		const BACK_ARROW_SYMBOL = '&#11013;';
		
		/**
		 * Constructor
		 *
		 * @param PostmanOptions $options        	
		 * @param PostmanOAuthToken $authorizationToken        	
		 * @param PostmanConfigTextHelper $oauthScribe        	
		 */
		function __construct($rootPluginFilenameAndPath, PostmanOptions $options, PostmanOAuthToken $authorizationToken, PostmanConfigTextHelper $oauthScribe, PostmanAdminController $adminController) {
			$this->options = $options;
			$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
			$this->authorizationToken = $authorizationToken;
			$this->oauthScribe = $oauthScribe;
			$this->adminController = $adminController;
			$this->logger = new PostmanLogger ( get_class ( $this ) );
			// PostmanUtils::registerAdminMenu ( $this, 'generateDefaultContent' );
			PostmanUtils::registerAdminMenu ( $this, 'addPurgeDataSubmenu' );
			
			// initialize the scripts, stylesheets and form fields
			add_action ( 'admin_init', array (
					$this,
					'registerStylesAndScripts' 
			), 0 );
		}
		
		/**
		 * Register the Email Test screen
		 */
		public function addPurgeDataSubmenu() {
			$page = add_submenu_page ( null, sprintf ( __ ( '%s Setup', Postman::TEXT_DOMAIN ), __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ) ), __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ), Postman::MANAGE_POSTMAN_CAPABILITY_NAME, PostmanAdminController::MANAGE_OPTIONS_PAGE_SLUG, array (
					$this,
					'outputPurgeDataContent' 
			) );
			// When the plugin options page is loaded, also load the stylesheet
			add_action ( 'admin_print_styles-' . $page, array (
					$this,
					'enqueueHomeScreenStylesheet' 
			) );
		}
		function enqueueHomeScreenStylesheet() {
			wp_enqueue_style ( PostmanViewController::POSTMAN_STYLE );
			wp_enqueue_script ( 'postman_script' );
		}
		
		/**
		 * Register and add settings
		 */
		public function registerStylesAndScripts() {
			if ($this->logger->isTrace ()) {
				$this->logger->trace ( 'registerStylesAndScripts()' );
			}
			// register the stylesheet and javascript external resources
			$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
			wp_register_style ( PostmanViewController::POSTMAN_STYLE, plugins_url ( 'style/postman.css', $this->rootPluginFilenameAndPath ), null, $pluginData ['version'] );
			wp_register_style ( 'jquery_ui_style', plugins_url ( 'style/jquery-steps/jquery-ui.css', $this->rootPluginFilenameAndPath ), PostmanViewController::POSTMAN_STYLE, '1.1.0' );
			wp_register_style ( 'jquery_steps_style', plugins_url ( 'style/jquery-steps/jquery.steps.css', $this->rootPluginFilenameAndPath ), PostmanViewController::POSTMAN_STYLE, '1.1.0' );
			
			wp_register_script ( PostmanViewController::POSTMAN_SCRIPT, plugins_url ( 'script/postman.js', $this->rootPluginFilenameAndPath ), array (
					PostmanViewController::JQUERY_SCRIPT 
			), $pluginData ['version'] );
			wp_register_script ( 'sprintf', plugins_url ( 'script/sprintf/sprintf.min.js', $this->rootPluginFilenameAndPath ), null, '1.0.2' );
			wp_register_script ( 'jquery_steps_script', plugins_url ( 'script/jquery-steps/jquery.steps.min.js', $this->rootPluginFilenameAndPath ), array (
					PostmanViewController::JQUERY_SCRIPT 
			), '1.1.0' );
			wp_register_script ( 'jquery_validation', plugins_url ( 'script/jquery-validate/jquery.validate.min.js', $this->rootPluginFilenameAndPath ), array (
					PostmanViewController::JQUERY_SCRIPT 
			), '1.13.1' );
			
			wp_localize_script ( PostmanViewController::POSTMAN_SCRIPT, 'postman_ajax_msg', array (
					'bad_response' => __ ( 'An unexpected error occurred', Postman::TEXT_DOMAIN ),
					'corrupt_response' => __ ( 'Unexpected PHP messages corrupted the Ajax response', Postman::TEXT_DOMAIN ) 
			) );
			
			wp_localize_script ( 'jquery_steps_script', 'steps_current_step', 'steps_current_step' );
			wp_localize_script ( 'jquery_steps_script', 'steps_pagination', 'steps_pagination' );
			wp_localize_script ( 'jquery_steps_script', 'steps_finish', _x ( 'Finish', 'Press this button to Finish this task', Postman::TEXT_DOMAIN ) );
			wp_localize_script ( 'jquery_steps_script', 'steps_next', _x ( 'Next', 'Press this button to go to the next step', Postman::TEXT_DOMAIN ) );
			wp_localize_script ( 'jquery_steps_script', 'steps_previous', _x ( 'Previous', 'Press this button to go to the previous step', Postman::TEXT_DOMAIN ) );
			wp_localize_script ( 'jquery_steps_script', 'steps_loading', 'steps_loading' );
		}
		
		/**
		 *
		 * @param unknown $title        	
		 * @param string $slug        	
		 */
		public static function outputChildPageHeader($title, $showLink = true, $slug = '') {
			printf ( '<h2>%s</h2>', __ ( 'Postman SMTP', Postman::TEXT_DOMAIN ) );
			printf ( '<div id="postman-main-menu" class="welcome-panel %s">', $slug );
			print '<div class="welcome-panel-content">';
			print '<div class="welcome-panel-column-container">';
			print '<div class="welcome-panel-column welcome-panel-last">';
			printf ( '<h4>%s</h4>', $title );
			print '</div>';
			if ($showLink) {
				$url = apply_filters ( 'postman_get_home_url', null );
				printf ( '<p id="back_to_main_menu">%s <a id="back_to_menu_link" href="%s">%s</a></p>', self::BACK_ARROW_SYMBOL, $url, __ ( 'Go to Postman Dashboard', Postman::TEXT_DOMAIN ) );
			}
			print '</div></div></div>';
		}
	}
}
		