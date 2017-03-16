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
class ProfileTest extends X2DbTestCase {

    public static function referenceFixtures() {
        return array('profile'=>'Profile');
    }

    public function testGetLayout() {
        $profile = $this->profile('testProfile');
        $profile->setAttribute('layout',null);
        $initialLayout = $profile->getLayout();
        // Try (just running) with empty layouts
        $profile->setAttribute('layout',json_encode(array('left'=>array(),'center'=>array(),'right'=>array(),'hidden'=>array(),'hiddenRight'=>array())));
        $emptyLayout = $profile->getLayout();
        // Test that non-existing layout widgets are removed:
        $profile->setAttribute('layout',json_encode(array('left'=>array('FooWidget'=>array('nothing to see here')),'center'=>array(),'right'=>array(),'hidden'=>array(),'hiddenRight'=>array())));
        $this->assertEquals($emptyLayout,$profile->getLayout());
    }
}

?>
