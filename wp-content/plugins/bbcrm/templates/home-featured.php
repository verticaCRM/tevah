<?php
global $apiserver, $a;
$searchparams = '';
$searchparams .= ($a['franchise']==true)?"c_listing_franchise_c=Yes;":'';
$searchparams .= ($a['featured']==true)?"c_listing_featured_c=1;":'';
$searchparams .= (!empty($a['broker']))?"c_assigned_user_id=".$a["broker"].";":'';

$json = x2apicall(array('_class'=>'Clistings/by:c_sales_stage=Active;'.$searchparams.".json"));
$featured_listings =json_decode($json);

if(is_array($featured_listings->directUris)){
	if(intval($a['num'])>0){
		$randnum = (intval($a["num"])>count($featured_listings->directUris))?count($featured_listings->directUris):intval($a["num"]);
		$featuredlistings = array_rand($featured_listings->directUris,intval($randnum));
	}else{
		$featuredlistings = array_keys($featured_listings->directUris);
}
$single=false;
}else{

$listing = $featured_listings; //one object
$featuredlistings = array(0=>1);
$single=true;
}
?>
<div id="divA" class="wpp_row_view wpp_property_view_result" style="margin:0 auto; vertical-align:top !important; display: flex;">
		<?php
	foreach ($featuredlistings as $idx=>$val){
		if(!$single){
			$json = x2apicall(array('_url'=>$featured_listings->directUris[$val]));
			$listing = json_decode($json);
		}
/* Plugin function to propagate the frontend URL into the CRM if blank */
		
		$frontendURL = (!empty($listing->c_listing_frontend_url))?$listing->c_listing_frontend_url:crm_add_frontend_url($listing);
		
		$json = x2apicall(array('_class'=>'Media/by:description=thumbnail;associationId='.$listing->id.'.json'));
		$thumbnail = json_decode($json);
//print_r($thumbnail);
		$home_propertycss=($listing->c_listing_exclusive_c)?'home_exclusive':'home_featured';
		?>
		<div 	id="divB"
			class="theme-background2-lt property_div clearfix <?php echo $home_propertycss;?>" 
    			style="
				position: relative;
				margin-right: 12px;
				width: 230px;
				border: 1px solid #ddd;
				margin-bottom: 18px;
				display: inline-block;">
					<?php
			$listingtxt = ($a['franchise']==true)?__("Franchise","bbcrm"):__("Listing","bbcrm");
			if($listing->c_listing_exclusive_c):	
					?>
				<div 	id="divC"
					style="	width: 230px;
						padding: 3px 6px;
						text-align: left;
						font-weight: 700;"  
					class="theme-background2-dk homepage-exclusive-listing-header"
				>
					<?php 
					_e("Premiere",'bbcrm'); echo " ".$listingtxt; 
					?>
				<!--divC-->
				</div>
					<?php 
			else:
					?>  
				<div	id="divD"
					style="	width: 230px;
						padding: 3px 6px;
						text-align: left;
						font-weight: 700;" 
					class="theme-background2-dk homepage-featured-listing-header"
				>
						<?php 
					_e("Featured",'bbcrm');echo " ".$listingtxt; 
						?>
				<!--divD-->
				</div>
		<?php 
			endif;
		?>
			<div id='thumbdiv' style="width:230px;height:100px;overflow:hidden;">
		<?php 
				if(!$thumbnail->fileName){
					echo '<a href="'.$frontendURL.'" class="theme-color2-dk listing_link" data-id="'.$listing->id.'"><img src="'.plugin_dir_url(__DIR__).'images/noimage.png"></a>';
				}else{
					echo '<a href="'.$frontendURL.'" class="theme-color2-dk listing_link" data-id="'.$listing->id.'"><img src="'.get_bloginfo('url').'/crm/uploads/media/'.$thumbnail->uploadedBy.'/'.$thumbnail->fileName.'" style="width:230px" /></a>';
				}
		?>
				<!--thumbdiv-->
				</div>
				<!-- Here is the new div-->
				<div id="divE" style="width:230px;margin-bottom: 50px;" class="featured_under_image">
					<h4 style="text-align: left;" class="featured-listing-title">
						<a	href="<?php echo $frontendURL; ?>" 
							class="theme-color2-dk listing_link" 
							data-id='<?php echo $listing->id;?>'
						>
						<?php echo $listing->c_name_generic_c;?>
						</a>
					</h4>
					<div id="divF">
						<ul class="">
							<?php 
							if($listing->c_listing_region_c):
							?>
								<li class="property_region overview_detail">
									<?php 
									if($listing->c_listing_town_c):
										echo ($listing->c_listing_town_c).",";
									endif; 
									?>
									<?php echo $listing->c_listing_region_c; ?>
								</li>
								<?php 
							endif; 
								?>
							<li class="overview_detail"><?php echo __('Asking:','bbcrm').' '.$listing->c_currency_id." ".number_format(str_replace("$","",$listing->c_listing_askingprice_c)) ;?></li>
							<li class="overview_detail"><?php echo __('Gross Sales:','bbcrm').' '.$listing->c_currency_id." ".number_format(str_replace("$","",$listing->c_financial_grossrevenue_c)) ;?></li>
							<li class="overview_detail"><?php echo __('Year Established:','bbcrm').' '.$listing->c_yearEstablished; ?></li>
						</ul>
					<!--divF-->
					</div>
				<!--divE-->
				</div>
				<div	id="featured_more_info"
					style="
						position: absolute;
						width: 130px;
						border: 2px solid ;
						border-radius: 4px;
						color: #fff;
						margin-bottom: 18px;
					"
					class="theme-background2-dk"
				>
					<a href="<?php echo $frontendURL; ?>" class="listing_link" data-id='<?php echo $listing->id;?>'>More Info</a>
				<!--featured_more_info-->
				</div>
				<!-- here is the closing tag of new div -->
			<!--divB-->
			</div>
	<?php } ?>
	<br clear=all>
<!--divA-->
</div>
