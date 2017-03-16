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

/**
 * Renders the action timers adjustment form.
 */

Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/actionTimersForm.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/actionTimersForm.css');

$timers = $model->timers;
if(!empty($timers)) {
$timerTypes = Dropdowns::getItems(120);
?>
<div class="form">

<label style="font-weight: bold; color: black;">
    <?php echo Yii::t('actions','{action} timers', array(
        '{action}' => Modules::displayName(false),
    )); ?>
</label>
<br />
<table class="action-timers-form" id="action-timers-form">
<thead>
<th></th>
<th>Type</th>
<th>Started</th>
<th>Stopped</th>
<th class="timer-total-column">Total</th>
</thead>
<tbody>
<?php foreach($model->timers as $timer) { ?>
<tr class="timer-record" id="timer-record-<?php echo $timer->id; ?>">
<td>
    <a class="delete-timer" href="javascript:void(0);" title="Delete this time interval">
        <img src="<?php echo Yii::app()->theme->baseUrl;?>/css/gridview/delete.png" alt="Delete">
    </a>
    <?php
    $attr = 'id';
    echo CHtml::activeHiddenField($timer,$attr,array('name'=>CHtml::resolveName($timer,$attr).'[]')); 
    ?>
</td><!-- Delete button + hidden ID field -->
<td>
<?php 
$attr = 'type';
echo CHtml::activeDropDownList($timer,$attr,$timerTypes,array(
    'name' => CHtml::resolveName($timer,$attr).'[]',
));

?>
</td><!-- Timer type -->
<?php 
foreach(array('timestamp','endtime') as $attr) {
?><td><?php
    $timer->$attr = Yii::app()->dateFormatter->formatDateTime($timer->$attr, 'medium', 'medium');
    echo Yii::app()->controller->widget('CJuiDateTimePicker', array(
            'model' => $timer, //Model object
            'attribute' => $attr, //attribute name
            'mode' => 'datetime', //use "time","date" or "datetime" (default)
            'options' => array(// jquery options
                'dateFormat' => Formatter::formatDatePicker('medium'),
                'timeFormat' => Formatter::formatTimePicker('',true),
                'ampm' => Formatter::formatAMPM(),
                'changeMonth' => true,
                'changeYear' => true,
                'showSecond' => true
            ),
            'htmlOptions' => array(
                'name' => CHtml::resolveName($timer,$attr).'[]',
                'id' => 'timer-record-'.$attr.'-'.$timer->id,
                'class' => 'time-input time-at-'.$attr
            ),
            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
    ), true);

?></td><?php } ?>
<td class="timer-total-column">
<input type="hidden" class="timer-total" name="timer-total-<?php echo $timer->id ?>" value="">
<span class="timer-total"></span>
</td><!-- Total -->
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr class="all-timers-total">
<td colspan="4"></td>
<td class="timer-total-column">
<input type="hidden" class="timer-total all-timers-total" name="timer-total-<?php echo $timer->id ?>" value="">
<span class="timer-total"></span>
</td>
</tfoot>
</table>

<?php
Yii::app()->clientScript->registerScript('action-timer-form-init-rows',
        "x2.actionTimersForm.getElement('tr.timer-record').each(function(){"
        . "x2.actionTimersForm.recalculateLine($(this));"
        ."});");

} ?>
</div><!-- .form -->
