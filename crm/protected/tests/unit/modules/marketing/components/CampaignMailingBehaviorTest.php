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

Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.components.*');

/**
 * 
 * @package application.tests.unit.modules.marketing.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CampaignMailingBehaviorTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'campaign' => 'Campaign',
            'lists' => 'X2List',
            'credentials' => 'Credentials',
            'users' => 'User',
            'profile' => array('Profile','.marketing')
        );
    }

    public $fixtures = array(
        'listItem' => 'X2ListItem',
        'contacts' => 'Contacts'
    );


    public function instantiate($config = array()) {
        $obj = new CComponent;
        $obj->attachBehavior('CampaignMailing', array_merge(array(
            'class' => 'CampaignMailingBehavior',
            'itemId' => $this->listItem('testUser_unsent')->id,
            'campaign' => $this->campaign('testUser')
        ),$config));
        return $obj;
    }

    public function testGetCredId() {
        $cmb = $this->instantiate();
        $this->assertEquals($this->campaign('testUser')->sendAs, $cmb->credId);
        $this->assertTrue($cmb->credentials instanceof Credentials);
    }

    public function testGetListItem() {
        $cmb = $this->instantiate();
        $this->assertTrue($cmb->listItem instanceof X2ListItem);
        $this->assertEquals($this->listItem('testUser_unsent')->id,$cmb->listItem->id);
    }

    public function testMarkEmailSent() {
        $cmb = $this->instantiate();
        $cmb->listItem->sending = 1;
        $cmb->listItem->update(array('sending'));
        $cmb->markEmailSent('abcde');
        $cmb->listItem->refresh();
        $this->assertFalse((bool) $cmb->listItem->sending);
        $this->assertTrue(abs($cmb->listItem->sent - time())<=1);
        $this->assertEquals('abcde',$cmb->listItem->uniqueId);
        $cmb->markEmailSent(null);
        // Expect: null unique ID corresponds to 
        $cmb->listItem->refresh();
        $this->assertTrue(abs($cmb->listItem->sent - time())<=1);
        $this->assertEquals(null,$cmb->listItem->uniqueId);
    }

    public function testPrepareEmail() {
        if(!Yii::app()->contEd('pro')) {
                $this->markTestSkipped();
        }

        $cmb = $this->instantiate();
        $contact = $this->contacts('testUser_unsent');
        $recipientAddress = $contact->email;
        $admin = Yii::app()->settings;
        // Set URL/URI to verify proper link generation:
        $admin->externalBaseUrl = 'http://examplecrm.com';
        $admin->externalBaseUri = '/X2Engine';
        list($subject,$message,$uniqueId) = $cmb->prepareEmail($this->campaign('testUser'),$contact,$this->listItem('testUser_unsent')->emailAddress);
        $email = $cmb->recipient->email;
        
        $this->assertEquals($recipientAddress,$email);
        $this->assertEquals(str_replace('{firstName}',$contact->firstName,$this->campaign('testUser')->subject),$subject);
        // Find the contact's name and tracking key:
        $replaceVars = array(
            '{firstName}' => $contact->firstName,
            '{signature}' => $this->users('testUser')->profile->signature,
            '{trackingKey}' => $uniqueId
        );
        $this->assertRegExp('/'.preg_quote(strtr($this->campaign('testUser')->content,$replaceVars),'/').'/',$message,'Variable replacement didn\'t take place');
        // Find the tracking image:
        $this->assertRegExp('/'.preg_quote('<img src="'.$admin->externalBaseUrl.$admin->externalBaseUri.'/index.php/marketing/marketing/click?uid='.$uniqueId,'/').'/',$message,'Tracking image not inserted');
        // Find the unsubscribe link:
        $this->assertRegExp('/'.preg_quote('To stop receiving these messages, click here: <a href="http://examplecrm.com/X2Engine/index.php/marketing/marketing/click?uid='.$uniqueId.'&type=unsub&email='.rawurlencode($recipientAddress).'">unsubscribe</a>','/').'/',$message,'Unsubscribe link not inserted');
        // Find the tracking key:
        $this->assertRegExp('/'.preg_quote('visit http://example.com/?x2_key=','/').$uniqueId.'/',$message,'Tracking key not inserted!');
    }


    public function testRecordEmailSent() {
        $contact = $this->contacts('testUser');
        $campaign = $this->campaign('testUser');
        $now = time();
        CampaignMailingBehavior::recordEmailSent($campaign,$contact);
        
        $action = Actions::model()->findByAttributes(array(
            'associationType' => 'contacts',
            'associationId' => $contact->id,
            'type' => 'email',
        ));
        $this->assertTrue((bool) $action);
        $this->assertTrue(abs($action->completeDate - $now)<=1);
    }

    public function testDeliverableItems() {
        $listItems = CampaignMailingBehavior::deliverableItems($this->lists('launchedEmailCampaign')->id);
        $this->assertEquals(array(
            array(
                'id' => '252',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
            array(
                'id' => '253',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
            array(
                'id' => '254',
                'sent' => '0',
                'uniqueId' => NULL,
            ),
                ), $listItems
        );
    }

    /**
     * Test the last-minute-check function
     */
    public function testMailIsStillDeliverable() {
        $cmb = $this->instantiate();
        // Bulk limit reached:
        $admAttr = Yii::app()->settings->attributes;
        Yii::app()->settings->emailBatchSize = 1;
        Yii::app()->settings->emailCount = 1;
        Yii::app()->settings->emailStartTime = time();
        Yii::app()->settings->emailInterval = 1000;
        $can = $cmb->mailIsStillDeliverable();
//        print_r($cmb->status); // This should be human-readable and make sense (it's the waiting message)
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_BULKLIMIT,$cmb->stateChangeType);
        $cmb->stateChange = false;
        Yii::app()->settings->attributes = $admAttr;
        // Temporary arrangement, in case the app's current settings are
        // actually going to interfere with the test:
        Yii::app()->settings->emailBatchSize = 10000000;

        // Mail was sent already:
        $cmb->listItem->sent = time();
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_SENT,$cmb->stateChangeType);
        $cmb->listItem->sent = 0;
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending','sent'));
        $cmb->stateChange = false;

        // Contact switched to "do not email"
        $cmb->recipient->doNotEmail = 1;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_DONOTEMAIL,$cmb->stateChangeType);
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending'));
        $cmb->stateChange = false;
        $cmb->recipient->doNotEmail = 0;

        // List item abruptly switched to unsubscribed
        $cmb->listItem->unsubscribed = 1;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_DONOTEMAIL,$cmb->stateChangeType);
        $cmb->listItem->sending = 0;
        $cmb->listItem->update(array('sending'));
        $cmb->stateChange = false;

        // Blank email:
        $oldEmail = $cmb->recipient->email;
        $cmb->recipient->email = null;
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_NULLADDRESS,$cmb->stateChangeType);
        $cmb->recipient->email = $oldEmail;
        $cmb->stateChange = false;

        // "Sending" flag enabled!!!
        $cmb->listItem->sending = 1;
        $cmb->listItem->update(array('sending'));
        $can = $cmb->mailIsStillDeliverable();
        $this->assertFalse($can);
        $this->assertEquals(CampaignMailingBehavior::STATE_RACECOND,$cmb->stateChangeType);

        // All clear
        $cmb->stateChange = false;
        $cmb->listItem->sending = 0;
        $cmb->listItem->unsubscribed = 0;
        $cmb->listItem->update(array('sending','unsubscribed'));
        $can = $cmb->mailIsStillDeliverable();
        $this->assertTrue($can);
    }
}

?>
