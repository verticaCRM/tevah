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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
Yii::app()->clientScript->registerCss('multiselectCss',"
.multiselect {
	width: 460px;
	height: 200px;
}
#switcher {
	margin-top: 20px;
}
",'screen, projection');


Yii::app()->clientScript->registerScript('renderMultiSelect',"
$(document).ready(function() {
	 $('.multiselect').multiselect({searchable: false});
});
",CClientScript::POS_HEAD);
?>

<div class="page-title"><h2><?php echo Yii::t('admin','Manage Action Publisher Tabs'); ?></h2></div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'manage-action-publisher-tabs',
	'enableAjaxValidation'=>false,
)); 
?>
<div class="form">
<?php 
echo X2Html::getFlashes ();
echo Yii::t('admin','Add, remove, or reorder action publisher tabs:'); ?>
<br><br>
<?php
echo CHtml::hiddenField('formSubmit','1');
echo CHtml::dropDownList('actionPublisherTabs[]',$selectedTabs,$tabOptions,array('class'=>'multiselect','multiple'=>'multiple', 'size'=>8));
?>
<br>
<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button')); ?>
</div>
</div>
<?php $this->endWidget(); ?>
