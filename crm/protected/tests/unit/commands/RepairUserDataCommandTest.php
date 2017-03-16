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
class RepairUserDataCommandTest extends CDbTestCase {


    public $fixtures = array (
        'users' => 'User',
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_2'),
        'actions' => array ('Actions', '.UserTest'),
        'contacts' => array ('Contacts', '.UserTest'),
        'events' => array ('Events', '.UserTest'),
        'social' => array ('Social', '.UserTest'),
        'profile' => array ('Profile', '.UserTest'),
    );

    public function testConstantSet () {
        // must be set to true so that the command uses the test database
        $this->assertEquals (true, YII_UNIT_TESTING);
    }

    /**
     * Performs user deletion. Asserts that former user's data is corrupt. Repairs data with
     * command line script. Finally, checks are made to ensure that script successfully repaired
     * data.
     */
    public function testCommand () {
        $this->assertTrue(YII_UNIT_TESTING,'YII_UNIT_TESTING must be set to TRUE for this test to run properly.');
        $user = $this->users ('testUser');
        Yii::app()->db->createCommand (
            'delete from x2_users where username="testUser"'
        )->execute();

        /*
        actions reassignment
        */

        // reassigned but left valid complete/updatedBy fields
        $action1 = $this->actions ('action1');
        $this->assertTrue ($action1->assignedTo === 'testUser');

        // reassigned and updated completedBy field
        $action2 = $this->actions ('action2');
        $this->assertTrue ($action2->assignedTo === 'testUser');
        $this->assertTrue ($action2->completedBy === 'testUser');
        $this->assertFalse ($action2->updatedBy === 'testUser');

        // reassigned and updated updatedBy fields
        $action3 = $this->actions ('action3');
        $this->assertTrue ($action3->assignedTo === 'testUser');
        $this->assertFalse ($action3->completedBy === 'testUser');
        $this->assertTrue ($action3->updatedBy === 'testUser');

        $action4 = $this->actions ('action4');
        $this->assertTrue ($action4->assignedTo === 'testUser2');
        $this->assertTrue ($action4->updatedBy === 'testUser');
        $this->assertTrue ($action4->updatedBy === 'testUser');


        /*
        contacts reassignment 
        */

        // reassigned but left valid updatedBy field
        $contact1 = $this->contacts ('contact1');
        $this->assertFalse ($contact1->assignedTo === 'Anyone');

        // reassigned but changed invalid updatedBy field
        $contact2 = $this->contacts ('contact2');
        $this->assertFalse ($contact2->assignedTo === 'Anyone');
        $this->assertFalse ($contact2->updatedBy === 'admin');

        $contact3 = $this->contacts ('contact3');
        $this->assertTrue ($contact3->assignedTo === 'testUser2');
        $this->assertTrue ($contact3->updatedBy === 'testUser');

        $return_var;
        $output = array ();
        $command = Yii::app()->basePath."/yiic repairuserdata repair --username='testUser'";
        if(VERBOSE_MODE)
            println("Running $command...");
        ob_start();
        println (exec ($command, $return_var, $output));
        if(VERBOSE_MODE)
            ob_end_flush();
        else
            ob_end_clean();
        VERBOSE_MODE && println ($output);
        VERBOSE_MODE && print_r ($return_var);

        /*
        actions reassignment
        */

        // reassigned but left valid complete/updatedBy fields
        $action1->refresh ();
        $this->assertEquals ('Anyone', $action1->assignedTo);
        $this->assertTrue ($action1->completedBy === 'testUser2');
        $this->assertTrue ($action1->updatedBy === 'testUser2');

        // reassigned and updated completedBy field
        $action2->refresh ();
        $this->assertTrue ($action2->assignedTo === 'Anyone');
        $this->assertTrue ($action2->completedBy === 'admin');
        $this->assertTrue ($action2->updatedBy === 'testUser2');

        // reassigned and updated complete/updatedBy fields
        $action3->refresh ();
        $this->assertTrue ($action3->assignedTo === 'Anyone');
        $this->assertTrue ($action3->completedBy === 'testUser2');
        $this->assertTrue ($action3->updatedBy === 'admin');

        // should be left untouched
        $action4->refresh ();
        $this->assertTrue ($action4->assignedTo === 'testUser2');
        $this->assertTrue ($action4->updatedBy === 'testUser');
        $this->assertTrue ($action4->updatedBy === 'testUser');


        /*
        contacts reassignment 
        */

        // reassigned but left valid updatedBy field
        $contact1->refresh ();
        $this->assertTrue ($contact1->assignedTo === 'Anyone');
        $this->assertTrue ($contact1->updatedBy === 'testUser2');

        // reassigned but changed invalid updatedBy field
        $contact2->refresh ();
        $this->assertTrue ($contact2->assignedTo === 'Anyone');
        $this->assertTrue ($contact2->updatedBy === 'admin');

        // should be left untouched
        $contact3->refresh ();
        $this->assertTrue ($contact3->assignedTo === 'testUser2');
        $this->assertTrue ($contact3->updatedBy === 'testUser');
    }
}

?>
