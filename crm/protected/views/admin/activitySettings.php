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

Yii::app()->clientScript->registerScript('updateChatPollSlider', "

$('#settings-form input, #settings-form select, #settings-form textarea').change(function() {
	$('#save-button').addClass('highlight'); //css('background','yellow');
});

$('#chatPollTime').change(function() {
	$('#chatPollSlider').slider('value',$(this).val());
});
$('#timeout').change(function() {
	$('#timeoutSlider').slider('value',$(this).val());
});
", CClientScript::POS_READY);

?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Activity Feed Settings'); ?></h2></div>
<div class='admin-form-container'>
	<?php
	$form = $this->beginWidget('CActiveForm', array(
	'id' => 'settings-form',
	'enableAjaxValidation' => false,
	    ));
	?>

	<div class="form">
	<?php
	echo $form->labelEx($model, 'eventDeletionTime')."<br /><br />";
	echo $form->dropDownList($model,'eventDeletionTime',array(
        1=>Yii::t('app','{n} day',array('{n}'=>1)),
        7=>Yii::t('app','{n} days',array('{n}'=>7)),
        30=>Yii::t('app','{n} days',array('{n}'=>30)),
        0=>Yii::t('app','Do not delete')
    ));
	?><br>
	<?php echo Yii::t('admin', 'Set how long activity feed events should last before deletion.'); ?>
	<br><br>
	<?php echo Yii::t('admin', 'Events build up quickly as they are triggered very often and it is highly recommended that some form of clean up is enabled.  Default is 7 days.'); ?>
    </div>
    <div class="form">
	<?php
	echo $form->labelEx($model, 'eventDeletionTypes')."<br /><br />";
	echo $form->checkBoxList($model,'eventDeletionTypes',array(
        'feed'=>Events::model()->parseType('feed'),
        'comment'=>Events::model()->parseType('comment'),
        'record_create'=>Events::model()->parseType('record_create'),
        'record_deleted'=>Events::model()->parseType('record_deleted'),
        'action_reminder'=>Events::model()->parseType('action_reminder'),
        'action_complete'=>Events::model()->parseType('action_complete'),
        'calendar_event'=>Events::model()->parseType('calendar_event'),
        'case_escalated'=>Events::model()->parseType('case_escalated'),
        'email_opened'=>Events::model()->parseType('email_opened'),
        'email_sent'=>Events::model()->parseType('email_sent'),
        'notif'=>Events::model()->parseType('notif'),
        'weblead_create'=>Events::model()->parseType('weblead_create'),
        'web_activity'=>Events::model()->parseType('web_activity'),
        'workflow_complete'=>Events::model()->parseType('workflow_complete'),
        'workflow_revert'=>Events::model()->parseType('workflow_revert'),
        'workflow_start'=>Events::model()->parseType('workflow_start'),
    ));
	?>
	<br><br>
	<?php echo Yii::t('admin', 'Set which types of events will be deleted.  Note that only events will be deleted and not the records themselves, except in the case of Social Posts, which are events.'); ?><br />
	<br />
    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n"; ?>
    <?php //echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div>
</div>
<style>
    div.form label{
        display:inline;
    }
</style>
