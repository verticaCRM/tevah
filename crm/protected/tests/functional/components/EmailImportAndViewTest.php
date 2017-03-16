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

Yii::import('application.tests.WebTestCase');
Yii::import('application.components.*');
Yii::import('application.models.*');
Yii::import('application.modules.users.models.User');
Yii::import('application.modules.contacts.models.Contacts');
Yii::import('application.modules.actions.models.Actions');
Yii::import('application.modules.users.models.User');

/**
 * Test for the end result of an email import (viewing the actual contact page)
 * 
 * @package application.tests.functional.components 
 */
class EmailImportAndViewTest extends X2WebTestCase {

	public $fixtures = array(
		'contact' => 'Contacts',
		'actions' => 'Actions'
	);

    protected function setUp () {
        $this->markTestSkipped ();
    }

	/**
	 * This will create a new contact named "Testtwo Contacttwo" in and attach the email-type action
	 */
	public function testCCNewContactImport() {
		$command = new EmailImportBehavior();
		$file = fopen(Yii::app()->basePath . '/tests/data/email/CC_Test_new.eml', 'r');
		$command->eml2records($file);
		fclose($file);
		$contact = X2Model::model('Contacts')->findByAttributes(array('firstName' => 'Testtwo', 'lastName' => 'Contacttwo', 'email' => 'customer2@prospect.com'));
		$this->assertEquals($contact->name, 'Testtwo Contacttwo');
		$this->assertEquals($contact->email, 'customer2@prospect.com');
		$action = X2Model::model('Actions')->findByAttributes(array('type' => 'email', 'associationType' => 'contacts', 'associationId' => $contact->id));
		$this->assertTrue((bool) $action);
		$this->assertRegExp('/%123%/m', $action->actionDescription);
		$this->openX2('contacts/view/' . $contact->id);
		$this->assertTextPresent('%123%');
	}

	/**
	 * This should put an email-type action on the preexisting contact named "Testfirstname Testlastname"
	 */
	public function testCCPreexistContactImport() {
		$command = new EmailImportBehavior();
		$file = fopen(Yii::app()->basePath . '/tests/data/email/CC_Test_preexist.eml', 'r');
		$command->eml2records($file);
		fclose($file);
		$contact = X2Model::model('Contacts')->findByAttributes(array('firstName' => 'Testfirstname', 'lastName' => 'Testlastname', 'email' => 'contact@test.com'));
		$this->assertTrue((bool) $contact);
		$action = X2Model::model('Actions')->findByAttributes(array('type' => 'email', 'associationType' => 'contacts', 'associationId' => $contact->id));
		$this->assertTrue((bool) $action);
		$this->assertRegExp('/%123%/m', $action->actionDescription);
		// Test that it's there, on the page
		$this->openX2('contacts/' . $contact->id);
		$this->assertTextPresent('%123%');
	}

	// More test ideas:
	// 
	// Test missing last name
	// Test importing action to a contact not assigned to the user
}

?>
