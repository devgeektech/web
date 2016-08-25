<?php auth_redirect(); ?>

<form id="translation-<?php the_ID(); ?>" class="translator-dashboard" method="post" action="<?php echo admin_url('admin.php'); ?>">
    <?php global $post;
    
    $requestlang = get_post_meta( $post->post_parent, 'requestlang', true );  ?>
    <h2><?php printf( __( 'Your Translation To %s', 'pdcrequest'), $requestlang ); ?></h2>
    
    <label><?php _e( 'Title', 'pdcrequest' ); ?><input type="text" name="post-title" value="<?php echo esc_html( get_the_title() ); ?>" required></label>
	
    	
    <label><?php _e( 'Content', 'pdcrequest' ); ?><textarea name="post-content"><?php echo esc_textarea( get_the_content()); ?></textarea></label>
	

<?php echo pdcrequest_show_appropriate_buttons(); ?>
</form>
