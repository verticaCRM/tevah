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

/*Yii::app()->clientScript->registerCssFile(
    Yii::app()->assetManager->publish(
            Yii::getPathOfAlias('application.modules.workflow.assets'), false, -1, true).
    '/css/workflowFunnel.css');*/

Yii::app()->clientScript->registerCss('workflowCenterWidgetCss',"

#funnel-container {
    position: relative;
    width: auto;
    margin-left: 12px;
    max-width: 500px;
}

#funnel-container .interaction-buttons > a {
    margin-right: 3px;
    display: inline-block;
    vertical-align: middle;
}

#funnel-container .interaction-buttons {
    height: 17px;
}


#funnel-container img {
    margin-right: 4px;
    opacity: 0.8;
}

#funnel-container img:hover {
    opacity: 1;
}

div.workflow-status {
    overflow: hidden;
    display: block;
    line-height: 20px;
    height: 24px;
    max-width: 340px;
    margin-right: 10px;
}

div.workflow-status b {
    float: left;
}

div.workflow-status a {
    float: right;
}

");

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

<?php 
$workflowList = Workflow::getList();
?>
<div class="row" style="text-align:center;">
    <?php
    echo CHtml::dropDownList('workflowId',$currentWorkflow,$workflowList,    
        array(
            'ajax' => array(
                'type'=>'GET', //request type
                'url'=>CHtml::normalizeUrl( //url to call.
                    array(
                        '/workflow/workflow/getWorkflow',
                        'modelId'=>$model->id,'type'=>$modelName
                    )
                ), 
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#workflow-diagram', //selector to update
                'data'=>array('workflowId'=>'js:$(this).val()')
                //leave out the data key to pass all form values through
            ),
            'id'=>'workflowSelector',
            'class'=>'x2-select',
        )); 
    ?>
</div>
<div class="row">
    <div id="workflow-diagram">
        <?php
        // true = include dropdowns
        $workflowStatus = Workflow::getWorkflowStatus($currentWorkflow,$model->id,$modelName);    
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
