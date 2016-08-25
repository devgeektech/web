<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		// Post thumbnail.
		twentyfifteen_post_thumbnail();
	?>
<?php 
global $current_user;
get_currentuserinfo();
$request = get_post( $post->post_parent );
$requester_id = $request->post_author;
$author_id = $post->post_author;



if ( $requester_id == $current_user->ID || $author_id == $current_user->ID ) :
?>
	<header class="entry-header">
		<?php
			if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
				$request_link = '<a href="' . get_permalink( $post->post_parent ) . '">' . get_the_title( $post->post_parent ) . '</a>';
				
				$request_author_link = '<a href="' . get_author_posts_url( get_the_author_meta( 'ID', $requester_id ) ) . '">' . get_the_author_meta( 'display_name', $requester_id ) . '</a>';
				echo ( get_the_title( $post->post_parent ) ) ? '<p>' . sprintf( __( 'In reply to %s by %s', 'pdcrequest' ), $request_link, $request_author_link ) . '</p>' : '<p class="error">' .__( 'Error: can not find request!', 'pdcrequest' ) . '</p>';
				
			else :
				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			endif;
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
			/* translators: %s: Name of current post */
			the_content( sprintf(
				__( 'Continue reading %s', 'twentyfifteen' ),
				the_title( '<span class="screen-reader-text">', '</span>', false )
			) );

			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentyfifteen' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php
		// Author bio.
		if ( is_single() /* && get_the_author_meta( 'description' )  */ ) :
			get_template_part( 'author-bio' );
		endif;
	?>
    
    
	<footer class="entry-footer">
		<?php twentyfifteen_entry_meta(); ?>
        
		<?php edit_post_link( __( 'Edit', 'twentyfifteen' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->
<?php
else :
?>
    <header class="entry-header">
    	<h1 class="entry-title error"><?php _e( 'Sorry! You are not allowed to view this application!', 'pdcrequest' ); ?></h1>
    </header>
    <div class="entry-content"></div>
    <footer class="entry-footer"></footer>
<?php endif; ?>
</article><!-- #post-## -->
