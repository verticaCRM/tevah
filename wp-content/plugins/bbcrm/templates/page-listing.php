<?php
global $pagetitle,$wp_query;
session_start();

//ini_set('display_errors','on');
//error_reporting(E_ALL);

$inportfolio = false;
$crmid = 0;
if(isset($_POST["id"])){
	$crmid = $_POST["id"];
}elseif(isset($_SESSION["listingid"]) ){
	$crmid = $_SESSION["listingid"];
}else{}
if($crmid>0){
$json = x2apicall(array('_class'=>'Clistings/'.$crmid.'.json'));
$listing = json_decode($json);
}else{

$trailing = (substr($_SERVER["REQUEST_URI"],-1)=="/")?"":"/";
$json = x2apicall(array('_class'=>'Clistings/by:c_listing_frontend_url='.substr($_SERVER["REQUEST_URI"].$trailing,0).'.json'));
$listing = json_decode($json);

if($listing->status=="404"){
        $unsanitized = str_replace("-","%25",substr($_SERVER["REQUEST_URI"].$trailing,9,-1));
	$json = x2apicall(array('_class'=>'Clistings/?c_name_generic_c='.$unsanitized.'&_partial=1&_escape=0'));
	$listing=json_decode($json);
	$listing=$listing[0]; //returns differently with this query
	}
}
$crmid = $listing->id;

$json = x2apicall(array('_class'=>'Clistings/'.$crmid.'/tags'));
$tags = json_decode($json);
$listingtags = array();
foreach ($tags as $idx=>$tag){
	$listingtags[] = urldecode(substr($tag, 1));
}
//print_r($listingtags);
/* Failsafe. Need to move to create flow */
/* Plugin function to propagate the frontend URL into the CRM if blank */
$frontendURL = crm_add_frontend_url($listing);
$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($listing->c_assigned_user_id).".json"));
$listingbroker =json_decode($json);

if(!$listingbroker->nameId){
$json = x2apicall(array('_class'=>'Brokers/by:nameId=House%20Broker_5.json'));
$listingbroker =json_decode($json);
}

if(is_user_logged_in() ){

unset($_SESSION["listingid"]);
	
	$json = x2apicall(array('_class'=>'Contacts/by:email='.urlencode($userdata->user_email).".json"));
	$buyer =json_decode($json);

$isuserregistered = ($buyer->c_buyer_status=="Registered")?true:false;
	$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($buyer->c_broker).".json"));
	$buyerbroker =json_decode($json);	

if(isset($_POST["add_to_portfolio"]) || isset($_POST['action']) && $_POST["action"]=="add_to_portfolio"){

	$json = x2apicall(array('_class'=>'Portfolio/by:c_listing_id='.$listing->id.";c_buyer=".urlencode($buyer->nameId).".json"));
	$prevlisting =json_decode($json);	

	if(!$prevlisting->status || $prevlisting->status=="404"){
	$data = array(
		'name'	=>	'Data Room listing for '.$listing->name,
		'c_listing'	=>	$listing->name,
		'c_listing_id'	=>	$listing->id,
		'c_buyer'	=>	$buyer->nameId,
		'c_buyer_id'	=>	$buyer->id,
		'c_release_status'	=>	'Added',
		'assignedTo'	=>	$buyerbroker->assignedTo,
	);

//print_r($data);
	$json = x2apipost( array('_class'=>'Portfolio/','_data'=>$data ) );
	$portfoliolisting =json_decode($json[1]);

//print_r($portfoliolisting);

	$json = x2apicall(array('_class'=>'Portfolio/'.$portfoliolisting->id.'.json'));
	$portfoliorelationships =json_decode($json);
	
	$json = x2apicall( array('_class'=>'Portfolio/'.$portfoliorelationships->id."/relationships?secondType=Contacts" ) );
	$rel = json_decode($json);
//echo "!!!";
//print_r($rel);

	$json = x2apipost( array('_method'=>'PUT','_class'=>'Portfolio/'.$portfoliolisting->id.'/relationships/'.$rel[0]->id.'.json','_data'=>$data ) );

//	print_r(json_decode($json));

	}
}
//print_r($buyer);
//print_r($listing);

