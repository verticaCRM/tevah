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

//////////////////////////////////////
// The action description text area //
//////////////////////////////////////
$saveButton = CHtml::ajaxSubmitButton(
    Yii::t('app', 'Save'), array('/actions/actions/publisherCreate'), 
    array(
        'beforeSend' => "x2.publisher.beforeSubmit",
        'success' => "function(data) {
            x2.publisher.updates();
            x2.publisher.reset();
             // event detected by x2chart.js
            $(document).trigger ('newlyPublishedAction');
        }",
        'type' => 'POST',
    ), 
    array('id' => 'save-publisher', 'class' => 'x2-button')
);


$users = User::getNames(); 
$form = $this->beginWidget('CActiveForm', array('id' => 'publisher-form')); 
?>
<div id="publisher">
    

    <?php if(!$calendar) { 
        ////////////////////
        // Publisher tabs //
        ////////////////////
        // When not used in calendar=true mode, tabs (for different action
        // record types) will be displayed.
        ?>
        <ul>
            <?php 
            if(!$hiddenTabs['log-a-call']) { 
            ?>
            <li>
                <a href="#log-a-call"><?php echo Yii::t('actions', 'Log A Call'); ?></a>
            </li>
            <?php 
            } 
            if(!$hiddenTabs['log-time-spent']) { 
            ?>
            <li>
                <a href="#log-time-spent"><?php echo Yii::t('actions', 'Log Time'); ?></a>
            </li>
            <?php 
            } 
            if(!$hiddenTabs['new-action']) { 
            ?>
            <li>
                <a href="#new-action"><b>+</b><?php echo Yii::t('actions', 'Action'); ?></a>
            </li>
            <?php 
            }
            if(!$hiddenTabs['new-comment']) { 
            ?>
            <li style='margin-right: 0'>
                <a href="#new-comment"><b>+</b><?php echo Yii::t('actions', 'Comment'); ?></a>
            </li>
            <?php 
            } 
            ?>
        </ul>
    <?php } ?>
    <div class="form x2-layout-island">
    <?php 
        if(!$calendar) {
        ///////////////////////////
        // Publisher tab content //
        ///////////////////////////
        //
        // Any extraneous markup to be displayed above the other publisher form
        // inputs when not in calendar mode, specific to each tab, should go in.
        ?>
        <div class="row">
            <?php 
            if(!$hiddenTabs['log-a-call']) { 
            ?>
            <div id="log-a-call">
            <?php 
            echo CHtml::label(
                Yii::t('app','Quick Note'), 'quickNote',
                array('style' => 'display:inline-block;')); 
            echo CHtml::dropDownList(
                'quickNote', '', array_merge(array('' => '-'), Dropdowns::getItems(117)), 
                array(
                    'ajax' => array(
                        'type' => 'GET', //request type
                        'url' => Yii::app()->controller->createUrl('/site/dynamicDropdown'),
                        'data' => 'js:{"val":$(this).val(),"dropdownId":"117"}',
                        'update' => '#quickNote2',
                        'complete' => 'function() {
                            auxlib.getElement("#action-description").val(""); 
                        }'
                )
            ));
            echo CHtml::dropDownList('quickNote2', '', array('' => '-')); 
            ?>
            </div>
            <?php 
            } 
            foreach(array('log-time-spent','new-action','new-comment') as $tab) { 
                if(!$hiddenTabs[$tab]) { ?>
                    <div id="<?php echo $tab; ?>"></div>
                <?php 
                } 
            } ?> 
            </div>
    <?php } else { ?>
        <span class="publisher-widget-title"><?php echo Yii::t('actions','New Event') ?></span>
    <?php } ?>

    <div class="row">
        <?php if(!$calendar) echo $saveButton; ?>
        <div class="text-area-wrapper">
            <?php 
            echo $form->textArea(
                $model, 'actionDescription', 
                array('rows' => 3, 'cols' => 40,'id'=>'action-description'));
            ?>
        </div>
    </div><!-- .row -->

    <?php if(Yii::app()->user->isGuest){ ?>
        <div class="row">
            <?php
            $this->widget('CCaptcha', array(
                'captchaAction' => '/actions/actions/captcha',
                'buttonOptions' => array(
                    'style' => 'display:block;',
                ),
            ));
            ?>
            <?php echo $form->textField($model, 'verifyCode'); ?>
        </div>
    <?php } 
    echo CHtml::hiddenField('SelectedTab', ''); // currently selected tab  
    echo $form->hiddenField($model, 'associationType'); 
    echo $form->hiddenField($model, 'associationId'); 
    ?>
    
    <div id="action-event-panel" class="row">
        
        <div class="cell" id="action-duration" style="display:none;">
            <div class="action-duration-input">
                <label for="timetrack-hours"><?php echo Yii::t('actions','Hours'); ?></label>
                <input class="action-duration-display" type="number" min="0" max="99" 
                 name="timetrack-hours" />
            </div>
            <span class="action-duration-display">:</span>
            <div class="action-duration-input">
                <label for="timetrack-minutes"><?php echo Yii::t('actions','Minutes'); ?></label>
                <input class="action-duration-display" type="number" min="0" max="59" 
                 name="timetrack-minutes" />
            </div>
        </div><!-- #action-duration .cell -->

        <div class="cell">

            <?php 
            echo CHtml::activeLabel(
                $model,'dueDate',
                array('id' =>  'action-due-date-label', 'style' => 'display: none;')); 
            echo CHtml::activeLabel(
                $model,'dueDate',array('label'=>Yii::t('actions', 'Start Date'),
                'id' => 'action-start-date-label', 'style' => 'display: none;')); 
            echo CHtml::activeLabel(
                $model,'dueDate',
                array('label'=>Yii::t('actions', 'Time started'),
                    'id' => 'action-start-time-label', 'style' => 'display: none;')); 
            Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
            $this->widget('CJuiDateTimePicker', array(
                'model' => $model, //Model object
                'attribute' => 'dueDate', //attribute name
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                'htmlOptions' => array(
                    'id'=>'action-due-date',
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                ), // fix datepicker so it's always on top
            ));

            echo CHtml::activeLabel(
                $model,'completeDate',
                array(
                    'label'=>Yii::t('actions', 'End Date'), 'id' => 'action-end-date-label',
                    'style' => 'display: none;'));
            echo CHtml::activeLabel(
                $model,'completeDate', 
                array(
                    'label'=>Yii::t('actions', 'Time ended'),'id' => 'action-end-time-label',
                    'style' => 'display: none;'));

            $model->dueDate = Formatter::formatDateTime(time());
            Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
            $this->widget('CJuiDateTimePicker', array(
                'model' => $model, //Model object
                'attribute' => 'completeDate', //attribute name
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                'htmlOptions' => array(
                    // fix datepicker so it's always on top
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '20');", 
                    'style' => 'display: none;',
                    'id' => 'action-complete-date',
                ),
            ));
            ?>
        </div><!-- .cell -->
            
        <div class="cell">
            <?php 
            echo CHtml::activeLabel(
                $model,'priority',
                array('id'=>'action-priority-label','style'=>'display: none;')); 
            echo $form->dropDownList($model, 'priority', array(
                '1' => Yii::t('actions', 'Low'),
                '2' => Yii::t('actions', 'Medium'),
                '3' => Yii::t('actions', 'High'))
                    ,
                array('id'=>'action-priority')
            );
            
            $form->label($model, 'color',array('id'=>'action-color-label')); 
            echo $form->dropDownList(
                $model, 'color', Actions::getColors(),array('id'=>'action-color-dropdown')); 
            ?>
        </div><!-- .cell -->
           
        <?php /* Assinged To */ ?>
        <div class="cell">
            <?php 
            /* Users */ 
            echo $form->label($model, 'assignedTo',array('id'=>'action-assigned-to-label')); 
            echo $form->dropDownList(
                $model, 'assignedTo', X2Model::getAssignmentOptions(true,true), 
                array('id' => 'action-assignment-dropdown')); 
            echo $form->label($model, 'visibility',array('id'=>'action-visibility-label')); 
            echo $form->dropDownList(
                $model, 'visibility', 
                array(
                    0 => Yii::t('actions', 'Private'), 1 => Yii::t('actions', 'Public'),
                    2 => Yii::t('actions', "User's Group")
                ),
                array('id'=>'action-visibility-dropdown')); 
            ?>
        </div><!-- .cell -->
        
    </div><!-- #action-event-panel -->

    </div><!-- .form -->
    <?php if($calendar) echo $saveButton; ?>
</div><!-- #publisher -->

<?php $this->endWidget(); ?>
