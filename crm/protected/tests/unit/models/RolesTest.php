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

Yii::import ('application.modules.users.models.*');
Yii::import ('application.modules.groups.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class RolesTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
            'role' => 'Roles',
            'roleToUser' => 'RoleToUser',
            'groupToUser' => 'GroupToUser'
        );
    }

    public function testGetUserTimeout() {
        $this->assertTrue (Yii::app()->cache->flush());
        $defaultTimeout = 60;
        Yii::app()->settings->timeout = $defaultTimeout;
        // admin's timeout should be the big one based on role
        $this->assertEquals(
            $this->role('longTimeout')->timeout, 
            Roles::getUserTimeout($this->user('admin')->id, false));
        // testuser's timeout should also be the big one, and not the "Peon"
        // role's timeout length
        $this->assertEquals(
            $this->role('longTimeout')->timeout,
            Roles::getUserTimeout($this->user('testUser')->id, false));
        // testuser2's timeout should be the "Peon" role's timeout length
        // because that user has that role, and that role has a timeout longer
        // than the default timeout
        $this->assertEquals(
            $this->role('shortTimeout')->timeout,
            Roles::getUserTimeout($this->user('testUser2')->id, false));
        // testuser3 should have no role. Here, let's ensure that in case the
        // fixtures have been modified otherwise
        RoleToUser::model()->deleteAllByAttributes(array('userId'=>$this->user('testUser3')->id));
        $this->assertEquals(
            $defaultTimeout,
            Roles::getUserTimeout($this->user('testUser3')->id, false));
    }

    /**
     * Ensure that upon deletion of roleToUser records, roles update immediately
     * (do not use an outdated cache entry)
     */
    public function testGetUserRoles () {
        $userId = $this->user['testUser']['id'];
        $userRoles = Roles::getUserRoles ($userId);

        // Assert that user has roles
        $this->assertTrue (sizeof ($userRoles) > 0);
        // Specifically, these (user groups only):
        $this->assertEquals(array(
            1,2
        ),$userRoles);

        // Test group-inherited user roles; fixture entry "testUser5" is a
        // member of a group:
        $userRoles = Roles::getUserRoles($this->user['testUser5']['id']);
        $this->assertEquals(array(3),$userRoles);

        // Iterate over and remove records explicitly to raise the afterDelete event
        $records = RoleToUser::model()->findAllByAttributes(array(
            'userId'=>$userId,
            'type'=>'user'));
        foreach ($records as $record) {
            $record->delete();
        }
        $userRoles = Roles::getUserRoles ($userId);

        // assert that user has no roles
        $this->assertTrue (sizeof ($userRoles) === 0);
    }
}

?>
