<?php
/*
Template Name: Buyer Portfolio
*/
session_start();
if(!is_user_logged_in()){
	wp_redirect('/register/');
	exit;
}
unset($_SESSION["listingid"]);
//print_r($_SESSION);
global $url;
if(isset($_GET) && "release" == $_GET['action']){ //ADDRESS REQUEST
	$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_GET["pid"].'.json','_data'=>array('c_release_status'=>'Released','c_date_released'=>strtotime("now"))));
//	print_r( $json );
parse_str($_SERVER["HTTP_REFERER"],$r);
$rAr = array_values($r);
$ref =$rAr[0]; 
	$json = x2apipost(array('_method'=>'PATCH','_class'=>'Actions/'.$ref.'.json','_data'=>array("color"=>"green","complete"=>"Yes","completeDate"=>strtotime("now"))));

	$json = x2apicall(array('_class'=>'Portfolio/'.$_GET["pid"].'.json'));
	$portfoliorelationships =json_decode($json);
	
	$json = x2apicall( array('_class'=>'Portfolio/'.$portfoliorelationships->id."/relationships?secondType=Contacts" ) );
	$rel = json_decode($json);
	$data = array(
		'firstLabel'	=>	'Released',
	);
	$json = x2apipost( array('_method'=>'PATCH','_class'=>'Portfolio/'.$_GET["pid"].'/relationships/'.$rel[0]->id.'.json','_data'=>$data ) );
	
header("HTTP/1.1 204 No Content");
exit;
}

$userdata = get_userdata(get_current_user_id());
$json = x2apicall(array('_class'=>'Contacts/by:email='.$userdata->user_email.'.json'));
$user = json_decode($json);

$json = x2apicall(array('_class'=>'Contacts/'.$user->id.'/relationships?firstType=Portfolio'));
//This gives us an array of json objects with Porfolio IDs. 
$portfolio = json_decode($json);

//print_r($user);
//print_r($portfolio);

    
if(isset($_POST['action'])):
//	print_r($_POST);
	# POST id = LISTING ID (OPPORTUNITY)
	# POST uid = BUYER/USER ID (LEAD)
	if("hide" ==$_POST['action']){
		//parameters are regimented. Failure to include all params makes things go Bad.
			$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_release_status'=>'Hidden')));
//print_r($json);
	   
	}
if("release" ==$_POST['action']){ //ADDRESS REQUEST
	//print_r($_POST);
	$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_release_status'=>'Released')));
}

if("request" ==$_POST['action']){ //ADDRESS REQUEST
	$json = x2apicall(array('_class'=>'Portfolio/'.$_POST["pid"].'.json'));
	$portfolioitem = json_decode($json);

if($portfolioitem->c_release_status== "Added"){	
	$json = x2apipost(array('_method'=>'PATCH','_class'=>'Portfolio/'.$_POST["pid"].'.json','_data'=>array('c_release_status'=>'Requested')));
	$request = json_decode($json);
	$json = x2apicall(array('_class'=>'Portfolio/'.$_POST["pid"].'.json'));
	$portfoliorelationships =json_decode($json);
	
	$json = x2apicall( array('_class'=>'Portfolio/'.$portfoliorelationships->id."/relationships?secondType=Contacts" ) );
	$rel = json_decode($json);
	$data = array(
		'firstLabel'	=>	'Requested',
	);
	$json = x2apipost( array('_method'=>'PUT','_class'=>'Portfolio/'.$_POST["pid"].'/relationships/'.$rel[0]->id.'.json','_data'=>$data ) );

	}
	$json = x2apicall(array('_class'=>'Clistings/'.$_POST["lid"].'.json'));
	$listing = json_decode($json);

	
		$data = array(
		'actionDescription'	=>	'Address Request release for '.$listing->name.'<br>Buyer: '.$user->name.'<br>Listing Broker:'.$listing->assignedTo."<br><a href='".get_bloginfo('url')."/data-room/?action=release&pid=".$_POST["pid"]."' class='x2-button'>Release address</a>",
		'assignedTo'	=>	$listing->assignedTo,
		'associationId' => $_POST["pid"],
		'associationType' => 'portfolio',
		'associationName' => $portfolioitem->name,
		'subject'	=>	'Address Request release for '.$portfolioitem->name,
	);	
	$json = x2apipost( array('_class'=>'Portfolio/'.$_POST["pid"].'/Actions','_data'=>$data ) );

	if($user->assignedTo != $listing->assignedTo){
		$data['assignedTo']= $user->assignedTo;
		$json = x2apipost( array('_class'=>'Portfolio/'.$_POST["pid"].'/Actions','_data'=>$data ) );
	}

} // END IF ADDRESS REQUEST 

endif;
global $pagetitle;
$pagetitle = "Data Room";

get_header();

   //retrieve records ----------------------------------------
?>
<section id="content" class="container" style="margin-top:80px">
        <div class="portfolio_group">
                <div class="article-page" style="display:inline-block;width:100%">
<h1 class="article-page-head"><?php _e('Data Room of', 'bbcrm');?> <?php echo $user->firstName.' '.$user->lastName ;?></h1>
<a href="/buyer-profile/">View/Modify Profile</a>
<br><br><br>
<?php
	the_content();
?>
<?php include "portfolio-search.php"; ?>
<br>

