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

class TestingAuxLibTest extends X2DbTestCase {

    public $fixtures = array (
        'authItems' => array (':x2_auth_item', '.MassDeleteTest'),
        'authItemChildren' => array (':x2_auth_item_child', '.MassDeleteTest'),
        'users' => 'User',
        'profiles' => 'Profile',
    );

    public function testSetPublic () {
        $fn = TestingAuxLib::setPublic ('TestingAuxLib', 'privateMethod');
        $this->assertTrue ($fn (array (1, 2)) === array (1, 2));
    }

    /**
     * Attempt to login with curlLogin and ensure that a page which requires login can be viewed 
     */
    public function testCurlLogin () {
        // ensure that page which should require login can't be viewed before logging in
        $sessionId = uniqid ();
        $cookies = "PHPSESSID=$sessionId; path=/;";
        $curlHandle = curl_init (TEST_BASE_URL.'profile/settings');
        curl_setopt ($curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $result = curl_exec ($curlHandle);
        ob_clean ();
        $this->assertFalse ((bool) preg_match ('/Change Personal Settings/', $result));

        // log in and then request the same page 
        $sessionId = TestingAuxLib::curlLogin ('testuser', 'password');
        $cookies = "PHPSESSID=$sessionId;";
        $curlHandle = curl_init (TEST_BASE_URL.'profile/settings');
        curl_setopt ($curlHandle, CURLOPT_HTTPGET, 1);
        curl_setopt ($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $result = curl_exec ($curlHandle);
        ob_clean ();
        //print_r ("document.cookie = 'PHPSESSID=$sessionId; path=/;';\n");
        //print_r ($result);
        $this->assertTrue ((bool) preg_match ('/Change Personal Settings/', $result));
    }
}

?>
