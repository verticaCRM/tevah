<?php
/***********************************************************************************
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
 **********************************************************************************/


class GuestProfileFixTest extends X2DbTestCase {

    /**
     * Contains dump of profile and users table at 4.2 Platinum after creating a new users after
     * a fresh install
     */
    public $fixtures = array (
        'profiles' => array ('Profile', '.GuestProfileFixTest'), 
        'users' => array ('User', '.GuestProfileFixTest'), 
    );

    public static function setUpBeforeClass () {
        // must be set to true so that the command uses the test database
        if (!YII_UNIT_TESTING || !YII_DEBUG) {
            self::$skipAllTests = true;
        }
        parent::setUpBeforeClass ();
    }

    /**
     * Runs 4.2.1 migration script 
     * Asserts that guest profile is properly deleted and recreated with correct id.
     * Asserts that user with missing profile is given a profile with correctly set attributes
     */
    public function testMigrationScript () {
        // ensure test user doesn't have a profile
        $userWithoutProfile = User::model ()->findByAttributes (array (
            'username' => 'test'
        ));
        $badProfile = $userWithoutProfile->profile;
        $this->assertEquals (Profile::GUEST_PROFILE_USERNAME, $badProfile->username);

        // run guest profile fix migration script
        $command = Yii::app()->basePath . '/yiic runmigrationscript ' .
            'migrations/pending/1410382532-guest-profile-fix.php';
        $return_var;
        $output = array ();
        VERBOSE_MODE && print_r (exec ($command, $return_var, $output));
        VERBOSE_MODE && print_r ($return_var);
        VERBOSE_MODE && print_r ($output);
            
        // ensure that guest profile has correct id
        $guestProfile = Profile::model ()->findByPk (-1);
        $this->assertNotEquals (null, $guestProfile);
        $this->assertEquals (Profile::GUEST_PROFILE_USERNAME, $guestProfile->username);

        // ensure that user which formerly had no profile now has a profile
        $userWithoutProfile = User::model ()->findByAttributes (array (
            'username' => 'test'
        ));
        $this->assertNotEquals (null, $userWithoutProfile->profile);

        // ensure that test user profile has correctly set attributes
        $newProfile = $userWithoutProfile->profile;
        $newProfileAttributes = $newProfile->getAttributes ();
        $this->assertEquals ($userWithoutProfile->username, $userWithoutProfile->profile->username);
        $this->assertEquals (
            $userWithoutProfile->firstName.' '.$userWithoutProfile->lastName, 
            $userWithoutProfile->profile->fullName);
        $this->assertEquals (
            $userWithoutProfile->emailAddress, $userWithoutProfile->profile->emailAddress);
        $this->assertEquals ($userWithoutProfile->id, $userWithoutProfile->profile->id);
        $this->assertEquals (1, $userWithoutProfile->profile->status);
        $this->assertEquals (1, $userWithoutProfile->profile->allowPost);

        // delete test user profile and create a new profile in the way that it would be created
        // by actionCreate () in the user controller and ensure that it's attributes match those
        // of the profile created by the migration script
        $newProfile->delete ();
        $profile = new Profile;
        $profile->fullName = $userWithoutProfile->firstName." ".$userWithoutProfile->lastName;
        $profile->username = $userWithoutProfile->username;
        $profile->allowPost = 1;
        $profile->emailAddress = $userWithoutProfile->emailAddress;
        $profile->status = $userWithoutProfile->status;
        $profile->id = $userWithoutProfile->id;
        $profile->save();
        $this->assertEquals ($newProfileAttributes, $profile->getAttributes ());

    }

}


?>
