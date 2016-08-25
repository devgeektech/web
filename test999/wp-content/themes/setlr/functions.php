<?php

if ( ! function_exists( 'setlr_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * @since Twenty Fifteen 1.0
 */
function setlr_setup() {

	//include_once('inc/MailChimp.php');
	//include_once('inc/mailchimpController.php');

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on twentyfifteen, use a find and replace
	 * to change 'twentyfifteen' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'setlr', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 825, 510, true );
        

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu',      'setlr' ),
		'social'  => __( 'Footer Social', 'setlr' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

}
endif; // setlr_setup
add_action( 'after_setup_theme', 'setlr_setup' );


/**
 * Enqueue scripts and styles.
 *
 */
function setlr_scripts() {
	// Add custom fonts, used in the main stylesheet.
	//wp_enqueue_style( 'setlr-fonts', setlr_fonts_url(), array(), null );

	// Add Genericons, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.2' );

	// Load our main stylesheet.
	wp_enqueue_style( 'setlr-style', get_stylesheet_uri() );

	// Load the Internet Explorer specific stylesheet.
	//wp_enqueue_style( 'twentyfifteen-ie', get_template_directory_uri() . '/css/ie.css', array( 'twentyfifteen-style' ), '20141010' );
	//wp_style_add_data( 'twentyfifteen-ie', 'conditional', 'lt IE 9' );

	// Load the Internet Explorer 7 specific stylesheet.
	//wp_enqueue_style( 'twentyfifteen-ie7', get_template_directory_uri() . '/css/ie7.css', array( 'twentyfifteen-style' ), '20141010' );
	//wp_style_add_data( 'twentyfifteen-ie7', 'conditional', 'lt IE 8' );

	wp_enqueue_script( 'setlr-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20141010', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'setlr-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20141010' );
	}

	/* load functions.js or functions.min.js (production file minimized) */
	//wp_enqueue_script( 'setlr-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), '20141212', true );
	wp_enqueue_script( 'setlr-script', get_template_directory_uri() . '/js/functions.min.js', array( 'jquery' ), '20141212', true );

	wp_localize_script( 'setlr-script', 'screenReaderText', array(
		'expand'   => '<span class="screen-reader-text">' . __( 'expand child menu', 'setlr' ) . '</span>',
		'collapse' => '<span class="screen-reader-text">' . __( 'collapse child menu', 'setlr' ) . '</span>',
	) );

}
add_action( 'wp_enqueue_scripts', 'setlr_scripts' );


function setlr_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Homepage Widget Area', 'setlr' ),
		'id'            => 'widget-area-front-page',
		'description'   => __( 'Add widgets here to appear in your homepage.', 'setlr' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
        
        register_sidebar( array(
		'name'          => __( 'Blog Widget Area', 'setlr' ),
		'id'            => 'widget-area-blog',
		'description'   => __( 'Add widgets here to appear in your blog sidebar.', 'setlr' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'setlr_widgets_init' );

/**
 * Customizer additions.
 *
 * @since Twenty Fifteen 1.0
 */
require get_template_directory() . '/inc/customizer.php';

function setlr_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Noto Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Noto Sans font: on or off', 'twentyfifteen' ) ) {
		$fonts[] = 'Noto Sans:400italic,700italic,400,700';
	}

	/* translators: If there are characters in your language that are not supported by Noto Serif, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Noto Serif font: on or off', 'twentyfifteen' ) ) {
		$fonts[] = 'Noto Serif:400italic,700italic,400,700';
	}

	/* translators: If there are characters in your language that are not supported by Inconsolata, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'twentyfifteen' ) ) {
		$fonts[] = 'Inconsolata:400,700';
	}

	/* translators: To add an additional character subset specific to your language, translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language. */
	$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'twentyfifteen' );

	if ( 'cyrillic' == $subset ) {
		$subsets .= ',cyrillic,cyrillic-ext';
	} elseif ( 'greek' == $subset ) {
		$subsets .= ',greek,greek-ext';
	} elseif ( 'devanagari' == $subset ) {
		$subsets .= ',devanagari';
	} elseif ( 'vietnamese' == $subset ) {
		$subsets .= ',vietnamese';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), '//fonts.googleapis.com/css' );
	}

	return $fonts_url;
}

/**
 * Get all widgets used on the home page.
 *
 * @since Jobify 1.0
 *
 * @return array $_widgets An array of active widgets
 */
function jobify_homepage_widgets() {
	global $wp_registered_sidebars, $wp_registered_widgets;

	$index            = 'widget-area-front-page';
	$sidebars_widgets = wp_get_sidebars_widgets();
	$_widgets         = array();

	if ( empty( $sidebars_widgets ) || empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) )
		return $_widgets;

	foreach ( (array) $sidebars_widgets[$index] as $id ) {
		$_widgets[] = isset( $wp_registered_widgets[$id] ) ? $wp_registered_widgets[$id] : null;
	}

	return $_widgets;
}


