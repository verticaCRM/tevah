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

Yii::app()->clientScript->registerCss('changeApplicationNameCSS',"

#submit-app-name-settings {
    margin-top: 15px;
}

");

?>

<div class="page-title"><h2><?php echo Yii::t('admin','Change the Application Name'); ?></h2></div>
<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'change-the-app-name-form',
	'enableAjaxValidation'=>false,
)); 
?>
<div class="form">

<?php echo Yii::t('admin','Change the name of the application as displayed on the sign-in page and on page titles.');?>

<br><br>
<?php
echo CHtml::label (Yii::t('app', 'Application Name'), 'appName', array ());
echo $form->textField ($model, 'appName', array (
    'id' => 'application-name'
));
echo $form->error ($model, 'appDescription');
echo CHtml::label (Yii::t('app', 'Application Description'), 'appDescription', array ());
?>
<div>
<?php echo Yii::t('app', 'This will be displayed on the login page below the application name.'); ?>
</div>
<?php
echo $form->textField ($model, 'appDescription', array (
    'id' => 'application-description'
));
echo $form->error ($model, 'appDescription');
?>
<br>
<div class="row buttons">
	<?php echo CHtml::submitButton(Yii::t('app','Submit'),
    array(
        'class'=>'x2-button',
        'id'=>'submit-app-name-settings'
    )); ?>
</div>

</div>
<?php $this->endWidget(); ?>
