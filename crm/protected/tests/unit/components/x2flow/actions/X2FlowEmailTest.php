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
class X2FlowEmailTest extends X2FlowTestBase {

    public $fixtures = array (
        'contacts' => 'Contacts',
    );

    public static function referenceFixtures () {
        return array (
            'x2flow' => array ('X2Flow', '.X2FlowEmailTest'),
        );
    }

    public function testEmailDeliveryBehaviorConstant () {
        $this->assertTrue (YII_UNIT_TESTING);
        if (!YII_UNIT_TESTING || !EmailDeliveryBehavior::DEBUG_EMAIL) {
            println (
                'X2FlowEmailTest will not run properly unless '.
                'YII_UNIT_TESTING and EmailDeliveryBehavior::DEBUG_EMAIL are set to true');
            self::$skipAllTests = true;
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
