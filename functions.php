<?php
/**
 * stars functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package stars
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

if ( current_user_can( "administrator" ) ) {
	define( 'ALLOW_UNFILTERED_UPLOADS', true );
};

add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes ( $existing_mimes=array() ) {
  $existing_mimes['svg'] = 'image/svg+xml';
  return $existing_mimes;
}
function fix_svg() {
    echo '<style type="text/css">
          .attachment-266x266, .thumbnail img {
               width: 100% !important;
               height: auto !important;
          }
          </style>';
 }
 add_action('admin_head', 'fix_svg');

remove_filter( 'the_content', 'wpautop' );

remove_filter( 'the_excerpt', 'wpautop' );

//VUE THEME

function ws_register_images_field() {
    register_rest_field( 
        'portfolio',
        'images',
        array(
            'get_callback'    => 'ws_get_images_urls',
            'update_callback' => null,
            'schema'          => null,
        )
    );
    register_rest_field( 
        'team',
        'images',
        array(
            'get_callback'    => 'ws_get_images_urls',
            'update_callback' => null,
            'schema'          => null,
        )
    );
    register_rest_field( 
        'partners',
        'images',
        array(
            'get_callback'    => 'ws_get_images_urls',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

add_action( 'rest_api_init', 'ws_register_images_field' );

function ws_get_images_urls( $object, $field_name, $request ) {
    $medium = wp_get_attachment_image_src( get_post_thumbnail_id( $object->id ), 'medium' );
    $medium_url = $medium['0'];

    $large = wp_get_attachment_image_src( get_post_thumbnail_id( $object->id ), 'large' );
    $large_url = $large['0'];

    return array(
        'medium' => $medium_url,
        'large'  => $large_url,
    );
}

function ws_register_cats() {
    register_rest_field( 
        'portfolio',
        'cats',
        array(
            'get_callback'    => 'ws_get_cats',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

add_action( 'rest_api_init', 'ws_register_cats' );

function ws_get_cats( $object, $field_name, $request ) {
    $formatted_categories = array();

    $categories = get_the_category( $object['id'] );
    $i = 0;

    foreach ($categories as $category) {
    	$formatted_categories[$i]['slug'] = $category->slug;
        $formatted_categories[$i]['name'] = $category->name;
        $i++;
    }

    return $formatted_categories;
}

function ws_register_catsf() {
    register_rest_field( 
        'portfolio',
        'hashTags',
        array(
            'get_callback'    => 'ws_get_catsf',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

add_action( 'rest_api_init', 'ws_register_catsf' );

function ws_get_catsf( $object, $field_name, $request ) {
    $formatted_categories = array();

    $categories = get_the_category( $object['id'] );

    foreach ($categories as $category) {
    	$formatted_categories[] = $category->name;
    }

    return $formatted_categories;
}



/**
 * remove wordpress version number from files
 **/
remove_action( 'wp_head', 'wp_generator' );


/**
 * Enable the option "show" in rest
 **/
add_filter( 'acf/rest_api/field_settings/show_in_rest', '__return_true' );

/**
 * Enable the option "edit" in rest
 **/
add_filter( 'acf/rest_api/field_settings/edit_in_rest', '__return_true' );

add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
}, 15);



if ( ! function_exists( 'stars_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function stars_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on stars, use a find and replace
		 * to change 'stars' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'stars', get_template_directory() . '/languages' );

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
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'stars' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'stars_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'stars_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function stars_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'stars_content_width', 640 );
}
add_action( 'after_setup_theme', 'stars_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function stars_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'stars' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'stars' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'stars_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function stars_scripts() {
	wp_enqueue_style( 'stars-style', get_stylesheet_uri(), array(), _S_VERSION );

}
add_action( 'wp_enqueue_scripts', 'stars_scripts' );

// VUE THEME
function enqueue_footer_scripts() {
	$theme_directory_name = get_template();


	$appBundlePath           = ABSPATH . "/dist/build.js";
	$appBundleUrl            = "/dist/build.js";
	
	$appBundleTime           = filemtime( $appBundlePath );

	wp_enqueue_script( 'stars-app-bundle', $appBundleUrl, "jquery", $appBundleTime );
}

add_action( 'wp_footer', 'enqueue_footer_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Customizer api.
 */
require get_template_directory() . '/inc/api2.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

add_filter('document_title_parts', 'my_custom_title');
function my_custom_title( $title ) {
 
  if (is_404()) {
    $title['title'] = '?????????????? ????????????????????';
  }

  return $title;
}

add_filter('jwt_auth_token_before_dispatch', 'add_user_role_response', 10, 2);
function add_user_role_response($data, $user){
        $data['roles'] = $user->roles;
        $data['id'] = $user->id;
        $data['city'] = get_field('gorod','user_'.$user->id);
        return $data;
}

add_role('zamershik', __(
   '????????????????'),
   array(
       'read'            => true, // Allows a user to read
       'create_posts'      => true, // Allows user to create new posts
       'edit_posts'        => true, // Allows user to edit their own posts
       'edit_others_posts' => true, // Allows user to edit others posts too
       'publish_posts' => true, // Allows the user to publish posts
       'manage_categories' => true, // Allows user to manage post categories
       )
);

add_role('zamershik', __(
   '????????????????'),
   array(
       'read'            => true, // Allows a user to read
       'create_posts'      => true, // Allows user to create new posts
       'edit_posts'        => true, // Allows user to edit their own posts
       'edit_others_posts' => true, // Allows user to edit others posts too
       'publish_posts' => true, // Allows the user to publish posts
       'manage_categories' => true, // Allows user to manage post categories
       )
);

/*
 * Add Revision support to WooCommerce Products
 * 
 */

add_filter( 'woocommerce_register_post_type_product', 'cinch_add_revision_support' );

function cinch_add_revision_support( $supports ) {
     $supports['supports'][] = 'revisions';

     return $supports;
}



function custom_admin_js() {

    $user = wp_get_current_user(); 

    if ($user->user_login != 'pv'){
       echo '"<script type="text/javascript">window.location.replace("https://door.webink.site/");</script>"'; 
    }
   
    
}
add_action('admin_footer', 'custom_admin_js');


add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {

     $user = wp_get_current_user(); 

    if ($user->user_login != 'pv'){

        show_admin_bar(false);
    }
}
