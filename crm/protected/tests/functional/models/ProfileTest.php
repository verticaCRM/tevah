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

class ProfileTest extends X2WebTestCase {

    public $autoLogin = false;

    /**
     * Copies a test controller into the controllers directory.
     */
    public static function setUpBeforeClass () {
        // ensure that a directory with the same name isn't already in the web root
        exec ('ls ../controllers', $output);
        if (!YII_UNIT_TESTING) {  
            // YII_UNIT_TESTING must be true for profileTest routing rule to be added
            self::$skipAllTests = true;
        } else if (in_array ('ProfileTestController.php', $output)) {
            VERBOSE_MODE && println ('Warning: tests are being aborted because file '.
                '"ProfileTestController" already exists in the protected/controllers');
            self::$skipAllTests = true;
        } else {
            // copy over webscripts and perform replacement on URL tokens
            exec ('cp -n webscripts/ProfileTestController.php ../controllers');
        }
        parent::setUpBeforeClass ();
    }

    /**
     * Remove all the test pages that were copied over 
     */
    public static function tearDownAfterClass () {
        if (!self::$skipAllTests)
            exec ('rm ../controllers/ProfileTestController.php');
        parent::tearDownAfterClass ();
    }

    /**
     * Visit a test action in a test controller whose sole purpose is to echo out the current 
     * user profile's username. Since autoLogin is disabled, the username should be the guest 
     * profile username.
     */
    public function testGuestProfile () {
        $this->openX2('profileTest/testGuestProfile');
        $this->assertTextPresent (Profile::GUEST_PROFILE_USERNAME);
    }
}



?>
