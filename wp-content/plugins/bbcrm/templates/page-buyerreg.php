<?php
/*
Template Name: Buyer Registration
*/

session_start();

if(!empty($_POST)){
	$_POST["name"]=$_POST["firstName"]." ".$_POST["lastName"];
	$_POST["visibility"] = '1';
	$_POST["c_username"] = $_POST["email"];

#Create new CRM record
	$result = x2apipost(array('_class'=>'Contacts/','_data'=>$_POST));

	if($result[0]=="201"){
			$buyer = json_decode($result[1]);
			//Create NDA Document
			$qy["data"] = http_build_query($_POST); 
			$userndadoc = json_decode(buyerreg_ajax_getnda($qy) );
			#Create new NDA
			$usernda = x2apipost(array('_class'=>'Docs/','_data'=>array('name'=>$_POST["name"].' NDA Agreement','text'=>$userndadoc->text)));
			$newuser = wp_create_user( $_POST["email"], $_POST["c_password"], $_POST["email"] );
			$buyerNDA = json_decode($usernda[1]);
			$data = array(
				'secondType'	=>'Docs',
				'secondId'	=> $buyerNDA->id,
			);
			$ndaRelationship = x2apipost( array('_class'=>'Contacts/'.$buyer->id.'/relationships/','_data'=>$data ) );		
			$listing = json_decode(x2apicall(array('_class'=>'Clistings/'.$_POST["listingid"].'.json')));
			$buyerbroker = json_decode(x2apicall(array('_class'=>'Brokers/by:nameId='.urlencode($buyer->c_broker).".json")));

	if(isset($_POST['listingid']) && $_POST["listingid"]!=""){

		$data = array(
			'name'	=>	'Portfolio listing for '.$listing->name,
			'c_listing'	=>	$listing->name,
			'c_listing_id'	=>	$listing->id,
			'c_buyer'	=>	$buyer->name,
			'c_buyer_id'	=>	$buyer->id,
			'c_release_status'	=>	'Added',
			'assignedTo'	=>	$buyerbroker->assignedTo,
		);
		$json = x2apipost( array('_class'=>'Portfolio/','_data'=>$data ) );

	}
		wp_redirect(get_permalink($bbcrm_option['bbcrm_pages_welcome']));
		exit;
	}else{
		echo $result[0];
	}
	
}
wp_enqueue_script('jquery');
get_header();

$listingoptions =array();

if(!empty($_SESSION["viewed_listings"])){	
	foreach($_SESSION["viewed_listings"] AS $k=>$v){
		$listingoptions[] = 	'"'.$v["listingname"].'":"'.$k.'"';
	}
}else{
		$listingoptions[] = 	'"":"No listings viewed"';
}

$json = x2apicall(array('_class'=>'dropdowns/'.$bbcrm_option["bbcrm_crm_states"].'.json'));
$states =json_decode($json);
if($states){
	$statesselect = array();
	foreach ($states->options AS $k=>$v){
		$statesselect[] = '"'.$k.'":"'.$v.'"';
	}	
}	

//Get the brokers in the system
$json = x2apicall(array('_class'=>'Brokers/'));
$brokers =json_decode($json);

