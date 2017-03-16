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

Yii::import('application.models.*');
Yii::import('application.modules.groups.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 *
 * @package application.tests.unit.components
 */
class LeadRoutingBehaviorTest extends X2DbTestCase {

    public $fixtures = array (
        'leadRouting' => array ('LeadRouting', '_1'),
        'users' => array ('User', '_1'),
        'sessions' => array ('Session', '_1'),
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_1'),
        'contacts' => array ('Contacts', '_1'),
        'profiles' => array ('Profile', '.LeadRoutingBehaviorTest'),
    );

    public function setUp () {
        // default onlineOnly value
        Yii::app()->settings->onlineOnly = 0;
        $this->assertSaves (Yii::app()->settings);
        parent::setUp ();
    }

    public function testFreeForAll () {
        Yii::app()->settings->leadDistribution = '';
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobin () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser1', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser1', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobinOnlineOnly () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        Yii::app()->settings->onlineOnly = 1;
        $this->assertTrue (Yii::app()->settings->save ());
        $testUser1 = $this->profiles ('testProfile1');
        $this->assertSaves ($testUser1);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
    }

    public function testRoundRobinAvailableOnly () {
        Yii::app()->settings->rrId = 0;
        Yii::app()->settings->leadDistribution = 'trueRoundRobin';
        $this->assertTrue (Yii::app()->settings->save ());
        $testUser1 = $this->profiles ('testProfile1');
        $testUser1->leadRoutingAvailability = 0; 
        $this->assertSaves ($testUser1);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser3', $leadRouting->getNextAssignee ()); 
        $this->assertEquals ('testUser4', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUser () {
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        $this->assertSaves (Yii::app()->settings);
        Yii::app()->settings->leadDistribution = 'singleUser';
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUserOnlineOnly () {
        TestingAuxLib::setUpSessions($this->sessions);
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        Yii::app()->settings->onlineOnly = 1;
        Yii::app()->settings->leadDistribution = 'singleUser';
        $this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('testUser2', $leadRouting->getNextAssignee ()); 

        $testUser1 = $this->users ('user1');
        Yii::app()->settings->rrId = $testUser1->id;
        $this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

    public function testSingleUserAvailableOnly () {
        $testProfile2 = $this->profiles ('testProfile2');
        $testProfile2->leadRoutingAvailability = 0;
        $this->assertSaves ($testProfile2);
        $testUser2 = $this->users ('user2');
        Yii::app()->settings->rrId = $testUser2->id;
        Yii::app()->settings->leadDistribution = 'singleUser';
        $this->assertSaves (Yii::app()->settings);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $this->assertEquals ('Anyone', $leadRouting->getNextAssignee ()); 
    }

	public function testCustomRoundRobinOnlineOnly () {
        Yii::app()->settings->onlineOnly = 1;
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(VERBOSE_MODE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');

        Yii::app()->settings->onlineOnly = 0;
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(VERBOSE_MODE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');
    }

	public function testCustomRoundRobin () {
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        TestingAuxLib::setUpSessions($this->sessions);
        $_POST['Contacts'] = array (
            'firstName' => 'contact1',
            'lastName' => 'contact1'
        );
        $leadRouting = new LeadRoutingBehavior ();
        $username = $leadRouting->customRoundRobin (); 
        if(VERBOSE_MODE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser1');

        $_POST['Contacts'] = array (
            'firstName' => 'contact2',
            'lastName' => 'contact2'
        );
        $username = $leadRouting->customRoundRobin (); 
        if(VERBOSE_MODE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'testUser2');

        $_POST['Contacts'] = array (
            'firstName' => 'contact3',
            'lastName' => 'contact3'
        );
        $username = $leadRouting->customRoundRobin (); 
        if(VERBOSE_MODE) print ("Getting assignee: username = $username\n");
        $this->assertTrue ($username === 'Anyone');

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinAvailableOnly () {
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();
        $testUser1 = $this->profiles ('testProfile1');
        $testUser1->leadRoutingAvailability = 0; 
        $this->assertSaves ($testUser1);

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser3', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinBetweenGroups () {
        $rule3 = $this->leadRouting ('leadRouting3'); 
        $rule3->groupType = 1;
        $rule3->users = '1, 2';
        $this->assertSaves ($rule3);
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('1', $leadRouting->customRoundRobin ()); 
	}

	public function testCustomRoundRobinWithinGroups () {
        $rule3 = $this->leadRouting ('leadRouting3'); 
        $rule3->groupType = 0;
        $rule3->users = '1';
        $this->assertSaves ($rule3);
        Yii::app()->settings->leadDistribution = 'customRoundRobin';
        $this->assertSaves (Yii::app()->settings);
        $leadRouting = new LeadRoutingBehavior ();

        $_POST['Contacts'] = array (
            'firstName' => 'contact4',
            'lastName' => 'contact4'
        );

        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser1', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser2', $leadRouting->customRoundRobin ()); 
        $this->assertEquals ('testUser4', $leadRouting->customRoundRobin ()); 
	}

}

?>
