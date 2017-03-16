<?php

?>
<div id="webcontactbroker" style="vertical-align:top"><img src="http://ibb.marcgottlieb.com/wp-content/uploads/2015/07/contactabroker.png" id="contactimage" class='sidebar-image' style="vertical-align:top">
<div  style='display:none' id='weblead-1' >Please fill out this form to contact the listing broker.<br><iframe src="<?php echo bloginfo('siteurl');?>/crm/index.php/contacts/weblead?fg=%23000000&bgc=&bc=&iframeHeight=229&webFormId=1" id=webleadform frameborder="0" scrolling="0" width="200" height="350"></iframe>
</div></div>

<script>

jQuery(document).ready(function(){

//		jQuery("#webleadform").attr('src',"<?php echo bloginfo('siteurl');?>/crm/index.php/contacts/weblead?fg=%23000000&bgc=&bc=&iframeHeight=229&webFormId=1");
//	
	
	jQuery("#contactimage").click(function(){



		jQuery(this).remove();
		jQuery("#weblead-1").css('display','inline-block');
		jQuery('#webleadform').contents().find('#Contacts_otherUrl').val(location.href);//.css("readonly","true")
		jQuery('#webleadform').contents().find('#Contacts_interest').val(jQuery("#property_listing_id").data('id'));
//console.log(jQuery('#webleadform').contents().find('#Contacts_otherUrl').attr('name') )
console.log (jQuery('#webleadform').contents().find('#Contacts_interest').val())
	//	jQuery('#webleadform').contents().find('#Contacts_otherUrl').prop("disabled",true);
//console.log(jQuery("#webform-iframe").contents() );
			})
	
	});
</script>
