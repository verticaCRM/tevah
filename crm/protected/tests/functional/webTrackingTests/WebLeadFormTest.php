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

/**
 * @package application.tests.functional.modules.contacts
 */
class WebLeadFormTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        // disables fingerprinting
        'admin' => array ('Admin', '.cookieTrackingTests'),
        'webForms' => array ('WebForm', '.WebLeadFormTest'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }

    protected function assertLeadCreated () {
        $lead = X2Leads::model()->findByAttributes (array (
            'name' => 'test test',
            'leadSource' => 'facebook',
        ));
        $this->assertTrue ($lead !== null);
        VERBOSE_MODE && println (
            'lead created');
        return $lead;
    }

    protected function assertAccountCreated () {
        $lead = Accounts::model()->findByAttributes (array (
            'name' => 'testAccount',
        ));
        $this->assertTrue ($lead !== null);
        VERBOSE_MODE && println (
            'account created');
        return $lead;
    }

    protected function clearLead () {
        Yii::app()->db->createCommand ('delete from x2_x2leads where name="test test"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_x2leads
             where name="test test"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    protected function clearAccount () {
        Yii::app()->db->createCommand ('delete from x2_accounts where name="testAccount"')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_accounts
             where name="testAccount"')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    protected function assertLeadNotCreated () {
        $lead = X2Leads::model()->findByAttributes (array (
            'name' => 'test test',
            'leadSource' => 'Facebook',
        ));
        $this->assertTrue ($lead === null);
        return $lead;
    }


    /**
     * Submit web lead form and wait for success message
     */
    public function testSubmitWebLeadForm () {
        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        $this->submitWebForm ();
        $this->pause (5000); // wait for database changes to enact and for cookie to be set
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertContactCreated ();
    }

    /**
     * Test web lead form tracking by revisiting the page with the web lead form after submission
     */
    public function testWebLeadFormTracking () {
        $this->deleteAllVisibleCookies ();
        $this->assertNotCookie ('regexp:.*x2_key.*');

        $this->submitWebForm ();
        $this->pause (5000); // wait for database changes to enact and for cookie to be set
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertContactCreated ();


        $this->clearWebActivity ();
        $this->openPublic('/x2WebTrackingTestPages/webFormTest.html');
        $this->pause (5000); // wait for database changes to enact
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->assertWebActivityGeneration ();
    }

    /**
     * Submit a web form that was created with the generate lead option checked. Assert that
     * a lead gets generated.
     */
    public function testGenerateLead () {
        $this->clearContact ();
        $this->clearLead ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestGenerateLead.html');
        if ($this->isOpera ()) $this->pause (5000);

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");
        // wait for iframe to load new page
        $this->waitForCondition (
            "selenium.browserbot.getCurrentWindow(document.getElementsByName ('web-form-iframe').length && document.getElementsByName ('web-form-iframe')[0].contentWindow.document.getElementById ('web-form-submit-message') !== null)",
            4000);
        $this->pause (5000); // wait for database changes to enact

        $this->assertContactCreated ();
        $this->assertLeadCreated ();
    }

    public function testGenerateAccount () {
        $this->clearContact ();
        $this->clearAccount ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestGenerateAccount.html');
        if ($this->isOpera ()) $this->pause (5000);

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->type("name=Contacts[company]", 'testAccount');
        $this->click("css=#submit");
        // wait for iframe to load new page
        $this->waitForCondition (
            "selenium.browserbot.getCurrentWindow(document.getElementsByName ('web-form-iframe').length && document.getElementsByName ('web-form-iframe')[0].contentWindow.document.getElementById ('web-form-submit-message') !== null)",
            4000);
        $this->pause (5000); // wait for database changes to enact

        $this->assertContactCreated ();
        $this->assertAccountCreated ();
    }

    /**
     * Submit a web form that was created with the generate lead option unchecked. Assert that
     * a lead doesn't get generated.
     */
    public function testDontGenerateLead () {
        $this->clearContact ();
        $this->clearLead ();

        $this->openPublic('x2WebTrackingTestPages/webFormTestDontGenerateLead.html');
        if ($this->isOpera ()) $this->pause (5000);

        $this->type("name=Contacts[firstName]", 'test');
        $this->type("name=Contacts[lastName]", 'test');
        $this->type("name=Contacts[email]", 'test@test.com');
        $this->click("css=#submit");
        // wait for iframe to load new page
        $this->waitForCondition (
            "selenium.browserbot.getCurrentWindow(document.getElementsByName ('web-form-iframe').length && document.getElementsByName ('web-form-iframe')[0].contentWindow.document.getElementById ('web-form-submit-message') !== null)",
            4000);
        $this->pause (5000); // wait for database changes to enact

        $this->assertContactCreated ();
        $this->assertLeadNotCreated ();
    }


}

?>
