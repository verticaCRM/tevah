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
 * Manages storage and retrieval of settings.
 *
 * @package application.components.X2Settings
 */
abstract class X2Settings extends CBehavior {

    /**
     * @var string|null $uid Allows state prefix to be explicitly set
     */
    public $uid = null; 
    
    /**
     * @var string $modelClass class of model for which settings are being saved/retrieved
     */
    public $modelClass; 

    /**
     * session/db JSON key
     * @var string|null
     */
    private $_statePrefix; 
    
    // commented out since they might become useful
    /**
     * @param string $uid 
     * @param array (<setting name> => <setting val>) $settings The settings to save
     * @return bool true for success, false otherwise
     */
    //public function saveSettings ($uid, array $settings);

    /**
     * @param string $uid 
     */
    //public function getSettings ($uid);

    /**
     * @param string key the setting name
     * @param string key the setting value
     * @return bool true for success, false otherwise
     */
    abstract public function saveSetting ($key, $val);

    /**
     * @param string key the setting name
     * @return mixed The value of the gv setting
     */
    abstract public function getSetting ($key);

    /**
     * state prefix defaults to uid or uid constructed from path and model id. It might be
     * better to call this getUID since the state prefix isn't actually a prefix, it is the key in
     * its entirety.
     * @return string 
     */
    public function getStatePrefix () {
        if (!isset ($this->_statePrefix)) {
            if (isset ($this->uid)) {
                $this->_statePrefix = $this->uid;
            } else {
                $this->_statePrefix = ((!Yii::app()->params->noSession ?
                    Yii::app()->controller->uniqueid . '/' . Yii::app()->controller->action->id . 
                        (isset($_GET['id']) ? '/' . $_GET['id'] : '') : '').
                    $this->modelClass);
            }
        }
        return $this->_statePrefix;
    }

}
?>
