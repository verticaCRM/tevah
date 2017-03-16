<?php
/*
Template Name: Broker Profile
*/


//ini_set('display_errors', true);
//error_reporting(E_ALL);
//print_r($wp_query);
//exit();



if(!isset($_POST['eid'])):
wp_redirect('/find-a-broker/');
endif;

//print_r($wp_query);


$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($_POST["eid"]).".json"));
$broker =json_decode($json);

$pagetitle = get_the_title().' | '.$broker->name;

//global $pagetitle;

get_header();

   //retrieve records ----------------------------------------

?>

<section id="content" class="container"> 

	<div class="portfolio_group">
		<div id="business_container" class="article-page">
<?php
////////////////////
 
//$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($_POST["eid"]).".json"));
//$broker =json_decode($json);
?>
	<h1 class='article-page-head'><?php echo __("Agent Profile -",'bbcrm')." <span class='theme-color1'>". $broker->name;?></span></h1>
<div>
<?php the_content();?>
<div id="broker-<?php echo $broker->nameId;?>" class="brokeritem theme-background" style="padding:10px;min-height:90px">
<?php
        if($broker->c_profilePicture){
                $json = x2apicall(array('_class'=>'Media/by:fileName='.$broker->c_profilePicture.".json"));
                $brokerimg =json_decode($json);
                echo '<div style="float:right;width:250px;height:250px;overflow:hidden;"><img src="http://'.$apiserver.'/uploads/media/'.$brokerimg->uploadedBy.'/'.$brokerimg->fileName.'" style="width:100%"  style="clear:both" /></div>';
        }else{

//print_r($broker);

                echo '<div style="float:right;display:inline"><img src="http://'.$apiserver.'/uploads/media/marc/broker-'.$broker->c_gender.'.png" height=170 /></div>';

        }
?>

		<div class="property_detail"><label><i class="fa fa-user"></i>&nbsp;<? _e('Name','bbcrm');?>:</label> <?php echo $broker->name; ?></div>		
		<div class="property_detail"><label><i class="fa fa-certificate"></i>&nbsp;<? _e('Title','bbcrm');?>:</label> <?php echo $broker->c_title; ?></div>		
		<div class="property_detail"><label><i class="fa fa-mobile-phone"></i>&nbsp;<? _e('Mobile Phone','bbcrm');?>:</label> <?php echo $broker->c_mobile; ?></div>		
		<div class="property_detail"><label><i class="fa fa-phone"></i>&nbsp;<? _e('Work Phone','bbcrm');?>:</label> <?php echo $broker->c_office; ?></div>		
<?php
	if($broker->c_hiredate){
?> 
		<div class="property_detail"><label><i class="fa fa-thumbs-o-up"></i>&nbsp;<? _e('Active Since','bbcrm');?>:</label> <?php echo date("F Y",$broker->c_hiredate); ?></div>
<?php } ?>


		<div class="property_detail"><label><i class="fa fa-globe"></i>&nbsp;Regions:</label> 
		<div style="width:100px; clear:none !important; display:inline-block; vertical-align:top;" ><?php echo $broker->c_region; ?> <?php echo $broker->c_region2; ?> <?php echo $broker->c_region3; ?> <?php echo $broker->c_region4; ?> <?php echo $broker->c_region5; ?></div>	

	</div>	
				
	<br>
		<div class="property_detail"><?php echo $broker->description; ?></div>		
<br clear=all><br>


<!--portfolioitem-->
</div>
</div>
<!-- start listing-->
<div style='margin-top:25px;'> 
<a name="list"></a><h1 class="article-page-head"><?php _e('My Business Listings','bbcrm'); ?> - <span class="theme-color1"><?php bloginfo('name');?></span></h1>
<?php echo do_shortcode('[featuredlistings num=-1 featured=0 broker="'.urlencode($_POST["eid"]).'"]');?>
</div>
</div >

<!-- end listing-->
<!-- #bc-->
<aside id="sidebar" class="sidebar" role="complementary">
	<ul>
<?php 
if(is_user_logged_in()){
$userdata = get_userdata(get_current_user_id());
$json = x2apicall(array('_class'=>'Contacts/by:email='.urlencode($userdata->user_email).".json"));
$buyer =json_decode($json);
$json = x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($buyer->c_broker).".json"));
$buyerbroker =json_decode($json);
$json = x2apicall(array('_class'=>'Media/by:fileName='.$buyerbroker->c_profilePicture.".json"));
$brokerimg =json_decode($json);
//print_r($buyer);

?>
						<h3 class="widget-title theme-color1-dk"><?php _e("Your Broker");?></h3>
<div class="textwidget">
<?php
if($brokerimg->fileName){
?>						
						<img src="<?php echo "http://".$apiserver."/uploads/media/".$brokerimg->uploadedBy."/".$brokerimg->fileName;?>" height=170 />
<?php } ?>
						<h3><?php echo $buyerbroker->name;?></h3>
<i class="fa fa-phone"></i> <?php _e("Cell phone",'bbcrm');?>:<a href="tel:<?php echo $buyerbroker->c_mobile;?>"><?php echo $buyerbroker->c_mobile;?></a><br>
<i class="fa fa-phone"></i> <?php _e("Office phone",'bbcrm');?>:<a href="tel:<?php echo $buyerbroker->c_office;?>"><?php echo $buyerbroker->c_office;?></a><br>
<i class="fa fa-at"></i> <?php _e("Contact Agent",'bbcrm');?>:<a href="mailto:<?php echo $buyerbroker->c_email;?>"><?php echo $buyerbroker->c_email;?></a><br>

</div>
<?php 
}
dynamic_sidebar( "content-sidebar" );
 ?>
	</ul>
</aside>
</div></div>
</section><!-- #primary .widget-area -->

<?php get_footer(); ?>
