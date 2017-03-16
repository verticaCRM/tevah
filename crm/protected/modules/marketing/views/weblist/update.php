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

$this->pageTitle = $model->name; 
$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'view', 'edit', 'delete', 'weblead', 'webtracker',
);
/* x2plastart */
$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);
/* x2plaend */
$this->insertMenu($menuOptions, $model, $authParams);


?>
<div class="page-title icon marketing">
<h2><span class="no-bold"><?php echo Yii::t('module','Update'); ?>:</span> <?php echo $model->name; ?></h2>
</div>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'contacts-form',
	'enableAjaxValidation'=>false));
?>
<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>

<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php
			if(empty($model->assignedTo))
				$model->assignedTo = Yii::app()->user->getName();
			echo $form->dropDownList($model,'assignedTo', User::getNames(), array('tabindex'=>null)); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'visibility'); ?>
		<?php
			echo $form->dropDownList($model,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>null));
		?>
	</div>
</div>

<?php
$validateName = <<<EOE
$('#save-button').click(function(e) {
	if ($.trim($('#X2List_name').val()).length == 0) {
		$('#X2List_name').addClass('error');
		$('[for="X2List_name"]').addClass('error');
		$('#X2List_name').after('<div class="errorMessage">Name cannot be blank.</div>');
		e.preventDefault();
	}
});
EOE;
Yii::app()->clientScript->registerScript('validateName', $validateName, CClientScript::POS_READY);
?>

<div class="row buttons">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

<?php
$this->endWidget();
?>

</div>
