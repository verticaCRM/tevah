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
 * Settings are stored in the generalGridViewSettings attribute of the user's profile record.
 * Grid view column settings and data provider results per page are not managed by this class.
 *
 * @package application.components.X2Settings
 */
class GridViewDbSettingsBehavior extends X2Settings {
    
// commented out since they might become useful
    /**
     * @param string $uid The UID of the grid view
     * @param array (<setting name> => <setting val>) $settings The settings to save
     * @return bool true for success, false otherwise
     */
//    public function saveSettings ($uid, array $settings) {
//        $profile = Yii::app()->params->profile;
//        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
//        if (!is_array ($gvSettings))
//            $gvSettings = array ();
//        $gvSettings[$uid] = $settings;
//        $profile->generalGridViewSettings = CJSON::encode ($gvSettings);
//        return $profile->save ();
//    }
//
//    /**
//     * @param string $uid The UID of the grid view
//     */
//    public function getSettings ($uid) {
//        $profile = Yii::app()->params->profile;
//        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
//        return CJSON::decode ($gvSettings[$uid]);
//    }

    /**
     * @param string $uid The UID of the grid view
     * @param string key the setting name
     * @param string key the setting value
     * @return bool true for success, false otherwise
     */
    public function saveSetting ($key, $val) {
        $uid = $this->getStatePrefix ();
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        if (!is_array ($gvSettings))
            $gvSettings = array ();
        if (!isset ($gvSettings[$uid]) || !is_array ($gvSettings[$uid]))
            $gvSettings[$uid] = array ();
        $gvSettings[$uid][$key] = $val;
        $profile->generalGridViewSettings = CJSON::encode ($gvSettings);
        return $profile->save ();
    }

    /**
     * @param string $uid The UID of the grid view
     * @param string key the setting name
     * @return mixed The value of the gv setting
     */
    public function getSetting ($key) {
        $uid = $this->getStatePrefix ();
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        if (is_array ($gvSettings) && isset ($gvSettings[$uid]) && is_array ($gvSettings[$uid]) &&
            isset ($gvSettings[$uid][$key])) {

            return $gvSettings[$uid][$key];
        }
    }

}
?>
