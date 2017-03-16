<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

Yii::import('application.modules.users.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class SessionTest extends X2DbTestCase {

    public $fixtures = array(
        'session' => array('Session','_1'),
    );

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
            'role' => 'Roles',
            'roleToUser' => 'RoleToUser'
        );
    }

    public function testCleanUpSessions() {
        Yii::app()->cache->flush();
        // Prepare expected data:
        $sessionCounts = array(
            'session1' => 1,
            'session2' => 1,
            'session3' => 0,
        );
        foreach(array_keys($sessionCounts) as $alias) {
            $sessionIds[$alias] = $this->session($alias)->id;
        }
        
        $defaultTimeout = 60;
        Yii::app()->settings->timeout = $defaultTimeout;
        
        Session::cleanUpSessions();
        // Session 1 shoud still be there
        // Sessions 2 and 3 should be gone
        foreach($sessionCounts as $alias => $count){
            $this->assertEquals((integer)$count, Session::model()->countByAttributes(array('id'=>$sessionIds[$alias])),"$alias did not get deleted");
        }
    }
}

?>
