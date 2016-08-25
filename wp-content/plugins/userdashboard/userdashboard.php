<?php
/*
Plugin Name: Userdashboard
Plugin URI: http://ispectors.com.com/plugins/userdashboard/
Description: Adds a dashboard widget to count users by category
Version: 0.5.0
Author: pdc
Author URI: http://ispectors.com/philippe-de-chabot/
Text Domain: userdash
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	_e( 'Hi there!  I\'m just a plugin, not much I can do when called directly.', 'userdash' );
	exit;
}

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function userdash_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'userdash_dashboard_widget',         // Widget slug.
                 'User Dashboard Widget',         // Title.
                 'userdash_dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'userdash_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function userdash_dashboard_widget_function() {
	global $wpdb;
	// Display whatever it is you want to show.
	$sql = "SELECT COUNT(user_id) FROM $wpdb->usermeta WHERE meta_key = 'main_role' AND meta_value = %s";
	
	$values = array( 'customer', 'helper' );
	
	echo '<table style="width:100%">';
	echo '<thead><tr><th scope="col" style="text-align:left">' . __( 'User Types', 'userdash' ) . '</th><th scope="col" style="align:left">' . __( 'User Count', 'userdash' ) . '</th></tr></thead>';
	echo '<tbody>';
	foreach ( $values as $value ) :
	
		$count = $wpdb->get_var( $wpdb->prepare( $sql, $value ) );
		echo '<tr><th scope="row" style="text-align:left">' . esc_html( $value ) . '</th><td style="text-align:center">' . absint( $count ) . '</td></tr>';	
	endforeach;
	echo '</tbody>';
	echo '</table>';
	
}