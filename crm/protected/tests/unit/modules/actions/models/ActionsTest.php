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
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.groups.models.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.X2Settings.*');
Yii::import('application.components.sortableWidget.profileWidgets.*');
Yii::import('application.components.sortableWidget.recordViewWidgets.*');

/**
 * Test for the Actions class
 * @package application.tests.unit.modules.actions.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionsTest extends CDbTestCase {

    public $fixtures = array(
        'actions'=>array ('Actions', '.ActionsTest'),
        'users'=> 'User',
        'profiles'=> 'Profile',
        'groupToUser'=>array ('GroupToUser', '.ActionsTest'),
        'groups'=>array ('Groups', '.ActionsTest'),
    );

    /**
     * Test special validation that avoids empty association when the type is
     * something meant to be associated, i.e. a logged call, note, etc.
     */
    public function testValidate() {
        $action = new Actions();
        $action->type = 'call';
        $action->actionDescription = 'Contacted. Will call back later';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
        // Do the same thing but with "None" association type. Validation should fail.
        $action = new Actions();
        $action->type = 'call';
        $action->associationType = 'None';
        $this->assertFalse($action->validate());
        $this->assertTrue($action->hasErrors('associationId'));
        $this->assertTrue($action->hasErrors('associationType'));
    }

    public function testIsAssignedTo () {
        $action = $this->actions('action1');

        // test assignedTo field consisting of single username
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertFalse ($action->isAssignedTo ('testuser2'));

        $action = $this->actions('action2');

        // test assignedTo field consisting of a group id
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertFalse ($action->isAssignedTo ('testuser2'));

        $action = $this->actions('action3');

        // test assignedTo field consisting of username and group id
        $this->assertTrue ($action->isAssignedTo ('testuser'));
        $this->assertTrue ($action->isAssignedTo ('testuser2'));
        $this->assertFalse ($action->isAssignedTo ('testuser3'));

        $action = $this->actions('action4');

        // test assignedTo field consisting of 'Anyone'
        $this->assertTrue ($action->isAssignedTo ('testuser4'));
        $this->assertFalse ($action->isAssignedTo ('testuser4', true));

        // test assignedTo field consisting of '' (i.e. no one)
        $this->assertTrue ($action->isAssignedTo ('testuser4'));
        $this->assertFalse ($action->isAssignedTo ('testuser4', true));
    }

    public function testGetProfilesOfAssignees () {
        // action assignedTo field consists of username and group id
        $action = $this->actions('action3');
        $profiles = $action->getProfilesOfAssignees ();

        // this should return profile for username and all profiles in group, without duplicates
        $profileUsernames = array_map (function ($a) { return $a->username; }, $profiles);

        VERBOSE_MODE && print ('sizeof ($profiles) = ');
        VERBOSE_MODE && print (sizeof ($profiles)."\n");

        VERBOSE_MODE && print ('$profileUsernames  = ');
        VERBOSE_MODE && print_r ($profileUsernames);

        $this->assertTrue (sizeof ($profiles) === 2);
        $this->assertTrue (in_array ('testuser', $profileUsernames));
        $this->assertTrue (in_array ('testuser2', $profileUsernames));

        /* 
        action assignedTo field consists of username and group id. Here the username is included
        twice: once explicitly in the assignedTo field and a second time, implicitly, by its 
        membership to the group.
        */
        $action = $this->actions('action6');
        $profiles = $action->getProfilesOfAssignees ();

        // this should return profile for username and all profiles in group, without duplicates
        $profileUsernames = array_map (function ($a) { return $a->username; }, $profiles);

        VERBOSE_MODE && print ('sizeof ($profiles) = ');
        VERBOSE_MODE && print (sizeof ($profiles)."\n");

        VERBOSE_MODE && print ('$profileUsernames  = ');
        VERBOSE_MODE && print_r ($profileUsernames);

        $this->assertTrue (sizeof ($profiles) === 2);
        $this->assertTrue (in_array ('testuser', $profileUsernames));
        $this->assertTrue (in_array ('admin', $profileUsernames));
        
    }

    public function testGetAssignees () {
        // action assignedTo field consists of username and group id
        $action = $this->actions('action3');
        $assignees = $action->getAssignees (true);

        VERBOSE_MODE && print ('sizeof ($assignees) = ');
        VERBOSE_MODE && print (sizeof ($assignees)."\n");

        $this->assertTrue (sizeof ($assignees) === 2);
        $this->assertTrue (in_array ('testuser', $assignees));
        $this->assertTrue (in_array ('testuser2', $assignees));

        /* 
        action assignedTo field consists of username and group id. Here the username is included
        twice: once explicitly in the assignedTo field and a second time, implicitly, by its 
        membership to the group.
        */
        $action = $this->actions('action6');

        /* 
        here assignees usernames are retrieved, if a group id is in the assignedTo string,  
        usernames of all users in that group are also retrieved. duplicate usernames should
        get removed.
        */
        $assignees = $action->getAssignees (true);

        VERBOSE_MODE && print ('sizeof ($assignees) = ');
        VERBOSE_MODE && print (sizeof ($assignees)."\n");

        $this->assertTrue (sizeof ($assignees) === 2);
        $this->assertTrue (in_array ('testuser', $assignees));
        $this->assertTrue (in_array ('admin', $assignees));
        
    }

    public function testCreateNotification () {
        // assigned to testuser and group 1
        $action = $this->actions('action6');

        $notifs = $action->createNotifications ('assigned');
        VERBOSE_MODE && print (sizeof ($notifs));
        $this->assertTrue (sizeof ($notifs) === 2);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        $this->assertTrue (in_array ('admin', $notifAssignees));
        $this->assertTrue (in_array ('testuser', $notifAssignees));

        $notifs = $action->createNotifications ('me');
        $this->assertTrue (sizeof ($notifs) === 1);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        VERBOSE_MODE && print_r ($notifAssignees);
        $this->assertTrue (in_array ('Guest', $notifAssignees));

        $notifs = $action->createNotifications ('both');
        $this->assertTrue (sizeof ($notifs) === 3);
        $notifAssignees = array_map (function ($a) { return $a->user; }, $notifs);
        $this->assertTrue (in_array ('admin', $notifAssignees));
        $this->assertTrue (in_array ('testuser', $notifAssignees));
        $this->assertTrue (in_array ('Guest', $notifAssignees));
    }

    public function testChangeCompleteState () {
        TestingAuxLib::suLogin ('admin');
        VERBOSE_MODE && print (Yii::app()->user->name ."\n");
        VERBOSE_MODE && print ((int) Yii::app()->params->isAdmin);
        VERBOSE_MODE && print ("\n");
        $action = $this->actions('action6');
        $completedNum = Actions::changeCompleteState ('complete', array ($action->id));
        $this->assertEquals (1, $completedNum);
        $action = Actions::model()->findByPk ($action->id);
        VERBOSE_MODE && print ($action->complete."\n");
        $this->assertTrue ($action->complete === 'Yes');
        Actions::changeCompleteState ('uncomplete', array ($action->id));
        $action = Actions::model()->findByPk ($action->id);
        $this->assertTrue ($action->complete === 'No');

    }
}

?>
