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
 * @package application.tests.unit.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailDeliveryBehaviorTest extends X2TestCase {

    /**
     * Test the parsing of a recipient header (i.e. "To:") into a PHPMailer-compatible array.
     */
    public function testAddressHeaderToArray() {
        $addressHeader = '"Butts, Seymour" <seymour@butts.com>, "I.P. Freely"<ip@free.ly>, johnsmith@gmail.com, <only@email.com>';
        $addressHeaderArray = EmailDeliveryBehavior::addressHeaderToArray($addressHeader);
        $this->assertEquals(array(
            array('Butts, Seymour','seymour@butts.com'),
            array('I.P. Freely','ip@free.ly'),
            array('','johnsmith@gmail.com'),
            array('','only@email.com')
        ),$addressHeaderArray);
    }

}

?>
