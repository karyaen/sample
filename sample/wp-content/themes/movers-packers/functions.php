<?php  
/**
 * SKT Movers Packers functions and definitions
 *
 * @package SKT Movers Packers
 */

/**
 * Filter the except length to 30 characters.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function movers_packers_excerpt_length( $length ) {
    return 28;
}
add_filter( 'excerpt_length', 'movers_packers_excerpt_length', 999 );

/**
 * Set the content width based on the theme's design and stylesheet.
 */

if ( ! function_exists( 'movers_packers_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function movers_packers_setup() {
	load_theme_textdomain( 'movers-packers', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support('woocommerce');
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-header' );
	add_theme_support( 'title-tag' );
	
	global $content_width;  
	if ( ! isset( $content_width ) )
		$content_width = 640; /* pixels */ 	
	
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 350,
		'flex-height' => true,
	) );
	add_theme_support( 'custom-header', array( 'header-text' => false ) );	
	
	add_image_size('movers-packers-homepage-thumb',240,145,true);
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'movers-packers' ),
		'topmenu' => __( 'Top Menu', 'movers-packers' ),
		'footer' => __( 'Footer Menu', 'movers-packers' ),
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	add_editor_style( 'editor-style.css' );
} 
endif; // movers_packers_setup
add_action( 'after_setup_theme', 'movers_packers_setup' );


function movers_packers_widgets_init() { 

	register_sidebar( array(
		'name'          => __( 'Header Info', 'movers-packers' ),
		'description'   => __( 'Appears on header of site', 'movers-packers' ),
		'id'            => 'header-info',
		'before_widget' => '<div class="headerinfo">',	
		'after_widget'  => '</div>',	
		'before_title'  => '<h3 style="display:none">',
		'after_title'   => '</h3>',		
	) );	
	
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'movers-packers' ),
		'description'   => __( 'Appears on blog page sidebar', 'movers-packers' ),
		'id'            => 'sidebar-1',
		'before_widget' => '',		
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3><aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
	) );	
	
}
add_action( 'widgets_init', 'movers_packers_widgets_init' );


function movers_packers_font_url(){
		$font_url = '';		
		
		/* Translators: If there are any character that are not
		* supported by Roboto, trsnalate this to off, do not
		* translate into your own language.
		*/
		$roboto = _x('on','roboto:on or off','movers-packers');		
		
		
		/* Translators: If there has any character that are not supported 
		*  by Scada, translate this to off, do not translate
		*  into your own language.
		*/
		$scada = _x('on','Scada:on or off','movers-packers');	
		
		if('off' !== $roboto ){
			$font_family = array();
			
			if('off' !== $roboto){
				$font_family[] = 'Roboto:300,400,600,700,800,900';
			}
					
						
			$query_args = array(
				'family'	=> urlencode(implode('|',$font_family)),
			);
			
			$font_url = add_query_arg($query_args,'//fonts.googleapis.com/css');
		}
		
	return $font_url;
	}


function movers_packers_scripts() {
	wp_enqueue_style('movers-packers-font', movers_packers_font_url(), array());
	wp_enqueue_style( 'movers-packers-basic-style', get_stylesheet_uri() );
	wp_enqueue_style( 'nivoslider-style', get_template_directory_uri().'/css/nivo-slider.css' );
	wp_enqueue_style( 'movers-packers-main-style', get_template_directory_uri().'/css/responsive.css' );		
	wp_enqueue_style( 'movers-packers-base-style', get_template_directory_uri().'/css/style_base.css' );
	wp_enqueue_script( 'nivo-script', get_template_directory_uri() . '/js/jquery.nivo.slider.js', array('jquery') );
	wp_enqueue_script( 'movers-packers-custom_js', get_template_directory_uri() . '/js/custom.js' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'movers_packers_scripts' );

function movers_packers_pagination() {
	global $wp_query;
	$big = 12345678;
	$page_format = paginate_links( array(
	    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	    'format' => '?paged=%#%',
	    'current' => max( 1, get_query_var('paged') ),
	    'total' => $wp_query->max_num_pages,
	    'type'  => 'array'
	) );
	if( is_array($page_format) ) {
		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
		echo '<div class="pagination"><div><ul>';
		echo '<li><span>'. $paged . ' of ' . $wp_query->max_num_pages .'</span></li>';
		foreach ( $page_format as $page ) {
			echo "<li>$page</li>";
		}
		echo '</ul></div></div>';
	}
}

define('SKT_URL','http://www.sktthemes.net', 'movers-packers');
define('SKT_THEME_URL','http://www.sktthemes.net/themes', 'movers-packers');
define('SKT_THEME_DOC','http://sktthemesdemo.net/documentation/movers_documentation/#playing-with-customizer/', 'movers-packers');
define('SKT_PRO_THEME_URL','http://www.sktthemes.net/shop/movers-packers-wordpress-theme/', 'movers-packers');

define('SKT_LIVE_DEMO','http://sktthemesdemo.net/moverspackers/', 'movers-packers');
define('SKT_THEME_FEATURED_SET_VIDEO_URL','https://www.youtube.com/watch?v=310YGYtGLIM', 'movers-packers');
define('SKT_FREE_THEME_URL','http://www.sktthemes.net/product-category/free-wordpress-themes/', 'movers-packers');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template for about theme.
 */
require get_template_directory() . '/inc/about-themes.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


function movers_packers_custom_blogpost_pagination( $wp_query ){
	$big = 999999999; // need an unlikely integer
	if ( get_query_var('paged') ) { $pageVar = 'paged'; }
	elseif ( get_query_var('page') ) { $pageVar = 'page'; }
	else { $pageVar = 'paged'; }
	$pagin = paginate_links( array(
		'base' 			=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' 		=> '?'.$pageVar.'=%#%',
		'current' 		=> max( 1, get_query_var($pageVar) ),
		'total' 		=> $wp_query->max_num_pages,
		'prev_text'		=> '&laquo; Prev',
		'next_text' 	=> 'Next &raquo;',
		'type'  => 'array'
	) ); 
	if( is_array($pagin) ) {
		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');
		echo '<div class="pagination"><div><ul>';
		echo '<li><span>'. $paged . ' of ' . $wp_query->max_num_pages .'</span></li>';
		foreach ( $pagin as $page ) {
			echo "<li>$page</li>";
		}
		echo '</ul></div></div>';
	} 
}

if ( ! function_exists( 'movers_packers_the_custom_logo' ) ) :
/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function movers_packers_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}
endif;


// get slug by id
function movers_packers_get_slug_by_id($id) {
	$post_data = get_post($id, ARRAY_A);
	$slug = $post_data['post_name'];
	return $slug; 
}

function movers_packers_customizer_styles() {
    wp_enqueue_style( 'movers-packers-customizer', trailingslashit( get_template_directory_uri() ).'css/customizer.css', null );
}
add_action( 'customize_controls_print_styles', 'movers_packers_customizer_styles', 99 );