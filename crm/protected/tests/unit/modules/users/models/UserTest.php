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
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.accounts.models.*');
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 *
 * @package application.tests.unit.components
 */
class UserTest extends X2DbTestCase {


    public $fixtures = array (
        'users' => 'User',
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_2'),
        'actions' => array ('Actions', '.UserTest'),
        'contacts' => array ('Contacts', '.UserTest'),
        'events' => array ('Events', '.UserTest'),
        'social' => array ('Social', '.UserTest'),
        'profile' => array ('Profile', '.UserTest'),
        'calendarPermissions' => 'X2Calendar',
    );

    public function testAfterDelete () {
        $user = User::model ()->findByPk ('2');
        if(VERBOSE_MODE){
            /**/print ('id of user to delete: ');
            /**/print ($user->id);
        }

        // test calendar permissions deletion
        $this->assertNotEquals (0,
            sizeof (X2CalendarPermissions::model()->findAllByAttributes (
                array ('user_id' => $user->id))));
        $this->assertNotEquals (0,
            sizeof (
                X2CalendarPermissions::model()->findAllByAttributes (
                    array ('other_user_id' => $user->id))));
        
        // assert that group to user records exist for this user
        $this->assertTrue (
            sizeof (
                GroupToUser::model ()->findAllByAttributes (array ('userId' => $user->id))) > 0);
        $this->assertTrue ($user->delete ());

        VERBOSE_MODE && print ('looking for groupToUser records with userId = '.$user->id);
        GroupToUser::model ()->refresh ();

        // assert that group to user records were deleted
        $this->assertTrue (
            sizeof (
                GroupToUser::model ()->findAllByAttributes (array ('userId' => $user->id))) === 0);


        // test profile deletion
        $this->assertTrue (
            sizeof (Profile::model()->findAllByAttributes (
                array ('username' => $user->username))) === 0);

        // test social deletion
        $this->assertTrue (
            sizeof (Social::model()->findAllByAttributes (
                array ('user' => $user->username))) === 0);
        $this->assertTrue (
            sizeof (
                Social::model()->findAllByAttributes (array ('associationId' => $user->id))) === 0);

        // test event deletion
        $this->assertTrue (
            sizeof (Events::model()->findAll (
                "user=:username OR (type='feed' AND associationId=".$user->id.")", 
                array (':username' => $user->username))) === 0);

        // test calendar permissions deletion
        $this->assertEquals (0,
            sizeof (X2CalendarPermissions::model()->findAllByAttributes (
                array ('user_id' => $user->id))));
        $this->assertEquals (0,
            sizeof (
                X2CalendarPermissions::model()->findAllByAttributes (
                    array ('other_user_id' => $user->id))));
    }

    public function testBeforeDelete () {
        $user = $this->users ('testUser');
        $user->delete ();

        /*
        actions reassignment
        */

        // reassigned but left valid complete/updatedBy fields
        $action1 = $this->actions ('action1');
        $this->assertTrue ($action1->assignedTo === 'Anyone');
        $this->assertTrue ($action1->completedBy === 'testUser2');
        $this->assertTrue ($action1->updatedBy === 'testUser2');

        // reassigned and updated completedBy field
        $action2 = $this->actions ('action2');
        $this->assertTrue ($action2->assignedTo === 'Anyone');
        $this->assertTrue ($action2->completedBy === 'admin');
        $this->assertTrue ($action2->updatedBy === 'testUser2');

        // reassigned and updated updatedBy fields
        $action3 = $this->actions ('action3');
        $this->assertTrue ($action3->assignedTo === 'Anyone');
        $this->assertTrue ($action3->completedBy === 'testUser2');
        $this->assertTrue ($action3->updatedBy === 'admin');


        /*
        contacts reassignment 
        */

        // reassigned but left valid updatedBy field
        $conctact1 = $this->contacts ('contact1');
        $this->assertTrue ($conctact1->assignedTo === 'Anyone');
        $this->assertTrue ($conctact1->updatedBy === 'testUser2');

        // reassigned but changed invalid updatedBy field
        $contact2 = $this->contacts ('contact2');
        $this->assertTrue ($contact2->assignedTo === 'Anyone');
        $this->assertTrue ($contact2->updatedBy === 'admin');
    }

    public function testUserAliasUnique() {
        $admin = $this->users('admin');

        // can't set username to someone else's username
        $admin->userAlias = $this->users('testUser')->username;
        $admin->validate(array('userAlias'));
        $this->assertTrue($admin->hasErrors('userAlias'));

        $newUser = new User;
        // can't set username to someone else's user alias
        $newUser->username = $this->users('testUser')->userAlias;
        $newUser->validate(array('username'));
        $this->assertTrue($newUser->hasErrors('username'));

        // ensure that user can have alias which matches their own username
        $newUser = new User;
        $newUser->username = 'username';
        $newUser->userAlias = 'username';
        $newUser->validate (array ('username'));
        $newUser->validate (array ('userAlias'));
        $this->assertFalse($newUser->hasErrors('username'));
        if ($newUser->hasErrors ()) {
            VERBOSE_MODE && print_r ($newUser->getErrors ());
        }
    }

    public function testUpdate () {
        $user = $this->users ('admin');

        // should be able to set userAlias to username
        $user->userAlias = $user->username;
        $this->assertSaves ($user);

        // user alias cannot have trailing or leading whitespace
        $user->userAlias = '      ';
        $this->assertFalse ($user->save ());
        $user->userAlias = 'admin  ';
        $this->assertFalse ($user->save ());
        $user->userAlias = '  admin  ';
        $this->assertFalse ($user->save ());
        $user->userAlias = '  admin';
        $this->assertFalse ($user->save ());

        // also cannot be the empty string
        $user->userAlias = '';
        $this->assertFalse ($user->save ());

        $user->userAlias = 'admin';
        $this->assertSaves ($user);
    }

    public function testFindByAlias () {
        $foundByName = User::model()->findByAlias($this->users('testUser')->username);
        $foundByAlias = User::model()->findByAlias($this->users('testUser')->userAlias);
        $this->assertEquals($this->users('testUser')->id,$foundByName->id);
        $this->assertEquals($foundByName->id,$foundByAlias->id);
    }

    public function testGetAlias() {
        $user = new User;
        $user->username = 'imauser';
        $this->assertEquals($user->username,$user->alias);
        $user->userAlias = 'imausertoo';
        $this->assertEquals($user->userAlias,$user->alias);
    }

    public function testCreate () {
        $user = new User;
        $user->setAttributes (array (
            'firstName' => 'test', 
            'lastName' => 'test',
            'username' => 'test',
            'password' => 'test',
            'status' => 1,
        ), false);

        $this->assertSaves ($user);
        // user alias should get set automatically in beforeValidate ()
        $this->assertEquals ('test', $user->userAlias);
    }
}

?>
