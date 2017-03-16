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

Yii::import('application.modules.calendar.controllers.CalendarController');
Yii::import('application.modules.calendar.models.X2Calendar');

/**
 * Widget class for the chat portlet.
 *
 * @package application.components
 */
class SmallCalendar extends X2Widget {

    public $visibility;
    public function init() {
        parent::init();
    }

    public function run() {
        // Prevent the small calendar from showing when using the larger calendar
        if(Yii::app()->controller->modelClass == 'X2Calendar' &&
           Yii::app()->controller->action->getId () == 'index'){
            return;
        }

        // Fetch the calendars to display
        $user = User::model()->findByPk(Yii::app()->user->getId());
        if (is_null($user->showCalendars))
            $user->initCheckedCalendars();
        $showCalendars = $user->showCalendars;

        // Possible urls for the calendar to call
        $urls = X2Calendar::getCalendarUrls();

        $widgetSettingUrl = $this->controller->createUrl('/site/widgetSetting');;

        $justMe = Profile::getWidgetSetting('SmallCalendar','justMe');

        Yii::app()->clientScript->registerCssFile(
            Yii::app()->baseUrl .'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.css');
        
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->theme->baseUrl .'/css/components/smallCalendar.css');
        
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl.'/js/fullcalendar-1.6.1/fullcalendar/fullcalendar.js');
        
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getModule('calendar')->assetsUrl.'/js/calendar.js', CClientScript::POS_END);

        $this->render(
            'smallCalendar',
            array(
                'showCalendars' => $showCalendars,
                'urls' => $urls,
                'user' => $user->username,
                'widgetSettingUrl' => $widgetSettingUrl,
                'justMe' => $justMe
            ));
    }
}

// This tab needs a new name
class PublisherSmallCalendarEventTab extends PublisherEventTab {
    public $tabId ='new-small-calendar-event';
}


?>
