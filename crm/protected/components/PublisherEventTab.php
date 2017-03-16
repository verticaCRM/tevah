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

/**
 * @package application.components
 */
class PublisherEventTab extends PublisherTimeTab {
    
    public $viewFile = 'application.components.views.publisher._eventForm';

    public $title = 'New Event';

    public $tabId = 'new-event'; 

    public $JSClass = 'PublisherEventTab';

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'PublisherEventTabTabJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/publisher/PublisherEventTab.js',
                        ),
                        'depends' => array ('PublisherTimeTabJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function renderTab ($viewParams) {
        // set date, time, and region format for when javascript replaces datetimepicker
        // datetimepicker is replaced in the calendar module when the user clicks on a day
        $dateformat = Formatter::formatDatePicker('medium');
        $timeformat = Formatter::formatTimePicker();
        $ampmformat = Formatter::formatAMPM();
        $region = Yii::app()->locale->getLanguageId(Yii::app()->locale->getId());
        if($region == 'en')
            $region = '';

        // save default values of fields for when the publisher is submitted and then reset
        Yii::app()->clientScript->registerScript('defaultValues', '
            // set date and time format for when datetimepicker is recreated
            $("#publisher-form").data("dateformat", "'.$dateformat.'");
            $("#publisher-form").data("timeformat", "'.$timeformat.'");
            $("#publisher-form").data("ampmformat", "'.$ampmformat.'");
            $("#publisher-form").data("region", "'.$region.'");
        ', CClientScript::POS_READY);

        parent::renderTab ($viewParams);
    }

}
