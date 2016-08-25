<?php

/*
function pdcrequestmgt_render_gifts( $id ) {
	$terms = get_the_terms( $id, 'gift' );
						
if ( $terms && ! is_wp_error( $terms ) ) : 

	$gift_links = array();

	$html  = '<div class="gifts">';
	
	foreach ( $terms as $term ) {
		$html .= '<div class="' . esc_attr( $term->slug ) . '"><span class="gift-value">' .  get_option( 'taxonomy_' . $term->term_id ). '</span></div>';
	}
	
	$html .= '</div>';
	echo $html;
endif;

}
*/

/*
function pdcrequest_list_gifts() {
	$args = array(
    'orderby'           => 'name', 
    'order'             => 'ASC',
    'hide_empty'        => false, 
    'exclude'           => array(), 
    'exclude_tree'      => array(), 
    'include'           => array(),
    'number'            => '', 
    'fields'            => 'all', 
    'slug'              => '',
    'parent'            => '',
    'hierarchical'      => true, 
    'child_of'          => 0, 
    'get'               => '', 
    'name__like'        => '',
    'description__like' => '',
    'pad_counts'        => false, 
    'offset'            => '', 
    'search'            => '', 
    'cache_domain'      => 'core'
); 

$taxonomies = 'gift';

$terms = get_terms($taxonomies, $args);
return $terms;
}
*/



