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
 * Admin section settings for controlling the cron 
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2CronSettingsAction extends CAction {

    public function run(){
        $cf = new CronForm;
        $cf->jobs = array(
            'app_update' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic update app --lock=1',
                'desc' => Yii::t('admin', 'Automatic software updates cron job'),
            ),
            'default' => array(
                'cmd' => 'curl '.Yii::app()->createAbsoluteUrl('/api/x2cron').' &>/dev/null',
                'desc' => 'Run delayed or recurring tasks within X2Engine using the scheduled tasks request URL'
            ),
            'default_console' => array(
                'cmd' => Yii::app()->basePath.DIRECTORY_SEPARATOR.'yiic cron &>/dev/null',
                'desc' => 'Run delayed or recurring tasks within X2Engine using the scheduled tasks request URL'
            ),
        );
        foreach($cf->jobs as $tag => $attributes) {
            $commands[$tag] = $attributes['cmd'];
        }
        if(isset($_POST['crontab_submit'])){
            // Save new updater cron settings in crontab
            $cf->save($_POST);
        }
        $this->controller->render('x2CronSettings',compact('commands'));
    }
}

?>
