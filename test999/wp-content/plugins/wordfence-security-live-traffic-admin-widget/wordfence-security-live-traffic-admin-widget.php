<?php
defined( 'ABSPATH' ) OR exit;
/*
Plugin Name: Wordfence Security - Live Traffic Admin Widget
Plugin URI: http://wordpress.org/plugins/wordfence-security-live-traffic-admin-widget/
Description: Monitor your site's real-time live traffic from the Main Dashboard page. This is a Wordfence extension and as such it does require Wordfence Security to be installed in order for it to function properly. Embed the Wordfence Live Traffic page into an admin widget alongside a few quickmenu actions. You can quickly enable/disable the Live Traffic scripts right from within the admin widget.
Version: 1.0.1
Author: Zoran C.
Author URI: http://zoranc.co/
License: GPL2
*/
class wordfence_LT_AW {
	public function __construct() {
  		load_plugin_textdomain( 'wfs-lt-aw', null, plugins_url( 'languages/', __FILE__ ) );
		if (is_admin()) {
		  	add_action('plugins_loaded', array( $this, 'check_for_wordfence' ));
		}
	}
	/**
	 * check_for_wordfence ()
	 *
	 * Check if wordfence is active and add necessary scripts otherwise display notice with instructions. 
	 * Listen for the $_GET request on the main page to toggle the liveTrafficEnabled setting.
	 * NOTE: liveTrafficEnabled setting is the final check and ajax scripts for live traffic view are only loaded if the setting is true
	 *
	 * @return none
	 */
	public function check_for_wordfence() {
	  if ( !class_exists( 'wordfence' ) ) {
		  add_action( 'admin_notices', array( $this, 'admin_wfs_lt_aw_notice_missing_wordfence' ) );
	  } else if ( current_user_can( 'manage_options' ) ) {
	    if ( isset($_GET['wordfence_live_traffic'] ) ) {
		    if ($_GET['wordfence_live_traffic'] == 'true') {
			    wfConfig::set('liveTrafficEnabled', 1);
		    } else if ($_GET['wordfence_live_traffic'] == 'false'){
			    wfConfig::set('liveTrafficEnabled', 0);
		    }
	    }
	    add_action( 'wp_dashboard_setup', array( $this, 'wfs_lt_aw_add_dashboard_widgets' ) );
	    if (wfConfig::get('liveTrafficEnabled')) {
		      add_action('wp_ajax_nopriv_wordfence_logHuman', 'wordfence::ajax_logHuman_callback');
		      add_action('wp_ajax_wordfence_logHuman', 'wordfence::ajax_logHuman_callback');
		      add_action('wp_ajax_nopriv_wordfence_doScan', 'wordfence::ajax_doScan_callback');
		      add_action('wp_ajax_wordfence_doScan', 'wordfence::ajax_doScan_callback');
		      add_action('wp_ajax_nopriv_wordfence_testAjax', 'wordfence::ajax_testAjax_callback');
		      add_action('wp_ajax_wordfence_testAjax', 'wordfence::ajax_testAjax_callback');
	        add_action('admin_enqueue_scripts', array( $this, 'admin_ajax_scripts'));
	    }
	    add_action('admin_enqueue_scripts', array( $this, 'admin_widget_menu_scripts'));
	  }
	}
	/**
	 * admin_wfs_lt_aw_notice_missing_wordfence ()
	 *
	 * The display notice shown if wordfence is missing or not active
	 *
	 * @return none
	 */
	public function admin_wfs_lt_aw_notice_missing_wordfence () {
	  echo '<div class="error"><p>' . sprintf( __( 'You must install and activate %sWordfence%s to use the Wordfence Live Traffic Admin Widget extension. For more information on this plugin you can visit the dedicated %sSupport Forum%s', 'wfs-lt-aw' ), '<a href="plugin-install.php?tab=search&s=wordfence&plugin-search-input=Search+Plugins" title="wordfence">', '</a>', '<a href="http://zoranc.co/support/support-forum/wordfence-security-live-traffic-admin-widget/" target="_blank" title="Wordfence Security - Live Traffic Admin Widget Support Forum" class="button button-primary">', '</a>' ) . '</p></div>';
	}
	/**
	 * admin_ajax_scripts ()
	 *
	 * Enqueue the admin ajax scripts required by Wordfence Live Traffic core
	 *
	 * TODO: Check which ajax action hooks are actually necessary
	 * @return none
	 */
	public function admin_ajax_scripts() {
		global $pagenow;
		if ('index.php' === $pagenow) {
		  // 
		  foreach(array('activate', 'scan', 'updateAlertEmail', 'sendActivityLog', 'restoreFile', 'bulkOperation', 'deleteFile', 'removeExclusion', 'activityLogUpdate', 'ticker', 'loadIssues', 'updateIssueStatus', 'deleteIssue', 'updateAllIssues', 'reverseLookup', 'unlockOutIP', 'loadBlockRanges', 'unblockRange', 'blockIPUARange', 'whois', 'unblockIP', 'blockIP', 'permBlockIP', 'loadStaticPanel', 'saveConfig', 'clearAllBlocked', 'killScan', 'saveCountryBlocking', 'saveScanSchedule', 'tourClosed', 'startTourAgain', 'downgradeLicense', 'addTwoFactor', 'twoFacActivate', 'twoFacDel', 'loadTwoFactor') as $func){
			add_action('wp_ajax_wordfence_' . $func, 'wordfence::ajaxReceiver');
		  }
		  wp_enqueue_script('json2');
		  wp_enqueue_script('jquery.tmpl', plugins_url('wordfence/js/jquery.tmpl.min.js'), array('jquery'), '');
		  wp_enqueue_script('wordfenceAdminDashjs', plugins_url('wordfence/js/admin.js'), array('jquery'), '');
		}
	}
	/**
	 * admin_widget_menu_scripts ()
	 *
	 * Enqueue the admin styles and scripts required by this plugin
	 *
	 * @return none
	 */
	public function admin_widget_menu_scripts() {
		global $pagenow;
		if ('index.php' === $pagenow) {
		  wp_enqueue_style('wordfence_lt_aw_css', plugins_url( '/assets/css/wordfence-security-live-traffic-admin-widget.css', __FILE__ ), '');
		  wp_enqueue_script('wordfence_lt_aw_js', plugins_url( '/assets/js/wordfence-security-live-traffic-admin-widget.js', __FILE__ ), array('jquery'), '');	  
		}
	}
	public function wfs_lt_aw_add_dashboard_widgets() {
		wp_add_dashboard_widget(
			 'wfs_lt_aw_dashboard_widget',
			 'Wordfence Live Activity',
			 array($this, 'wfs_lt_aw_dashboard_widget_function')
		);
	}
	public function wfs_lt_aw_dashboard_widget_function() {
    $html = "";
    ob_start();
    include (plugin_dir_path(__FILE__) . 'includes/widget-menu.php');
    if (wfConfig::get('liveTrafficEnabled')) {
	    include (plugin_dir_path(__FILE__) . '../wordfence/lib/menu_activity.php');
    }
    $html .= ob_get_clean();
    echo $html;
  }
}
$wfs_lt_aw = new wordfence_LT_AW;
