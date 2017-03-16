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
 * 
 * @package
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetFormTest extends X2DbTestCase {

    public $fixtures = array(
        'user' => 'User',
        'resets' => 'PasswordReset'
    );


    public function testSave() {
        $user = $this->user('testUser');
        $form = new PasswordResetForm($user);
        $form->password = 'a really bad password';
        $expectmd5 = md5('a really bad password');
        $form->confirm = $form->password;
        $form->save();
        $user->refresh();
        $this->assertEquals($expectmd5,$user->password);
        $this->assertEquals(0,PasswordReset::model()->countByAttributes(array('userId'=>$user->id)));

        // Test validation as well, as a "bonus", since there needn't be any
        // fixture loading for it, and it thus saves a few seconds when running
        // the test:
        $form = new PasswordResetForm($user);
        $passwords = array(
            false => array(
                'n#6', // 3 character classes but too short
                'ninininini' // long enough but not enough character classes
            ),
            true => array(
                'D83*@)1', // 5 characters long and multiple character classes
                'this that and the next thing', // only two characters but very long
            )
        );
        foreach($passwords as $good => $passes) {
            foreach($passes as $pass) {
                $form->password = $pass;
                $form->confirm = $pass;
                $this->assertEquals($good,$form->validate(array('password')));
            }
        }
    }
}

?>
