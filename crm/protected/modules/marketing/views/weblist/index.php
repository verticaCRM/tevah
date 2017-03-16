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

$this->pageTitle = Yii::t('marketing','Newsletters');

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead', 'webtracker',
);
/* x2plastart */
$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);
/* x2plaend */
$this->insertMenu($menuOptions);


Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('contacts-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>
<div class="page-title icon marketing"><h2><?php echo Yii::t('marketing','Newsletters'); ?></h2></div>
<div class="form">
<h4><?php echo Yii::t('marketing','Create Newsletter') .':'; ?></h4>
<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}

$model = new X2List;
$model->assignedTo = Yii::app()->user->getName();
$users = User::getNames();
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'weblist-form',
	'action'=>'create',
	'enableClientValidation'=>true,
	'clientOptions'=>array('validateOnSubmit'=>true),
));
?>

<div class="row">
	<div class="cell">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textField($model,'description',array('size'=>30,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'assignedTo'); ?>
		<?php echo $form->dropDownList($model,'assignedTo',$users); ?>
		<?php echo $form->error($model,'assignedTo'); ?>
	</div>
	<div class="cell">
		<?php echo $form->labelEx($model,'visibility'); ?>
		<?php echo $form->dropDownList($model,'visibility',array( 1=>Yii::t('contacts','Public'), 0=>Yii::t('contacts','Private'))); ?>
	</div>
	<div class="cell">
		<?php /*
		echo CHtml::ajaxSubmitButton(Yii::t('app','Create'), 'create', 
			array('success'=>'function() { $.fn.yiiGridView.update("weblist-grid"); $("#weblist-form").each(function() { this.reset(); });}'), 
			array('class'=>'x2-button','id'=>'save-button','style'=>'margin-top: 1em;')
		); */?>
		<?php echo CHtml::submitButton(Yii::t('app','Create'), array('class'=>'x2-button','id'=>'save-button','style'=>'margin-top: 1em;')); ?>
	</div>
</div>
<?php $this->endWidget(); ?>
</div>

<style>div.contact-lists table.items tbody tr:last-child td {font-weight:normal;}</style>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'weblist-grid',
	'enableSorting'=>false,
	'baseScriptUrl'=>Yii::app()->theme->getBaseUrl().'/css/gridview',
	'htmlOptions'=>array('class'=>'grid-view contact-lists'),
	'template'=> '{summary}{items}{pager}',
	'dataProvider'=>$dataProvider,
	// 'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'name',
			'type'=>'raw',
			'value'=>'CHtml::link($data->name, Yii::app()->controller->createUrl("view", array("id"=>$data->id)))',
			'headerHtmlOptions'=>array('style'=>'width:25%;'),
		),
		array(
			'name'=>'description',
			'type'=>'raw',
			'headerHtmlOptions'=>array('style'=>'width:40%;'),
		),
		array(
			'name'=>'assignedTo',
			'type'=>'raw',
			'value'=>'User::getUserLinks($data->assignedTo)',
		),
		array(
			'name'=>'count',
			'headerHtmlOptions'=>array('class'=>'contact-count'),
			'htmlOptions'=>array('class'=>'contact-count'),
			'value'=>'Yii::app()->locale->numberFormatter->formatDecimal($data->count)',
			'headerHtmlOptions'=>array('style'=>'width:10%;'),
		),
	),
)); ?>
