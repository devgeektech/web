content-request.php
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    
    <div class="author-metadata">
		<?php get_template_part( 'author', 'bio' ); ?>
        <div class="entry-metadata"><?php setlr_entry_meta(); ?></div>
    </div>
	

	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			endif;
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
                <?php echo get_the_ID(); ?>
		
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php edit_post_link( __( 'Edit', 'setlr' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->