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


Yii::import('application.modules.workflow.*');
Yii::import('application.modules.workflow.controllers.*');
Yii::import('application.modules.workflow.models.*');
Yii::import('application.modules.users.models.*');

/**
 * @package application.tests.unit.modules.workflow.controllers
 */
class WorkflowTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.WorkflowTests'),
        'actions' => array ('Actions', '.WorkflowTests'),
    );

    public static function referenceFixtures(){
        return array(
            'x2flow' => array ('X2Flow', '.WorkflowTests'),
            'workflows' => array ('Workflow', '.WorkflowTests'),
            'workflowStages' => array ('WorkflowStage', '.WorkflowTests'),
            'roleToWorkflow' => array (':x2_role_to_workflow', '.WorkflowTests'),
            'users' => 'User',
        );
    }

    public function testGetStagePermissions () {
        $workflow = $this->workflows ('workflow2'); 
        $status = Workflow::getWorkflowStatus ($workflow->id);
        $permissions = Workflow::getStagePermissions ($status);

        // admin should have permissions for all stages
        $this->assertTrue (!in_array (0, $permissions));

        $this->assertTrue (TestingAuxLib::suLogin('testuser'));
        $status = Workflow::getWorkflowStatus ($workflow->id);
        $permissions = Workflow::getStagePermissions ($status);
        VERBOSE_MODE && print_r ($permissions);

        // testuser does not have permission for stage 4
        $this->assertFalse ($permissions[3]);

        $this->assertTrue (TestingAuxLib::suLogin ('admin'));
    }

    public function testCanUncomplete () {
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');
        Yii::app()->settings->workflowBackdateWindow = 0;

        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);

        // admin user unaffected by backdate window
        $this->assertTrue ($success);
        
        $this->assertTrue (TestingAuxLib::suLogin ('testuser'));

        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);

        // can't revert because backdate window has passed
        $this->assertFalse ($success);

        $this->assertTrue (TestingAuxLib::suLogin ('admin'));
    }

    public function testMoveFromStageAToStageB () {
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');

        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 4, 5, $model, array ('4' => 'test comment'));
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        $this->assertTrue ($retVal[0]);

        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 5, 1, $model);
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        $this->assertTrue ($retVal[0]);

        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 1, 5, $model);
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        // should fail since stage 4 requires a comment
        $this->assertFalse ($retVal[0]);


        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 1, 4, $model);
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        $this->assertTrue ($retVal[0]);

        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 4, 1, $model);
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        $this->assertTrue ($retVal[0]);

        $this->assertTrue (TestingAuxLib::suLogin ('testuser'));
        $retVal = Workflow::moveFromStageAToStageB (
            $workflow->id, 1, 4, $model);
        if (!$retVal[0] && VERBOSE_MODE) println ($retVal[1]);
        // should fail since testuser doesn't have permission to go through stage 3
        $this->assertFalse ($retVal[0]);

        $this->assertTrue (TestingAuxLib::suLogin ('admin'));
    }

    public function testCompleteStage () {
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');

        list ($success, $status) = Workflow::completeStage (
            $workflow->id, 4, $model, '');

        // failed to completed next stage because comment is required
        $this->assertFalse ($success);
        list ($success, $status) = Workflow::completeStage (
            $workflow->id, 4, $model, 'test comment');

        // completed next stage
        $this->assertTrue ($success);

        list ($success, $status) = Workflow::completeStage (
            $workflow->id, 3, $model, 'test comment');

        // couldn't complete already completed stage
        $this->assertFalse ($success);


        // unstart stage 4 by reverting it twice
        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);
        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);

        list ($success, $status) = Workflow::completeStage (
            $workflow->id, 4, $model, 'test comment');

        // can't complete stages which haven't been started yet
        $this->assertFalse ($success);

    }

    public function testRevertStage () {
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');

        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);

        // reverted a started stage
        $this->assertTrue ($success);

        list ($success, $status) = Workflow::revertStage (
            $workflow->id, 4, $model);

        // couldn't revert an unstarted stage
        $this->assertFalse ($success);
    }

    public function testStartStage () {
        $workflow = $this->workflows ('workflow2'); 
        $model = $this->contacts ('contact935');

        list ($success, $status) = Workflow::startStage (
            $workflow->id, 5, $model);

        // couldn't start a stage which requires previous, uncompleted stage
        $this->assertFalse ($success);

        // complete stage 4 and disable auto start so that stage 5 doesn't get started
        list ($success, $status) = Workflow::completeStage (
            $workflow->id, 4, $model, 'test comment', false);
        list ($success, $status) = Workflow::startStage (
            $workflow->id, 5, $model);

        // should have been able to start stage 5 now that 4 is completed
        $this->assertTrue ($success);

    }

    public function testGetStageCounts () {
        $workflow = $this->workflows ('workflow2'); 
        $workflowStatus = Workflow::getWorkflowStatus($workflow->id);
        $counts = Workflow::getStageCounts ($workflowStatus, array (
            'start' => 0,
            'end' => time (),
            'workflowId' => $workflow->id,
        ), array ('range' => 'all'));
        VERBOSE_MODE && print_r ($counts);
        $this->assertEquals (1, array_reduce ($counts, function ($a, $b) { return $a + $b; }, 0));
        $action = Actions::model ()->findByAttributes (array (
            'workflowId' => $workflow->id,
            'complete' => 'No',
            'stageNumber' => 4,
        ));

        // make record invisible
        $record = X2Model::getModelOfTypeWithId ($action->associationType, $action->associationId);
        $record->visibility = 0;
        $record->assignedTo = 'admin';
        $this->assertSaves ($record);

        // ensure that admin user can still see the record
        $counts = Workflow::getStageCounts ($workflowStatus, array (
            'start' => 0,
            'end' => time (),
            'workflowId' => $workflow->id,
        ), array ('range' => 'all'));
        VERBOSE_MODE && print_r ($counts);
        $this->assertEquals (1, array_reduce ($counts, function ($a, $b) { return $a + $b; }, 0));

        // ensure that testuser cannot still see the record
        TestingAuxLib::suLogin ('testuser');
        $counts = Workflow::getStageCounts ($workflowStatus, array (
            'start' => 0,
            'end' => time (),
            'workflowId' => $workflow->id,
        ), array ('range' => 'all'));
        VERBOSE_MODE && print_r ($counts);
        $this->assertEquals (0, array_reduce ($counts, function ($a, $b) { return $a + $b; }, 0));

        // unless it's assigned to testuser
        $record->assignedTo = 'testuser';
        $this->assertSaves ($record);
        $counts = Workflow::getStageCounts ($workflowStatus, array (
            'start' => 0,
            'end' => time (),
            'workflowId' => $workflow->id,
        ), array ('range' => 'all'));
        VERBOSE_MODE && print_r ($counts);
        $this->assertEquals (1, array_reduce ($counts, function ($a, $b) { return $a + $b; }, 0));

        TestingAuxLib::suLogin ('admin');
    }

    /**
     * Tests a method in WorkflowController which belongs in the Workflow model class
     */
    public function testGetStageMemberDataProvider () {
        $workflow = $this->workflows ('workflow2'); 
        $workflowStatus = Workflow::getWorkflowStatus($workflow->id);
        
        $this->assertDataProviderCountMatchesStageCount ($workflow, $workflowStatus, 1);
        $this->assertDataProviderCountMatchesStageCount ($workflow, $workflowStatus, 4);

        // make record invisible
        $action = Actions::model ()->findByAttributes (array (
            'workflowId' => $workflow->id,
            'complete' => 'No',
            'stageNumber' => 4,
        ));
        $record = X2Model::getModelOfTypeWithId ($action->associationType, $action->associationId);
        $record->visibility = 0;
        $record->assignedTo = 'admin';
        $this->assertSaves ($record);

        $counts = $this->assertDataProviderCountMatchesStageCount ($workflow, $workflowStatus, 4);
        $this->assertEquals (1, $counts[3]);

        TestingAuxLib::suLogin ('testuser');
        $counts = $this->assertDataProviderCountMatchesStageCount ($workflow, $workflowStatus, 4);
        $this->assertEquals (0, $counts[3]);

        $record->assignedTo = 'testuser';
        $this->assertSaves ($record);
        $counts = $this->assertDataProviderCountMatchesStageCount ($workflow, $workflowStatus, 4);
        $this->assertEquals (1, $counts[3]);
    }

    private function assertDataProviderCountMatchesStageCount (
        $workflow, $workflowStatus, $stageNumber) {

        $counts = Workflow::getStageCounts ($workflowStatus, array (
            'start' => 0,
            'end' => time (),
            'workflowId' => $workflow->id,
        ), array ('range' => 'all'));

        VERBOSE_MODE && print_r ($counts);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $controllerName = 'WorkflowController'; 
        $moduleName = 'WorkflowModule'; 
        Yii::app()->controller = new $controllerName (
            'Workflow', new $moduleName ('Workflow', null));

        $contactsDataProvider = Yii::app()->controller->getStageMemberDataProvider (
            'contacts', $workflow->id, array (
                'start' => 0,
                'end' => time (),
                'workflowId' => $workflow->id,
            ), array ('range' => 'all'), $stageNumber, '');

        $opportunitiesDataProvider = Yii::app()->controller->getStageMemberDataProvider (
            'opportunities', $workflow->id, array (
                'start' => 0,
                'end' => time (),
                'workflowId' => $workflow->id,
            ), array ('range' => 'all'), $stageNumber, '');
        $accountsDataProvider = Yii::app()->controller->getStageMemberDataProvider (
            'accounts', $workflow->id, array (
                'start' => 0,
                'end' => time (),
                'workflowId' => $workflow->id,
            ), array ('range' => 'all'), $stageNumber, '');

        // ensure that number of record in dataproviders matches count
        // stage 0 should have no records in it
        $this->assertEquals (
            $counts[$stageNumber - 1], 
            count ($contactsDataProvider->data) + count ($opportunitiesDataProvider->data) + 
                count ($accountsDataProvider->data));

        return $counts;
    }

}

?>
