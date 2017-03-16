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
 * Displays the details of a workflow stage.
 * 
 * @package application.components 
 */
class WorkflowStageDetails extends X2Widget {
    public $model;
    public $modelName;
    public $currentWorkflow;


    public function init() {

        Yii::app()->clientScript->registerScriptFile(
            $this->module->assetsUrl.'/js/WorkflowManagerBase.js'); 
        Yii::app()->clientScript->registerScriptFile(
            $this->module->assetsUrl.'/js/WorkflowManager.js'); 
        
        Yii::app()->clientScript->registerScript('workflowDialog_'.$this->id,'
    
            x2.workflowManager = new x2.WorkflowManager ({
                translations: '.CJSON::encode (array (
                    'Comment Required' => Yii::t('workflow', 'Comment Required'),
                    'Stage {n}' => Yii::t('workflow', 'Stage {n}'),
                    'Save' => Yii::t('app', 'Save'),
                    'Edit' => Yii::t('app', 'Edit'),
                    'Cancel' => Yii::t('app', 'Cancel'),
                    'Close' => Yii::t('app', 'Close'),
                    'Submit' => Yii::t('app', 'Submit'),
                )).',
                modelId: '.$this->model->id.',
                modelName: "'.$this->modelName.'",
                startStageUrl: "'.
                    CHtml::normalizeUrl(array('/workflow/workflow/startStage')).
                '",
                revertStageUrl: "'.
                    CHtml::normalizeUrl(array('/workflow/workflow/revertStage')).
                '",
                getStageDetailsUrl: "'.
                    CHtml::normalizeUrl(array('/workflow/workflow/getStageDetails')).
                '",
                completeStageUrl: "'.
                    CHtml::normalizeUrl(array('/workflow/workflow/completeStage')).
                '"
            });

        ',CClientScript::POS_END);
        

        parent::init();
    }

    public function run() {
        $this->render(
            'application.modules.workflow.components.views._workflow',
            array(
                'model'=>$this->model,'modelName'=>$this->modelName,
                'currentWorkflow'=>$this->currentWorkflow
            )
        );
    }
}
