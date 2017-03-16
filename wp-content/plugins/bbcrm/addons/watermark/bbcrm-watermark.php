<?php
include_once(plugin_dir_path(__FILE__)."../../functions-bbcrm_template.php");
function get_watermark_confidential_files(){
	global $listing,$buyer;
	$html = '';
	$currentListingFiles = get_fileslisting();
	//print_r($currentListingFiles);
	if (!empty($currentListingFiles)) { 
		$html .= '<div id="confidential_files"><h3 class=detailheader>Confidential Files</h3>';
			foreach ($currentListingFiles as $mediaFile) {
				$downloadqy = base64_encode(http_build_query(array("uploadedBy"=>$mediaFile->uploadedBy,"fileName"=>$mediaFile->fileName,"firstName"=>$buyer->firstName,"lastName"=>$buyer->lastName,"company"=>$buyer->company)));
			   //$html .='<div class="property_detail" style="margin-bottom:3px;"><a target="_blank" href="/crm/uploads/media/'.$mediaFile->uploadedBy.'/'.$mediaFile->fileName.'">'.$mediaFile->mediaIcon.'&nbsp;'.$mediaFile->fileName.'</a></div>';
			   $html .='<div class="property_detail" style="margin-bottom:3px;"><a href="/download/'.$downloadqy.'">'.$mediaFile->mediaIcon.'&nbsp;'.$mediaFile->fileName.'</a></div>';
			} 
		$html .="</div>";
	    } 
	    return $html;
}

function bbcrm_download_rewrite() {
	global $wp_rewrite,$wp_query;	
//flush_rewrite_rules();		
//print_r(plugins_url('',__FILE__).'/download.php?dl=');
$addonURL=substr(wp_make_link_relative(plugins_url('',__FILE__)),1).'/download.php?dl=';
		add_rewrite_rule('download/(.*)$',$addonURL.'$1','top');
		add_rewrite_endpoint( 'download', EP_NONE );
}
add_action( 'init', 'bbcrm_download_rewrite', 2,0 );

if(shortcode_exists('confidential_files')){
	remove_shortcode('confidential_files');
	add_shortcode('confidential_files','get_watermark_confidential_files');
}
?>
