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

 Yii::import ('application.components.sortableWidget.ChartWidget');


/**
 * @package application.components
 */
class UsersChartProfileWidget extends ChartWidget {

    public $chartType = 'usersChart';

    public $viewFile = '_usersChartWidget';

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'User Events',
                    'hidden' => true,
                    'containerNumber' => 2,
                    'chartSubtype' => 'pie', 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getSettingsFormFields () {
    }

    /**
     * Send the chart type to the widget content view 
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'chartType' => $this->chartType,
                    'usersDataProvider' => User::getUsersDataProvider ()
                )
            );
        }
        return $this->_viewFileParams;
    }
     
}
?>
