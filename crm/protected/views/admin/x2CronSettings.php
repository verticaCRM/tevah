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

Yii::app()->clientScript->registerScript('x2CronSettingsJS',"
$(function () {
    // show cron log
    $('#view-log-button').on ('click', function () {
        if ($(this).attr ('disabled')) return;
        $('#view-log-button').attr ('disabled', 'disabled');
        $.ajax ({
            'url': ".CJSON::encode(
                $this->createUrl(
                    '/admin/viewLog',
                    array('name' => 'automation.log'))).",
            'success': function (data) {
                $('body').append($('<div>', {
                    id: 'cron-log-dialog',
                    html: data
                }).css({
                    'max-height':'500px',
                    'overflow-x':'hidden'
                }));
                $('#cron-log-dialog').dialog ({
                    autoOpen: true,
                    maxHeight: '500px',
                    title: ".CJSON::encode(
                        Yii::t(
                            'studio', 'Viewing log file {file}',
                            array('{file}' => 'automation.log'))).",
                    width: 'auto',
                    resizable: true,
                    close: function (event, ui) {
                        $('#cron-log-dialog').remove ();
                        $('#view-log-button').removeAttr ('disabled');
                    }
                });
            }
        });
    });
})
", CClientScript::POS_END);


?>
<div class="page-title">
    <h2><?php echo Yii::t('admin', 'Cron Table') ?></h2>
    <a class="x2-button right" id="view-log-button" href="javascript:void(0);">
        <?php echo Yii::t('studio', 'View Cron Log'); ?>
    </a>
</div>
<div class="span-24">
    <div class="form">

        <h3><?php echo Yii::t('admin','Disclaimer'); ?></h3>
        <p><?php echo Yii::t('admin','Using this form may interfere with third-party cron table managers.')
                .'&nbsp;'.Yii::t('admin','If you are not using X2Engine Cloud / On Demand, and your hosting service provides a scheduled tasks manager, it is recommended that you use that instead, with the commands as listed here.'); ?></p>
        <?php
        $form = Yii::app()->controller->beginWidget('CActiveForm', array(
            'id' => 'cron-settings-form',
                ));
        $this->widget('CronForm', array(
            'labelCssClass' => 'cron-checkitem big',
            'formData' => $_POST,
            'displayCmds' => $commands,
            'jobs' => array(
                'default' => array(
                    'title' => Yii::t('admin', 'Run scheduled X2Engine tasks via web request'),
                    'longdesc' => Yii::t('admin', 'If enabled, a web reqeust will be made from this web server to itself at the scheduled task runner URL.* This will trigger events such as X2Flow delayed actions and periodic triggers, and will attempt to send a batch of unsent email campaign messages.'),
                    'instructions' => Yii::t('admin', 'Specify a cron schedule below. Note that for this to work properly requires that the domain name of the server can be resolved from itself, and there is a valid network route to its public/external network address. To check whether this is true, use the {link}.',array('{link}' => CHtml::link('local API resolvability test', Yii::app()->baseUrl.'/resolve_self.php', array('target' => '_blank'))))
                    .'<br /><br />'.Yii::t('admin','If the above link does not work, download the script from {link} and copy it to the web root of X2Engine. If the script produces a message saying that it cannot resolve the local server, consider disabling this and enabling the alternate scheduled task running method, below.',array('{link}'=>CHtml::link(Yii::t('admin','here'),'https://raw.github.com/X2Engine/X2Engine/master/x2engine/resolve_self.php')) ).'<br /><br />* '.Yii::app()->controller->createAbsoluteUrl('/api/x2cron'),
                ),
                'default_console' => array(
                    'title' => Yii::t('admin','Run scheduled X2Engine tasks via command line interface'),
                    'longdesc' => Yii::t('admin','If enabled, the Yii console command runner will be used to perform scheduled tasks.'),
                    'instructions' => Yii::t('admin', 'Specify a cron schedule below. This will perform all of the same tasks as the web-based scheduled task runner, except for sending batches of campaign emails.'),
                ),
                'app_update' => array(
                    'title' => Yii::t('admin', 'Update automatically'),
                    'longdesc' => Yii::t('admin', 'If enabled, X2Engine will periodically check for updates and update automatically if a new version is available.'),
                    'instructions' => Yii::t('admin', 'Specify an update schedule below. Note, X2Engine will be locked when the update is being applied, and so it is recommended to schedule updates at times when the application will encounter the least use. If any compatibility issues are detected, the update package will not be applied, but will be retrieved and unpacked for manual review and confirmation.'),
                ),
            ),
        ));
        echo '<hr />';
        echo CHtml::hiddenField('crontab_submit',1);
        echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n";
        Yii::app()->controller->endWidget();
        ?>
    </div>
</div>
