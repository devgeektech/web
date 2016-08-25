<?php
get_header(); 
 ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                    <article class="entry">
                        <header class="entry-header">
                            <h1 class="page-title"><?php the_title(); ?></h1>
                        </header>
                        <div class="entry-content">
                             <?php the_content(); ?>
                            <?php var_dump( $data ); ?>
                        </div>
                        <footer class="entry-footer"></footer>
                    </article>
			
                <?php endwhile; endif; ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
