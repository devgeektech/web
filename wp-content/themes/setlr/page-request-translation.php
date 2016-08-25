<?php
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
                
		<?php //if ( have_posts() ) : ?>

			
				<header>
					<h1 class="page-title"><?php the_title(); ?></h1>
				</header>
                    <div class="entry-content"><?php do_action( 'pdcrequest_create_new_request' ); ?></div>
			
		<?php //endif; ?>	

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
