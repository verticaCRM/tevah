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

Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.accounts.models.*');

/**
 * @package application.tests.unit.components
 */
class DuplicateBehaviorTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => array('Contacts', '.DuplicateTest'),
        'accounts' => array('Accounts', '.DuplicateTest'),
    );

    public function testCheckDuplicates() {
        // First contact has duplicates
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());
        // Contact 6 is unique
        $uniqueContact = $this->contacts('contact6');
        $this->assertFalse($uniqueContact->checkForDuplicates());

        // Same deal for accounts
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());
        $uniqueAccount = $this->accounts('account6');
        $this->assertFalse($uniqueAccount->checkForDuplicates());
    }

    public function testDuplicateField() {
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());
        // After setting dupeCheck to 1, we shouldn't find any duplicates
        $contact->duplicateChecked();
        $this->assertFalse($contact->checkForDuplicates());
        // Resetting dupeCheck means we find duplicates again
        $contact->resetDuplicateField();
        $this->assertTrue($contact->checkForDuplicates());

        // Same deal for accounts
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());

        $account->duplicateChecked();
        $this->assertFalse($account->checkForDuplicates());

        $account->resetDuplicateField();
        $this->assertTrue($account->checkForDuplicates());
    }

    public function testAfterSave() {
        // After save if a duplicate defining field (name, email) is changed,
        // dupeCheck should be reset
        $contact = $this->contacts('contact1');
        $this->assertTrue($contact->checkForDuplicates());

        $contact->duplicateChecked();
        $this->assertFalse($contact->checkForDuplicates());

        $contact->email = 'alpha@gamma.com';
        $contact->save();
        $this->assertTrue($contact->checkForDuplicates());

        // Same for accounts, but fields are name, tickerSymbol, website.
        $account = $this->accounts('account1');
        $this->assertTrue($account->checkForDuplicates());

        $account->duplicateChecked();
        $this->assertFalse($account->checkForDuplicates());

        $account->tickerSymbol = 'TEST';
        $account->save();
        $this->assertTrue($account->checkForDuplicates());
    }

    public function testGetDuplicates() {
        // We have 8 total duplicates
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());
        // The getDuplicates method only returns 5
        $duplicates = $contact->getDuplicates();
        $this->assertEquals(5, count($duplicates));
        // Unless we pass the optional getAll parameter
        $allDuplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($allDuplicates));
    }

    public function testMarkDuplicate() {
        // Confirm that markDuplicate field sets all relevant fields correctly
        Yii::app()->params->adminProf = Profile::model()->findByPk(1);
        $contact = $this->contacts('contact1');
        $this->assertEquals(0, $contact->dupeCheck);
        $this->assertEquals(1, $contact->visibility);
        $this->assertEquals('Anyone', $contact->assignedTo);
        $contact->markAsDuplicate();
        $this->assertEquals(1, $contact->dupeCheck);
        $this->assertEquals(0, $contact->visibility);
        $this->assertEquals('admin', $contact->assignedTo);

        $contact->markAsDuplicate('delete');
        $contact = Contacts::model()->findByPk(1);
        $this->assertEquals(null, $contact);

        // Same for accounts
        $account = $this->accounts('account1');
        $this->assertEquals(0, $account->dupeCheck);
        $this->assertEquals('Anyone', $account->assignedTo);
        $account->markAsDuplicate();
        $this->assertEquals(1, $account->dupeCheck);
        $this->assertEquals('admin', $account->assignedTo);

        $account->markAsDuplicate('delete');
        $account = Accounts::model()->findByPk(1);
        $this->assertEquals(null, $account);
    }

    public function testHideDuplicates() {
        // Hiding duplicates shouldn't delete any contacts
        Yii::app()->params->adminProf = Profile::model()->findByPk(1);
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());
        $duplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($duplicates));
        $contact->hideDuplicates();
        $this->assertEquals(8, $contact->countDuplicates());
        $newDuplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($newDuplicates));
        // Spot check the fields of one of the duplicates
        $dupeContact = $this->contacts('contact2');
        $this->assertEquals(1, $dupeContact->dupeCheck);
        $this->assertEquals(0, $dupeContact->visibility);
        $this->assertEquals('admin', $dupeContact->assignedTo);
    }

    public function testDeleteDuplicates() {
        // Deleting duplicates should remove them
        $contact = $this->contacts('contact1');
        $this->assertEquals(8, $contact->countDuplicates());
        $duplicates = $contact->getDuplicates(true);
        $this->assertEquals(8, count($duplicates));
        $contact->deleteDuplicates();
        $this->assertEquals(0, $contact->countDuplicates());
        $newDuplicates = $contact->getDuplicates(true);
        $this->assertEquals(0, count($newDuplicates));
        // Spot check a duplicate to ensure deletion was successful
        $dupeContact = $this->contacts('contact2');
        $this->assertEquals(null, $dupeContact);
    }

}
?>
