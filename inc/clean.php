<?php
/*********************
Start all the functions
at once for modish.
*********************/

// start all the functions
add_action('after_setup_theme','modish_startup');

if( ! function_exists( 'modish_startup ' ) ) {
	function modish_startup() {

	    // launching operation cleanup
	    add_action('init', 'modish_head_cleanup');
	    // remove WP version from RSS
	    add_filter('the_generator', 'modish_rss_version');
	    // remove pesky injected css for recent comments widget
	    add_filter( 'wp_head', 'modish_remove_wp_widget_recent_comments_style', 1 );
	    // clean up comment styles in the head
	    add_action('wp_head', 'modish_remove_recent_comments_style', 1);
	    // clean up gallery output in wp
	    add_filter('gallery_style', 'modish_gallery_style');

	    // enqueue base scripts and styles
	    add_action('wp_enqueue_scripts', 'modish_scripts_and_styles', 999);
	    
	    // additional post related cleaning
	    add_filter('get_image_tag_class', 'modish_image_tag_class', 0, 4);
	    add_filter('get_image_tag', 'modish_image_editor', 0, 4);

	} /* end modish_startup */
}


/**********************
WP_HEAD GOODNESS
The default WordPress head is
a mess. Let's clean it up.

Thanks for Bones
http://themble.com/bones/
**********************/

if( ! function_exists( 'modish_head_cleanup ' ) ) {
	function modish_head_cleanup() {
		// category feeds
		// remove_action( 'wp_head', 'feed_links_extra', 3 );
		// post and comment feeds
		// remove_action( 'wp_head', 'feed_links', 2 );
		// EditURI link
		remove_action( 'wp_head', 'rsd_link' );
		// windows live writer
		remove_action( 'wp_head', 'wlwmanifest_link' );
		// index link
		remove_action( 'wp_head', 'index_rel_link' );
		// previous link
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
		// start link
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
		// links for adjacent posts
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		// WP version
		remove_action( 'wp_head', 'wp_generator' );
	  // remove WP version from css
	  add_filter( 'style_loader_src', 'modish_remove_wp_ver_css_js', 9999 );
	  // remove Wp version from scripts
	  add_filter( 'script_loader_src', 'modish_remove_wp_ver_css_js', 9999 );

	} /* end head cleanup */
}

// remove WP version from RSS
if( ! function_exists( 'modish_rss_version ' ) ) {
	function modish_rss_version() { return ''; }
}

// remove WP version from scripts
if( ! function_exists( 'modish_remove_wp_ver_css_js ' ) ) {
	function modish_remove_wp_ver_css_js( $src ) {
	    if ( strpos( $src, 'ver=' ) )
	        $src = remove_query_arg( 'ver', $src );
	    return $src;
	}
}

// remove injected CSS for recent comments widget
if( ! function_exists( 'modish_remove_wp_widget_recent_comments_style ' ) ) {
	function modish_remove_wp_widget_recent_comments_style() {
	   if ( has_filter('wp_head', 'wp_widget_recent_comments_style') ) {
	      remove_filter('wp_head', 'wp_widget_recent_comments_style' );
	   }
	}
}

// remove injected CSS from recent comments widget
if( ! function_exists( 'modish_remove_recent_comments_style ' ) ) {
	function modish_remove_recent_comments_style() {
	  global $wp_widget_factory;
	  if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
	    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
	  }
	}
}

// remove injected CSS from gallery
if( ! function_exists( 'modish_gallery_style ' ) ) {
	function modish_gallery_style($css) {
	  return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
	}
}

/**********************
Enqueue CSS and Scripts
**********************/

// loading modernizr and jquery, and reply script
if( ! function_exists( 'modish_scripts_and_styles ' ) ) {
	function modish_scripts_and_styles() {
		if (!is_admin()) {
			wp_enqueue_style( 'uikit-style', get_template_directory_uri() . '/css/uikit.css' );
			wp_enqueue_style( 'uikit-flat-style', get_template_directory_uri() . '/css/uikit.almost-flat.css' );
			wp_enqueue_style( 'modish-wp-style', get_template_directory_uri() . '/css/style.css' );

			wp_register_script( 'modish-modernizr', get_template_directory_uri() . '/js/modernizr.js', array(), '2.6.2', false );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'uikit-js', get_template_directory_uri() . '/js/uikit.min.js', array(), '', true );

			
			global $is_IE;
		    if ($is_IE) {
		       wp_register_script ( 'html5shiv', "http://html5shiv.googlecode.com/svn/trunk/html5.js" , false, true);
		    }

			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
		}
	}
	add_action( 'wp_enqueue_scripts', 'modish_scripts_and_styles' );
}


/*********************
Post related cleaning
*********************/

// Clean the output of attributes of images in editor. Courtesy of SitePoint. http://www.sitepoint.com/wordpress-change-img-tag-html/
if( ! function_exists( 'modish_image_tag_class ' ) ) {
	function modish_image_tag_class($class, $id, $align, $size) {
		$align = 'align' . esc_attr($align);
		return $align;
	} /* end modish_image_tag_class */
}

// Remove width and height in editor, for a better responsive world.
if( ! function_exists( 'modish_image_editor ' ) ) {
	function modish_image_editor($html, $id, $alt, $title) {
		return preg_replace(array(
				'/\s+width="\d+"/i',
				'/\s+height="\d+"/i',
				'/alt=""/i'
			),
			array(
				'',
				'',
				'',
				'alt="' . $title . '"'
			),
			$html);
	} /* end modish_image_editor */
}