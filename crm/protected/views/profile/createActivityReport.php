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

$this->actionMenu = array(
    array('label' => Yii::t('profile', 'View Profile'), 'url' => array('view', 'id' => Yii::app()->user->getId())),
    array('label' => Yii::t('profile', 'Edit Profile'), 'url' => array('update', 'id' => Yii::app()->user->getId())),
    array('label' => Yii::t('profile', 'Change Settings'), 'url' => array('settings'),),
    array('label' => Yii::t('profile', 'Change Password'), 'url' => array('changePassword', 'id' => Yii::app()->user->getId())),
    array('label' => Yii::t('profile', 'Manage Apps'), 'url' => array('manageCredentials'))
);
?>
<div class="page-title icon profile"><h2><?php echo Yii::t('profile', 'Create Activity Feed Report Email'); ?></h2></div>
<div class="form">
    <div style="width:600px;">
        <?php
        echo Yii::t('profile', 'This form will allow you to create a periodic email with information about events in the Activity Feed.') . "<br>";
        echo Yii::t('profile', 'The filters you had checked on the previous page will be used to determine which content to give you information about.') . "<br>";
        echo Yii::t('profile', 'Please note that this report will not function if you do not have the Cron service turned on, please check with your administrator if you are unsure.');
        ?>
    </div>
    <div class='form'>
        <?php echo CHtml::form(); ?>
        <?php echo '<h3>' . Yii::t('profile', 'Report Settings') . '</h3>'; ?>
        <div>
            <?php echo CHtml::label(Yii::t('profile', 'Report Name'), 'reportName'); ?>
            <?php
            echo CHtml::textField('reportName', 'Daily Activity Feed Report', array(
                'style' => 'width:250px;'
            ));
            ?>
        </div>
        <br>
        <span style='float:left;'>
            <?php echo CHtml::label(Yii::t('profile', 'Date Range'), 'range'); ?>
            <?php
            echo CHtml::dropDownList('range', 'daily', array(
                'daily' => Yii::t('profile', 'Daily'),
                'weekly' => Yii::t('profile', 'Weekly'),
                'monthly' => Yii::t('profile', 'Monthly'),
            ));
            ?>
        </span>
        <span style='float:left;margin-left:20px;'>
            <?php
            Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
            echo CHtml::label(Yii::t('profile', 'Hour'), 'hour');
            $this->widget('CJuiDateTimePicker', array(
                'name' => 'hour', //attribute name
                'value' => Formatter::formatTime(strtotime('9 AM')),
                'mode' => 'time', //use "time","date" or "datetime" (default)
                'options' => array(
                    'timeFormat' => Formatter::formatTimePicker(),
                ),
            ));
            ?>
        </span>
        <span style='float:left;margin-left:20px;'>
            <?php echo CHtml::label(Yii::t('profile', 'Limit'), 'limit'); ?>
            <?php echo CHtml::textField('limit', '10'); ?>
        </span>
        <?php echo CHtml::hiddenField('filters', $filters); ?>
        <?php echo CHtml::hiddenField('userId', Yii::app()->user->getId()); ?>
        <div style='clear:both;'>
            <?php
            echo CHtml::submitButton(Yii::t('profile', 'Create'), array(
                'class' => 'x2-button',
                'style' => 'float:left;'
            ));
            ?>
            <?php
            echo CHtml::ajaxButton(Yii::t('profile', 'Send Test Email'), 'sendTestActivityReport', array(
                'data' => array(
                    'userId' => Yii::app()->user->getId(),
                    'filters' => $filters,
                ),
                'complete' => '$("#test-email-button").hide().after("<span style=\"margin-left: 10px; line-height: 40px; font-weight: bold; color: green;\">' . Yii::t('profile', 'Test email sent!') . '</span>")'), array(
                'class' => 'x2-button',
                'style' => 'float:left;margin-left:15px;',
                'id' => 'test-email-button',
                    )
            );
            ?>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>  
    <?php ?>
</div>
