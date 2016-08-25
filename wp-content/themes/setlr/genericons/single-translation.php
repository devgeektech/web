<?php
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main template-single-translation.php" role="main">
                        
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
                        /*
                         * check for message from pdcrequestmgt plugin
                         */
                        if (function_exists('pdcrequest_show_notification')) echo pdcrequest_show_notification();
			/*
			 * Include the post format-specific template for the content. If you want to
			 * use this in a child theme, then include a file called called content-___.php
			 * (where ___ is the post format) and that will be used instead.
			 */
                        echo pdcrequest_show_original_post( get_the_ID() );
                        
			get_template_part( 'dashboard', 'translator' );

			

		// End the loop.
		endwhile;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
