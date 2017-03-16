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
 * @package application.tests.unit.components.x2flow.triggers
 */
class WorkflowStartStageTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.WorkflowTests'),
        'actions' => array ('Actions', '.WorkflowTests'),
    );

    public static function referenceFixtures(){
        return array(
            'x2flow' => array ('X2Flow', '.WorkflowStartStageTriggerTest'),
            'workflows' => array ('Workflow', '.WorkflowTests'),
            'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
            'roleToWorkflow' => array (':x2_role_to_workflow', '.WorkflowTests'),
        );
    }

    /**
     * Trigger the flow by completing a workflow stage on the contact. Assert that the flow 
     * executes without errors.
     */
    public function testFlowExecution () {
        $this->clearLogs ();
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');
        // complete stage 4, autostarting stage 5. This should trigger the flow
        $retVal = Workflow::completeStage (
            $workflow->id, 4, $model, 'test comment');
        $newLog = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($newLog));

        // complete stage 5. This shouldn't trigger the flow since the flow checks that stage
        // 4 was completed
        $this->clearLogs ();
        $retVal = Workflow::completeStage (
            $workflow->id, 5, $model, '');
        $newLog = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertFalse ($this->checkTrace ($newLog));
    }

}

?>
