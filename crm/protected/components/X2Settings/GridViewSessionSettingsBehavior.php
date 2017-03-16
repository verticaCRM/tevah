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
 * Manages storage and retrieval of grid view settings.
 * Settings are stored in the $_SESSION PHP superglobal.
 * Grid view column settings and data provider results per page are not managed by this class.
 *
 * @package application.components.X2Settings
 */
class GridViewSessionSettingsBehavior extends X2Settings {
    
    /**
     * @param string $uid The UID of the grid view
     * @param string key the setting name
     * @param string val the setting value
     * @return bool true for success, false otherwise
     */
    public function saveSetting ($key, $val) {
        $uid = $this->getStatePrefix ();

        $gvSettings = CJSON::decode (Yii::app()->user->getState($uid));
        if (!is_array ($gvSettings))
            $gvSettings = array ();
        $gvSettings[$key] = $val;
        Yii::app()->user->setState($uid, CJSON::encode ($gvSettings));
        return true;
    }

    /**
     * @param string $uid The UID of the grid view
     * @param string key the setting name
     * @return null|mixed The value of the gv setting
     */
    public function getSetting ($key) {
        $uid = $this->getStatePrefix ();

        $gvSettings = CJSON::decode (Yii::app()->user->getState($uid));
        if (is_array ($gvSettings) && isset ($gvSettings[$key])) {
            return $gvSettings[$key];
        }
    }

}
?>
