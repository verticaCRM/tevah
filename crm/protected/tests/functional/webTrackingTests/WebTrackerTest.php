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
 * 
 * @package application.tests.functional.modules.contacts
 */
class WebTrackerTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }

    /**
     * Submit the web lead form and then visit a page that has the web tracker on it
     */
    public function testWebTracker () {
        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        // initiate tracking by submitting the web form
        $this->submitWebForm ();
        $this->assertContactCreated ();
        $this->assertCookie ('regexp:.*x2_key.*');

        // visit page with web tracker on it
        $this->assertWebTrackerTracksWithCookie ();

    }

    /**
     * Initiates tracking using the test web root, and then, using a separate subdomain, visits a 
     * page containing the web tracker and asserts that tracking does work
     */
    public function testWebTrackerAcrossSubDomains () {
        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        // initiate tracking by submitting the web form
        $this->submitWebForm ('differentSubdomain');
        $this->assertContactCreated ();
        $this->assertCookie ('regexp:.*x2_key.*');

        // ensure that when the webtracker makes requests to a different subdomain 
        // the cookies can be accessed on the server

        // visit the page with the web tracker on it
        $this->clearWebActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentSubdomain.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->pause (5000); // wait for database changes to enact
        $this->assertWebActivityGeneration ();

    }

    /**
     * Initiates tracking using the test web root, and then, using a separate domain, visits a 
     * page containing the web tracker .
     * In browsers that block third party cookies by default, web tracking should fail.
     * Unlike with the custom web form, if third party cookies are not blocked, having the tracker
     * make requests to a separate domain should not prevent web tracking.
     */
    public function testWebTrackerAcrossDomains () {
        VERBOSE_MODE && println ('testWebTrackerAcrossDomains: isIE8 () === '.$this->isIE8());

        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        // initiate tracking by submitting the web form
        $this->submitWebForm ('differentDomain');
        $this->assertContactCreated ();

        // even though the cookie is set, it's set on a domain that's different than the one
        // that the web form is being accessed through, so selenium can't read it
        // Commented out because, for some reason, ie8 can read this cookie
        // $this->assertNotCookie ('regexp:.*x2_key.*'); 

        // ensure that when the webtracker makes requests to a different domain 
        // the cookies can be accessed on the server

        // visit the page with the web tracker on it
        $this->clearWebActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->pause (5000); // wait for database changes to enact

        if ($this->isIE8 ()) // ie8 blocks third party cookies by default
            $this->assertNoWebActivityGeneration ();
        else // chrome and ff do not
            $this->assertWebActivityGeneration ();

    }
}

?>