if($brokers){
	$brokerselect = array();
	foreach ($brokers AS $broker){
	$brokerselect[] ='"'.$broker->name.'":"'.$broker->nameId.'"';
	}
}		
?>
<script>
	jQuery(document).ready(function(){ 
		var newOptions = {<?php echo join(',',$statesselect);?>};
		var el = jQuery("#state");
			el.append(jQuery("<option></option>").attr("value", "").text("<?php __('Choose A State');?>"));
			jQuery.each(newOptions, function(key, value) {
				el.append(jQuery("<option></option>").attr("value", value).text(key));
			});

		var newListings = {<?php echo join(',',$listingoptions);?>};
		var el = jQuery("#listingid");
			el.append(jQuery("<option></option>").attr("value", "").text("<?php __('Choose A Listing');?>"));
			jQuery.each(newListings, function(key, value) {
				el.append(jQuery("<option></option>").attr("value", value).text(key));
			});

		var newBrokers = {<?php echo join(',',$brokerselect);?>};
		var el = jQuery("#c_broker");
		el.append(jQuery("<option></option>").attr("value", "House Broker_5").text("<?php __('Choose A Broker');?>"));			
			jQuery.each(newBrokers, function(key, value) {
				el.append(jQuery("<option></option>").attr("value", value).text(key));
			});
jQuery("#broker").change(function(){
	jQuery.getJSON('<?php echo plugins_url().'/bbcrm/_auth.php'; ?>',
	{query:	'AJAX',action: 'x2apicall',_class:"Brokers/by:nameId="+encodeURI(jQuery(this).val())+".json"}, 
	function(response){
		jQuery("#assignedTo").remove();
		jQuery("#form_buyerreg").append("<input type='hidden' id='assignedTo' name='assignedTo' value='"+response.assignedTo+"' />");
	});
})		

jQuery("#email").blur(function(){
	jQuery("#emailerr").remove();		
	emailaddr = jQuery(this).val()
	jQuery.get('<?php echo plugins_url().'/bbcrm/_auth.php'; ?>',{query:'AJAX',action: 'isvalidemail',email:emailaddr}, 
	function(response){
		if(!response){
			jQuery("#emailerr").remove();
			jQuery("#email").parent().prepend("<div id='emailerr' class='formerr'>That is not a valid email</div>")
			jQuery("#email").select();								
		//return false;		
		}else{
			jQuery.getJSON('<?php echo plugins_url().'/bbcrm/_auth.php'; ?>',{query:'AJAX',action: 'x2apicall',_class:'Contacts/by:email='+emailaddr+'.json'}, 
				function(response){
					if(!response.status){
						jQuery("#emailerr").remove();
						jQuery("#email").parent().prepend("<div id='emailerr' class='formerr'>That email is already in the system.</div>")
						jQuery("email").select();		
					}else{
						jQuery("#emailerr,#emailsuc").remove();
						jQuery("#email").parent().prepend("<div id='emailsuc' class='formsuc'>That email is available.</div>")
					}        				
 				});
		}
	});
});
			jQuery("#form-reg").click(function(event){
					//event.preventDefault();					
					if(jQuery("#c_password").val() != jQuery("#password2").val() ){
						jQuery("#c_password").parent().before("<div id='pwderr' class='formerr'>The passwords do not match.</div>");
						jQuery("#form1,#form2").toggle();
						return false;
					}else{
						jQuery("#pwderr").remove();					
					}
					if(jQuery("#accept-sig").val() != jQuery("#firstName").val()+" "+jQuery("#lastName").val() ){
						jQuery("#accept-sig").parent().before("<div id='accept-sigerr' class='formerr'>This field must match the first name and last name you entered. Please check your entries.</div>");
						return false;
					}else{
						jQuery("#accept-sigerr").remove();
					}
					if(!jQuery("#acceptance").prop("checked") ){
						jQuery("#acceptance").parent().prepend("<div id='acceptanceerr' class='formerr'>Please check this field in order to continue.</div>");
						return false;
					}
					else{
						jQuery("#acceptanceerr").remove();
						jQuery("#form-reg").submit();
					}
			});
						
			jQuery("#form-next,#form-back").click(function(event){
				event.preventDefault();
				jQuery(".formerr").remove()
				jQuery("#prefinal").show()
		       		jQuery("#regfinal").hide()
fail=false;
fail_log = '';
jQuery('#form_buyerreg input, #form_buyerreg select' ).each(function(){
            if(typeof jQuery(this ).attr('required' ) == "undefined" || jQuery(this).prop('required') == false){
            } else {
                if (!jQuery(this ).val() ) {
                    fail = true;
                    name = jQuery(this ).attr('name' );                   
			jQuery(this ).prev().append("<span class='formerr'>This field is required.</span>");
                    fail_log += jQuery("label[for='"+name+"']").html() + " is required \n";
                }
            }
        });

        if (!fail ) {
//console.log(jQuery("#form_buyerreg").serialize())        	
			response = '';
				jQuery("#agreement").html("<h3>Please wait a moment while we generate your Confidentiality Agreement...</h3>")
				jQuery.getJSON(
					'<?php echo plugins_url().'/bbcrm/_auth.php'; ?>',
					{
						query:	'AJAX',
						action: 'buyerreg_ajax_getnda',
						ndadoc: <?php echo $bbcrm_option["bbcrm_crm_nda"]?>,
						data:jQuery("#form_buyerreg").serialize()						
    				}, 
    				function(response){ 
//console.log(response) 				
						jQuery("#agreement").empty().html(response.text).css({'height':'350px','overflow':'scroll'});
						//jQuery("#agreement").prepend('<div id="pb" style="padding-top:30px;"><a class="printbutton" href="javascript:window.print()">Print Registration Form</a></div>');
						
    				}
				);
				jQuery("#form1,#form2").toggle();
			

        	} else {}
	});
});
</script>
 <section id="content" class="container"> 
  <div id="business_container">	
	    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="portfolio_group">
		   <h2><?php the_title(); ?></h2>
		   <div class="">
		      <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>');	?>
	    </div>	
         </div>
