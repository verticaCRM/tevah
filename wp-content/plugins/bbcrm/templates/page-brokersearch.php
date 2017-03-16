<?php
/*
Template Name: Broker Search
*/

//ini_set('display_errors','on');
//error_reporting(E_ALL);

global $wp_query,$url,$bbcrm_option;
if(is_user_logged_in()){
$userdata = get_userdata(get_current_user_id());
}
//print_r($bbcrm_option);
get_header();
?>
<section id="content" class="container" style="margin-top:20px">
 
	<div class="portfolio_group">
		<div id="business_container" class="article-page" style="display:inline-block;width:100%">


<h1 class="article-page-head"><?php echo get_the_title();?></h1>
<?php

////////////////////
the_content();
 
$json = x2apicall(array('_class'=>'Brokers/'));
$brokers =json_decode($json);
//print_r($brokers);
if($brokers){
	echo "<div style='display:inline-block;width:100%'>"; //this is so the page doesn't scroll endlessly.
//////////////////

$altcss = "#dddddd";
foreach ($brokers AS $broker){ //The l

	if("Active" == $broker->c_status){
		$phone_mobile = $broker->c_mobile;
		$phone_office = $broker->c_office;
		$broker_email = $broker->c_email;
                $broker_description = $broker->description;
		$broker_position = $broker->c_position;		
		$altcss = ($altcss == "#dddddd")?"#dddddd":"#dddddd";
		$altclass = ($altcss == "#dddddd")?"":"";
		$butclass = ($altcss == "")?"altbrokerprofilebutton":"brokerprofilebutton";
?>
<div id="broker-<?php echo $broker->id;?>" class="brokeritem" style="padding:10px;margin-bottom:12px;min-height:230px; width:95%; display:inline-block; ">
 <div class="row">
  <div class="col-md-4" style="display:inline-block; clear:none;height:auto; width:155px;margin-top:12px; "  >
	<?php
		if($broker->c_profilePicture){
		//print_r($broker);
			$json = x2apicall(array('_class'=>'Media/by:nameId='.$broker->c_profilePicture.".json"));
			$brokerimg =json_decode($json);
			echo '<div style="float:left;width:130px;height:130px;overflow:hidden;margin:10px 10px 10px 1px;"><img src="http://'.$apiserver.'/uploads/media/'.$brokerimg->uploadedBy.'/'.$brokerimg->fileName.'" style="width:100%"  style="clear:both" /></div>';	
		}else{

		//print_r($broker);

		echo '<div style="float:left; width:130px;height:130px;overflow:hidden;margin:10px 10px 10px 1px;"><img src="http://'.$apiserver.'/uploads/media/marc/broker-'.$broker->c_gender.'.png" style="width:100%"  /></div>';
	
	}
?>

    <div class="theme-color1-dk" style="display:block;margin-top:7px;" > 
      <i class="fa fa-phone-square"></i> &nbsp;<? _e('','bbcrm');?> <?php echo $phone_office; ?><br>
	  <a href="mailto:<?php echo $broker_email; ?>"><i style="padding-right:7px;" class="fa fa-envelope-o"></i> Email</a> </br>
	</div>
  </div>		  
		  
		  
		  <div  class="col-md-8" style="display:inline-block;float:left; clear:none;" >
					<h3><?php echo $broker->name; ?></h3>
					<p><span class="badge outline radius blue"> <? _e('','bbcrm');?> <?php echo $broker_position; ?><br></span></p>
					<p><?php echo $broker->description;?></p>
		            
			<form method=POST action="<?php echo get_permalink($bbcrm_option["bbcrm_pageselect_broker"]);?>">
		              <input type=hidden name=eid value="<?php echo $broker->nameId; ?>">
		              <input class="theme-button2-dk" style="margin:0 !important;padding:8px;" type=submit value="<?php printf("Read %s's Full bio",$broker->name);?>">
		             </form>
		</div>
	</div>	  
</div>
	
<!--portfolioitem-->
<div style="clear:both"></div>
<?php 
		} //end if active
	}
}
?>
</div>
</div>
<!-- #bc-->
<aside id="sidebar" class="" style="vertical-align:top;width:25%" role="complementary">
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

if($buyerbroker->name){
?>
<h2><?php _e("Your Broker");?></h2>
<?php
if($brokerimg->fileName){
?>
<img src="<?php echo "http://".$apiserver."/uploads/media/".$brokerimg->uploadedBy."/".$brokerimg->fileName;?>" height=170 />
<?php
}
?>
<h3><?php echo $buyerbroker->name;?></h3>
Cell phone: <?php echo $buyerbroker->c_mobile;?><br>
Office phone: <?php echo $buyerbroker->c_office;?><br>
<?php 
} 
get_sidebar();
} 
 ?>
	</ul>
</aside>


</div>

	</div>
</section><!-- #primary .widget-area -->

<?php
get_footer();
?>
