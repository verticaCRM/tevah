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

/* @edition:pro */

Yii::import('application.modules.services.models.*');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.contacts.models.*');

/**
 * 
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimerTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'users'=>'User',
        );
    }

    public $fixtures = array(
        'timers' => 'ActionTimer',
        'actions' => 'Actions',
        'contacts'=>'Contacts'
    );

    /**
     * Instantiation test
     */
    public function testSetup() {
        $user = $this->users('testUser');
        Yii::app()->suModel = $user;
        $timer = ActionTimer::setup(true);
        $time = time();
        // No existing timer, default values:
        $this->assertEquals($user->id,$timer->userId);
        $this->assertEquals(null,$timer->associationId);
        $this->assertEquals(null,$timer->type);
        $this->assertTrue(abs($time-$timer->timestamp) <= 1);

        $timer->data = 'match this text';
        $timer->save();
        // Now there is such a timer. Assert they're the same.
        $anotherTimer = ActionTimer::setup();
        foreach($anotherTimer->attributes as $name=>$value) {
            $this->assertEquals($timer->$name,$value);
        }
        $timer = ActionTimer::setup(true, array('associationId' => 1, 'userId' => $this->users('testUser')->id));
        $this->assertEquals(1, $timer->associationId);
        $this->assertEquals($this->users('testUser')->id, $timer->userId);
        
    }

    /**
     * Test for ending the timer and creating an associated action record
     */
    public function testEnd() {
        $timer = $this->timers('testcontact_timelog');
        $timerAttr = $timer->attributes;

        $actionOut = $timer->stop();
        // Assert it's a timestamp... Insertions should never take more than a second
        $this->assertTrue(abs($timer->endtime - time()) <= 1,'ActionTimer.stop() did not return, or took WAAAAY too long to run.');
    }
    
    public function testTimeSpent() {
        $user = $this->users('testUser');
        Yii::app()->suModel = $user;
        $timeSpent = ActionTimer::getTimeSpent(67890);
        $this->assertEquals(2, $timeSpent);
    }
    
}

?>
