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
 * @package application.components.X2GridView
 */
class GridViewDbSettingsBehavior extends CBehavior {
    
    /**
     * @param string $gridViewUID The UID of the grid view
     * @param array (<setting name> => <setting val>) $settings The settings to save
     * @return bool true for success, false otherwise
     */
    public static function saveSettings ($gridViewUID, array $settings) {
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        if (!is_array ($gvSettings))
            $gvSettings = array ();
        $gvSettings[$gridViewUID] = $settings;
        $profile->generalGridViewSettings = CJSON::encode ($gvSettings);
        return $profile->save ();
    }

    /**
     * @param string $gridViewUID The UID of the grid view
     */
    public static function getSettings ($gridViewUID) {
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        return CJSON::decode ($gvSettings[$gridViewUID]);
    }

    /**
     * @param string $gridViewUID The UID of the grid view
     * @param string key the setting name
     * @param string key the setting value
     * @return bool true for success, false otherwise
     */
    public static function saveSetting ($gridViewUID, $key, $val) {
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        if (!is_array ($gvSettings))
            $gvSettings = array ();
        if (!is_array ($gvSettings[$gridViewUID]))
            $gvSettings[$gridViewUID] = array ();
        $gvSettings[$gridViewUID][$key] = $val;
        $profile->generalGridViewSettings = CJSON::encode ($gvSettings);
        return $profile->save ();
    }

    /**
     * @param string $gridViewUID The UID of the grid view
     * @param string key the setting name
     * @return mixed The value of the gv setting
     */
    public static function getSetting ($gridViewUID, $key) {
        $profile = Yii::app()->params->profile;
        $gvSettings = CJSON::decode ($profile->generalGridViewSettings); 
        if (is_array ($gvSettings) && is_array ($gvSettings[$gridViewUID]) &&
            isset ($gvSettings[$gridViewUID][$key])) {

            return $gvSettings[$gridViewUID][$key];
        }
    }

}
?>

