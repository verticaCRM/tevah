<?php 
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/


$imgUrl = Yii::app()->theme->baseUrl."/images/widgets.png";
$analog = Yii::t('app', 'Analog');
$digital = Yii::t('app', 'Digital');
$digital24 = Yii::t('app', 'Digital 24h');
?>

<script type='text/javascript'>
var setting = '<?php echo $widgetSettings ?>';

$(function() { 
// Options: analog, digital, digital24


	function callback(li){
		li = $(li.target);
		li.siblings().removeClass('option-active');
		li.addClass('option-active');
		switchSetting(li.attr('value'));
	}


	// Add the gear icon menu
	// var imgUrl= '<?php echo $imgUrl ?>';

	// Create the Confi menu
	if( $("#widget_TimeZone").find('.gear-img-container').length == 0 ) {
		var dropdown = $("#widget_TimeZone").addConfigMenu({
				analog: '<?php echo $analog ?>',
				digital: '<?php echo $digital ?>',
				digital24: '<?php echo $digital24 ?>'
			}, callback);

		// // Set the currently blue option to true
		dropdown.find("div[value='"+setting+"']").addClass('option-active');
	}

	// Make the ajax call to save the setting in the profile
	function switchSetting(id){
		setting = id;
		$.ajax({
			url: "<?php echo Yii::app()->createUrl('/site/widgetSetting') ?>",
			data: { widget: 'TimeZone',
					setting: 'clockType',
					value: id
				  }
		}); 
	}
	

});


</script>