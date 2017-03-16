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

?>
<div id="actionTimer">
    <?php
$form = $this->beginWidget('CActiveForm', array(
    'enableAjaxValidation' => false,
    'id' => 'actionTimerControl-form',
    'method' => 'post',
        ));
echo $form->dropDownList($timer, "type", Dropdowns::getItems(120), array('style' => 'width: 100%; margin-bottom: 5px;','class'=>'x2-minimal-select'));
echo '<span id="actionTimerDisplay">00:00:00</span>';
echo $form->hiddenField($timer, 'associationId', array('value' => $model->id));
echo $form->hiddenField($timer, 'associationType', array('value' => get_class($model)));
echo $form->hiddenField($timer, 'userId', array('value' => Yii::app()->user->id));
echo CHtml::link(( $started ? Yii::t('app', 'Stop') : Yii::t('app', 'Start')),'javascript:void(0);', array('class' => 'x2-minimal-button', 'id' => 'actionTimerStartButton'));
echo CHtml::link(Yii::t('app', 'Reset'),'javascript:void(0);', array('class' => 'x2-minimal-button', 'id' => 'timerReset'));
$this->endWidget();
?>
<div id="actionTimerControl-summary"><?php
echo Yii::t('app', 'Total time elapsed: ').'<br /><span id="actionTimerControl-total"><br /></span>';
?></div>
<?php
$action = new Actions;
$action->type = 'time';
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'actionTimerLog-form',
    'htmlOptions' => array ('style' => ($hideForm ? 'display: none;' : '')),
));
echo CHtml::hiddenField('timers', '', array('id' => 'timetrack-timers')); // Timers to be applied
echo CHtml::hiddenField('SelectedTab', 'log-time-spent', array('id' => 'timetrack-timers')); // Timers to be applied
echo $form->hiddenField($action,'type',array('value'=>'time'));
echo $form->hiddenField($action,'timeSpent', array('id' => 'timetrack-timespent'));
echo $form->hiddenField($action,'dueDate',array('id'=>'timetrack-start'));
echo $form->hiddenField($action,'completeDate',array('id'=>'timetrack-end'));
echo $form->hiddenField($action,'associationType',array('value'=>$associationType));
echo $form->hiddenField($action,'associationId',array('value'=>$model->id));

echo $form->textArea($action,'actionDescription',array('id'=>'timetrack-log-description'));

echo CHtml::submitButton(Yii::t('app','Submit'),array(
        'class'=>'x2-button highlight',
        'id' => 'actionTimerLog-submit'
    )
);
$this->endWidget();
?>
</div>