<form id="form_buyerreg" action="<?php echo $bbcrm_option["bbcrm_pages_registration"];?>" method="post">
<input name="c_contacttype" type="hidden" value="Buyer" />
<div id="form1" style="display: block;">
<?php
$crmfields=json_decode(x2apicall(array('_class'=>'Contacts/fields')));
$formfields = array();
$formorder = array(
	'email'=>array('label'=>'Your Email Address','req'=>1,'type'=>'email'),
	'firstName'=>array('label'=>'','req'=>1),
	'lastName'=>array('label'=>'','req'=>1),
	'address'=>array('label'=>'','req'=>1),
	'address2'=>array('label'=>'Address (cont.)','req'=>0),
	'city'=>array('label'=>'','req'=>1),
	'state'=>array('label'=>'','req'=>1,'type'=>'dropdown'),
	'zipcode'=>array('label'=>'','req'=>0),
	'phone'=>array('label'=>'','req'=>1),
	'c_cellphone'=>array('label'=>'','req'=>0),
	'c_fax'=>array('label'=>'','req'=>0),
	'c_password'=>array('label'=>'','req'=>1,'type'=>'password'),
	'c_newsletter'=>array('label'=>'Would you like to receive our newsletter?',req=>0,'type'=>'checkbox'),
	'name'=>array('label'=>'','req'=>0),
	'title'=>array('label'=>'','req'=>0),
	'website'=>array('label'=>'','req'=>0),
	'twitter'=>array('label'=>'','req'=>0),
	'linkedin'=>array('label'=>'','req'=>0),
	'skype'=>array('label'=>'','req'=>0),
	'googleplus'=>array('label'=>'','req'=>0),
	'country'=>array('label'=>'','req'=>0),
	'backgroundInfo'=>array('label'=>'','req'=>0),
	'facebook'=>array('label'=>'','req'=>0),
	'doNotCall'=>array('label'=>'','req'=>0),
	'timezone'=>array('label'=>'','req'=>0),
	'doNotEmail'=>array('label'=>'','req'=>0),
	'c_position'=>array('label'=>'','req'=>0),
	'c_gender'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_alternateemail'=>array('label'=>'','req'=>0),
	'c_middlename'=>array('label'=>'','req'=>0),
	'c_fax'=>array('label'=>'','req'=>0),
	'c_proofoffunds'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_pofammount'=>array('label'=>'','req'=>0),
	'c_comment'=>array('label'=>'','req'=>0),
	'c_county'=>array('label'=>'','req'=>0),
	'c_businesscategory'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_preventmarketingemail'=>array('label'=>'','req'=>0,'type'=>'checkbox'),
	'c_newbuyer'=>array('label'=>'','req'=>0,'type'=>'checkbox'),
	'c_username'=>array('label'=>'','req'=>0),
	'c_Whatkindofworkdoyoudo'=>array('label'=>'','req'=>0),
	'c_Preferedlocation'=>array('label'=>'','req'=>0),
	'c_Cashonhandandinbank'=>array('label'=>'','req'=>0),
	'c_cds'=>array('label'=>'','req'=>0),
	'c_MoneyMarketFunds'=>array('label'=>'','req'=>0),
	'c_StockAndBonds'=>array('label'=>'','req'=>0),
	'c_AccountsNotesReceivable'=>array('label'=>'','req'=>0),
	'c_VestedPortionofPensionorProfitSharing'=>array('label'=>'','req'=>0),
	'c_CashValueOfLifeInsurance'=>array('label'=>'','req'=>0),
	'c_RealEstate'=>array('label'=>'','req'=>0),
	'c_HomeAptCondoFarmBusiness'=>array('label'=>'','req'=>0),
	'c_OtherAssets1'=>array('label'=>'','req'=>0),
	'c_OtherAssets2'=>array('label'=>'','req'=>0),
	'c_Automobiles'=>array('label'=>'','req'=>0),
	'c_Furniture'=>array('label'=>'','req'=>0),
	'c_DueBanksLoans'=>array('label'=>'','req'=>0),
	'c_NameofBanks'=>array('label'=>'','req'=>0),
	'c_RealestateMortgageHome'=>array('label'=>'','req'=>0),
	'c_AutoLoans'=>array('label'=>'','req'=>0),
	'c_RevolvingCharges'=>array('label'=>'','req'=>0),
	'c_OtherDebtegHealthLifeIns'=>array('label'=>'','req'=>0),
	'c_TotalAssets'=>array('label'=>'','req'=>0),
	'c_TotalLiabilities'=>array('label'=>'','req'=>0),
	'c_ProfitSharing'=>array('label'=>'','req'=>0),
	'c_AmountofDownPayment'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Doyoucurrentlyownabusiness'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Haveyoueverfiledforbankruptcy'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Haveyoueverownedabusinessbefore'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Howdidyouhearaboutus'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Howlonghaveyoubeenlookingforabusiness'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Education'=>array('label'=>'','req'=>0,'type'=>'dropdown','type'=>'dropdown'),
	'c_Experiance'=>array('label'=>'','req'=>0,'type'=>'dropdown','type'=>'dropdown'),
	'c_DoYouHaveAPartnerartner'=>array('label'=>'','req'=>0,'type'=>'dropdown','type'=>'dropdown'),
	'c_Ifyeswhatisthatpersonrelationshiptoyou'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Isyourdownpaymentavailabletoday'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_NetCashFlowDesired'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Whatisthesourceofyourdownpayment'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Whatisyourcurrentworkstatus'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Whatisyourtimeframeforpurchase'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_Wouldyouliketoreceiveemailsdescribingnewlistingsorlis'=>array('label'=>'','req'=>0,'type'=>'dropdown'),
	'c_DescribeAdditionalSourceofFunding'=>array('label'=>'','req'=>0),
	'c_HowMuchHaveYouAllocatedForTheDownpayment'=>array('label'=>'','req'=>0),
	'c_billingstreet'=>array('label'=>'','req'=>0),
	'c_billingcity'=>array('label'=>'','req'=>0),
	'c_billingstate'=>array('label'=>'','req'=>0),
	'c_billingpostalcode'=>array('label'=>'','req'=>0),
	'c_billingcountry'=>array('label'=>'','req'=>0),
	'c_jobTitle'=>array('label'=>'','req'=>0),
	'c_phone3'=>array('label'=>'','req'=>0),
	'c_email2'=>array('label'=>'','req'=>0),
	'c_email3'=>array('label'=>'','req'=>0),
	'c_suiteNumber'=>array('label'=>'','req'=>0),
	);
