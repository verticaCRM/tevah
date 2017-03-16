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
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class UserIdentityTest extends X2DbTestCase {

    public $fixtures = array(
        'users' => 'User'
    );

    public function testAuthenticate() {
        // Test using user OR alias
        $tu = $this->users('testUser');
        $ui = new UserIdentity($tu->username,'password');
        $this->assertEquals($tu->id,$ui->getUserModel()->id);
        $this->assertTrue($ui->authenticate());
        $ui = new UserIdentity($tu->userAlias,'password');
        $this->assertEquals($tu->id,$ui->getUserModel()->id);
        $this->assertTrue($ui->authenticate());
        $tu->status = User::STATUS_INACTIVE;

        // Test incorrect password:
        $ui = new UserIdentity($tu->username,'notthepassword');
        $this->assertFalse($ui->authenticate());
        $this->assertEquals(UserIdentity::ERROR_PASSWORD_INVALID,$ui->errorCode);

        // Test incorrect username:
        $ui = new UserIdentity('nousernamethatexistsoreverwillexistintheusersfixture','passwor');
        $this->assertFalse($ui->authenticate());
        $this->assertEquals(UserIdentity::ERROR_USERNAME_INVALID,$ui->errorCode);

        // Test lockout:
        $tu->update(array('status'));
        $ui = new UserIdentity($tu->username,'password');
        $this->assertFalse($ui->authenticate());
        $this->assertEquals(UserIdentity::ERROR_DISABLED,$ui->errorCode);


    }
}

?>
