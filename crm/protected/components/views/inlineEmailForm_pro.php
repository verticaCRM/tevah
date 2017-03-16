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

/* @edition:pro */

// add javascript for email send time selector
Yii::app()->clientScript->registerScript('setupEmailEditor_pro',
'$(document).bind("setupInlineEmailEditor",function() {

	$("#emailSendTimeDropdown").unbind("change").change(function(){
		if($(this).val() == "1") {
			$("#emailSendTime").fadeIn(300);
		} else {
			$("#emailSendTime").fadeOut(300);
			$("#emailSendTime").val("");
		}
	});

	var emailSendTimeOptions = {minDate:new Date()};
	$.extend(emailSendTimeOptions,$.datepicker.regional[yii.language]);
	$("#emailSendTime").datetimepicker(emailSendTimeOptions);
});
',CClientScript::POS_READY);

echo CHtml::dropdownList('emailSendTimeDropdown',$model->emailSendTime==0?0:1,
	array(0=>Yii::t('app','Send Now'),1=>Yii::t('app','Send Later')),
	array('id'=>'emailSendTimeDropdown')
);
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

echo $form->textField($model,'emailSendTime',array(
	'title'=>Yii::t('app','Date'),
	'id'=>'emailSendTime',
	'style'=>$model->emailSendTime==0? 'display:none;':''
));







