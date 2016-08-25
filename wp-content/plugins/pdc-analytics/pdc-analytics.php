<?php
/*
Plugin Name: PdC-Analytics
Plugin URI: http://ispectors.com.com/plugins/pdc-analytics/
Description: Add Google Analytics Universal code on all pages including wp-login 
Version: 1.0
Author: pdc
Author URI: http://ispectors.com/philippe-de-chabot/
Text Domain: pdcanalytics
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	_e( 'Hi there!  I\'m just a plugin, not much I can do when called directly.', 'pdcanalytics' );
	exit;
}

function pdcanalytics_add_universal_code() {
	?>
	<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-59065417-1', 'auto');
  ga('send', 'pageview');

</script>
<?php
}

// add to login page
add_action('login_head', 'pdcanalytics_add_universal_code');

//add to other pages
add_action('wp_head', 'pdcanalytics_add_universal_code');
