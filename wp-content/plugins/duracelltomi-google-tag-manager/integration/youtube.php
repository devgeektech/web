<?php
function gtm4wp_youtube( $return, $url, $data ) {
  if ( false !== strpos( $return, "youtube.com" ) ) {
	  return str_replace( "feature=oembed", "feature=oembed&enablejsapi=1&origin=" . site_url(), $return );
	} else {
	  return $return;
	}
}

add_filter( "oembed_result", "gtm4wp_youtube", 10, 3 );

if ( ! is_admin() ) {
	wp_enqueue_script( "gtm4wp-youtube", $gtp4wp_plugin_url . "js/gtm4wp-youtube.js", array( "jquery" ), "1.0", false );
}
