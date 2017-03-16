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

/* @edition:pro */

Yii::import('application.tests.functional.webTrackingTests.WebTrackingTestBase');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');

/**
 * @package application.tests.functional.modules.webTrackingTests
 */
class TargetedContentTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'flows' => array ('X2Flow', '.TargetedContentFunctionalTest'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        if (!(YII_DEBUG && WebListenerAction::DEBUG_TRACK)) {
            self::$skipAllTests = true;
        }
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }

    /**
     * No contact available, assert that default content gets displayed 
     */
    public function testDefaultContent () {
        $this->openPublic('x2WebTrackingTestPages/targetedContentTest.html');
        $this->waitForCondition ("
            window.document.getElementsByTagName ('body')[0].textContent.match (
                /Default Web Content/)
        ", 5000);
    }

    /**
     * Submit the web form and then assert that targeted, rather than default, content gets 
     * displayed
     */
    public function testTargetedContent () {
        $this->submitWebForm ();
        $this->openPublic('x2WebTrackingTestPages/targetedContentTest.html');
        $this->waitForCondition ("
            window.document.getElementsByTagName ('body')[0].textContent.match (
                /Targeted Web Content/)
        ", 5000);
    }

}

?>
