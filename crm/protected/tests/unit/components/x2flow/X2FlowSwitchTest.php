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

Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.workflow.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class X2FlowSwitchTest extends X2FlowTestBase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.X2FlowSwitchTest'),
        'actions' => array ('Actions', '.WorkflowTests'),
        'workflows' => array ('Workflow', '.WorkflowTests'),
        'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
        'x2flow' => array ('X2Flow', '.X2FlowSwitchTest'),
    );

    /**
     * Tests the stage completed condition
     */
    public function testWorkflowConditionCompleted () {
        if (!self::$loadFixtures) return;

        $flow10 = $this->x2flow ('flow10');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow10, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow11 = $this->x2flow ('flow11');
        $retVal = $this->executeFlow ($flow11, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }

    /**
     * Should work exactly like testWorkflowConditionCompleted except assertions are swapped
     */
    public function testWorkflowConditionNotCompleted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow12');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has completed the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow13');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not completed the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }

    public function testWorkflowConditionStarted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow14');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);

        $flow = $this->x2flow ('flow15');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);
    }


    public function testWorkflowConditionNotStarted () {
        if (!self::$loadFixtures) return;

        $flow = $this->x2flow ('flow16');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has started the stage that's checked by this flow. The conditional
        // should have evaluated to false
        $this->assertFalse ($trace[1]['branch']);

        $flow = $this->x2flow ('flow17');
        $retVal = $this->executeFlow ($flow, $params);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);

        // this contact has not started the stage that's checked by this flow. The conditional
        // should have evaluated to true
        $this->assertTrue ($trace[1]['branch']);
    }

    public function testHasTagsCondition () {
        $flow = $this->x2flow ('flow18');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        VERBOSE_MODE && print_r ($this->contacts ('contact935')->getTags ());
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertFalse ($trace[1]['branch']);

        $this->contacts ('contact935')->clearTags ();
        $this->contacts ('contact935')->addTags (array ('test', 'test2'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertTrue ($trace[1]['branch']);
        VERBOSE_MODE && print_r ($trace);

        $flow = $this->x2flow ('flow19');
        $params = array (
            'model' => $this->contacts ('contact935'),
        );
        $this->contacts ('contact935')->clearTags ();
        $this->contacts ('contact935')->addTags (array ('test'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        VERBOSE_MODE && print_r ($trace);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertTrue ($trace[2]['branch']);
        $this->contacts ('contact935')->removeTags (array ('test'));
        $retVal = $this->executeFlow ($flow, $params);
        $trace = $this->flattenTrace ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
        $this->assertFAlse ($trace[2]['branch']);
        VERBOSE_MODE && print_r ($trace);
    }
}

?>