foreach($crmfields AS $idx=>$obj){
	if(in_array($obj->id,$bbcrm_option["bbcrm_buyerreg_fields"])){
		$formfields[$obj->fieldName]=$obj;
	}
}
foreach($formorder as $name=>$valAr){
	if($formfields[$name]){
		echo "<div class='regformfield'>";
		$fieldlabel = ($valAr['label']=='')?$formfields[$name]->attributeLabel:$valAr['label'];
		echo "<label for='".$name."' style='margin:5px;clear:left'>".$fieldlabel.'</label>';
		
		if($formfields[$name]->type == "dropdown"){
			$json = x2apicall(array('_class'=>'dropdowns/'.$formfields[$name]->linkType.'.json'));
			$states =json_decode($json);
		if($states){
			echo "<select name='".$name."'>";
			$statesselect = array();
			foreach ($states->options AS $k=>$v){
			echo '<option value="'.$k.'">'.$v.'</option>';
			}	
			echo "</select>";
		}	
		}else{
			echo "<input type='".(($valAr["type"])?$valAr["type"]:'text')."' name='".$name."' id='".$name."'>";
		}

		if($name=='c_password'){
			echo "<small>(required)</small>";
			echo "</div><div class='regformfield'><label for='password2'>Repeat password: </label>";
			echo "<input type='password' name=password2 id='password2'>";
		}

		if($valAr['req']){
			echo "<small>(required)</small><br>";
		}
		echo "</div>";
	}
}
?>
<?php
//print_r($bbcrm_option);
if($bbcrm_option["bbcrm_broker_showhide"]){
?>
<div class="regformfield"><label for="c_broker" style='margin:5px;clear:left'>Broker:</label><select id="c_broker" name="c_broker"></select></div>
<?php
}else{
?>
<input type=hidden name="assignedTo" value="<?php echo $bbcrm_option["bbcrm_crm_assignedTo"];?>" />
<?php
}
if($bbcrm_option["bbcrm_listing_showhide"]){
?>
<div class="regformfield"><label for="listingid" style='margin:5px;clear:left'>Listing:</label><select id="listingid" name="listingid"></select></div>
<?php } ?>

