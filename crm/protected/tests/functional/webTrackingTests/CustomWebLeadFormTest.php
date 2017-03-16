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

Yii::import('application.tests.functional.webTrackingTests.WebTrackingTestBase');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.users.models.User');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class CustomWebLeadFormTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'users' => array ('User', '.CustomWebLeadFormTest'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }

    /**
     * Submits the custom web lead form and ensures successful submission
     */
    protected function submitCustomWebForm ($formVersion='') {
        if ($formVersion === 'differentDomain') {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTestDifferentDomain.html');
        } else if ($formVersion === 'differentSubdomain') {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTestDifferentSubdomain.html');
        } else {
            $this->openPublic('x2WebTrackingTestPages/customWebFormTest.html');
        }

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
		$this->click("css=#submit");
        // wait for response
        $this->waitForCondition (
            "selenium.browserbot.getCurrentWindow(document.getElementById ('success'))",
            4000);
        $this->pause (5000); // wait for database changes to enact
    }



    /**
     * Submit web lead form and wait for success message
     */
    public function testSubmitCustomWebLeadForm () {
        $this->deleteAllVisibleCookies ();
        $this->submitCustomWebForm ();
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertContactCreated ();
    }

    /**
     * Test that submission of custom web form initiates cookie-based tracking
     */
    public function testCustomWebLeadFormTracking () {
        $this->deleteAllVisibleCookies ();

        // assert that cookie-based tracking doesn't work before form submission
        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTest.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertNoWebActivityGeneration (TEST_WEBROOT_URL_ALIAS_1);

        $this->submitCustomWebForm ();
        $this->assertCookie ('regexp:.*x2_key.*');

        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTest.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertWebActivityGeneration ();
    }

    /**
     * Test that submission of custom web form does not initiate tracking if request from webtracker
     * is made across domains.
     */
    public function testCustomWebLeadFormTrackingAcrossDomains () {
        $this->deleteAllVisibleCookies ();

        // assert that cookie-based tracking doesn't work before form submission
        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertNoWebActivityGeneration ();

        $this->submitCustomWebForm ('differentDomain');
        $this->assertCookie ('regexp:.*x2_key.*');

        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertNoWebActivityGeneration ();
    }

    /**
     * Test that submission of custom web form initiates cookie-based tracking if request from 
     * webtracker is made across subdomains.
     */
    public function testCustomWebLeadFormTrackingAcrossSubdomains () {
        $this->deleteAllVisibleCookies ();

        // assert that cookie-based tracking doesn't work before form submission
        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTestDifferentSubdomain.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertNoWebActivityGeneration (TEST_WEBROOT_URL_ALIAS_1);

        $this->submitCustomWebForm ('differentSubdomain');
        $this->assertCookie ('regexp:.*x2_key.*');

        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webTrackerTestDifferentSubdomain.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertWebActivityGeneration ();
    }

}

?>
