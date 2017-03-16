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

class X2FlowTestingAuxLibTest extends X2DbTestCase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '_1'),
        'accounts' => array ('Accounts', '_1')
    );

    public function testGetFlow () {
        $this->assertTrue (is_array (X2FlowTestingAuxLib::getFlow ($this)));
    }

    public function testExecuteFlow () {
        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
        );
        $this->assertTrue (is_array (X2FlowTestingAuxLib::executeFlow (
            X2Flow::Model ()->findByAttributes ($this->x2flow['flow1']), $params)));
    }

    public function testCheckTrace () {

        // this trace shows a flow which executed without error
        $trace =  array (
            true, 
            array (
                array (
                    "X2FlowCreateNotif", 
                    array (
                        true, 
                        ""
                    )
                ), 
                array (
                    "X2FlowSwitch", 
                    true, 
                    array (
                        array (
                            "X2FlowCreateNotif", 
                            array (
                                true, 
                                ""
                            )
                        ), 
                        array (
                            "X2FlowSwitch", 
                            true, 
                            array ()
                        )
                    )
                )
            )
        );
        $this->assertTrue (X2FlowTestingAuxLib::checkTrace ($trace));

        // this trace shows a flow which executed with errors
        $trace =  array (
            true, 
            array (
                array (
                    "X2FlowCreateNotif", 
                    array (
                        true, 
                        ""
                    )
                ), 
                array (
                    "X2FlowSwitch", 
                    true, 
                    array (
                        array (
                            "X2FlowCreateNotif", 
                            array (
                                false, 
                                ""
                            )
                        ), 
                        array (
                            "X2FlowSwitch", 
                            true, 
                            array ()
                        )
                    )
                )
            )
        );
        $this->assertFalse (X2FlowTestingAuxLib::checkTrace ($trace));
    }
}

?>