<?php
if(count($portfolio)>0){
	echo "<div style='height:400px;overflow:auto;'>"; //this is so the page doesn't scroll endlessly.
//////////////////
//	echo count($portfolio);

foreach ($portfolio AS $item){ //The l
//echo "<h3>ITEM</h3>";
//print_r($item);
$json = x2apicall(array('_class'=>'Portfolio/'.$item->firstId.'.json'));
$portfolioitem = json_decode($json);
//echo "<h3>PFITEM</h3>";

//print_r($portfolioitem);

$json = x2apicall(array('_class'=>'Clistings/'.$portfolioitem->c_listing_id.'.json'));
$listingitem = json_decode($json);
//echo "<h3>LSITEM</h3>";
//print_r($listingitem);
//echo "<hr>";

		$addreqstatus = $portfolioitem->c_release_status;
		$portfoliolistingid = $listingitem->id;
		
		$isaddressrequested=($addreqstatus =="Requested")?1:0;
		$isaddressreleased=($addreqstatus =="Released")?1:0;
		$islistinghidden=($addreqstatus =="Hidden")?1:0;
//echo $addreqstatus;

//echo $listingitem->c_sales_stage.'<br>';
if("Active" == $listingitem->c_sales_stage && !$islistinghidden){
?>
<div id="listing-<?php echo $portfolioitem ->name_value_list->listing_frontend_id_c->value;?>" class="portfolioitem" style="margin-top:10px;border-top:2px dashed #666666">
<?php
if($isaddressrequested):
echo '<div class="portfoliostatus added">&#10003; ' .	__("Your request for this address has been sent",'bbcrm') . "</div>";
//	echo '<input type=button onclick=location.href=\'/data-room\' class="portfolio_action_button" value="'. __("View Data Room",'brokernet').'">';
elseif($isaddressreleased):
echo '<div class="portfoliostatus released"> &#9733; ' .	__("The address of this business is available to you",'bbcrm') . "</div>";
else:
	//	echo $item->name_value_list->status->value;
endif;
?>
<?php
//$permalink = '/'.get_locale().'/listing/'.sanitize_title($listingitem->c_name_generic_c).'';
$permalink = '/listing/'.sanitize_title($listingitem->c_name_generic_c).'';
echo '<h3><a href="'.$permalink .'">'.$listingitem->c_name_generic_c.'</a></h3>' ;


//	print_r( $portfolioitem->name_value_list);
?>
		<div class="property_detail"><label><?php _e("Listing ID:", 'brokernet');?></label> <?php echo $listingitem->id; ?></div>
		<div class="property_detail"><label><?php _e("Listed on:", 'brokernet');?></label> <?php echo date("F j, Y",$listingitem->c_listing_date_approved_c); ?></div>
		<div class="property_detail"><label><?php _e("Region:", 'brokernet');?></label><?php echo $listingitem ->c_listing_region_c ;?></div>
		<div class="property_detail"><label><?php _e("Financial Info:", 'brokernet');?></label></div>

		<div class="property_detail"><label><?php _e("Gross Sales:", 'brokernet');?></label> <?php echo $listingitem ->c_currency_id;?><?php echo number_format($listingitem ->c_financial_sales_c);?></div>
		<div class="property_detail"><label><?php _e("Gross Revenue:", 'brokernet');?></label> <?php echo $listingitem ->c_currency_id;?><?php echo number_format($listingitem ->c_financial_grossrevenue_c);?></div>
		<div class="property_detail"><label><?php _e("Owner's Cash Flow:", 'brokernet');?></label> <?php echo $listingitem ->c_currency_id;?><?php echo number_format($listingitem ->c_financial_owner_cashflow_c);?></div>
		<div class="property_detail"><label><?php _e("Asking Price:", 'brokernet');?></label> <?php echo $listingitem ->c_currency_id;?><?php echo number_format($listingitem ->c_listing_askingprice_c);?></div>
		<div class="property_detail"><label><?php _e("Down Payment:", 'brokernet');?></label> <?php echo $listingitem ->c_currency_id;?><?php echo number_format($listingitem ->c_listing_downpayment_c);?></div>
<?php if($isaddressreleased):?>
	<div class="portfolio-released-address" style="border:1px solid #990000;padding:5px;">
		<h3><?php _e("Address", 'bbcrm');?></h3>
		<?php
		echo __("Business Name: ","bbcrm").$listingitem->c_name_dba_c.'<br>';
		echo __("Address: ","bbcrm").$listingitem->c_listing_address_c.'<br>';
		echo  $listingitem->c_listing_city_c." ".$listingitem->c_listing_region_c." ".$listingitem->c_listing_postal_c.'<br>';
		echo __("This address was released to you on ","bbcrm").date("F j, Y",$portfolioitem->c_date_released).'<br>';
		echo '</div>';
		endif; ?>

<br />
<a href="<?php echo $permalink;?>" class="portfoliobutton" data-id="<?php echo $listingitem->id;?>" class="portfoliobutton"><?php _e('View Listing Details','bbcrm');?></a>
<form method="post" style="display:inline"><input type=hidden name="uid" value="<?php echo $crmid;?>"><input type=hidden name="pid" value="<?php echo $portfolioitem->id;?>"><input type=hidden name="action" value="hide"><input type=submit value="Hide from Data Room" class="theme-button1-lt portfoliobutton"></form> 

<?php if(!$isaddressreleased){?>
<form method="post" style="display:inline"><input type=hidden name="pid" value="<?php echo $portfolioitem->id; ?>"><input type=hidden name="lid" value="<?php echo $listingitem->id; ?>"><input type=hidden name="action" value="request"><input type=submit value="Request Address" class="theme-button1-lt portfoliobutton"></form>  
<?php } ?>
</div><!--portfolioitem-->


<?php 
	} //if is active
}
?>
</div>
<?php } ?>

</div>
<!-- #bc-->
<aside id="sidebar" class="widget-area wpp_sidebar_listing" role="complementary">
	<ul>
<?php 


?>
		<?php dynamic_sidebar( "portfolio" ); ?>
	</ul>
</aside>

</div>
</section><!-- #primary .widget-area -->

<?php get_footer(); ?>