//Is this listing in the user's portfolio?	
	$json = x2apicall(array('_class'=>'Portfolio/by:c_listing_id='.$listing->id.';c_buyer='.urlencode($buyer->nameId).'.json'));
	
	$portfoliolisting =json_decode($json);	
//echo"<br><br>".'Portfolio/by:c_listing_id='.$listing->id.';c_buyer='.urlencode($buyer->nameId).'.json';
//print_r($portfoliolisting);
	if($portfoliolisting->id){
	$inportfolio=true;		
		}
}
//////////////////
//print_r($listing);

		$status =$listing->c_sales_stage;
		$listing_id =$listing->id;
		$listing_dateapproved = $listing->c_listing_date_approved_c;
		$generic_name =$listing->c_name_generic_c;
		$description =$listing->description;
		$region=$listing->c_listing_region_c;
		$terms=$listing->c_listing_terms_c;
		$currency_symbol=$listing->c_currency_id;
		$grossrevenue=number_format($listing->c_financial_grossrevenue_c);
		$amount=number_format($listing->c_listing_askingprice_c);
		$downpayment=number_format($listing->c_listing_downpayment_c);
		$ownercashflow=number_format($listing->c_ownerscashflow);
		$brokername = $listing->assignedTo;
		$brokerid = substr($listing->c_assigned_user_id, strpos($listing->c_assigned_user_id, "_") + 1);;
		$categories = 	join(",",json_decode($listing->c_businesscategories)); //

$_SESSION["viewed_listings"][$listing_id] = array("brokerid"=>$listingbroker->name,"listingname"=>$generic_name);

	$cssclass = '';

if("Active"!=$status){
	//this listing is marked as inactive. This shouldn't be visible. Fail gracefully.
//echo $status;	
}
if( is_user_logged_in() ){
if($portfoliolisting->c_release_status== "Released"){
	$isaddressreleased = true;
	$cssclass = 'nareq_released';
	$generic_name = $listing->name_dba_c.' "'.$generic_name.'" ';
	$address = $listing->listing_address_c."<br>";
	$city = $listing->listing_city_c." ";
	$postal = $listing->listing_postal_c."<br>";
}

}

wp_enqueue_script('galleria',plugin_dir_url(__FILE__).'../js/galleria-1.4.2.min.js',array('jquery'),'1.4.2');
wp_enqueue_script('galleriatheme',plugin_dir_url(__FILE__).'../js/galleria.classic.min.js',array('jquery'));
wp_enqueue_style('galleriacss',plugin_dir_url(__FILE__).'../css/galleria.classic.css');

$pagetitle = get_bloginfo('name')." - ".$listing->c_name_generic_c;
get_header();
?>
<link rel="stylesheet" href="/wp-content/plugins/bbcrm/css/bootstrap.min.css" type="text/css" media="all">
<style type="text/css">
.sidebar-primary {
	float: none !important;
	width: 100% !important;
}
.cform {
    width: 100% !important;
    color: black;
}
.cform textarea, .cform input {
    width: 100% !important;
}
.sidebar .widget {
    padding: 20px !important;
}
.home-bottom .widget-title, .sidebar .widget-title {
    margin: auto !important;
    padding: 0 !important;
}
.textonly {
    width: auto !important;
}
</style>

