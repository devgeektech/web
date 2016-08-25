<?php
get_header(); 
 ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                    <article class="entry">
                        <header class="entry-header">
                            <h1 class="page-title screen-reader-text"><?php the_title(); ?></h1>
                        </header>
                        <div class="entry-content">
                             <?php the_content(); ?>
                            <?php 
                            $nonce = ( isset($_GET['setlrnonce']) ) ? $_GET['setlrnonce'] : $_GET['amp;setlrnonce'];
                            write_log( $_GET );
                            if ( ! wp_verify_nonce( $nonce, 'request-form' ) ) :
                                echo 'error nonce';
                            else :
                                $project_id = ( isset( $_REQUEST['request_id'] ) ) ? absint( $_REQUEST['request_id']) : absint( $_REQUEST['amp;request_id']);
                                if (absint( $project_id ) ) :
                                    write_log( 'project_id=' . $project_id );
                                    echo do_action( 'pdcrequest_project_quote', $project_id );
                                else :
                                    write_log( 'error project_id=' . $project_id );
                                    echo 'error request_id';
                                endif;
                            endif;
                             ?>
                        </div>
                        <footer class="entry-footer"></footer>
                    </article>
			
                <?php endwhile; endif; ?>
		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
