<?php

/*
Plugin Name: Helper Dashboard
Plugin URI: http://ispectors.com/plugin/helper-dashboard
Description: Add a helper dashboard to any page
Version: 1.0
Author: p2chabot
Author URI: http://ispectors.com/philippe-de-chabot
Text Domain: pdchp
Domain Path: /lang/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;


add_shortcode( 'helper-dashboard', 'pdc_helper_dashboard' );

function pdc_helper_dashboard() {
	global $wpdb;
	
	$user_id = get_current_user_id();
	
	
	//$sql = $wpdb->prepare( "SELECT DISTINCT jl.ID, jl.post_title, jl.post_content, jl.post_date, jl.post_author FROM $wpdb->posts ja JOIN $wpdb->posts jl ON jl.ID = ja.post_parent AND jl.post_type = 'job_listing' WHERE ja.post_author = %d AND ja.post_type = 'job_application'", $user_id );
	
	$sql = $wpdb->prepare( "SELECT DISTINCT jl.ID, jl.post_title, jl.post_content, jl.post_date, jl.post_author
		FROM $wpdb->posts jl
		JOIN $wpdb->postmeta pm ON pm.meta_key =  '_candidate_user_id'
		AND pm.meta_value = %d
		JOIN $wpdb->posts ja ON jl.ID = ja.post_parent
		AND ja.ID = pm.post_id
		AND ja.post_type =  'job_application'
		WHERE jl.post_type =  'job_listing'", $user_id );
	$jobs_applied_to = $wpdb->get_results( $sql );
	
	if ( $jobs_applied_to ) :
		
		$output  = '<table id="jobs-applied-to" class="user-' . esc_attr( $user_id ) . '">';
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '<th>' . __( 'Request Title', 'pdchp' ) . '</th>';
		$output .= '<th>' . __( 'Date', 'pdchp' ) . '</th>';
		$output .= '<th>' . __( 'Actions', 'pdchp' ) . '</th>';
		$output .= '</tr>';
		$output .= '</thead>';
		
		$output .= '<tbody>';
		
		foreach( $jobs_applied_to as $job ) :
			$output .= '<tr>';
			$output .= '<td>' . esc_html__( $job->post_title ) . '</td>';
			$output .= '<td>' . esc_html__( $job->post_date ) . '</td>';
			$output .= '<td><a class="job-request-chat" title="chat" data-user2="' . absint( $job->post_author ) . '" href="#">' . esc_html__( 'chat', 'pdchp' ) . '</a></td>';
			$output .= '</tr>';
		endforeach;
		$output .= '</tbody>';
		
		$output .= '</table>';
	else :
		$output = '<p>' . __( 'You have not yet applied to Help requests', 'pdchp' ) . '</p>';
	endif;
	
	return $output;
}