<?php
/*
Plugin Name: PdC-Requestmgt
Plugin URI: http://ispectors.com.com/plugins/pdc-requestmgt/
Description: management of requests for Setlr.com
Version: 0.9.9.7
Author: pdc
Author URI: http://ispectors.com/philippe-de-chabot/
Text Domain: pdcrequest
Domain Path: /languages
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	_e( 'Hi there!  I\'m just a plugin, not much I can do when called directly.', 'pdcrequest' );
	exit;
}

/**
 * set up custom post type request
 * set up custom post type help
 * define custom capabilities for managing request and help
 * add shortcodes for request and help dashboards
 * add settings page for admin
 * add js files for frontend ajax
 * add admin buttons to ban request or help, and/or user
 * play nice with relo-chat plugin
 * play nice with future rating and review system (prepare hooks and filters)
 * play nice with future gift system (prepare hooks and filters)
 */
 
class Pdc_Requestmgt {

    const CURRENT_VER = '0.9.9.6';
    const CURRENT_DB_VER = 1.5; 
     /**
      * Setup the environment for the plugin
      */
	public function bootstrap() {
            register_activation_hook( __FILE__, array( $this, 'activate' ) );
		
            require_once 'includes/classes/request-cpt.php';
            require_once 'includes/classes/setlr-translation.php';
            require_once 'includes/classes/extra-users.php';
            //require_once 'includes/classes/application-form.php';
            require_once 'includes/functions/functions.php';
            require_once 'includes/classes/apikey.php';
            //require_once 'payment_methods/method-Stripe.php';
            //require_once 'includes/classes/request-form.php';
            require_once 'includes/classes/lang.php';
            require_once 'includes/classes/lang_pairs.php';
            require_once 'includes/classes/request-status.php';
            require_once 'includes/classes/translation-status.php';
            require_once 'includes/classes/setlr-count.php';
            require_once 'includes/classes/setlr-profile.php';
            //require Stripe API wrapper
            require_once 'payment_methods/stripe/init.php';
            require_once 'includes/classes/setlr-revision.php';
            require_once 'includes/classes/setlr-revision-request.php';
            require_once 'includes/classes/revision-request-metabox.php';
            require_once 'includes/classes/setlr-rejection-request.php';
            require_once 'includes/classes/revision-calculator.php';
            require      'includes/classes/setlr-payment.php';
            require_once 'includes/classes/setlr-pricing.php';
            require_once 'includes/classes/braintree-status.php';
            require_once 'includes/classes/admin-braintree.php';
            require_once 'includes/classes/paypal-payment.php';
            require_once 'includes/classes/Setlr_Request_Profile.php';
            require_once 'includes/classes/setlr-request.php';
            require_once 'includes/classes/setlr-profile-form.php';
            
            //load braintree SDK
            require_once 'payment_methods/braintree/Braintree.php';
            require_once 'includes/classes/payment-status.php';

            add_action( 'init', array( 'Request_CPT', 'create' ) );
            add_action( 'init', array( 'Request_CPT', 'create_taxonomy' ) );
            //add_action( 'init', array( 'Request_CPT', 'create_gift_taxonomy' ) );
            add_action( 'init', array( $this, 'add_capabilities' ) );
            add_action( 'init', array( 'Setlr_Translation', 'create' ) );
            add_action( 'init', array( $this, 'check_database'));
            add_action( 'init', array( 'Setlr_Revision', 'create_revision_post_type' ) );
            add_action( 'init', array( 'Setlr_Revision_Request', 'create_revision_request_post_type' ) );
            add_action( 'init', array( 'Setlr_Rejection_Request', 'create_rejection_request_post_type' ) );
            add_action( 'init', 'call_Setlr_Payment' );
            add_action( 'plugins_loaded', array( $this, 'pdcrequest_load_textdomain' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this, 'settings_init' ) );
            
		//add_action( 'init', array( $this, 'add_capabilities' ) );
            //add_action( 'gift_add_form_fields', array( 'Request_CPT', 'add_meta_fields' ), 10, 2 );
            //add_action( 'gift_edit_form_fields', array( 'Request_CPT', 'edit_meta_fields' ), 10, 2 );
            //add_action( 'edited_gift', array( 'Request_CPT', 'save_gift_custom_meta' ), 10, 2 );  
            //add_action( 'create_gift', array( 'Request_CPT', 'save_gift_custom_meta' ), 10, 2 );
            //add_filter( 'manage_edit-gift_columns', array( 'Request_CPT', 'add_gift_value_column' ), 10, 1 );
            //add_filter( 'manage_gift_custom_column', array( 'Request_CPT', 'add_gift_value_column_content' ), 10, 3);
            add_filter( 'manage_edit-request_columns', array( 'Request_CPT', 'add_language_values_columns'), 10, 1);
            add_action( 'manage_request_posts_custom_column', array( 'Request_CPT', 'add_language_values_columns_content' ), 10, 2);
            
            add_filter( 'manage_edit-translation_columns', array( 'Setlr_Translation', 'add_language_values_column'), 10, 1 );
            add_action( 'manage_translation_posts_custom_column', array( 'Setlr_Translation', 'add_language_values_columns_content' ), 10, 2 );
            
            add_action( 'show_user_profile', array( 'Extra_Users', 'show_extra_user_fields' ) );
            add_action( 'edit_user_profile', array( 'Extra_Users', 'show_extra_user_fields' ) );
            add_action( 'personal_options_update', array( 'Extra_Users', 'update_extra_user_fields' ) );
            add_action( 'edit_user_profile_update', array( 'Extra_Users', 'update_extra_user_fields' ) );
            
            //add setlr_status_history db table
            add_action( 'plugins_loaded', array('request_status', 'create_db_table' ) );
		
            
            /* Add actions in sequence */
            //add_action( 'pdcrequest_create_new_request', array( 'Request_Form', 'render_form_new_request') );
            /* pdcrequest_validate_new_request */
            //add_action( 'admin_action_pdc_new_request', array( 'Request_Form', 'validate_request_form') );
            
            add_action( 'admin_action_pdcrequest_cancel_request', array( 'Request_CPT', 'cancel_request') );
            add_action( 'admin_action_pdcrequest_reopen_request', array( 'Request_CPT', 'reopen_request') );
            
            
            add_action( 'admin_action_pdcrequest_apply_to_request', array( $this, 'request_applied'), 10, 1 );
            
            add_action( 'admin_action_pdcrequest_update_translation', array( 'Setlr_Translation', 'update_translation'), 10, 1 );
            /*
            add_action( 'pdcrequest_open_dashboard', array( 'class', 'method'), 'priority', 'args' );
            add_action( 'pdcrequest_save_wip', array( 'class', 'method'), 'priority', 'args' );
            add_action( 'pdcrequest_translation_finished', array( 'class', 'method'), 'priority', 'args' );
            add_action( 'pdcrequest_translation_accepted', array( 'class', 'method'), 'priority', 'args' );
            add_action( 'pdcrequest_translation_need_review', array( 'class', 'method'), 'priority', 'args' );
            add_action( 'pdcrequest_translation_refused', array( 'class', 'method'), 'priority', 'args' );
            */
            
            
            add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
            
            /* admin add columns to users table */
            add_filter( 'manage_users_columns', array( 'Extra_Users', 'admin_add_columns_to_users_list' ) );
            add_filter( 'manage_users_custom_column', array( 'Extra_Users', 'admin_modify_user_table_row'), 10, 3 );
            
            
            
            
            
            /* other actions, filters and shortcodes */
            //add_action( 'admin_action_pdc_new_application', array( 'Application_Form', 'validate_form' ) );
            //add_action( 'wp_ajax_pdc_new_application', array( 'Application_Form', 'validate_form' ) );
            //add_action( 'wp_ajax_nopriv_pdc_new_application', array( 'Application_Form', 'validate_form' ) );
            //add_action( 'wp_ajax_pdcrequest_get_request_form_by_type', array( 'Request_Form', 'ajax_request_form' ) );
            //add_action( 'wp_ajax_nopriv_pdcrequest_get_request_form_by_type', array( 'Request_Form', 'ajax_request_form' ) );
            add_action( 'wp_ajax_pdcrequest_get_request_form_by_type', array( 'Setlr_Request', 'ajax_request_form' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_get_request_form_by_type', array( 'Setlr_Request', 'ajax_request_form' ) );
            add_action( 'admin_post_pdc_update_profile', array( 'Setlr_Profile', 'validate_profile' ) );
		//add_action( 'wp_ajax_pdc_update_profile', array( $this, 'validate_profile' ) );
		//add_action( 'wp_ajax_nopriv_pdc_update_profile', array( $this, 'validate_profile' ) );
		
            add_action( 'admin_action_pdc_save_updated_profile', array( 'Setlr_Profile', 'update_profile'), 10, 2 );
		
            //require_once( 'includes/classes/request-form.php' );
            //add_shortcode( 'pdcrequest_form', array( 'Request_Form', 'render_form_new_request' ) );
            add_shortcode( 'pdcrequest_form', array( 'Setlr_Request', 'render_form_new_request' ) );
            add_shortcode( 'pdcrequest_list', array( $this, 'list_requests' ) );
            add_shortcode( 'pdcrequest_own_list', array( $this, 'list_own_requests' ) );
            add_shortcode( 'apply_button', array( $this, 'maybe_show_apply_button' ) );
            add_shortcode( 'pdcrequest_list_applications', array( $this, 'list_applications' ) );
            add_shortcode( 'pdcrequest_all_lang_pairs', array( 'langpairs', 'get_langpairs' ) );
            add_shortcode( 'pdcrequest_list_revisions', array( $this, 'list_revisions') );
           // add_shortcode( 'pdcrequest_new_request_profile', array( $this, 'create_new_request_profile' ) );
            //add_shortcode( 'pdcrequest_translation_dashboard', 'pdcrequest_translation_dashboard');
		
            //add_action( 'admin_action_pdc_new_request', array( $this, 'validate_form' ) );
            add_action( 'wp_ajax_pdc_new_request', array( $this, 'validate_form' ) );
            add_action( 'wp_ajax_nopriv_pdc_new_request', array( $this, 'validate_form' ) );
            
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            //add_action( 'wp_enqueue_scripts', array( 'Setlr_Payment', 'enqueue_scripts' ) );
            
            add_action( 'wp_ajax_pdcrequest_word_count', array( $this, 'ajax_word_count') );
            add_action( 'wp_ajax_nopriv_pdcrequest_word_count', array( $this, 'ajax_word_count') );
            
            //methodStripe::set_actions();
            /* action for update_revision form to save data in db */
            add_action( 'admin_action_validate_revision_request', array('Setlr_Revision_Request', 'validate_revision_request') );
            add_action( 'admin_action_validate_rejection_request', array('Setlr_Rejection_Request', 'validate_rejection_request') );
            
            
            add_action( 'admin_action_process_stripe_token', array('methodStripe', 'create_customer') );
            add_action( 'add_meta_boxes_request', array($this, 'add_metabox_from_to' ) );
            add_action( 'add_meta_boxes_translation', array($this, 'add_metabox_from_to' ) );
            add_action( 'save_post', array( $this, 'save_metabox_from_to' ) );
            
            
            add_action( 'wp_ajax_pdcrequest_get_locales', array('lang','render_locales_form'));
            add_action( 'wp_ajax_pdcrequest_get_locales_for_request', array( 'lang', 'ajax_request_locales_form' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_get_locales_for_request', array( 'lang', 'ajax_request_locales_form' ) );
            add_action( 'wp_ajax_pdcrequest_get_short_profile', array( 'Extra_Users', 'ajax_short_profile' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_get_short_profile', array( 'Extra_Users', 'ajax_short_profile' ) );
            
            add_filter( 'add_menu_classes', 'pdcrequest_show_pending_number');
            
            add_action( 'pdcrequest_payment_form', array( 'Setlr_Payment', 'render_payment_form' ), 10, 4 );
            add_action( 'pdcrequest_project_quote', array( 'Setlr_Pricing', 'render_update_quote_form'), 10, 2 );
            
            add_action( 'wp_ajax_get_braintree_client_token', array( 'Setlr_Payment', 'generate_client_token') );
            add_action( 'wp_ajax_nopriv_get_braintree_client_token', array( 'Setlr_Payment', 'generate_client_token' ) );
            
            add_action( 'admin_action_make_payment', array( 'Setlr_Payment', 'do_payment') );
            
            add_action( 'admin_notices', array( $this, 'sandbox_admin_notice') );
            add_action( 'pdcrequest_settle_transaction', array( 'Setlr_Payment', 'settle_transaction' ), 10, 1 );
            add_action( 'pdcrequest_refund_transaction', array( 'Setlr_Payment', 'refund_transaction' ), 10, 2 );
            add_action( 'settlement_update_status', array('Braintree_Status', 'update_status'), 10, 2 );
            
            add_action( 'wp_ajax_pdcrequest_question_countries_form', array( 'Setlr_Profile', 'question_countries_form'));
            
            //add_action( 'admin_action_pdc_new_request_profile', array( $this, 'validate_request_profile' ) );
            //add_action( 'wp_ajax_nopriv_pdcrequest_validate_request_profile', array( 'Pdc_Requestmgt', 'ajax_validate_request_profile'));
            //add_action( 'wp_ajax_nopriv_pdcrequest_validate_full_request_profile', array( 'Pdc_Requestmgt', 'ajax_validate_full_profile') );
            
            add_action( 'admin_action_pdc_update_request', array( 'Setlr_Request', 'validate_updated_request' ) );
            add_action( 'wp_ajax_pdc_request', array( 'Setlr_Request', 'validate_request_form' ) );
            add_action( 'wp_ajax_nopriv_pdc_request', array( 'Setlr_Request', 'validate_request_form' ) );
            //add_action( 'wp_enqueue_scripts', array( 'Setlr_Request', 'enqueue_scripts' ) );
            
            add_action( 'wp_ajax_pdcrequest_validate_request', array( 'Setlr_Request', 'ajax_get_quote' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_validate_request', array( 'Setlr_Request', 'ajax_get_quote' ) );
            
            add_action( 'wp_ajax_pdcrequest_validate_full_request', array( 'Setlr_Request', 'ajax_full_validate' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_validate_full_request', array( 'Setlr_Request', 'ajax_full_validate' ) );
            
            add_action( 'wp_ajax_pdcrequest_register_customer', array( 'Setlr_Profile_Form', 'ajax_register_customer') );
            add_action( 'wp_ajax_nopriv_pdcrequest_register_customer', array( 'Setlr_Profile_Form', 'ajax_register_customer') );
            
            add_action( 'wp_ajax_pdcrequest_login_customer', array( 'Setlr_Profile_Form', 'ajax_validate_customer' ) );
            add_action( 'wp_ajax_nopriv_pdcrequest_login_customer', array( 'Setlr_Profile_Form', 'ajax_validate_customer' ) );
            
            add_action( 'pdcrequest_update_project_payment', array( 'Setlr_Request', 'update_project_payment' ), 3 );
            
    }

	public function activate() {
            $this->init_options();
            // Flush rewrite rules so that users can access custom post types on the
            // front-end right away
            require_once 'includes/classes/request-cpt.php';
            $request_cpt = new Request_CPT();
            require_once 'includes/classes/setlr-translation.php';
            $application_cpt = new Setlr_Translation();
            
            require_once 'includes/classes/setlr-revision-request.php';
            Setlr_Revision_Request::create_revision_request_post_type();
            
            require_once 'includes/classes/setlr-rejection-request.php';
            Setlr_Rejection_Request::create_rejection_request_post_type();
            //$this->register_post_types();	
            flush_rewrite_rules();
            
            //install custom db tables
            require_once 'includes/classes/setlr-payment.php';
            Setlr_Payment::install_payment_table();
            require_once 'includes/classes/braintree-status.php';
            Braintree_Status::install_settlement_table();
            //write_log( 'pdcrequestmgt_activate');
            
	}
	
        
	    /**
     * Initialize default option values
     */
    public function init_options() {
	update_option( 'requestmgt_version', self::CURRENT_VER );
        add_option( 'requestmgt_db_version', self::CURRENT_DB_VER );
        add_option( 'requestmgt_posts_per_page', 10 );
        add_option( 'requestmgt_show_welcome_page', true );
		
    }
	
    
	
	public function add_capabilities() {
            global $wp_roles;
		
            //add_role( 'setler', 'Setler', array( 'read' => true, 'level_0' => true ) );
            add_role( 'helper', __( 'Helper', 'pdcrequest' ), array( 'read' => true, 'level_0' => true ) );
            add_role( 'customer', __('Customer', 'pdcrequest' ), array( 'read' => true, 'level_0' => true ) );
            add_role( 'senior_helper', __('Senior Helper', 'pdcrequest'), array( 'read' => true, 'level_0'  => true ) );
            
            remove_role( 'setler');
            
            /* admin & helpers */
            $user_roles = array( 'administrator', 'helper' );
            foreach ( $user_roles as $user_role ) :
		// gets the author role
		$role = get_role( $user_role );
	
		// This only works, because it accesses the class instance.
		// would allow the author to edit others' posts for current theme only
		$role->add_cap( 'pdc_application_post' );
                $role->add_cap( 'helper');
            endforeach;
            
            /* admin & customers */
            $user_roles = array( 'administrator', 'customer' );
            foreach ( $user_roles as $user_role ) :
		// gets the author role
		$role = get_role( $user_role );
	
		// This only works, because it accesses the class instance.
		// would allow the author to edit others' posts for current theme only
		$role->add_cap( 'pdc_request_post' ); 
                $role->add_cap( 'customer');
            endforeach;
            
            /* admin & senior helpers */
            $user_roles = array( 'administrator', 'senior_helper' );
            foreach ( $user_roles as $user_role ) :
		// gets the author role
		$role = get_role( $user_role );
	
		// This only works, because it accesses the class instance.
		// would allow the author to edit others' posts for current theme only
		$role->add_cap( 'pdc_request_post' ); 
                $role->add_cap( 'helper');
                $role->add_cap( 'senior_helper');
                $role->add_cap( 'can_review_translations' );
            endforeach;
	
	}
	
       

	public function enqueue_scripts() {
		wp_register_script( 'dashboard_profile', plugins_url( 'pdc-requestmgt/assets/js/newrequest-validate.js' ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'dashboard_profile', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		
                //wp_enqueue_script( 'pdcrequest_word_count', plugins_url( 'pdc-requestmgt/assets/js/pdcrequest-word-count.js' ), array( 'jquery' ), '1.0', true );
		//wp_localize_script( 'pdcrequest_word_count', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		//if ( is_post_type( 'request' ) ) :
		wp_enqueue_style( 'pdcrequest_style', plugins_url( 'pdc-requestmgt/assets/css/style.css' ), array(), '1.0', 'screen' );
		//endif;
                wp_enqueue_script( 'gilly3_word_count', plugins_url( 'pdc-requestmgt/assets/js/gilly3-word-count.js' ), array( 'jquery' ), '1.0', true );        
                
                //script will be enqueued in shortcode
                wp_register_script( 'request_lang_locale', plugins_url( 'pdc-requestmgt/assets/js/pdcrequest-request-lang-locales.js' ), array( 'jquery' ), '0.2' );    
                wp_localize_script( 'request_lang_locale', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                
                wp_register_script( 'form_fragment', plugins_url( 'pdc-requestmgt/assets/js/form-fragment.js' ), array( 'jquery' ), '0.3', true );    
                wp_localize_script( 'form_fragment', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                
                wp_register_script( 'validate_request_profile', plugins_url( 'pdc-requestmgt/assets/js/validate_request_profile.js' ), array( 'jquery' ), '0.2', true );
                wp_localize_script( 'validate_request_profile', 'pdcrequest', array( 
                            'ajaxurl' => admin_url( 'admin-ajax.php' ),
                            'ajax_nonce' => wp_create_nonce('validate_request_profile')
                        ) );
               
                wp_register_script( 'project_validate', plugins_url( 'pdc-requestmgt/assets/js/request-validate.js'), array( 'jquery' ), '0.4' );
                wp_localize_script( 'project_validate', 'pdcrequest', array( 'ajaxurl' => admin_url('admin-ajax.php') ) );
                    
                wp_register_script( 'lang_locale', plugins_url( 'pdc-requestmgt/assets/js/pdcrequest-lang-locale.js' ), array( 'jquery' ), '0.4', true );
                    
                wp_localize_script( 'lang_locale', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                    //wp_enqueue_script( 'lang_locale');
                wp_register_script( 'short_profile', plugins_url( 'pdc-requestmgt/assets/js/pdcrequest-short-profile.js' ), array( 'jquery' ), '0.6', true );
                    
                wp_localize_script( 'short_profile', 'pdcrequest', array( 
                            'ajaxurl' => admin_url( 'admin-ajax.php' ),
                            'ajax_nonce' => wp_create_nonce('short-profile')
                        ) );
               // wp_register_script( 'test', plugins_url( 'pdc-requestmgt/assets/js/test.js'));
                /*
                if ( is_page('add-a-new-project') ) :
                    wp_enqueue_script('request_lang_locale');
                    wp_enqueue_script( 'request_form_fragment');
                    wp_enqueue_script( 'request_validate' );
                endif;
                */ 
                //endif;
                wp_register_script( 'braintree', 'https://js.braintreegateway.com/v2/braintree.js', array(), 'v2' );
                wp_register_script( 'setlr-braintree-payment', plugins_url('pdc-requestmgt/assets/js/setlr-braintree-payment.js'), array( 'jquery', 'braintree'), '0.9' );
                wp_localize_script( 'setlr-braintree-payment', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
        
                if ( is_page( array('project-quote', 'add-a-new-project' ) ) ) :
                    wp_enqueue_script( 'braintree');
                    wp_enqueue_script( 'setlr-braintree-payment'); 
                    wp_enqueue_script( 'form_fragment');
                endif;
                
                if ( is_page( array( 'my-dashboard', 'add-a-new-project') ) ) :
                    
                    wp_enqueue_script( 'project_validate' );
                    wp_enqueue_script( 'gilly3_word_count');
                    wp_enqueue_script('request_lang_locale');
                    
                    //write_log( plugins_url( 'pdc-requestmgt/assets/js/pdcrequest-request-form-fragment.js' ) );
                endif;
                
                if ( is_page( 'my-dashboard') ) :
                     wp_enqueue_script( 'dashboard_profile');
                    wp_enqueue_script('short_profile');
                endif;
               
                
	}
        
        
        public function admin_enqueue_scripts( $hook ) {
            if ( $hook == 'user-edit.php' || $hook == 'profile.php') :
                wp_enqueue_script( 'admin_lang_locale', plugins_url( 'pdc-requestmgt/assets/js/admin-lang-locale.js' ), array( 'jquery' ), '0.4', true );
                //wp_localize_script( 'admin_lang_locale', 'pdcrequest', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
                wp_enqueue_style('pdcrequest-admin', plugins_url( 'pdc-requestmgt/assets/css/pdcrequest-admin-style.css'));
            endif; 
            
            
        }
	
	public function validate_form() {
		if ( ! isset( $_POST['pdcrequestnonce'] ) || ! wp_verify_nonce( $_POST['pdcrequestnonce'], 'pdc-new-request' ) ) :
			
			echo 'nonce pb';
			exit;
		endif;
		
		//check required fields
		$required = array( 'title', 'content', 'user' );
		
		foreach ( $required as $field ) :
			if ( ! isset( $_POST[$field] ) || empty( $_POST[$field] ) ) :
				$errors[] = $field . '_missing';
			endif;
		endforeach;
		
		if ( ! empty( $errors ) ) :
			return array( false, $errors, $_POST );
		else :
			// do something
			$post = array(
			  'post_content'   => $_POST['content'],
			  //'post_name'      => [ <string> ] // The name (slug) for your post
			  'post_title'     => wp_strip_all_tags( $_POST['title'] ),
			  'post_status'    => 'publish',
			  'post_type'      => 'request',
			  //'post_author'    => [ <user ID> ] // The user ID number of the author. Default is the current user ID.
			  //'post_parent'    => [ <post ID> ] // Sets the parent of the new post, if any. Default 0.
			  //'post_category'  => [ array(<category id>, ...) ] // Default empty.
			  //'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
			 // 'tax_input'      => [ array( <taxonomy> => <array | string> ) ] // For custom taxonomies. Default empty.
			  //'page_template'  => [ <string> ] // Requires name of template file, eg template.php. Default empty.
			);  
			$insert = wp_insert_post( $post );
			
			
			if ( $insert > 0 ) :
				$message = urlencode( sprintf( __( 'Post added with ID:', 'pdcrequest' ),$insert ) );
			else :
				$message = urlencode( __( 'Could not add your post', 'pdcrequest'));
			endif;
		endif;
		$_POST['_wp_http_referer'] = add_query_arg('message', $message, $_POST['_wp_http_referer']);
		wp_redirect($_POST['_wp_http_referer']);
		//echo 'message = ' . $message;
		exit;
	}
	
        
	public function list_requests( $atts = array() ) {
		global $current_user;
		get_currentuserinfo();
		
                $user_langs = Extra_Users::get_working_lang_codes_for( $current_user->ID );
                $native_lang = Extra_Users::get_native_language_for( $current_user->ID );
                $user_locale = Extra_Users::get_native_locale_for( $current_user->ID );
                
		$a = shortcode_atts( array(
                                        'number'	=> -1,
					'type'		=> 'recent',
					'action'	=> 'none'
					), $atts );
		$orderby = '';			
		switch ( $a['type'] ) :
			case 'recent' :
				$orderby = 'post_date';
				break;
			case 'top' :
				$orderby = '';
				break;
			default:
				$orderby = 'post_date';
		endswitch;
		
		/* we show only requests with correct language pairs for a given translator */
		$args = array(
				'post_type'			=> 'request',
				'posts_per_page'	=>	$a['number'],
				//'sort_column'		=> $orderby,
				//'sort_order'		=> 'DESC'
                                'meta_query'            => array(
 
                                            array(
                                                'key'       => 'payment_status',
                                                'value'     => 'paid',
                                                'compare'   => '='
                                            ),
                                            array(
                                                'key'       => 'setlr_status',
                                                'value'     => 'open',
                                                'compare'   => '='
                                            )
                                 )
                                
                                
			);
		
		$requests = get_posts( $args );
		
		$count = count( $requests );
		
		
		if ( isset($count) && absint($count) && $count > 0 ) :
			
			$html = '<table class="pdcrequest-table">';
                        $html .= '<caption>' . __( 'Up For Grabs', 'pdcrequest' )  . '</caption>';
			$columns = '<tr>';
                        $columns .= '<th>' . __( 'Projects', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Customers', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Services', 'pdcrequest') . '</th>';
                        $columns .= '<th>' . __( 'Languages', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Freshness', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Offer', 'pdcrequest' ) . '</th>';
			$columns .= '<th>' . __( 'Status', 'pdcrequest' ) . '</th>';
			if ( $a['action'] != 'none' ) :
				$columns .= '<th>' . __( 'Actions', 'pdcrequest' ) . '</th>';
			endif;
			$columns .= '</tr>';
                        
			$html .= '<thead>' . $columns . '</thead>';
			
			if ( $count >= 10 ) :
				$html .= '<tfoot>' . $columns . '</tfoot>';
			endif;
			echo '<tbody>';
			foreach( $requests as $request ) :
                                // applied is int or null
                                $applied = get_post_meta( $request->ID, 'setlr_translation_id', true );
                                //status returns string
                                $status = request_status::get_request_status($request->ID);
                                $can_apply = self::maybe_show_apply($request->ID);
                                $service = get_post_meta( $request->ID, 'setlr_service', true );
                                $locale = get_post_meta( $request->ID, 'setlr-locale', true );
                                
				if ( $applied  || $status == 'cancelled' || $status == 'closed' || ! $can_apply || ( ! empty($locale) && $locale != $user_locale ) ) :
                                    //do nothing
                                else:
                                    $html .= '<tr id="request-id-' . absint( $request->ID ) . '">';
                                    //projects column
                                    $html .= '<td><a href="' .get_permalink( $request->ID ) . '" title="'. esc_attr( get_the_title( $request->ID ) ) . '">' .esc_html( get_the_title( $request->ID ) ) . '</a></td>';
                                    //customers column
                                    $html .= '<td>' . Setlr_Translation::get_picture_for($request->ID) . '</td>';
                                    //$html .= '<td>' . Extra_Users::get_user_for_table( $request->post_author ) . '</td>';
                                    //services column
                                    $html .= '<td>' . esc_html( $service ) . '</td>';
                                    //languages column
                                    $html .= '<td>' . langpairs::render_lang_pair_for_request( $request->ID ) . '</td>';
                                    //freshness column
                                    $html .= '<td>' . sprintf( __( '%s ago', 'pdcrequest' ), human_time_diff( get_the_time( 'U', $request->ID ), current_time('timestamp') ) ) . '</td>';
                                    //word counts column
                                    
                                    $html .= '<td>' . Setlr_Pricing::get_price_for( $request->ID ) . '</td>';
                                    
                                    
                                    //statuses column
                                    $html .= '<td>' . esc_html( pdcrequest_translate_status( $status ) ) . '</td>';
                                    //actions column
                                    $complete_url = pdcrequest_apply_link( $request->ID );
                                    $reply = '<a href="' . esc_url( $complete_url) . '" class="pdcrequest-action-button pdcrequest-reply-action" title="' . esc_attr( _x( 'apply', 'request action button', 'pdcrequest' ) ) . '">' . esc_html( _x( 'apply', 'request action button', 'pdcrequest' ) ) . '</a>';
				
                                    $closed = get_post_meta( $request->ID, 'pdcrequest_closed', true );
                                    if ( $a['action'] != 'none' ) :
					$html .= '<td>';
					//requester should not reply to own request
					if ( Pdc_Requestmgt::maybe_show_apply($request->ID)) :
                           
                                            if ( Setlr_Translation::check_if_applied( $request->ID ) ) :
						$html .= __( 'You have applied', 'pdcrequest' );
                                            else :
						$html .= $reply;
                                            endif;//end check if applied
					endif;//end maybe show apply
					$html .= '</td>';
                                    endif;//end action != none
                                    $html .= '</tr>';
                                endif;// end ! applied
			endforeach;
			$html .= '</tbody></table>';
			return $html;
		else:
			return '<p class="pdcrequest-empty">' . __( 'There are no requests available at this time.', 'pdcrequest' ) . '</p>'; 
		endif;
	}
	
	    
        /**
         * 
         * @global int $current_user
         * @global object $wpdb
         * @return string
         */
	public function list_own_requests( $atts = array() ) {
		global $current_user, $wpdb;
		get_currentuserinfo();
		
                $a = shortcode_atts( array( 'type' => 'active' ), $atts );
		$orderby = '';			
		switch ( $a['type'] ) :
			case 'active' :
				$statuses = array( 'open', 'pending', 'customer_review', 'in_revision' );
                                $caption = __( 'My Outstanding Projects', 'pdcrequest');
				break;
			case 'history' :
				$statuses = array( 'closed', 'cancelled', 'rejected' );
                                $caption = __( 'My History', 'pdcrequest');
				break;
			default:
                                $statuses = array( 'open', 'pending', 'customer_review', 'in_revision' ); 
                                $caption = __( 'My Outstanding Projects', 'pdcrequest');
		endswitch;		
                
		$args = array(
                            'post_type'         => 'request',
                            'posts_per_page'	=> -1,
                            'author'		=> absint( $current_user->ID ),
                            'meta_query'            => array(
                                    array(
                                        'key'       => 'setlr_status',
                                        'value'     => $statuses,
                                        'compare'   => 'IN'
                                    )
                                )
			);
		$requests = get_posts( $args );
               
		$count = count( $requests );
		
		
		if ( $requests ) :
			
			$html = '<table class="pdcrequest-table">';
			$html .= '<caption>' . esc_html( $caption ) . '</caption>';
			$columns = '<tr>';
                        $columns .= '<th>' . __( 'Projects', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Languages', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Status', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Payment Status', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Helpers', 'pdcrequest' ) . '</th>';
                        
			
			if ( !isset( $args['action'] ) || $args['action'] != 'none' ) :
				$columns .= '<th>' . __( 'Actions', 'pdcrequest' ) . '</th>';
			endif;
			$columns .= '</tr>';
                        
			$html .= '<thead>' . $columns . '</thead>';
			
			if ( $count >= 10 ) :
				$html .= '<tfoot>' . $columns . '</tfoot>';
			endif;
			echo '<tbody>';
			foreach( $requests as $request ) :
				$status = request_status::get_request_status($request->ID);
                                $payment_status = Payment_Status::get_payment_status($request->ID);
                                $translation_id = pdcrequest_get_translation_for_request( $request->ID );
                                
                                $helper_id = Setlr_Translation::get_author_for( $translation_id );
                                
                                $customer_id = get_current_user_id();
                               
                                
                                $payment_button = Setlr_Pricing::show_quote_button( $request->ID );
                                $amount = (Setlr_Pricing::get_price_for( $request->ID ) ) ? Setlr_Pricing::get_price_for( $request->ID ) : '';
				$html .= '<tr id="request-id-' . absint( $request->ID ) . '">';
                                if ( in_array( $status, array( 'customer_review', 'accepted' ) ) && absint( $translation_id) ) :
                                    $html .= '<td><a href="' . get_permalink( $translation_id ) . '">' . get_the_title($request->ID) . '</a></td>';
                                else :
                                    $html .= '<td><a href="' . get_permalink( $request->ID ) . '">' . get_the_title($request->ID) . '</a></td>';
                                endif;
                                
				$html .= '<td>' . langpairs::render_lang_pair_for_request( $request->ID ) . '</td>';
                      
                                $html .= '<td>' . esc_html( pdcrequest_translate_status( $status ) ) . '</td>';
                                
                                $html .= '<td>' . esc_html( pdcrequest_translate_payment_status( $payment_status ) ) . ' ' . $amount . ' ' . $payment_button . '</td>';
                                
                                $html .= '<!-- helper id: ' . $helper_id . '-->';
                                if ( absint( $translation_id) && absint( $helper_id) ) :
                                    $html .= '<td>' . Setlr_Translation::get_picture_for($translation_id) . '</td>';
                                    //$html .= '<td>' . Extra_Users::get_user_for_table( $helper_id ) . '</td>';
                                else :
                                    $html .= '<td></td>';
                                endif;
                                
                                
				if ( !isset( $args['action'] ) || $args['action'] != 'none' ) :
					$html .= '<td>' . $this->list_own_action_buttons( $request->ID ) . '</td>';
				endif;
				$html .= '</tr>';
			endforeach;
			$html .= '</tbody></table>';
			return $html;
		else:
			return '<p class="pdcrequest-empty">' . __( 'You have no outstanding request at this time.', 'pdcrequest' ) . '</p>'; 
		endif;
	}
	
	
	/**
	 * Show apply button as a shortcode
	 *
	 * @return html
	 */
	public function maybe_show_apply_button( $atts ) {
		global $current_user, $post;
		get_currentuserinfo();
		
		//get post language pair
                $from_lang = get_post_meta( $post->ID, 'from-lang', true );
                $to_lang = get_post_meta( $post->ID, 'to-lang', true);
                
                //get user languages
                $native = Extra_Users::get_native_language_for( $current_user->ID );
                $other_langs = Extra_Users::get_working_languages_for( $current_user->ID );
		
		extract( shortcode_atts( array( 'id' => '' ), $atts ) );
		
                $payment_status = Payment_Status::get_payment_status($post->ID);
                
                if ( 'paid' !== $payment_status ) :
                    $html = '<p class="error no-payment">' . __( 'This request is in stand-by.', 'pdcrequest' ) . '</p>';
                else :
                    $applications = Setlr_Translation::check_if_applied( $post->ID );

                    if ( $applications ) :
                            $html = '<p class="error already-applied">' . __( 'This request has already benn filled!', 'pdcrequest' ) . '</p>';
                    else :

                            $html = '<div class="pdcrequest-apply" id="pdcrequest-apply">';
                            /* insufficient rights to apply */
                            if ( ! current_user_can( 'pdc_application_post' )  ) :
                                    $html .= '<p class="error no-rights">' . __( "You don't have sufficient rights to apply to this request.", 'pdcrequest' ) . '</p>';
                            /* own request */
                            elseif ( $post->post_author == $current_user->ID ) :
                                    $html .= '<p class="error own-request">' . __( "It's your own request!", 'pdcrequest' ) . '</p>';
                            /* non matching main language pairs */
                            elseif ( $to_lang !== $native ) :
                                $html .= '<p class="error non-native">' . __( "You don't have the right native language to translate this request", 'pdcrequest' ) . '</p>';
                            /* non matching languages */
                            elseif ( ! in_array( $from_lang, $other_langs ) ) :
                                $html .= '<p class="error no-lang">' . __( 'Languages can not be retrieved', 'pdcrequest' ) . '</p>';

                            else :
                                    $html .= '<a class="button-toggle-application" href="#" title="' . esc_attr( __( 'Appply', 'pdcrequest' ) ) . '">' . esc_html( __( 'Apply', 'pdcrequest' ) ) . '</a>';	

                                    $html .= Application_Form::create_form_new_application( array( 'request_id' => $post->ID ) );	
                            endif;
                            $html .= '</div>';
                    endif;
                endif;//payment_status
		return $html;
	}
        
        
        /**
         * maybe show apply
         * @global WP_User $current_user
         * @param int $request_id the request id
         * @return boolean true if matching language pairs and not applied
         */
        public static function maybe_show_apply( $request_id ) {
            global $current_user;
            get_currentuserinfo();
                
                //write_log( 'maybe_show_apply : ' . $request_id );
                $post = get_post( $request_id );
                
                if ( $post->post_type == 'setlr_rev_request' ) :
                   // write_log( $post->post_type );
                    return true;
                else :
                    /* check for paid payment status */
                    $payment_status = Payment_Status::get_payment_status($request_id);
                    if ( 'paid' != $payment_status ) :
                        return false;
                    endif;

                    /* check for own request */
                    $post = get_post( $request_id );
                    if ( $post->post_author == $current_user->ID ) :  
                        return false;
                    endif;

                    /* check if request has already been applied to */
                    $application = Setlr_Translation::check_if_applied( $request_id );
                    if ( $application ) :
                        return false;
                    endif;
                    //get user languages
                    $native = Extra_Users::get_native_language_for( $current_user->ID );
                    $other_langs = Extra_Users::get_working_lang_codes_for( $current_user->ID );

                    $service = get_post_meta( $request_id, 'setlr_service', true );
                    switch( $service ) :
                        case 'translation' :
                            //get post language pair
                            $from_lang = get_post_meta( $request_id, 'from-lang', true );
                            $to_lang = get_post_meta( $request_id, 'to-lang', true);
                             /* check matching lang pairs */
                            if ( $to_lang !== $native || ! in_array( $from_lang, $other_langs ) ) :
                                return false;
                            endif;
                            break;
                        case 'question' :
                            $question_lang = get_post_meta( $request_id, 'questionlang', true );
                            if ( $question_lang != $native ) :
                                return false;
                            endif;
                            break;
                    endswitch;

                    return true;
                endif;
                
        }

        
        /**
         * list helpers past works (used in shortcode pdcrequest_list_applications)
         * @global int $current_user
         * @global WP_Post $post
         * @param array $atts (number, type, action)
         * @return string HTML table or paragraph empty
         * @todo update with helper payment status
         */
	public function list_applications( $atts = array() ) {
		global $current_user, $post;
		get_currentuserinfo();
		
		if ( ! is_user_logged_in() ) :
			printf( __( 'You need to %s or %s to view this content!', 'pdcrequest' ), wp_loginout( '', '', false ), wp_register('', '', false ) );
			exit;
		endif;
		
		/* we output only for the request author or on the my-dashboard page */
		if ( $post->post_author != $current_user->ID && 'my-dashboard' != $post->post_name ) :
			return;
		else :
		/* now we can evaluate the demand */
		$a = shortcode_atts( array(
                                            'number'	=> -1,
                                            'type'	=> 'history',
                                            'action'	=> 'none'
					), $atts );
		$orderby = '';		
		$caption = '';
                $where = '';
		switch ( $a['type'] ) :
			case 'history' :
				$caption = __( 'My History', 'pdcrequest' );
                                $where = array( 'author' => $current_user->ID );
				$orderby = 'date';
                                $meta_query = array('meta_query' => array(
                                                    array(
                                                            'key' => 'setlr_status',
                                                            'value' => array('customer_review','in_revision', 'finished', 'accepted', 'rejected'),
                                                            'meta_compare' => 'IN'
                                                    )
                                            ) );
                                $empty_message = __( "You don't have any past jobs.", 'pdcrequest' );
				break;
			case 'in_progress' :
				$caption = __( 'In Progress', 'pdcrequest' );
				$where = array( 'author' => $current_user->ID );
                                $orderby = 'date';
                                $meta_query = array('meta_query' => array(
                                                    array(
                                                            'key' => 'setlr_status',
                                                            'value' => array('pending', 'in_progress'),
                                                            'meta_compare' => 'IN'
                                                    )
                                            ) );
                                $empty_message = __( "You don't have any jobs in-progress.", 'pdcrequest' );
				break;
			default:
				$caption = __( 'Translations', 'pdcrequest' );
				$orderby = 'date';
                                $meta_query = '';
		endswitch;
		
		
		$args = array(
				'post_type'		=> 'translation',
				'posts_per_page'	=> $a['number'],
                                'post_status'           => array( 'draft', 'publish'),
				'orderby'		=> $orderby,
				'order'                 => 'DESC'
					);
		$args = array_merge( $args, $meta_query, $where );
			
		$applications = get_posts( $args );
		
		$count = count( $applications );
		
		
		if ( $applications  && $count > 0 ) :
			
			$html = '<table class="pdcrequest-table">';
			
			
			$html .= '<caption>' . esc_html( $caption ) . '</caption>';
			
			$columns = '<tr>';
                        $columns .= '<th>' . __( 'Projects', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Customers', 'pdcrequest' ) . '</th>';
                        
                        if ( ! in_array($a['type'], array('history', 'in_progress') ) ) :
                            $columns .= '<th>' . __( 'Helpers', 'pdcrequest' ) . '</th>';
                        endif;
                        $columns .= '<th>' . __( 'Services', 'pdcrequest' ) . '</th>';
                        
                        if ( ! in_array($a['type'], array('history', 'in_progress' ) ) ) :
                            $columns .= '<th>' . __( 'Translators', 'pdcrequest' ) . '</th>';
                        endif;
                        
                        $columns .= '<th>' . __( 'Languages', 'pdcrequest' ) . '</th>';
			$columns .= '<th>' . __( 'Working Time', 'pdcrequest') . '</th>';
                        
			
                        if ( $a['type'] == 'history' ) :
                            $columns .= '<th>' . __( 'Status', 'pdcrequest' ) . '</th>';
                            $columns .= '<th>' . __( 'Payment Status', 'pdcrequest' ) . '</th>';
                        endif;
                        
                        if ( $a['type'] == 'in_progress' ) :
                            $columns .= '<th>' . __( 'Actions', 'pdcrequest' ) . '</th>';
                        endif;
                        
                        $columns .= '</tr>';
			
			$html .= '<thead>' . $columns . '</thead>';
			
			if ( $count >= 10 ) :
				$html .= '<tfoot>' . $columns . '</tfoot>';
			endif;
			echo '<tbody>';
                        
			foreach( $applications as $app ) :
                                $html .= "<!-- id:" . $app->ID . " -->";
                                
                                $request_id = $app->post_parent;
                                $request = get_post( $request_id );
				//projects column
				$html .= '<tr><td><a href="' .get_permalink( $app->ID ) . '" title="'. esc_attr( get_the_title( $app->ID ) ) . '">' .esc_html( get_the_title( $app->ID ) ) . '</a></td>';
				
                                //customers column
                                $html .= '<td><a href="#" class="setlr-user" data-user_id="'. esc_attr( $request->post_author ) . '">' . Setlr_Translation::get_picture_for($request->ID) . '</a></td>';
                                //$html .= '<td><a href="#" class="setlr-user" data-user_id="'. esc_attr( $request->post_author ) . '">' . get_the_author_meta( 'nicename', $request->post_author ) . '</a></td>';
                                
                                //helpers column
                                if ( ! in_array($a['type'], array('history', 'in_progress') ) ) :
                                    $html .= '<td><a href="#" class="setlr-user" data-user_id="'. esc_attr( $app->post_author ) . '">' . get_the_author_meta( 'nicename', $app->post_author ) . '</a></td>';
                                endif; 
                                //services column
                                $html .= '<td>' . esc_html( get_post_meta( $request_id, 'setlr_service', true ) ) . '</td>';
                                
				//languages column
                                $html .= '<td>' . get_post_meta( $app->post_parent, 'from-lang', true ) . ' --> ' . get_post_meta( $app->post_parent, 'to-lang', true ) . '</td>';
				
                                //working-time column
                                $html .= '<td>' . translation_status::get_working_time( $app->ID ) . '</td>';
                                
                                
				
				//if in_progress table then show resume button
				if ( $a['type'] == 'in_progress' ) :
					//requester should not reply to own request
					$html .= '<td><a href="' .get_permalink( $app->ID ) . '" title="'. esc_attr( get_the_title( $app->ID ) ) . '">' . __( 'Resume', 'pdcrequest') . '</a></td>';	
				endif;
                                //if history table then show status
                                if ( $a['type'] == 'history' ) :
                                    $html .= '<td>';
                                    $html .= pdcrequest_translate_status( translation_status::get_translation_status( $app->ID ) );
                                    $html .= '</td>';
                                    $html .= '<td>';
                                    $html .= 'not available';
                                    $html .= '</td>';
                                endif;
				$html .= '</tr>';
			endforeach;
			$html .= '</tbody></table>';
			
		else:
                    $html = '<p class="setlr-empty">' . esc_html( $empty_message ) . '</p>'; 
		endif;
                    return $html;
		endif; //request author or my-dashbard
	}
	
	
	
        
        
        /**
         * redirect user to dashboard with notification
         * @param string $message
         * @param array data in the form of key => value
         */
        public static function redirect_to_dashboard( $message = '', $data = array() ) {
           
            $dashboard = get_page_by_path( 'my-dashboard');
            $dashboard_url = get_permalink( $dashboard->ID );
            
            if ( $message && ! empty( $message )) :
                $dashboard_url = add_query_arg( 'message', urlencode( $message ), $dashboard_url);
            endif;
            
            if ( isset( $data ) && ! empty( $data ) ) :
                $url = add_query_arg( array_keys( $data ), array_values($data));
            endif;
            
            wp_safe_redirect( $dashboard_url);
        }
        
        
        /**
         * redirect user to a page with notification
         * 
         * @param int page_id
         * @param string $message
         * @param array data in the form of key => value
         */
        public static function redirect_to_page( $page_id, $message = '', $data = array() ) {
            write_log( 'Pdc_Requestmgt redirect_to_page');
            $url = get_permalink( $page_id );
            
            
            if ( $message && ! empty( $message ) ) :
                $url = add_query_arg( 'message', urlencode( $message ), $url);
            endif;
            
            if ( isset( $data ) ) :
                if ( !array_key_exists('setlrnonce', $data ) ) :
                    $data['setlrnonce'] = wp_create_nonce( 'request-form' ); 
                endif;
                foreach ( $data as $key => $value ) :
                    $url = add_query_arg( $key, urlencode( $value ), $url );
                endforeach;
            endif;
            
            
            
            wp_redirect( $url);
        }
        
        
        

        
        function add_query_vars_filter( $vars ){
            $vars[] = "message";
            $vars[] = "message_type";
            return $vars;
        }

        public function request_applied() {
            global $current_user;
            get_currentuserinfo();
            
            if ( isset( $_REQUEST['request_id']) && absint($_REQUEST['request_id'])) :
                $request_id = absint( $_REQUEST['request_id'] );
            else :
                new WP_Error('200', __('No request id', 'pdcrequest'));
            endif;
            
            $request = get_post( $request_id );
           
            $updated_status = request_status::update_status($request_id, 'pending');
            
            
            $args = array(
                'post_content'   => __( 'pending translation', 'pdcrequest'),
                'post_title'     => $request->post_title,
                'post_status'    => 'publish', 
                'post_type'      => 'translation',
                'post_author'    => $current_user->ID, 
                'post_parent'    => $request_id,
              );  
            
            $translation_id = wp_insert_post( $args );
           
            
            $message = '';
            
            if ( absint( $translation_id )) :
                update_post_meta( $request_id, 'setlr_translation_id', $translation_id );
                translation_status::update_status($translation_id, 'pending' );
                do_action( 'pdcrequest_settle_transaction', $request_id );
                $message = sprintf( __( 'You have applied to %s. Happy translating.', 'pdcrequest'), $request->post_title );
            endif;
            
           self::redirect_to_dashboard( $message );
        }
        
        
        public static function metabox_from_to_callback( $post ) {
            // Add a nonce field so we can check for it later.
            wp_nonce_field( 'pdcrequest_meta_box', 'pdcrequest_meta_box_nonce' );
            
            /*
             * Use get_post_meta() to retrieve an existing value
             * from the database and use the value for the form.
             */
            
            
            if ( 'request' == $post->post_type ) :
                $from = get_post_meta( $post->ID, 'from-lang', true );
                $to = get_post_meta( $post->ID, 'to-lang', true );
            
                echo lang::render_lang_select( 'from', $from );
                echo lang::render_lang_select( 'to', $to );
            else : 
                $from = get_post_meta( $post->post_parent, 'doclang', true );
                $to = get_post_meta( $post->post_parent, 'requestlang', true );
            
                echo lang::render_lang_info( $from, $to );
            endif;
       
        }
        
        public function add_metabox_from_to() {
            $screens = array( 'request', 'translation' );

            foreach ( $screens as $screen ) :

		add_meta_box(
			'pdcrequest_from_to',
			__( 'Translation Langage Pair', 'pdcrequest' ),
			array($this, 'metabox_from_to_callback'),
			$screen,
                        'side',
                        'high'
		);
            endforeach;
        }
        
        
        
        public static function save_metabox_from_to( $post_id ) {
            /*
            * We need to verify this came from our screen and with proper authorization,
            * because the save_post action can be triggered at other times.
            */

           // Check if our nonce is set.
           if ( ! isset( $_POST['pdcrequest_meta_box_nonce'] ) ) {
              
                   return;
           }

           // Verify that the nonce is valid.
           if ( ! wp_verify_nonce( $_POST['pdcrequest_meta_box_nonce'], 'pdcrequest_meta_box' ) ) {
              
                   return;
           }

           // If this is an autosave, our form has not been submitted, so we don't want to do anything.
           if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
               
                   return;
           }

           
           /* OK, it's safe for us to save the data now. */
	
            // Make sure that it is set.
            if ( ! isset( $_POST['from-lang'] ) || ! isset( $_POST['to-lang'] ) ) {
               
                    return;
            }

            // Sanitize user input.
            $from = sanitize_text_field( $_POST['from-lang'] );
            $to = sanitize_text_field( $_POST['to-lang'] );

            lang::save_lang_info( $post_id, 'from-lang', $from );
            lang::save_lang_info( $post_id, 'to-lang', $to );
           
        }
       
        
        public function check_database() {
            global $wpdb;
            
            translation_status::create_db_table();
        }
        
        
        
        public function ajax_word_count() {
            $nonce = check_ajax_referer( 'pdcrequest_word_count', 'nonce', false );
            
            $text = $_POST['content'];
            $lang = esc_attr( $_POST['lang'] );
           
            $count = new Setlr_Count( $text, $lang );
            $total = $count->count();
            echo $total;
            exit();
        }
        
        
        
        private function list_own_action_buttons( $request_id ) {
            //we are inside a td
            $html = '';
            
            $url = admin_url( 'admin.php' );
            
            $status = request_status::get_request_status($request_id);
            $translation_id = pdcrequest_get_translation_for_request( $request_id );
            
            if ( in_array( $status, array( 'open', 'pending', 'waiting', 'customer_review' ) ) ) :
                $action = '';
                if ( absint( $translation_id) > 0 ) :
                    $url = get_permalink($translation_id);
                else :
                    $url = get_permalink($request_id);
                endif;
                
                $html .= '<a class="setlr-action-button view" title="' . __( 'View Project', 'pdcrequest') . '" href="' . wp_nonce_url( $url, $action, 'pdcrequest_nonce') . '">' . __( 'View Project', 'pdcrequest') . '</a>';
            endif;
            /* cancel request if translation has not started */
            if ( ! absint($translation_id) && in_array( $status, array( 'open' ) ) ) :
                
                $action = 'pdcrequest_cancel_request';
                
                $url = add_query_arg(array('action'=> $action, 'request_id' => $request_id ), admin_url( 'admin.php' ) );
                $html .= ' <a class="setlr-action-button cancel" title="' . __( 'Cancel Project', 'pdcrequest') . '" href="' . wp_nonce_url( $url, $action, 'pdcrequest_nonce') . '">' . __( 'Cancel Project', 'pdcrequest') . '</a>';
            endif;
            
            /* reopen request if cancelled */
            if ( $status === 'cancelled' ) :
                $action = 'pdcrequest_reopen_request';
                $url = add_query_arg(array('action' => $action, 'request_id' => $request_id), $url );
                $html .= ' <a class="setlr-action-button reopen" title="' . __( 'Reopen Project', 'pdcrequest') . '" href="' . wp_nonce_url( $url, $action, 'pdcrequest_nonce') . '">' . __( 'Reopen Project', 'pdcrequest') . '</a>';
            endif;
            return $html;
        }
        
       
        public static function get_requests_for_current_user( $statuses = array() ) {
            global $current_user;
            
            
            $args = array(
                            'post_type'         => 'request',
                            'posts_per_page'	=> -1,
                            'fields'            => array( 'ID', 'post_title' )
			);
            if ( !current_user_can( 'manage_options')):
                $author_arg = array('author'		=> absint( $current_user->ID ));
                $args = array_merge( $args, $author_arg );
            endif;
                if ( ! empty( $statuses ) ) :
                    $meta_query_arg = array('meta_query'            => array(
                                    array(
                                        'key'       => 'setlr_status',
                                        'value'     => $statuses,
                                        'compare'   => 'IN'
                                    )
                                ) 
                        );
                $args = array_merge( $args, $meta_query_arg );
                endif;
                
		$requests = get_posts( $args );
                
                return $requests;
        }

        
        public function list_revisions( $atts = array() ) {
            global $current_user;
		get_currentuserinfo();
		
                $user_langs = Extra_Users::get_working_lang_codes_for( $current_user->ID );
                $native_lang = Extra_Users::get_native_language_for( $current_user->ID );
                $user_locale = Extra_Users::get_native_locale_for( $current_user->ID );
                
		$a = shortcode_atts( array(
                                        'number'	=> -1,
					'type'		=> 'recent',
					'action'	=> 'none'
					), $atts );
		$orderby = '';			
		switch ( $a['type'] ) :
			case 'recent' :
				$orderby = 'post_date';
				break;
			case 'top' :
				$orderby = '';
				break;
			default:
				$orderby = 'post_date';
		endswitch;
		
		/* we show only requests with correct language pairs for a given translator */
		$args = array(
				'post_type'		=> 'setlr_rev_request',
				'posts_per_page'	=> $a['number']
				//'sort_column'		=> $orderby,
				//'sort_order'		=> 'DESC'
                                
                                );
					
		
		$requests = get_posts( $args );
		
		$count = count( $requests );
		
		
		if ( $requests ) :
			
			$html = '<table class="pdcrequest-table">';
                        $html .= '<caption>' . __( 'Revisions For Grabs', 'pdcrequest' ) . '</caption>';
			$columns = '<tr>';
                        $columns .= '<th>' . __( 'Projects', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Customers', 'pdcrequest' ) . '</th>';
                        //$columns .= '<th>' . __( 'Services', 'pdcrequest') . '</th>';
                        $columns .= '<th>' . __( 'Languages', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Freshness', 'pdcrequest' ) . '</th>';
                        $columns .= '<th>' . __( 'Word Count', 'pdcrequest' ) . '</th>';
			$columns .= '<th>' . __( 'Status', 'pdcrequest' ) . '</th>';
			if ( $a['action'] != 'none' ) :
				$columns .= '<th>' . __( 'Actions', 'pdcrequest' ) . '</th>';
			endif;
			$columns .= '</tr>';
                        
			$html .= '<thead>' . $columns . '</thead>';
			
			if ( $count >= 10 ) :
				$html .= '<tfoot>' . $columns . '</tfoot>';
			endif;
			$html .= '<tbody>';
			foreach( $requests as $request ) :
                                // applied is int or null
                                $applied = get_post_meta( $request->ID, 'setlr_translation_id', true );
                                //status returns string
                                
                                $can_apply = self::maybe_show_apply($request->ID);
                                
                                $info = Setlr_Revision_Request::get_rev_request_information( $request->ID );
                                $project_id = $info['project_id'];
                                $translation_id = $info['translation_id'];
                                
                                
                                $locale = get_post_meta( $project_id, 'setlr-locale', true );
                                $status = request_status::get_request_status($project_id );
                                
				if ( ! $can_apply ) :
                                    //do nothing
                                else:
                                    $html .= '<tr>';
                                    //projects column
                                    $html .= '<td><a href="' . get_permalink( $request->ID ) . '" title="'. esc_attr( get_the_title( $request->ID ) ) . '">' .esc_html( get_the_title( $request->ID ) ) . '</a></td>';
                                    //customers column
                                    $html .= '<td>' . Extra_Users::get_user_for_table( $request->post_author ) . '</td>';
                                    //services column
                                   // $html .= '<td>' . esc_html( $service ) . '</td>';
                                    //languages column
                                    $html .= '<td>' . langpairs::render_lang_pair_for_request( $project_id ) . '</td>';
                                    //freshness column
                                    $html .= '<td>' . sprintf( __( '%s ago', 'pdcrequest' ), human_time_diff( get_the_time( 'U', $request->ID ), current_time('timestamp') ) ) . '</td>';
                                    //word counts column
                                    $html .= '<td>' . pdcrequest_get_word_count( $project_id ) . '</td>';
                                    //statuses column
                                    $html .= '<td>' . esc_html( pdcrequest_translate_status( $status ) ) . '</td>';
                                    //actions column
                                    $complete_url = pdcrequest_apply_link( $request->ID );
                                    $reply = '<a href="' . esc_url( $complete_url) . '" class="pdcrequest-action-button pdcrequest-reply-action" title="' . esc_attr( _x( 'apply', 'request action button', 'pdcrequest' ) ) . '">' . esc_html( _x( 'apply', 'request action button', 'pdcrequest' ) ) . '</a>';
				
                                    $closed = get_post_meta( $request->ID, 'pdcrequest_closed', true );
                                    if ( $a['action'] != 'none' ) :
					$html .= '<td>';
					//requester should not reply to own request
					if ( Pdc_Requestmgt::maybe_show_apply($request->ID)) :
                           
                                            if ( Setlr_Translation::check_if_applied( $request->ID ) ) :
						$html .= __( 'You have applied', 'pdcrequest' );
                                            else :
						$html .= $reply;
                                            endif;//end check if applied
					endif;//end maybe show apply
					$html .= '</td>';
                                    endif;//end action != none
                                    $html .= '</tr>';
                                endif;// end ! applied
			endforeach;
			$html .= '</tbody></table>';
			return $html;
		else:
			return '<p class="pdcrequest-empty">' . __( 'There are no requests available at this time.', 'pdcrequest' ) . '</p>'; 
		endif;
        }
        
        
        public function admin_assign_translator_to_revision( $post_id ) {
           $info = Setlr_Revision_Request::get_rev_request_information( $post_id );
            
           if ( ! isset( $info['project_id'] ) && ! isset( $info['translation_id'] ) && ! absint( $info['project_id'] ) && ! absint( $info['translation_id'] ) ) :
               return new WP_Error( 'setr_undifined', __( 'The revision request is undefined', 'pdcrequest') );
           endif;
           
           //we need the lang pairs of the project
           $from = get_post_meta( $project_id, 'from-lang', true );
           
           $to = ( get_post_meta( $project_id, 'setlr-locale', true ) ) ? get_post_meta( $project_id, 'setlr-locale', true ) : get_post_meta( $project_id, 'to-lang', true );
           
           
        }
        
       public function add_admin_menu() {
           add_options_page( 'PdC-Requestmgt Options', 'PdC-Requestmgt', 'manage_options', 'pdc-requestmgt', array( $this, 'pdcrequest_options_page' ) );
       }
       
       
       public function settings_init() {
        
            register_setting( 'setlroptionspage', 'pdcrequest_settings' );
            
            add_settings_section(
                    'pdcrequest_braintree_section', 
                    __( 'Braintree', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_settings_braintree_section_callback' ), 
                    'setlroptionspage'
            );
            add_settings_field( 
                    'braintree_environment', 
                    __( 'Braintree Environment', 'pdcrequest' ), 
                    array( $this, 'braintree_environment_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_sandbox_merchant_id', 
                    __( 'Sandbox Merchant ID', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_sandbox_merchant_id_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_sandbox_publickey', 
                    __( 'Sandbox Public Key', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_sandbox_publickey_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_sandbox_privatekey', 
                    __( 'Sandbox Private Key', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_sandbox_privatekey_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_production_merchant_id', 
                    __( 'Production Merchant ID', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_production_merchant_id_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_production_publickey', 
                    __( 'Production Public Key', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_production_publickey_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_field( 
                    'pdcrequest_production_privatekey', 
                    __( 'Production Private Key', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_production_privatekey_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_braintree_section' 
            );
            add_settings_section(
                    'pdcrequest_pricing_section', 
                    __( 'Pricing', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_settings_section_callback' ), 
                    'setlroptionspage'
            );

            add_settings_field( 
                    'pdcrequest_translation_price', 
                    __( 'Translation Price', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_translation_price_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_translation_price_unit', 
                    __( 'Translation Price Unit', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_translation_price_unit_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );

            add_settings_field( 
                    'pdcrequest_question_price', 
                    __( 'Question Price', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_question_price_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_question_price_unit', 
                    __( 'Question Price Unit', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_question_price_unit_render' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_letterhead', 
                    __( 'Letterhead of receipt', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_letterhead' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_signature', 
                    __( 'Signature of receipt', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_signature' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_sandbox_account', 
                    __( 'PayPal Sandbox Account', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_sandbox_account' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_sandbox_client_id', 
                    __( 'PayPal Sandbox Client ID', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_sandbox_client_id' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_sandbox_secret', 
                    __( 'PayPal Sandbox Secret', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_sandbox_secret' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_production_account', 
                    __( 'PayPal Production Account', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_production_account' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_production_client_id', 
                    __( 'PayPal Production Client ID', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_production_client_id' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
            
            add_settings_field( 
                    'pdcrequest_paypal_production_secret', 
                    __( 'PayPal Production Secret', 'pdcrequest' ), 
                    array( $this, 'pdcrequest_paypal_production_secret' ), 
                    'setlroptionspage', 
                    'pdcrequest_pricing_section' 
            );
       }
       
       
       function braintree_environment_render() {
           $options = get_option( 'pdcrequest_settings' );
            ?>
            <select name='pdcrequest_settings[braintree_environment]'>
                <option value="sandbox" <?php selected( 'sandbox', $options['braintree_environment'] ); ?>><?php echo 'sandbox'; ?></option>
                <option value="production" <?php selected( 'production', $options['braintree_environment'] ); ?>><?php echo 'production'; ?></option>
            </select>
            <?php
       }
       
       function pdcrequest_sandbox_merchant_id_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_sandbox_merchant_id]' value='<?php echo $options['pdcrequest_sandbox_merchant_id']; ?>'>
            <?php
       }
       
       
       function pdcrequest_sandbox_publickey_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_sandbox_publickey]' value='<?php echo $options['pdcrequest_sandbox_publickey']; ?>'>
            <?php
       }
       
       
       function pdcrequest_sandbox_privatekey_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_sandbox_privatekey]' value='<?php echo $options['pdcrequest_sandbox_privatekey']; ?>'>
            <?php
       }
       
       
       
       function pdcrequest_production_merchant_id_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_production_merchant_id]' value='<?php echo $options['pdcrequest_production_merchant_id']; ?>'>
            <?php
       }
       
       function pdcrequest_production_publickey_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_production_publickey]' value='<?php echo $options['pdcrequest_production_publickey']; ?>'>
            <?php
       }
       
       
       function pdcrequest_production_privatekey_render() {
            $options = get_option( 'pdcrequest_settings' );
            ?>
            <input type='text' name='pdcrequest_settings[pdcrequest_production_privatekey]' value='<?php echo $options['pdcrequest_production_privatekey']; ?>'>
            <?php
       }
       
       function pdcrequest_question_price_render(  ) { 

                $options = get_option( 'pdcrequest_settings' );
                ?>
                <input type='text' name='pdcrequest_settings[pdcrequest_question_price]' value='<?php echo $options['pdcrequest_question_price']; ?>'>
                <?php

        }
        
        
        function pdcrequest_translation_price_render(  ) { 

                $options = get_option( 'pdcrequest_settings' );
                ?>
                <input type='text' name='pdcrequest_settings[pdcrequest_translation_price]' value='<?php echo $options['pdcrequest_translation_price']; ?>'>
                <?php

        }
        
        
        function pdcrequest_question_price_unit_render(  ) { 

                $options = get_option( 'pdcrequest_settings' );
                $values = $this->get_pricing_units();
                
                //write_log( $options['pdcrequest_question_price_unit'] );
                ?>
                <select name='pdcrequest_settings[pdcrequest_question_price_unit]'>
                    <?php foreach ( $values as $value => $name ) :
                    echo '<option value="' . esc_attr( $value ) . '" ' . selected($value, $options['pdcrequest_question_price_unit'], false ) . '>' . esc_html( $name ) . '</option>';
                    endforeach; ?>
                </select>
                <?php
        }
        
        
        
        function pdcrequest_translation_price_unit_render(  ) { 

                $options = get_option( 'pdcrequest_settings' );
                $values = $this->get_pricing_units();
                ?>
                <select name='pdcrequest_settings[pdcrequest_translation_price_unit]'>
                    <?php foreach ( $values as $value => $name ) :
                    echo '<option value="' . esc_attr( $value ) . '" ' . selected($value, $options['pdcrequest_translation_price_unit'], false ) . '>' . esc_html( $name ) . '</option>';
                    endforeach; ?>
                </select>
                <?php
        }


        function pdcrequest_settings_section_callback(  ) { 

            echo __( 'Setlr Pricing Scheme', 'pdcrequest' );

        }
        
        function pdcrequest_settings_braintree_section_callback() {
            echo __( 'Braintree Payments', 'pdcrequest' );
        }
        
        
        function pdcrequest_options_page(  ) { 

                ?>
                <form action='options.php' method='post'>

                        <h2>PdC-Requestmgt</h2>

                        <?php
                        settings_fields( 'setlroptionspage' );
                        do_settings_sections( 'setlroptionspage' );
                        submit_button();
                        ?>

                </form>
                <?php

        }

        
        public function get_pricing_units() {
            $units = array(
                'per_word'  => __( 'per word', 'pdcrequest' ),
                'fixed'     => __( 'per question', 'pdcrequest' )
            );
            
            return $units;
        }

        
        public function sandbox_admin_notice() {
            $options = get_option('pdcrequest_settings');
            
            if ( !isset( $options['braintree_environment']) || 'sandbox' == $options['braintree_environment'] ) :
                echo '<div class="error"><p>' . __( 'Payment system is in sandbox mode.', 'pdcrequest' ) . '</p></div>';
            endif;
        }
        
        
        public function pdcrequest_letterhead() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_letterhead'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_letterhead]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_signature() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_signature'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_signature]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        
        
        public function pdcrequest_paypal_sandbox_account() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_sandbox_account'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_sandbox_account]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_paypal_sandbox_client_id() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_sandbox_client_id'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_sandbox_client_id]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_paypal_sandbox_secret() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_sandbox_secret'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_sandbox_secret]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_paypal_production_account() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_production_account'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_production_account]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_paypal_production_client_id() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_production_client_id'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_production_client_id]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        public function pdcrequest_paypal_production_secret() {
            $options = get_option('pdcrequest_settings');
            
                $value = $options['pdcrequest_paypal_production_secret'];
                ?>
                <textarea name='pdcrequest_settings[pdcrequest_paypal_production_secret]'><?php echo esc_textarea($value); ?></textarea>
                    
                <?php
        }
        
        
        
        /**
         * Load plugin textdomain.
         *
         * @since 1.0.0
         */
        function pdcrequest_load_textdomain() {
          load_plugin_textdomain( 'pdcrequest', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
        }
        
        
        function create_new_request_profile() {
            $request = new Setlr_Request_Profile();
            $html = $request->create_request();
            
            return $html;
        }
        
        public static function ajax_validate_request_profile() {
            $form = $_POST['form'];
            parse_str($form, $data);
            
            $request = new Setlr_Request_Profile();
            $quote = $request->validate_request($data);
            
            
            if ( ! is_wp_error( $quote ) ) :
                $message = sprintf( __( 'Our best quote for your project is: %s', 'pdcrequest'), $quote );
                echo '<p>' .  $message  . '</p>';
                echo self::customer_profile();
            else :
                echo 'is wp_error';
            endif;
            exit();
        }
        
        
        public static function customer_profile() {
            $request = new Extra_Users();
            $profile = $request->show_profile_form();
            
            return $profile;
        }
        
        public static function ajax_validate_full_profile() {
            write_log( 'Pdc_Requestmgt ajax_validate_full_profile');
            write_log( $_POST );
            
            $form = $_POST['form'];
            parse_str($form, $data);
            write_log( $data );
            
            
            echo 'ok';
            exit();
        }
}
 
global $requestmgt;
if ( !$requestmgt ):
$requestmgt = new Pdc_Requestmgt();
$requestmgt->bootstrap();
endif;