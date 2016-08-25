<?php
//auth_redirect();
get_header(); ?>
        <!-- page-my-dashboard -->
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php 
                if ( have_posts() ) : ?>

			
                    
                    <?php
                    /*
                     * check for message from pdcrequestmgt plugin
                     */
                    
                    
                    // Start the loop.
                    while ( have_posts() ) : the_post();
	
                        /*
                         * Include the Post-Format-specific template for the content.
                         * If you want to override this in a child theme, then include a file
                         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                         */
                        get_template_part( 'content', 'page' );
	
                    // End the loop.
                    endwhile;
			
		// If no content, include the "No posts found" template.
		else :
			//get_template_part( 'content', 'none' );
                    echo 'no content';
                
		endif;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->
            <?php //do_action( 'pdcrequest_payment_form', 1, 100, 'GBP', 15 ); ?>
<?php get_footer(); ?>