<input id="form-next" type="button" value="Next" class="theme-button1-dk" />
</div><!--form1-->
<div id="form2" style="display: none;">
<div id="agreement"></div>
<div id="prefinal">Please scroll to the end of the contract to indicate that you've read it.</div>
<div id="regfinal" style="display: none;">

Please fill out this textbox with the same first name and last name you used on the first part of the form. The field is case-sensitive. By filling out this textbox and clicking on the checkbox, you agree to the terms of this Non-Disclosure Agreement
<span class="accept-sig"><input id="accept-sig" name="accept-sig" size="40" type="text" value="" placeholder="Your full name" /></span>
<span class="acceptance"><input id="acceptance" name="acceptance" type="checkbox" value="I accept" /> <span class="wpcf7-list-item-label">I accept</span></span>

</div>
<input id="form-back" type="button" value="Back" class="theme-button1-dk" /><input id="form-reg" class="theme-button1-dk wpcf7-form-control wpcf7-submit" style="margin-left: 10px;" type="submit" value="Register" />

</div>
</form>
	 <?php endwhile; endif;?>
   </div>	
           <div  class="col-md-3 sidebar">
		<?php dynamic_sidebar( "content-sidebar" ); ?>
         </div>
 </section>
<script>
jQuery(document).ready(function(){
	jQuery("#agreement").scroll(function() {
	   if(Math.ceil(jQuery(this).scrollTop()) + jQuery(this).height()  >= jQuery(this)[0].scrollHeight-20 ) {
	       jQuery("#prefinal").slideUp();
	       jQuery("#regfinal").slideDown();
	   }
	});
})
</script>
<?php get_footer();?>
