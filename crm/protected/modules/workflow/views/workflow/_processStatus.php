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


$dragAndDropView = isset ($parentView) && $parentView === '_dragAndDropView';

?>
    
<div id='process-status-container' class="form" style="clear:none;">
    <?php
    if (!$dragAndDropView) {
    ?>
    <h2>
        <?php
        echo Yii::t('workflow', '{process} Status', array(
            '{process}' => Modules::displayName(false),
        )); ?>
    </h2>
    <?php 
    }
    $form = $this->beginWidget('CActiveForm', array(
        'action'=>'view',
        'id'=>'dateRangeForm',
        'enableAjaxValidation'=>false,
        'method'=>'get',
    )); ?>
    <div class="row">
        <div class='date-range-title'><?php echo Yii::t('app', 'Stage Start Date:'); ?> </div>
        <?php
        $this->widget ('DateRangeInputsWidget', array (
            'startDateName' => 'start',
            'startDateLabel' => Yii::t('workflow', 'Start Date'),
            'startDateValue' => $dateRange['start'],
            'endDateName' => 'end',
            'endDateLabel' => Yii::t('app', 'End Date'),
            'endDateValue' => $dateRange['end'],
            'dateRangeName' => 'range',
            'dateRangeLabel' => Yii::t('app', 'Date Range'),
            'dateRangeValue' => $dateRange['range'],
        ));
        ?>
    </div>
    <div class="row">
        <div class='date-range-title'><?php echo Yii::t('app', 'Expected Close Date:'); ?> </div>
        <?php
        $this->widget ('DateRangeInputsWidget', array (
            'startDateName' => 'expectedCloseDateStart',
            'startDateLabel' => Yii::t('workflow', 'Start Date'),
            'startDateValue' => $expectedCloseDateDateRange['start'],
            'endDateName' => 'expectedCloseDateEnd',
            'endDateLabel' => Yii::t('app', 'End Date'),
            'endDateValue' => $expectedCloseDateDateRange['end'],
            'dateRangeName' => 'expectedCloseDateRange',
            'dateRangeLabel' => Yii::t('app', 'Date Range'),
            'dateRangeValue' => $expectedCloseDateDateRange['range'] === '' ? 
                'all' : $expectedCloseDateDateRange['range'],
        ));
        ?>
    </div>
    <div class="row row-no-title">
        <div class="cell">
            <?php echo CHtml::label(Yii::t('app', 'Record Type'),'modelType'); ?>
            <?php
            echo CHtml::dropDownList('modelType',$modelType,array(
                ''=>Yii::t('workflow','All'),
                'contacts'=>Yii::t('workflow','Contacts'),
                'opportunities'=>Yii::t('workflow','Opportunities'),
                'accounts'=>Yii::t('workflow','Accounts'),
            ),array(
                'id'=>'workflow-model-type-filter',
                'style'=>'display: none;',
                'multiple' => 'multiple',
                'class' => 'x2-multiselect-dropdown',
                'data-selected-text' => Yii::t('workflow', 'record type(s)'),
            ));
            ?>
        </div>
    </div>
    <div class="row row-no-title">
        <div class="cell">
            <?php 
            echo CHtml::label(Yii::t('workflow','{user}', array(
                '{user}' => Modules::displayName(false, "Users"),
            )), 'users');
            echo CHtml::dropDownList(
                'users',$users,
                array_merge(array(''=>Yii::t('app','All')),User::getNames())); ?>
        </div>
        <?php echo CHtml::hiddenField('id',$model->id); ?>
        <div class="cell">
            <?php echo CHtml::submitButton(
                Yii::t('charts','Go'),
                array(
                    'name'=>'','class'=>'x2-button',
                    'style'=>'margin-top:13px;'
                )
            ); ?>
        </div>
    </div>
    <?php $this->endWidget();?>
</div>
<div id="data-summary-box"></div>