/**
 * Registers widgets, and widget areas.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_widgets_init() {
	register_widget( 'Jobify_Widget_Callout' );
	register_widget( 'Jobify_Widget_Video' );
	register_widget( 'Jobify_Widget_Blog_Posts' );
	register_widget( 'Jobify_Widget_Slider_Generic' );

	register_sidebar( array(
		'name'          => __( 'Homepage Widget Area', 'jobify' ),
		'id'            => 'widget-area-front-page',
		'description'   => __( 'Choose what should display on the custom static homepage.', 'jobify' ),
		'before_widget' => '<section id="%1$s" class="homepage-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="homepage-widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Sidebar', 'jobify' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Choose what should display on blog pages.', 'jobify' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="sidebar-widget-title">',
		'after_title'   => '</h3>',
	) );

	/*
	 * Figure out how many columns the footer has
	 */
	$the_sidebars = wp_get_sidebars_widgets();
	$footer       = isset ( $the_sidebars[ 'widget-area-footer' ] ) ? $the_sidebars[ 'widget-area-footer' ] : array();
	$count        = count( $footer );
	$count        = floor( 12 / ( $count == 0 ? 1 : $count ) );

	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'jobify' ),
		'id'            => 'widget-area-footer',
		'description'   => __( 'Display columns of widgets in the footer.', 'jobify' ),
		'before_widget' => '<aside id="%1$s" class="footer-widget %2$s col-md-' . $count . ' col-sm-6 col-xs-12">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="footer-widget-title">',
		'after_title'   => '</h3>',
	) );


}
add_action( 'widgets_init', 'jobify_widgets_init' );



/* Widgets */

require_once( get_template_directory() . '/inc/class-widget.php' );
$widgets = array(
	'class-widget-callout.php',
	'class-widget-video.php',
	'class-widget-blog-posts.php',
	'class-widget-slider-generic.php',
	'class-widget-stats.php'
);

foreach ( $widgets as $widget ) {
	require_once( get_template_directory() . '/inc/widgets/' . $widget );
}

if ( ! function_exists( 'setlr_post_thumbnail' ) ) :
/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 *
 */
function setlr_post_thumbnail() {
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
	?>

	<div class="post-thumbnail">
		<?php the_post_thumbnail(); ?>
	</div><!-- .post-thumbnail -->

	<?php else : ?>

	<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
		<?php
			the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) );
		?>
	</a>

	<?php endif; // End is_singular()
}
endif;


if ( ! function_exists( 'setlr_entry_meta' ) ) :
/**
 * Prints HTML with meta information for the categories, tags.
 *
 */
function setlr_entry_meta() {
	if ( is_sticky() && is_home() && ! is_paged() ) {
		printf( '<span class="sticky-post">%s</span>', __( 'Featured', 'setlr' ) );
	}

	$format = get_post_format();
	if ( current_theme_supports( 'post-formats', $format ) ) {
		printf( '<span class="entry-format">%1$s<a href="%2$s">%3$s</a></span>',
			sprintf( '<span class="screen-reader-text">%s </span>', _x( 'Format', 'Used before post format.', 'setlr' ) ),
			esc_url( get_post_format_link( $format ) ),
			get_post_format_string( $format )
		);
	}

	if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		/*
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}
		*/
		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'F j, Y' ) ),
			get_the_date( 'F j, Y' ),
			esc_attr( get_the_modified_date( 'F j, Y' ) ),
			get_the_modified_date( 'F j, Y' )
		);

		printf( '<span class="posted-on"><span class="screen-reader-text">%1$s </span><a href="%2$s" rel="bookmark">%3$s</a></span>',
			_x( 'Posted on', 'Used before publish date.', 'setlr' ),
			esc_url( get_permalink() ),
			$time_string
		);
	}
/*
	if ( 'post' == get_post_type() ) {
		if ( is_singular() || is_multi_author() ) {
			printf( '<span class="byline"><span class="author vcard"><span class="screen-reader-text">%1$s </span><a class="url fn n" href="%2$s">%3$s</a></span></span>',
				_x( 'Author', 'Used before post author name.', 'setlr' ),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);
		}


		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'setlr' ) );
		if ( $categories_list && twentyfifteen_categorized_blog() ) {
			printf( '<span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'setlr' ),
				$categories_list
			);
		}

		$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'setlr' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Tags', 'Used before tag names.', 'setlr' ),
				$tags_list
			);
		}


	}

*/
	if ( is_attachment() && wp_attachment_is_image() ) {
		// Retrieve attachment metadata.
		$metadata = wp_get_attachment_metadata();

		printf( '<span class="full-size-link"><span class="screen-reader-text">%1$s </span><a href="%2$s">%3$s &times; %4$s</a></span>',
			_x( 'Full size', 'Used before full size attachment link.', 'setlr' ),
			esc_url( wp_get_attachment_url() ),
			$metadata['width'],
			$metadata['height']
		);
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( __( '0 Comment', 'setlr' ), __( '1 Comment', 'setlr' ), __( '% Comments', 'setlr' ) );
		echo '</span>';
	}
}
endif;

add_action( 'setlr_credits', 'setlr_credits' );
function setlr_credits() {
	?>
	<p class="site-credits"><?php printf( __( '&copy; %s %s - All Rights Reserved', 'setlr' ), date( 'Y', time() ), get_bloginfo( 'sitename' ) . ' Ltd' ); ?></p>
    <?php
}

function setlr_comments_nav($nav) {
	if (get_option('page_comments')) {
		$total_pages = get_comment_pages_count();
		$args = (array(
    			'echo' => false,
    			'prev_text' => '&laquo;',
    			'next_text' => '&raquo;',
    			'add_fragment' => '#comments'
				));
		if ($total_pages > 1) {
			$nav = '<div id="comment_nav" class="prev_next"><p class="previous">';
			$nav .= paginate_comments_links($args);
			$nav .= "</p></div>\n\n";
			}
		}
	return $nav;
}

add_filter('thesis_comments_navigation', 'my_comments_nav');


/**
 * Show admin bar on front-end for administrators only
 */
if ( ! current_user_can( 'manage_options' ) ) {
    show_admin_bar( false );
} else {
    show_admin_bar( true );
}

/**
 * Custom template tags for this theme.
 *
 * @since Twenty Fifteen 1.0
 */
require get_template_directory() . '/inc/template-tags.php';



//require get_template_directory() . '/inc/second-featured.php';