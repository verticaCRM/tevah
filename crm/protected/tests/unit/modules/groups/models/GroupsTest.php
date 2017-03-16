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
Yii::import('application.components.*');
Yii::import('application.components.permissions.*');
Yii::import('application.components.util.*');

/**
 *
 * @package application.tests.unit.components
 */
class GroupTest extends CDbTestCase {

    public $fixtures = array (
        'users' => 'User',
        'groups' => array ('Groups', '_1'),
        'groupToUser' => array ('GroupToUser', '_2'),
        'session' => array('Session','_2'),
    );

    /**
     * Ensures that all users accessed via the users relation belong to the group 
     */
    public function testUsersRelation () {
        foreach ($this->groups as $key => $val) {
            $groupModel = Groups::model ()->findByAttributes ($val);
            $userIds = array_map (function ($a) { return $a['id']; }, $groupModel->users);

            if(VERBOSE_MODE) {
                print ($groupModel->id."\n");
                print_r ($userIds);
            }
            
            /*
            For each user, ensure that there is a corresponding groupToUser entry
            */
            foreach ($userIds as $uid) {
                $found = false;
                foreach ($this->groupToUser as $key => $val) {
                     if ($val['groupId'] == $groupModel->id && $val['userId'] == $uid) {
                        $found = true;
                     }
                }
                $this->assertTrue ($found);
            }
        }
    }

    public function testHasOnlineUsers () {
        // Group 3 should have no online users.
        $this->assertFalse($this->groups('group3')->hasOnlineUsers ());

        // Group 1 should have online users.
        $this->assertTrue ($this->groups('group1')->hasOnlineUsers ());

    }

    public function testAfterDelete () {
        $group = Groups::model ()->findByPk ('1');
        if (VERBOSE_MODE) {
            print ('id of group to delete: ');
            print ($group->id);
        }
        
        // assert that group to user records exist for this group
        $this->assertTrue (
            sizeof (GroupToUser::model ()->findByAttributes (array ('groupId' => $group->id))) > 0);
        $group->delete ();

        // assert that group to user records were deleted
        $this->assertTrue (
            sizeof (
                GroupToUser::model ()->findByAttributes (array ('groupId' => $group->id))) === 0);

    }

}

?>
