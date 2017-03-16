<?php
include_once('functions-bbcrm_template.php');

if ( function_exists('register_sidebar') ){
	$sidebars = array(
		'property-registered'=>array('title'=>'Property Sidebar for Registered Users'),
		'property-unregistered'=>array('title'=>'Property Sidebar for Unregistered Users'),
		'portfolio'=>array('title'=>'Portfolio Sidebar'),
		'page'=>array('title'=>'Page Sidebar'),
		'home-bl'=>array('title'=>'Home Bottom Left'),
		'home-bm'=>array('title'=>'Home Bottom Middle'),
		'home-br'=>array('title'=>'Home Bottom Right'),
		'home-ft'=>array('title'=>'Home Footer','before_title'=>'<h3>','after_title'=>'</h3>')
	);
	foreach ($sidebars AS $id=>$args){
		register_sidebar(array(
			'id'	=>$id,
			'name'	=>$args["title"],
			'before_widget' => '<div class="sidewidget">',
			'after_widget' => '</div>',
			'before_title' => (($args["before_title"])?$args["before_title"]:'<h2>'),
			'after_title' => (($args["after_title"])?$args["after_title"]:'<h2>'),
		));
	}
}

if ( function_exists('register_nav_menus') )
	register_nav_menus(array(
			'main_nav' => 'Main Navigation',
			'footer_nav' => 'Footer Site Map',
			'sec_nav' => 'Secondary Navigation',
		));    

// Register Theme Features
function custom_theme_features()  {
	// Add theme support for Translation
	load_theme_textdomain( 'bbcrm', get_template_directory() . '/language' );	
}
add_action( 'after_setup_theme', 'custom_theme_features' );

// Unescapes URLs in titles. No idea why.
function decode_slug($title) {
    return urldecode($title);
}
add_filter('sanitize_title', 'decode_slug');

// We need to register the custom post type so we can use /listing/ in the URL
function add_listing_type(){
	$args = array(
		'publicly_queryable' => true,
		'query_var'          => true,
	);
	register_post_type( 'listing', $args ); 
}
add_action( 'init', 'add_listing_type' );

// Listing post_types don't exist really, so it throws a 404. This loads the correct template and unsets the 404 status.
function bbcrm_listing_template(){
    global $wp_query;
    if( isset($wp_query->query['listing']) || $wp_query->_post_type=='listing' ){
		status_header( 200 );
		$wp_query->is_404=false;
		// Borrows helper function get_custom_template() from shortcodes
		return (get_custom_template("page-listing.php"));
    }
}
add_filter( '404_template', 'bbcrm_listing_template' );

//Enables searching by frontend URL in the CRM.
function crm_add_frontend_url($listing){
	$frontendURL = '';
	if(empty($listing->c_listing_frontend_url)){
		$frontendURL = '/listing/'.sanitize_title($listing->c_name_generic_c)."/";
		$json = x2apipost( array('_method'=>'PUT','_class'=>'Clistings/'.$listing->id.'.json','_data'=>array('c_listing_frontend_url'=>$frontendURL) ) );
	}else{
		$frontendURL = $listing->c_listing_frontend_url;
	}
	return $frontendURL;
}

//Hide all non -child themes
function kill_theme_wpse_188906($themes) {
	foreach($themes as $name=>$data){
		if(!strpos($name,'-child')){
			  unset($themes[$name]);
		}
	}
	return $themes;
}
add_filter('wp_prepare_themes_for_js','kill_theme_wpse_188906');
?>
