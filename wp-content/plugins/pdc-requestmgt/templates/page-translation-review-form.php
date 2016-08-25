<?php
//auth_redirect();
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php 
                if ( have_posts() ) : ?>

			
                    <header>
                        <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                    </header>
                    <?php
                    /*
                     * check for message from pdcrequestmgt plugin
                     */
                    if (function_exists('pdcrequest_show_notification')) echo pdcrequest_show_notification();
                    
                    // Start the loop.
                    while ( have_posts() ) : the_post();
	
                        echo Setlr_Revision_Request::show_revision_request_form();
	
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

<?php get_footer(); ?>
