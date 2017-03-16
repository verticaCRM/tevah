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

/**
 * 
 * @package application.tests.unit.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User'
        );
    }

    public $fixtures = array(
        'resets' => 'PasswordReset'
    );

    public function testGetIsExpired() {
        $this->assertFalse($this->resets('1')->isExpired);
        $this->assertTrue($this->resets('9')->isExpired);
    }

    public function testGetLimitReached() {
        $reset = new PasswordReset;
        $reset->ip = '127.0.0.1';
        $this->assertTrue($reset->limitReached);
        $reset = new PasswordReset;
        $reset->ip = '127.0.0.2';
        $this->assertFalse($reset->limitReached);
    }

    public function testBeforeSave() {
        // Fixture data should contain five expired reset requests, which should
        // be cleared out in beforeSave()
        $n0 = PasswordReset::model()->countByAttributes(array('ip'=>'127.0.0.1'));
        $reset = new PasswordReset;
        $reset->email = $this->user('testUser')->emailAddress;
        $reset->ip = '127.0.0.1';
        $reset->beforeSave();
        $n1 = PasswordReset::model()->countByAttributes(array('ip'=>'127.0.0.1'));
        $this->assertEquals($this->user('testUser')->id,$reset->userId);
        $this->assertEquals(5,$n0-$n1);
    }

    public function validUserId() {
        $reset = new PasswordReset;
        $reset->email = $this->user('testUser')->emailAddress;
        $reset->validUserId('email');
        $this->assertFalse($reset->hasErrors());
        $this->assertEquals($this->user('testUser')->id,$reset->userId);
        $reset = new PasswordReset;
        $reset->email = 'a00000000@a99999999999.com';
        $reset->validUserId('email');
        $this->assertTrue($reset->hasErrors());
        $this->assertEmpty($reset->userId);
    }

}

?>