<script>
document.title = '<?php echo $pagetitle;?>';
</script>
<section style="margin-top: 50px !important;" id="content" class="container" data="property">
	<div class="row" id="divA">
		<?php 
			if( is_user_logged_in() ){
				if($portfoliolisting->c_release_status== "Deleted"){
					echo '<div class="portfoliostatus deleted">&#10006; ' .	__("This property was removed from your portfolio",'bbcrm') . "</div>";
				}elseif($isaddressreleased){
					echo '<div class="portfoliostatus released"> &#9733; ' .	__("The address of this business is available to you",'bbcrm') . "</div>";
				}elseif($inportfolio){
					echo '<div class="portfoliostatus added">&#10003; ' .	__("This propery is in your portfolio",'bbcrm') . "</div>";
				}
			}
		?>
		<div style="width:100% !important" id="divB" role="main">
			<div id="divC" style="padding-top:10px;">
				<h1 style=" padding-left: 15px;" class="theme-color1 property-title  entry-title <?php echo $cssclass;?>"><?php echo $generic_name; ?></h1>
				<h3 style="padding-left: 15px;" class="theme_gray ">
					<?php 
						if($userdata){ 
							if($isaddressreleased): 
					?>
					<?php 		echo $listing->name." ".$address;
							endif; 
						}
						echo $city." ".$region;
					?>
				</h3>
			</div><!--divC-->
			<!--cont divB-->
			<div id="divD" class="entry-content col-md-9" style="width:70%;display:inline-block; padding-right:10px;">
				  <?php echo do_shortcode('[confidential_files]'); ?>
				<?php
					global $wpdb;
					$results = $wpdb->get_results( 'SELECT gp.* FROM x2_gallery_photo gp RIGHT JOIN x2_gallery_to_model gm ON gm.galleryId = gp.gallery_id WHERE gm.modelName="Clistings" AND gm.modelId='.$listing->id, OBJECT );
					if(!empty($results[0]->id)):
				?>
						<div id="divE" class="galleria">
							<?php
								foreach ($results as $image){
									echo "<img src='/crm/uploads/gallery/_".$image->id.".jpg' />";
								}
							?>
						</div><!--divE-->
						<script>
							Galleria.run('.galleria', {
								height: 400,
								width:750,
								debug:true
							});
						</script>
						<?php 
					endif; 
						?>
					<div id="divF" style="margin-top:36px;margin-bottom:10px;" class="container-fluid">
						<div id="divG" class="row"><!--this div is optional (extra info table)-->
							<div id="divH" class="col-md-4" style="border: 2px solid black;padding: 10px 5px;border-right: 0px;">
								<div style="width:100% !important; display:inline-block; float: left; border-bottom: 1px solid #6b9f67; text-align:left !important;margin-bottom: 5px;" class="">
									<label style="width: 100px !important;"><?php _e("Asking Price:", 'bbcrm');?></label> 
									<div style="background-color: #6b9f67; padding-left:7px;float:right; width:95px; "><?php echo $currency_symbol." ".$amount;?></div>
								</div> 
								<br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label ><?php _e("Gross Revenue:", 'bbcrm');?></label> <div style="background-color: #ddd; padding-left:7px;float:right; width:95px;  "><?php echo $currency_symbol." ".$grossrevenue;?></div></div><br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;"><?php _e("Cash Flow:", 'bbcrm');?></label> <div style="background-color: #ddd; padding-left:7px;float:right; width:95px; "><?php echo $currency_symbol." ".$ownercashflow;?></div ></div><br>
							</div><!--divH-->
							<div class="col-md-4" style="border: 2px solid black;padding: 10px 5px;border-right: 0px;">
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">EBITDA: </label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px; "> EBITDA </div ></div><br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">FF&E: </label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px;"><?php echo $currency_symbol . number_format($listing->c_ffae);?> </div ></div><br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">Inventory:</label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px; "> Inventory </div ></div><br>
							</div>
							<div class="col-md-4" style="border: 2px solid black;padding: 10px 5px;">
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">Real Estate:</label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px; "><?php echo $listing->c_listing_reavail_c;?></div ></div><br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">Established:</label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px;"> Established </div ></div><br>
								<div style="width:100% !important;display:inline-block; float: left; border-bottom: 1px solid #ddd; text-align:left !important;margin-bottom: 5px;" class=""><label style="width: 100px !important;">Employees: </label> <div  style="background-color: #ddd; padding-left:7px;float:right; width:95px;"><?php echo number_format($listing->c_listing_emp_ft_c);?></div ></div><br>
							</div>
						</div><!--divG-->
					</div><!--divF-->
					<!--cont divD-->
					<h2 class="theme-color1"><?php _e("Business Description","bbcrm");?></h2>
					<div class=""><?php echo nl2br($description); ?></div>
					<hr>
					<?php
						$detailsheader = __("Detailed Information", 'bbcrm');
						if($isuserregistered && $inportfolio){
							$detailsheader = __("Complete Business Profile", 'bbcrm');
						}
					?>
					<h3 class=detailheader onclick='jQuery("#propertydetails ").slideToggle()'><?php echo $detailsheader;?></h3>
					<div id="propertydetails" class="property_details_div">
						<div id="divPD" class="property_details">
							<div class="property_detail"><label><?php _e("Listed on:", 'bbcrm');?></label> <?php echo date('F j, Y',$listing_dateapproved); ?></div>
							<div class="property_detail" id="property_listing_id" data-id="<?php echo $listing_id;?>"><label><?php _e("Listing ID:", 'bbcrm');?></label> #<?php echo $listing_id; ?></div>		
							<div class="property_detail"><label><?php _e("Categories:", 'bbcrm');?></label> <?php echo $categories; ?></div>
							<div class="property_detail"><label><?php _e("State:", 'bbcrm');?></label> <?php echo $region;?></div>
							<div class="property_detail"><label><?php _e("Down Payment:", 'bbcrm');?></label> <?php echo $currency_symbol." ".$downpayment;?></div>
							<div class="property_detail"><label><?php _e("Terms:", 'bbcrm');?></label> <?php echo $terms;?></div>
							<?php 
								if( $inportfolio ):
									//print_r($listing);
									if($isaddressreleased){
							?>
							<br />
							<h4 class=detailheader><?php _e("Location", 'bbcrm');?></h4>
							<div class="property_detail"><label><?php _e("Address:", 'bbcrm');?></label> <?php echo $listing->c_listing_address_c;?></div>
							<div class="property_detail"><label><?php _e("City:", 'bbcrm');?></label> <?php echo $listing->c_listing_city_c;?></div>
							<div class="property_detail"><label><?php _e("State:", 'bbcrm');?></label> <?php echo $listing->c_listing_region_c;?></div>					
							<div class="property_detail"><label><?php _e("Zip/Postal:", 'bbcrm');?></label> <?php echo $listing->c_listing_postal_c;?></div>
							<h4 class=detailheader>Additional Information</h4>
							<div class="property_detail"><label>Reason for Selling:</label> <?php echo $listing->c_listing_reasonforselling_c;?></div>
							<div class="property_detail"><label>Hours of Operation:</label> <?php echo $listing->c_listing_hours_c;?></div>
							<div class="property_detail"><label>Lease Terms:</label> <?php echo $listing->c_Leaseterms;?></div>
							<div class="property_detail"><label>Lease Contract Date Start:</label> <?php echo date_format($listing->c_Contractdatestart);?></div>
							<div class="property_detail"><label>Lease Contract Date End:</label> <?php echo date_format($listing->c_Contractdateend);?></div>
							<div class="property_detail"><label>Lease Improvements:</label> <?php echo $listing->c_financial_leaseimpr_c;?></div>
							<div class="property_detail"><label>Lease Copy Available?</label> <?php echo $listing->c_listing_leasecopy_c;?></div>
							<div class="property_detail"><label>Security:</label> <?php echo $listing->c_listing_security_c;?></div>
							<div class="property_detail"><label>Rental Increase:</label> <?php echo $currency_symbol . number_format($listing->c_financial_rentincrease_c);?></div>
							<?php 
									} 
							?>
							<h3 class=detailheader>Business Information</h3>
							<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Franchise?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_franchise_c;?></td>
						<td class="listingtablelabel">New Franchise?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_newfranchise_c;?></td>
					</tr>
					<tr>
						<td height="22" class="listingtablelabel">Relocatable?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_relocatable_c;?></td>
						<td class="listingtablelabel">Home-Based Business?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_homebusiness_c;?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Currently Operating?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_currently_operating_c;?></td>
						<td class="listingtablelabel">Support/Training?</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_support_training_c;?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Real Estate Available?</td>
						<td class="listingtabledata"></td>
						<td class="listingtablelabel">Store Size (Sq.m.):</td>
						<td class="listingtabledata"><?php echo number_format($listing->c_listing_area_c);?></td>
				 	</tr>
					<tr>
						<td class="listingtablelabel">Parking Spaces:</td>
						<td class="listingtabledata"><?php echo $listing->c_listing_pkgspace_c;?></td>
						<td class="listingtablelabel">Inventory Value:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_inventoryval_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">FT Employees:</td>
					  	<td class="listingtabledata"><?php echo number_format($listing->c_listing_emp_ft_c);?></td>
						<td class="listingtablelabel">PT Employees:</td>
					  	<td class="listingtabledata"><?php echo number_format($listing->c_listing_emp_pt_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">FF&E:</td>
					  	<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_ffae);?></td>
					  	<td class="listingtablelabel">Rent up to Date?</td>
					  	<td class="listingtabledata"><?php echo $listing->c_listing_rentutd_c;?></td>
					</tr>
				</table>
				<div style="height:10px;"></div>
				<div class="property_detail"><label>Inventory/Stock Included in Price?</label> <?php echo $listing->c_listing_inventory_incl_c;?></div>   
				<div class="property_detail"><label>Recent Leasehold Improvements:</label> <?php echo $currency_symbol . number_format($listing->c_recentleaseholdimprovements);?></div>
				<p>&nbsp;</p>
				<h3 class=detailheader>Financial Information</h3>
		                <h4 class=detailheader>Income</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Gross Sales:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_sales_c);?></td>
						<td class="listingtablelabel">Monthly Gross Sales:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_sales_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Gross Revenue:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_grossrevenue_c);?></td>
						<td class="listingtablelabel">Monthly Gross Revenue:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_revenue_c);?></td>
					</tr>					
					<tr>
						<td class="listingtablelabel">Less Sales Tax (-):</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_lesssalestax);?></td>
						<td class="listingtablelabel">Monthly Gross Profit:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_profit_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Cost of Goods Sold (%):</td>
						<td class="listingtabledata"><?php echo number_format($listing->c_financial_cgs_c) . "%";?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td class="listingtablelabel">Cost of Goods Sold:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_cgstotal_c);?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td class="listingtablelabel">Other Income:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_other_income_c);?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td class="listingtablelabel">Gross Profit:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_grossprofit_c);?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
