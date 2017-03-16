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

/**
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class AdminTest extends X2DbTestCase {


    public function testCountEmail() {
        $admin = Yii::app()->settings;
        $admin->emailCount = 0;
        $admin->emailInterval = 2;
        $admin->update(array('emailCount','emailInterval'));
        $now = time();
        // This should register five emails as having been sent:
        $admin->countEmail(5);
        $this->assertEquals($now,$admin->emailStartTime);
        $this->assertEquals(5,$admin->emailCount);
        // One more:
        $admin->countEmail();
        $this->assertEquals(6,$admin->emailCount);
        sleep(3);
        // After the interval ends, the count should be reset
        $now = time();
        $admin->countEmail(3);
        $this->assertEquals($now,$admin->emailStartTime);
        $this->assertEquals(3,$admin->emailCount);
    }

    public function testEmailCountWillExceedLimit() {
        $admin = Yii::app()->settings;
        $admin->emailCount = 0;
        $admin->emailInterval = 1;
        $admin->emailBatchSize = 5;
        $admin->update(array('emailCount','emailInterval','emailBatchSize'));
        $admin->countEmail(4);
        // One more won't kill us
        $this->assertFalse($admin->emailCountWillExceedLimit(1));
        // Two more will exceed the limit
        $this->assertTrue($admin->emailCountWillExceedLimit(2));
    }
}

?>
