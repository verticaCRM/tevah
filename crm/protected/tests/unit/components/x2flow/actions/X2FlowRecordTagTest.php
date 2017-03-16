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

Yii::import ('application.modules.accounts.models.*');

/**
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordTagTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowRecordTagTest'),
        'contacts' => array ('Contacts', '.WorkflowTests'),
    );


    public function testAddTags () {
        $flow = $this->x2flow ('flow1');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->removeTags (array ('test1', 'test2'));
        $tags = $this->contacts ('contact935')->getTags ();
        print_r ($tags);
        $this->assertEmpty ($tags);

        $retVal = $this->executeFlow ($flow, $params);
        print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertTrue (in_array ('#test1', $tags));
        $this->assertTrue (in_array ('#test2', $tags));
    }

    public function testRemoveTags () {
        $flow = $this->x2flow ('flow2');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $tags = $this->contacts ('contact935')->getTags ();
        print_r ($tags);
        $this->assertEmpty ($tags);

        $tags = $this->contacts ('contact935')->addTags (array ('test1', 'test2'));
        $retVal = $this->executeFlow ($flow, $params);
        print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertTrue (!in_array ('#test1', $tags));
        $this->assertTrue (!in_array ('#test2', $tags));
    }

    public function testClearTags () {
        $flow = $this->x2flow ('flow3');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $tags = $this->contacts ('contact935')->addTags (array ('test1', 'test2'));
        print_r ($tags);
        $this->assertNotEmpty ($tags);

        $retVal = $this->executeFlow ($flow, $params);
        print_r ($retVal);
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $tags = $this->contacts ('contact935')->getTags ();
        $this->assertEmpty ($tags);
    }
}

?>
