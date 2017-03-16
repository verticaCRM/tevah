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
Yii::import('application.modules.accounts.models.Accounts');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.components.*');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class CampaignTrackingLinkTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        'campaign' => 'Campaign',
        'lists' => 'X2List',

        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'credentials' => 'Credentials',
        'users' => 'User',
        'profile' => array('Profile','.marketing'),
        'listItem' => 'X2ListItem',
        'contacts' => 'Contacts'
    );

    /**
     * Used in conjunction with assertClickActivityGeneration (). 
     * Clears web activity actions so that we can easily test later that a new email_clicked action
     * was generated
     */
    protected function clearClickActivity () {
        Yii::app()->db->createCommand ('delete from x2_actions where type="email_clicked"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="email_clicked"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Used in conjunction with clearClickActivity (). Ensures that a email_clicked action was 
     * generated.
     */
    protected function assertClickActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="email_clicked"')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '1');
    }

    public function instantiate($config = array()) {
        $obj = new CComponent;
        $obj->attachBehavior('CampaignMailing', array_merge(array(
            'class' => 'CampaignMailingBehavior',
            'itemId' => $this->listItem('testUser_unsent')->id,
            'campaign' => $this->campaign('testUser')
        ),$config));
        return $obj;
    }

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }


    /**
     * Assert that tracking links correctly track contacts. Also ensures that tracking link-based
     * tracking does not interfere with cookie-based tracking.
     */
    public function testTrackingLinks () {
        $this->deleteAllVisibleCookies ();

        // first initiate cookie based tracking
        $this->submitWebForm ();
        $this->assertContactCreated ();
        $this->assertWebTrackerTracksWithCookie ();

        // next, generate the tracking link and attempt to track the user with a campaign click 
        $cmb = $this->instantiate ();
        $contact = $this->contacts('testUser_unsent');

        // Set URL/URI to verify proper link generation:
        $admin = Yii::app()->settings;
        $admin->externalBaseUrl = TEST_WEBROOT_URL_ALIAS_1;
        $admin->externalBaseUri = '';

        // generate unique id and associate it with the test contact
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'),$contact,$this->listItem('testUser_unsent')->emailAddress);
        $cmb->markEmailSent ($uniqueId, true);
        VERBOSE_MODE && println($uniqueId);

        // visit page with tracking link, specifying campaign tracking key
        $this->clearClickActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTest.html?x2_key='.$uniqueId);
        $this->pause (5000); // wait for database changes to enact
        $this->assertClickActivityGeneration ();

        // ensure that even after tracking the contact with a campaign click, we can still track 
        // them with their tracking cookie
        $this->assertWebTrackerTracksWithCookie ();
    }

    /**
     * First track the contact with a campaign click, then check if the tracking cookie has been 
     * properly set in the contact's browser
     */
    public function testThatCampaignClickInitiatesCookieTracking () {
        $this->deleteAllVisibleCookies (TEST_WEBROOT_URL_ALIAS_1);
        // we haven't simulated the tracking link click yet so tracking should fail
        $this->assertWebTrackerCannotTrackWithCookie ();

        // next, generate the tracking link and attempt to track the user with a campaign click 
        $cmb = $this->instantiate ();
        $contact = $this->contacts('testUser_unsent');

        // Set URL/URI to verify proper link generation:
        $admin = Yii::app()->settings;
        $admin->externalBaseUrl = TEST_WEBROOT_URL_ALIAS_1; 
        $admin->externalBaseUri = '';

        // generate unique id and associate it with the test contact
        list($subject,$message,$uniqueId) = $cmb->prepareEmail(
            $this->campaign('testUser'),$contact,$this->listItem('testUser_unsent')->emailAddress);
        $cmb->markEmailSent ($uniqueId, true);

        VERBOSE_MODE && println($uniqueId."\n");

        // visit page with tracking link, specifying campaign tracking key
        $this->clearClickActivity ();
        $this->openPublic ('/x2WebTrackingTestPages/webTrackerTest.html?x2_key='.$uniqueId);
        $this->pause (5000); // wait for database changes to enact
        $this->assertClickActivityGeneration ();

        // assert that key was set on the server after campaign click track
        $this->assertCookie ('regexp:.*x2_key.*'); 

        // ensure that campaign click initiated cookie-based tracking
        $this->assertWebTrackerTracksWithCookie ();
    }


}

?>
