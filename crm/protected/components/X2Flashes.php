<?php
/***********************************************************************************
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
 **********************************************************************************/

/**
 * Used to collect error, notice, and success messages which can then be echoed back to the client.
 * This can be used in conjunction with the UI library X2Flashes.js.
 */

class X2Flashes {

    /**
     * @var array used to hold success, warning, and error messages
     */
    private static $_flashes;

    public static function getFlashes() {
        if (!isset (self::$_flashes)) {
            self::$_flashes = array (
                'notice' => array (),
                'success' => array (),
                'error' => array (),
            );
        }
        return self::$_flashes;
    }

    public static function setFlashes ($flashes) {
        self::$_flashes = $flashes;
    }

    /**
     * @return bool true if flashes have been added, false otherwise 
     */
    public static function hasFlashes () {
        return array_reduce (array_values (self::getFlashes ()), function ($a, $b) { 
            return $a + sizeof ($b); }) !== 0;
    }

    /**
     * Echoes flashes in the flash arrays
     */
    public static function echoFlashes () {
        echo CJSON::encode (self::getFlashes ());
    }

    public static function getFlashesResponse () {
        return CJSON::encode (self::getFlashes ());
    }

    /**
     * Adds a flash of a specified type 
     * @param string $key 'notice'|'success'|'error'
     * @param string $message
     */
    public static function addFlash ($key, $message) {
        $flashes = self::getFlashes ();
        $flashes[$key][] = $message;
        self::setFlashes ($flashes);
    }

}

?>
