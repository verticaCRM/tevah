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

class X2CalendarPermissionsTest extends X2DbTestCase {

    public $fixtures = array (
        'calendarPermissions' => 'X2Calendar',
        'users' => 'User',
    );

    /**
     * Ensure that list of viewable calendars correctly reflects calendar permissions records
     */
    public function testGetViewableUserCalendarNames () {
        TestingAuxLib::suLogin ('admin');        
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $this->assertEquals (array_merge (
            array ('Anyone'), Yii::app()->db->createCommand ("
                SELECT username
                FROM x2_users
            ")->queryColumn ()), 
            ArrayUtil::sort ($viewable));

        $user = $this->users ('testUser');
        TestingAuxLib::suLogin ('testUser');        
        $viewable = array_keys (X2CalendarPermissions::getViewableUserCalendarNames ());
        $grantedUsers = array_unique (array_merge (
            array ('Anyone', 'testuser'), Yii::app()->db->createCommand ("
                /**
                 * get names of users who have granted view permission to testuser and names of
                 * users who have not set up calendar permissions
                 */
                SELECT distinct(username)
                FROM x2_users as t, x2_calendar_permissions
                WHERE other_user_id=:userId OR t.id NOT in (
                    SELECT distinct(user_id)
                    FROM x2_calendar_permissions
                )
            ")->queryColumn (array (':userId' => $user->id))));
        $this->assertEquals (ArrayUtil::sort ($grantedUsers), 
            ArrayUtil::sort ($viewable));
    }

}

?>
