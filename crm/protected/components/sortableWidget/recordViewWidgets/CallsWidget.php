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
 * @package application.components.sortableWidget
 */
class CallsWidget extends TransactionalViewWidget {

    public static $position = -1; 

    protected $labelIconClass = 'fa-phone'; 
    protected $historyType = 'call';

    public function getCreateButtonTitle () {
        return Yii::t('app', 'Log a call');
    }
    
    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Calls',
                    'containerNumber' => 1,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'defaultGvSettings' => array (
                        'actionDescription' => '35%',
                        'assignedTo' => '21%',
                        'duration' => 79,
                        'createDate' => 60,
                    ),
                    'columnOverrides' => array (
                        'assignedTo' => array (
                            'header' => Yii::t('app', 'Completed By'),
                        ),
                        'createDate' => array (
                            'header' => Yii::t('app', 'Call Date'),
                        ),
                    ),
                )
            );
            $this->_gridViewConfig['specialColumns'] = array_merge (
                $this->_gridViewConfig['specialColumns'],
                array (
                    'duration' => array (
                        'header' => Yii::t('app', 'Duration') ,
                        'value' => 'Formatter::secondsToHours (
                            $data->completeDate - $data->dueDate)',
                        'type' => 'raw',
                    ),
                )
            );
        }
        return $this->_gridViewConfig;
    }

    protected function getActionDescriptionHeader () {
        return Yii::t('actions', 'Comment');
    }

}

?>
