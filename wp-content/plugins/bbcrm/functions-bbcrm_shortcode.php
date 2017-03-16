<?php

add_shortcode('bbcrm_loginbar','bbcrm_get_loginbar');
add_shortcode('featuredsearch','get_featured_search');
add_shortcode('sidebarsearch','get_sidebar_search');
add_shortcode('featuredlistings','get_featured_listings');
add_shortcode('searchbyid','get_id_search');
add_shortcode('confidential_files','get_confidential_files');

add_filter( 'no_texturize_shortcodes', 'shortcodes_to_exempt_from_wptexturize' );

/*
Shortcode for BBCRM Loginbar
*/
function bbcrm_get_loginbar(){
bbcrm_load_textdomain();
	ob_start();
   include( get_custom_template("loginbar.php") );
   return ob_get_clean();
}

/*
Shortcode for BBCRM Search Widget
*/
function get_featured_search( $atts ){
	$a = shortcode_atts( array(
		'num'=>'4',    
		'title' => 'Businesses for Sale',
		'type' => 'all',
		'broker'=>'',
		'featured'=>1,
		'franchise'=>false
	), $atts );
	ob_start();
        include( get_custom_template("home-search.php") );
        return ob_get_clean();
}

/*
Shortcode for BBCRM Sidebar Search Widget
*/
function get_sidebar_search( $atts ){
	$a = shortcode_atts( array(
		'num'=>'4',    
		'title' => 'Businesses for Sale',
		'type' => 'all',
		'broker'=>'',
		'featured'=>1,
		'franchise'=>false
	), $atts );
	ob_start();
        include( get_custom_template("sidebar-search.php") );
        return ob_get_clean();
}

/*
Shortcode for BBCRM Featured Listings Widget
*/
function get_featured_listings($atts){
global $a;

	$a = shortcode_atts( array(
		'num'=>'4',    
		'franchise'=>0,
		'broker'=>'',
		'featured'=>1,
	), $atts );
	ob_start();
        include( get_custom_template("home-featured.php") );
        return ob_get_clean();
}

/*
Shortcode for BBCRM Search By ID Widget
*/
function get_id_search($atts){
global $a;

$a = shortcode_atts( array(
	'num'=>'4',    
	'franchise'=>0,
	'broker'=>'',
	'featured'=>1,
	'addbutton'=>true,
    ), $atts );
	ob_start();
        include( get_custom_template("portfolio-search.php") );
        return ob_get_clean();
}

/*
Helper Functions
*/
function get_custom_template($template){

	return (file_exists(plugin_dir_path(__FILE__)."custom-templates/".$template))?plugin_dir_path(__FILE__)."custom-templates/".$template:plugin_dir_path(__FILE__)."templates/".$template;
}

function shortcodes_to_exempt_from_wptexturize( $shortcodes ) {
	$shortcodes[] = 'featuredlistings';
	$shortcodes[] = 'featuredsearch';
	return $shortcodes;
}
/*
Shortcode for BBCRM Confidential files
*/
function get_confidential_files(){
	global $listing,$buyer;
	$html = '';
	$currentListingFiles = get_fileslisting();
	if (!empty($currentListingFiles)) { 
		$html .= '<div id="confidential_files"><h3 class=detailheader>Confidential Files</h3>';
			foreach ($currentListingFiles as $mediaFile) {
			   $html .='<div class="property_detail" style="margin-bottom:3px;"><a target="_blank" href="/crm/uploads/media/'.$mediaFile->uploadedBy.'/'.$mediaFile->fileName.'">'.$mediaFile->mediaIcon.'&nbsp;'.$mediaFile->fileName.'</a></div>';
			} 
		$html .="</div>";
	    } 
	    return $html;
} 
?>
