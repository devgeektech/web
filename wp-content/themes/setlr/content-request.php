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
                <?php echo pdcrequest_format_date( get_the_ID() ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
                <?php echo pdcrequest_show_request( get_the_ID() ); ?>
		
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php if ( Pdc_Requestmgt::maybe_show_apply(get_the_ID())) :
                            pdcrequest_apply_button( get_the_ID() );
                        endif;
                        
                        edit_post_link( __( 'Edit', 'setlr' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->