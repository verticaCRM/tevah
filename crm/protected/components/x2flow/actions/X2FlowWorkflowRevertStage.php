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
 * X2FlowAction that reverts a workflow stage
 * 
 * @package application.components.x2flow.actions
 */
class X2FlowWorkflowRevertStage extends BaseX2FlowWorkflowStageAction {
	public $title = 'Revert Process Stage';
	public $info = '';
	
	public function execute(&$params) {
        $workflowId = $this->parseOption ('workflowId', $params);
        $stageNumber = $this->parseOption ('stageNumber', $params);

        $model = $params['model'];

        $type = lcfirst (X2Model::getModuleName (get_class ($model)));
        $modelId = $model->id;

        $workflowStatus = Workflow::getWorkflowStatus($workflowId,$modelId,$type);
        $message = '';
        if (Workflow::validateAction (
            'revert', $workflowStatus, $stageNumber, '', $message)) {

            list ($started, $workflowStatus) = 
                Workflow::revertStage (
                    $workflowId, $stageNumber, $model, $workflowStatus);
            assert ($started);
            return array (true, Yii::t('studio', 'Stage "{stageName}" reverted for {recordName}', 
                array (
                    '{stageName}' => $workflowStatus['stages'][$stageNumber]['name'],
                    '{recordName}' => $model->getLink (),
                )
            ));
        } else {
            return array (false, $message);
        }
		
	}
}
