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
class ActionsWidget extends TransactionalViewWidget {

    public static $position = 1; 

    protected $labelIconClass = 'fa-play-circle'; 
    protected $historyType = 'action';

    public function getCreateButtonTitle () {
        return Yii::t('app', 'New action');
    }
 
    private static $_JSONPropertiesStructure;
    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Actions',
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $this->_dataProvider = parent::getDataProvider ();
            if (!isset ($_GET[$this->getWidgetKey ().'_sort'])) {
                $this->_dataProvider->criteria->order = 'dueDate asc';
            }
        }
        return $this->_dataProvider;
    }

    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'defaultGvSettings' => array (
                        'actionDescription' => '38%',
                        'assignedTo' => '22%',
                        'dueDate' => 155,
                    ),
                )
            );
            $this->_gridViewConfig['specialColumns'] = array_merge (
                $this->_gridViewConfig['specialColumns'],
                array (
                    'dueDate' => array (
                        'name' => 'dueDate',
                        'header' => Yii::t('app', 'Due Date'),
                        'value' => 'Actions::parseStatus ($data->dueDate, "short", "short")',
                        'type' => 'raw',
                    ),
                )
            );

        }
        return $this->_gridViewConfig;
    }
}

?>
