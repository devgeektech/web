<?php
wp_nonce_field( 'testimonials_metabox_save', 'testimonials_metabox_nonce' );

echo '<div style="width: 90%;">';

	printf( '<p><label>%s<input type="text" class="large-text" id="sub_title" name="tm[_sub_title]" value="%s" /></label></p>', __( 'Designation/Position: ', 'tm' ), esc_attr( get_post_meta(get_the_ID(), '_sub_title', true) ) );
	printf( '<p><span class="description">%s</span></p>', __( 'This text will display below the user name', 'tm' ) );

echo '</div>';

echo '<div style="width: 90%;">';

	printf( '<p><label>%s<input type="text" name="tm[_website]" id="website" class="large-text" value="%s" /></label></p>', __( 'Website URL: ', 'tm' ), esc_attr( get_post_meta(get_the_ID(), '_website', true) ) );
	printf( '<p><span class="description">%s</span></p>', __( 'Link of Persons Website', 'tm' ) );

echo '</div>';

echo '<div style="width: 90%;">';

	printf( '<p><label>%s<input type="text" name="tm[_website_text]" id="website_text" class="large-text" value="%s" /></label></p>', __( 'Website Name: ', 'tm' ), esc_attr( get_post_meta(get_the_ID(), '_website_text', true) ) );
	printf( '<p><span class="description">%s</span></p>', __( 'LinkText for Above URL', 'tm' ) );

echo '</div><br style="clear: both;" />';
