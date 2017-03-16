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

/**
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordEmailTest extends X2FlowTestBase {

    const LIVE_DELIVERY = 0;

    public $fixtures = array (
        'contacts' => 'Contacts',
    );

    public static function referenceFixtures () {
        return array (
            'x2flow' => array ('X2Flow', '.X2FlowRecordEmailTest'),
            'credentials' => 'Credentials',
            'profile' => 'Profile',
            'user' => 'User',
        );
    }
     
    /**
     * Replace credId token with cred id from x2_credentials-local.
     */
    public static function setUpBeforeClass () {
        $file = 'fixtures/x2_flows.X2FlowRecordEmailTestTemplate.php';
        $content = file_get_contents ($file);
        if (self::LIVE_DELIVERY) {
            $localCredentialFixture = require ('fixtures/x2_credentials-local.php');
            $credId = $localCredentialFixture['liveDeliveryTest']['id'];
        } else {
            $credId = -1;
        }

        $content = preg_replace (
            '/EMAIL_CREDENTIAL_ID/', $credId, $content);
        file_put_contents ('fixtures/x2_flows.X2FlowRecordEmailTest.php', $content);

        if (!YII_UNIT_TESTING || !EmailDeliveryBehavior::DEBUG_EMAIL) {
            println (
                'X2FlowRecordEmailTest will not run properly unless '.
                'YII_UNIT_TESTING is true and EmailDeliveryBehavior::DEBUG_EMAIL is true');
            self::$skipAllTests = true;
        }

        parent::setUpBeforeClass ();
    }


    public function testDoNotEmailLinkInsertion () {
        Yii::app()->settings->x2FlowRespectsDoNotEmail = 1;
        Yii::app()->settings->externalBaseUrl = 'http://localhost';
        $flow = $this->getFlow ($this,'flow1');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $contact->doNotEmail = 0;
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        // assert flow executed without errors
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($trace));
        $emailMessage = array_pop (array_pop ($this->flattenTrace ($trace)));
        $this->assertTrue (
            (bool) preg_match ('/'.Admin::getDoNotEmailLinkDefaultText ().'/', $emailMessage));
        VERBOSE_MODE && println ($emailMessage);

        VERBOSE_MODE && print_r ($trace);

        // ensure that "Do Not Email" link text can be changed
        Yii::app()->settings->doNotEmailLinkText = 'test';
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($trace));

        // ensure that the "Do Not Email" link, when followed, causes the contact's do not
        // email field to be set to true
        $emailMessage = array_pop (array_pop ($this->flattenTrace ($trace)));
        preg_match ("/href=\"([^\"]+)\"/", $emailMessage, $matches);
        $link = $matches[1];
        curl_exec (curl_init ($link));
        $contact->refresh ();
        $this->assertEquals (1, $contact->doNotEmail);
        $this->assertTrue (
            (bool) preg_match ('/test/', $emailMessage));

        // now trigger the flow again and ensure that the email doesn't get sent since the
        // contact followed the "Do Not Email" link
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];
        VERBOSE_MODE && print_r ($trace);
        $this->assertFalse ($this->checkTrace ($trace));
    }

    /**
     * Sends an email from x2flow to the address TEST_EMAIL_TO, inserting the "Do Not Email"
     * link into the body of the email
     */
    public function testCheckEmailSent () {
        if (self::LIVE_DELIVERY) {
            /*Yii::app()->settings->doNotEmailLinkText = null;
            Yii::app()->settings->externalBaseUrl = 'http://localhost';
            $flow = $this->getFlow ($this,'flow1');
            $contact = $this->contacts ('testAnyone');
            $contact->email = TEST_EMAIL_TO;
            $this->assertSaves ($contact);
            $params = array (
                'model' => $contact,
                'modelClass' => 'Contacts',
            );
            $contact->doNotEmail = 0;
            $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
            $trace = $retVal['trace'];
            VERBOSE_MODE && print_r ($trace);
            $this->assertTrue ($this->checkTrace ($trace));
            println ('testCheckEmailSent: Email sent, check your inbox');


            // ensure that link text can be changed 
            Yii::app()->settings->doNotEmailLinkText = 'test';
            $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
            $trace = $retVal['trace'];
            VERBOSE_MODE && print_r ($trace);
            $this->assertTrue ($this->checkTrace ($trace));
            println ('testCheckEmailSent: Email sent, check your inbox');*/
        }
    }

    /**
     * Ensure that email doesn't get set if x2FlowRespectsDoNotEmail admin setting is set to true
     * and email recipients list contains a contact that has their doNotEmail field set to true.
     */
    public function testDoNotEmailCheck () {
        Yii::app()->settings->externalBaseUrl = 'http://localhost';
        Yii::app()->settings->x2FlowRespectsDoNotEmail = 1;
        $flow = $this->getFlow ($this,'flow1');
        $contact = $this->contacts ('testAnyone');
        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $contact->doNotEmail = 0;
        $this->assertSaves ($contact);
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        VERBOSE_MODE && print_r ($trace);

        // email should be sent since contact does not have  doNotEmail field set to 1
        $this->assertTrue ($this->checkTrace ($trace));

        $contact->doNotEmail = 1;
        $this->assertSaves ($contact);
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        $trace = $retVal['trace'];

        VERBOSE_MODE && print_r ($trace);

        // email should not be sent since contact has doNotEmail field set to 1
        $this->assertFalse ($this->checkTrace ($trace));

        $contact->doNotEmail = 0;
        $this->assertSaves ($contact);
        $contact2 = $this->contacts ('testUser');
        $contact2->doNotEmail = 1;
        $this->assertSaves ($contact2);

        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);
        $trace = $retVal['trace'];

        // email should not be sent because a contact in the CC list has doNotEmail set to
        // 1
        $this->assertFalse ($this->checkTrace ($trace));

        $contact2->doNotEmail = 0;
        $this->assertSaves ($contact2);

        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);
        $trace = $retVal['trace'];

        // email should be sent because all contacts, including those in the CC list have
        // doNotEmail set to 0
        $this->assertTrue ($this->checkTrace ($trace));
    }

}

?>
