<?php
/**
 * The template for displaying Author bios
 *
 */
?>

<div class="author-info">
	
	<div class="author-avatar">
		<?php
		/**
		 * Filter the author bio avatar size.
		 *
		 * @since Twenty Fifteen 1.0
		 *
		 * @param int $size The avatar height and width size in pixels.
		 */
		pdcrequest_author_photo( get_the_ID() );
		?>
	</div><!-- .author-avatar -->
	
	<div class="author-description">
		<p class="author-title">
                    
			<?php echo get_the_author_meta( 'nickname'); ?>
                    
        </p>
        
	</div><!-- .author-description -->
</div><!-- .author-info -->
