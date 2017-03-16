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
Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
 ?>

<div id='<?php echo $this->resolveId ('log-a-call'); ?>' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


    <div class="row">
        <div>
            <?php 
            echo CHtml::label(
                Yii::t('app','Quick Note'), 'quickNote',
                array(
                    'style' => 'display:inline-block; margin-right: 10px;'
                )); 
            echo X2Html::dropDownList(
                'quickNote', '', array_merge(array('' => '-'), Dropdowns::getItems(117)), 
                array(
                    'ajax' => array(
                        'type' => 'GET', //request type
                        'url' => Yii::app()->controller->createUrl('/site/dynamicDropdown'),
                        'data' => 'js:{"val":$(this).val(),"dropdownId":"117"}',
                        'update' => $this->resolveIds ('#quickNote2'),
                        'complete' => $this->resolveIds ('function() {
                            auxlib.getElement("#call-action-description").val(""); 
                        }'),
                    ),
                    'id' => $this->resolveId ('quickNote'),
                )
            );
            echo X2Html::dropDownList(
                'quickNote2',
                '',
                array('' => '-'),
                array (
                    'id' => $this->resolveId ('quickNote2'),
                )
            ); 
            ?>
        </div>
    </div>
    <div class="row">
        <div class="text-area-wrapper">
            <?php 
            echo $model->renderInput ('actionDescription',
                array(
                    'rows' => 3,
                    'cols' => 40,
                    'class'=>'action-description x2-textarea',
                    'id'=>$this->resolveId ('call-action-description'),
                ));
            ?>
        </div>
    </div>

    <div class='row action-event-panel'>
        
        <div class="cell action-duration">
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
        </div>

        <div class="cell">

            <?php 
            $model->type = 'call';
            echo CHtml::activeLabel(
                $model,'dueDate',
                array('class' => 'action-start-time-label')); 
            echo X2Html::activeDatePicker ($model, 'dueDate', array(
                    // fix datepicker so it's always on top
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                    'class' => 'action-due-date',
                    'id' => $this->resolveId ('call-form-action-due-date'),
                ), 'datetime', array (
                    'dateFormat' => Formatter::formatDatePicker ('medium'),
                    'timeFormat' => Formatter::formatTimePicker (),
                    'ampm' => Formatter::formatAMPM (),
                ));

            echo CHtml::activeLabel(
                $model,'completeDate', 
                array('class' => 'action-end-time-label'));
            echo X2Html::activeDatePicker ($model, 'completeDate', array(
                    // fix datepicker so it's always on top
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                    'class' => 'action-complete-date',
                    'id' => $this->resolveId ('call-form-action-complete-date'),
                ), 'datetime', array (
                    'dateFormat' => Formatter::formatDatePicker ('medium'),
                    'timeFormat' => Formatter::formatTimePicker (),
                    'ampm' => Formatter::formatAMPM (),
                ));
            ?>
        </div>
    </div><!-- #action-event-panel -->
</div>