<!-- Gail's added tables start here -->	
				<h4 class=detailheader>Occupancy Expenses</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Rent:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_rent_c);?></td>
						<td class="listingtablelabel">Utilities:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_utilities_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">CAM:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_cam);?></td>
						<td class="listingtablelabel">Financial Insurance:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format(intval($listing->c_financial_ins_c));?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Repairs/Maintenance:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_repairsmaint_c);?></td>
						<td class="listingtablelabel">Rubbish Removal:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_rubbish_c);?></td>
					</tr>					
				</table>
				<h4 class=detailheader>Operating Expenses</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Advertising:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_advertising_c);?></td>
			                        <td class="listingtablelabel">Credit Card Fees:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ccfees_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Business Loans:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_businessloans_c);?></td>
						<td class="listingtablelabel">Telephone:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_telephone_c);?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Cell Phones:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_cellphones_c);?></td>
						<td class="listingtablelabel">Supplies:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_supplies_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Interest (eg. Line of Credit):</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_addback_interest_c);?></td>
						<td class="listingtablelabel">Leased Vehicles:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_vehicles_c);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Leased Equipment:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_leasedequip_c);?></td>
						<td class="listingtablelabel">Postage:</td>
						    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_postage_c);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Legal/Accounting:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_legal_acct_c);?></td>
						<td class="listingtablelabel">Travel &amp; Entertainment:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_te_c);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Fuel and Vehicle Expense:</td>
						 <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_fuelvehicle_c);?></td>
					     <td>&nbsp;</td>
						 <td>&nbsp;</td>
			  		</tr>					
				</table>
				<h4 class=detailheader>Payroll Expenses</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Officer Salary:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_officersalary_c);?></td>
						<td class="listingtablelabel">Payroll:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_payroll_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Payroll Taxes:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_payrolltaxes_c);?></td>
						<td class="listingtablelabel">Employee Health Insurance:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_employeehealthinsurance);?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Owner's Health Insurance:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_healthins_owner_c);?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
				<h4 class=detailheader>Miscellaneous Expenses</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Miscellaneous 1:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc1);?></td>
						<td class="listingtablelabel">Miscellaneous 2:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc2);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Miscellaneous 3:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc3);?></td>
						<td class="listingtablelabel">Miscellaneous 4:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc4);?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Miscellaneous 5:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc5);?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
				<h4 class=detailheader>Add-Backs/Adjustments</h4>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Officers' Salaries:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_officersalaries_c);?></td>
			                        <td class="listingtablelabel">Owner's Health Insurance:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerhealthins_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Loans:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_loans_c);?></td>
						<td class="listingtablelabel">Interest:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_interest_c);?></td>
					</tr>						
					<tr>
						<td class="listingtablelabel">Owner's Credit Card:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownercc_c);?></td>
						<td class="listingtablelabel">Owner Car Lease Payments:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerlease_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Owner's Cell Phone:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownercell_c);?></td>
						<td class="listingtablelabel">Owner's Fuel Expense:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_ownerfuel_c);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Miscellaneous 1:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc6);?></td>
						<td class="listingtablelabel">Miscellaneous 2:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc7);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Miscellaneous 3:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc8);?></td>
						<td class="listingtablelabel">Miscellaneous 4:</td>
					    <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc9);?></td>
			 		</tr>
					<tr>
						<td class="listingtablelabel">Miscellaneous 5:</td>
						 <td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_misc10);?></td>
					     <td>&nbsp;</td>
						 <td>&nbsp;</td>
			  		</tr>
				</table>
				<h3 class=detailheader>Totals:</h3>
				<table id="listingtable">
					<tr>
						<td class="listingtablelabel">Total Expenses:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_total_expenses_c);?></td>
						<td class="listingtablelabel">Monthly Expenses:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_monthly_expense_c);?></td>
					</tr>
					<tr>
						<td class="listingtablelabel">Yearly Expenses:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_yearlyexpense);?></td>
						<td class="listingtablelabel">Net Profit:</td>
						<td class="listingtabledata"><?php echo $currency_symbol . number_format($listing->c_financial_net_profit_c);?></td>
					</tr>						
				</table>
				<!-- Gail's added tables end here -->	
				<?php 
					elseif($isuserregistered):
						_e('For more details on this listing, please add it to your data room.','bbcrm');
					else:
						_e("In order to see more details about this listing, please contact your broker to become registered.",'bbcrm');
					endif; 
				?>
				</div><!--divPD-->
			</div><!--propertydetails-->
			<!--cont divD-->
			<?php
				if($portfoliolisting->c_release_status!= ""){
					echo '<input type=button class="theme-button1-lt" onclick=location.href=\''. get_permalink($bbcrm_option['bbcrm_loginbar_dataroom']). '\' class="portfolio_action_button portfolio-view" value="'.__('View Data Room','bbcrm').' &#10151;">';
				}
				if($inportfolio ){
			?>
					<input type=button class="theme-button1-lt" onclick=location.href=\'<?php echo get_permalink($bbcrm_option['bbcrm_loginbar_dataroom']);?>'  class="portfolio_action_button portfolio-remove"  value="<?php _e('Hide from Data Room','bbcrm');?> &#10006;">
			<?php
				}
				if($inportfolio && !$isaddressreleased && $portfoliolisting->c_release_status != "Requested"){
			?>
					<form method="post" style="display:inline"><input type=hidden name="uid" value="<?php echo $listing_id;?>"><input type=hidden name="action" value="request"><input type=submit class="theme-button1-lt" value="<?php _e('Request This Address','bbcrm');?> &#9733;"  class="portfolio_action_button request-address" ></form>
			<?php
				}
				if(is_user_logged_in() && !$inportfolio){
			?>
					<form method=post>
							<input type=submit class="theme-button1-lt" style="display:inline" value="<?php _e('Add to my portfolio','bbcrm');?> &#10010;" class="portfolio_action_button portfolio-add"  />
							<input type=hidden name="action" value="add_to_portfolio" />
							<input type=hidden name="id" value="<?echo $listing->id;?>" />
					</form>
			<?php
				}
				$json = x2apicall(array('_class'=>'Media/by:fileName='.$listingbroker->c_profilePicture.".json"));
				$brokerimg =json_decode($json);
			?>
			</div><!--divD-->
			<div id="sidebar" class="col-md-3 sidebar" style="vertical-align:top;display:inline-block; ">
				<div id="inner-blue" class="theme-background1-dk" style="margin-bottom: 20px; color:#fff; width:100%;padding:18px; ">
					<div id="brokerdetails" class="broker_details_div">
						<?php
							if($brokerimg->fileName){
						?>							
								<img src="<?php echo "http://".$apiserver."/uploads/media/".$brokerimg->uploadedBy."/".$brokerimg->fileName;?>" height=150 align=left />
						<?php 
							} 
						?>                  
						<h5 class="detailheader" style="width:100%; color:#ffffff !important;margin-bottom:0;" ><?php _e('Business Listed By:');?></h5>
						<form method=POST action="<?php echo get_permalink($bbcrm_option["bbcrm_pageselect_broker"]);?>" id="brokerinfo">
							<input type=hidden name=eid value="<?php echo $listingbroker->nameId; ?>">
							<input type="button" class="theme-color2-dk theme-background1-dk <?php echo $butclass;?>" style="font-weight: bold; font-size: 22px; border:none; padding: 0;" value="<?php echo $listingbroker->name ;?>" onclick="jQuery('#brokerinfo').submit()">
						</form>
						Cell: <?php echo $listingbroker->c_mobile;?><br>
						Office: <?php echo $listingbroker->c_office;?><br>
						<?php 
							if(is_user_logged_in()){ 
								//print_r($buyer);
						?>
								<br clear=all ><br>
						<?php 
							}
						?>
						</div><!--brokerdetails-->
					</div><!--inner-blue-->
					<?php 
						if(is_user_logged_in()){ 
							$json = x2apicall(array('_class'=>'Media/by:fileName='.$buyerbroker->c_profilePicture.".json"));
							$brokerimg =json_decode($json);
					?>
							<br clear=all></div>
					<?php 
							echo wp_get_attachment_image( 5575, 'full', 0, array('class'=>'contactbroker','data-buyerid'=>$buyer->id,'data-listingid'=>$listing->id,'data-portfolioid'=>$portfoliolisting->id) ); 
					?>
					<?php
							dynamic_sidebar( "property-registered" );
						}else{
							get_sidebar("visitor");
							dynamic_sidebar( "property-unregistered" );
						}
					?>
				</div><!--sidebar-->
			</div><!--divB-->
		</div><!--divA-->
	</section>	
<style type="text/css">
.textonly {
    width: auto !important;
}
.cform label {
    width: 100% !important;
    margin: 4px 10px 0 0;
    display: block !important;
    text-align: left !important;
}
.sidebar .featured-content .entry-header {
    padding: 20px 0px !important;
}
.sidebar .featured-content .entry-content {
    padding: 20px 0px !important;
}
label {
    margin-bottom: 0px !important;
}
</style>
<?php
get_footer();
?>
