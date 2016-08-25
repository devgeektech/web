<?php

global $post;

$original = get_post( $post->post_parent );

?>
<article id="post-<?php echo absint( $original->ID ); ?>" class="request type-request status-publish hentry">
	<div class="author-metadata">
            
		
        <div class="entry-metadata"><?php setlr_entry_meta(); ?></div>
    </div>
	<?php
		if ( is_single( $original->ID ) ) :
		// Post thumbnail.
			setlr_post_thumbnail();
		endif;
	?>

	<header class="entry-header">
		<h1 class="entry-title"><?php echo get_the_title( $original->ID ); ?></h1>

	</header><!-- .entry-header -->

	<div class="entry-content">
    	
		<?php
        echo autop( $original->post_content );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		
	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
