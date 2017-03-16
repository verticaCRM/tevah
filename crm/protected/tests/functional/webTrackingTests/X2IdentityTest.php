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

/* @edition:pla */

Yii::import('application.tests.functional.webTrackingTests.WebTrackingTestBase');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.accounts.models.Accounts');

/**
 * 
 * @package application.tests.functional.modules.contacts
 */
class X2IdentityTest extends WebTrackingTestBase {

    public $autoLogin = false;

    public $fixtures = array(
        'fingerprints' => 'Fingerprint',
        'contacts' => array ('Contacts', '.WebTrackingTestBase'),
        'admin' => array ('Admin', '.X2IdentityTest'),
    );

    /**
     * Assert that tracking cooldown is disabled 
     */
    public function testAssertDebugMode () {
        $this->assertTrue (YII_DEBUG && WebListenerAction::DEBUG_TRACK);
    }

    protected function clearAnonContacts () {
        Yii::app()->db->createCommand ('delete from x2_anon_contact where 1=1')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_anon_contact where 1=1')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Asserts that anon contact was created 
     * @param bool $getAnonContact
     * @return null|AnonContact the anon contact that was created or null if getAnonContact is false
     */
    protected function assertAnonContactGeneration ($getAnonContact=false) {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_anon_contact
             where 1=1')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '1');
        if ($getAnonContact) {
            return AnonContact::model ()->findByAttributes (array ());
        }
    }

    /**
     * Asserts that the anon contact was tracked by checking if anon contact web activity was
     * generated.
     */
    protected function assertAnonContactWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity" and associationType="anoncontact"')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '1');
    }

    protected function assertNoAnonContactWebActivityGeneration () {
        $newCount = Yii::app()->db->createCommand (
            'select count(*) from x2_actions
             where type="webactivity" and associationType="anoncontact"')
             ->queryScalar ();
        VERBOSE_MODE && println ($newCount);
        $this->assertTrue ($newCount === '0');
    }


    /**
     * Visit the page with the web tracker and assert that an anonymous contact was generated 
     */
    public function testWebTrackerAnonContactGeneration () {
        $this->clearAnonContacts ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000);
        $this->assertAnonContactGeneration ();
    }

    /**
     * Visit the web tracker page twice, once to generate the fingerprint record, and the second
     * time to assert that a fingerprint match is found.
     */
    public function testFingerprintBasedTrackingUsingWebTracker () {
        $this->deleteAllVisibleCookies ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000);
        $this->clearWebActivity ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000);
        $this->assertAnonContactWebActivityGeneration ();
    }

    /**
     * Visit the web tracker page to initiate fingerprint-based tracking, then visit the web form
     * and assert that tracking does not work since the tracker embedded in the web form only uses
     * cookie-based tracking.
     */
    public function testFingerprintBasedTrackingUsingWebForm () {
        $this->deleteAllVisibleCookies ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->assertCookie ('regexp:.*x2_key.*');
        $this->clearWebActivity ();
        $this->openPublic('x2WebTrackingTestPages/webFormTestDifferentDomain.html');
        $this->pause (5000);
        $this->assertNoAnonContactWebActivityGeneration ();
    }

    /**
     * Initiate fingerprint-based tracking and then submit the web form. Assert that anon contact
     * gets converted into a contact.
     */
    public function testAnonymousContactConversion () {
        $this->deleteAllVisibleCookies ();
        $this->clearAnonContacts ();
        $this->openPublic ('x2WebTrackingTestPages/webTrackerTestDifferentDomain.html');
        $this->pause (5000);
        $anonContact = $this->assertAnonContactGeneration (true);
        $anonContact->leadscore = 3;
        $anonContact->email = '1@1.com';
        VERBOSE_MODE && print_r ($anonContact->getAttributes());
        $this->assertTrue ($anonContact->save ());

        $this->submitWebForm ('differentDomain');
        $contact = $this->assertContactCreated ();

        // fingerprint and lead score should be migrated from anon contact
        $this->assertTrue ($anonContact->fingerprintId === $contact->fingerprintId);
        VERBOSE_MODE && print_r ($contact->leadscore);
        $this->assertTrue ($contact->leadscore == 3);

        // email should not be overwritten
        $this->assertTrue ($contact->email === 'test@test.com');
    }

}

?>
