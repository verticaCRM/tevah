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

?>

<!-- dialog for completing a stage requiring a comment-->
<div id='workflowCommentDialog'>
    <form>
        <div class="row">
            <?php echo Yii::t('workflow','Please summarize how this stage was completed.'); ?></div>
        <div class="row">
            <?php
            echo CHtml::textArea(
                'workflowComment','',array('style'=>'width:260px;height:80px;'));
            echo CHtml::hiddenField(
                'workflowCommentWorkflowId','',array('id'=>'workflowCommentWorkflowId'));
            echo CHtml::hiddenField(
                'workflowCommentStageNumber','',array('id'=>'workflowCommentStageNumber'));
            ?>
        </div>
    </form>
</div>

<!-- dialog to contain Workflow Stage Details-->
<div id="workflowStageDetails"></div>

<div class="row">
    <div id="workflow-diagram">
        <?php
        // true = include dropdowns
        $workflowStatus = Workflow::getWorkflowStatus(
            $currentWorkflow,$model->id, X2Model::getAssociationType (get_class ($model)));
        //echo Workflow::renderWorkflow($workflowStatus); 
        if (sizeof ($workflowStatus['stages']) > 1) {
            $workflow = Workflow::model()->findByPk ($workflowStatus['id']);
            $colors = $workflow->getWorkflowStageColors (sizeof ($workflowStatus['stages']));

            Yii::app()->controller->renderPartial (
                'application.modules.workflow.views.workflow._inlineFunnel', array (
                    'workflowStatus' => $workflowStatus,
                    'stageCount' => sizeof ($workflowStatus['stages']),
                    'colors' => $colors,
            ));
        }
        ?>
    </div>
</div>
