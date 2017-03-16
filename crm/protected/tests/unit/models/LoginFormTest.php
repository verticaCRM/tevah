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
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class LoginFormTest extends X2DbTestCase {

    public static function referenceFixtures(){
        return array(
            'user' => 'User'
        );
    }

    public function testGetUser() {
        $lf = new LoginForm;
        // Use username
        $lf->username = $this->user('testUser')->username;
        $lf->password = 'password';
        $this->assertEquals($this->user('testUser')->id,$lf->getUser()->id);
        $this->assertEquals($this->user('testUser')->id,$lf->getUser()->id);
        $lf = new LoginForm;
        // Use alias
        $lf->username = $this->user('testUser')->userAlias;
        $lf->password = 'password';
        $this->assertEquals($this->user('testUser')->id,$lf->getUser()->id);
        $this->assertEquals($this->user('testUser')->id,$lf->getUser()->id);
    }

    public function testGetSessionUsername() {
        // Must be username
        $lf = new LoginForm;
        $lf->username = $this->user('testUser')->userAlias;
        $lf->password = 'password';
        $this->assertEquals($this->user('testUser')->username,$lf->getSessionUsername());
    }
}

?>
