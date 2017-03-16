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
<div id='per-stage-view-container-first' class='x2-layout-island x2-layout-island-merge-top-bottom'>
    <?php
    $this->renderFunnelView (
        $model->id, $dateRange, $expectedCloseDateDateRange, $users, null, $modelType);
    //$workflowStatus = Workflow::getWorkflowStatus($model->id, 0, $modelType);	// true = include dropdowns
    //echo Workflow::renderWorkflowStats($workflowStatus, $modelType);
    ?>
    <?php
    $this->renderPartial ('_processStatus', array (
        'dateRange' => $dateRange,
        'expectedCloseDateDateRange' => $expectedCloseDateDateRange,
        'model' => $model,
        'modelType' => $modelType,
        'users' => $users,
    ));
    ?>
</div>
<div id="workflow-gridview" class='x2-layout-island x2-layout-island-merge-top' 
 style="clear:both;">
    <?php
    if(isset($viewStage)){ // display grids for individual stages
    	echo Yii::app()->controller->getStageMembers(
            $model->id,
            $viewStage,
            Formatter::formatDate($dateRange['start']),
            Formatter::formatDate($dateRange['end']),
            $dateRange['range'],
            Formatter::formatDate($expectedCloseDateDateRange['start']),
            Formatter::formatDate($expectedCloseDateDateRange['end']),
            $expectedCloseDateDateRange['range'],
            $users,
            $modelType
        );
    }else { // display information about all stages
    $this->widget('zii.widgets.grid.CGridView', array(
    	// 'id'=>'docs-grid',
    	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
            '/css/gridview',
    	'template'=> '{items}{pager}',
    	'dataProvider'=>X2Model::model('WorkflowStage')->search($model->id),
    	// 'filter'=>$model,
    	'columns'=>array(
    		array(
    			'name'=>'stageNumber',
    			'header'=>'#',
    			'headerHtmlOptions'=>array('style'=>'width:8%;'),
    		),
    		array(
    			'name'=>'name',
    			// 'value'=>'CHtml::link($data->title,array("view","id"=>$data->name))',
    			'type'=>'raw',
    			// 'htmlOptions'=>array('width'=>'30%'),
    		),
    		array(
    			'name'=>'requirePrevious',
    			'value'=>'Yii::t("app",($data->requirePrevious? "Yes" : "No"))',
    			'type'=>'raw',
    			'headerHtmlOptions'=>array('style'=>'width:15%;'),
    		),
    		array(
    			'name'=>'requireComment',
    			'value'=>'Yii::t("app",($data->requireComment? "Yes" : "No"))',
    			'type'=>'raw',
    			'headerHtmlOptions'=>array('style'=>'width:15%;'),
    		),
    		array(
    			'name'=>'conversionRate',
    			// 'value'=>'User::getUserLinks($data->createdBy)',
    			// 'type'=>'raw',
    			'headerHtmlOptions'=>array('style'=>'width:15%;'),
    		),
    		array(
    			'name'=>'value',
    			// 'value'=>'User::getUserLinks($data->createdBy)',
    			// 'type'=>'raw',
    			'headerHtmlOptions'=>array('style'=>'width:15%;'),
    		),
    	),
    ));
    }
    ?>
</div>
