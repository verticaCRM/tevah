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
 * @package application.components.sortableWidget 
 */
class WorkflowStageDetailsWidget extends SortableWidget {

    public $model;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{workflowSelector}{closeButton}{minimizeButton}</div>{widgetContents}';

    public $viewFile = '_workflowStageDetailsWidget';

    protected $_module;

    private static $_JSONPropertiesStructure;

    private $_currentWorkflow;

    private $_associationType;

    public function getAssociationType () {
        if (!isset ($this->_associationType)) {
            $this->_associationType = X2Model::getAssociationType (get_class ($this->model));
        }
        return $this->_associationType;
    }

    public function getModule () {
        if (!isset ($this->_module)) {
            $this->_module = Yii::app()->getModule ('workflow');
        }
        return $this->_module;
    }

    public function getCurrentWorkflow () {
        if (!isset ($this->_currentWorkflow)) {
            $this->_currentWorkflow = $this->controller->getCurrentWorkflow(
                $this->model->id, $this->getAssociationType ());
        }
        return $this->_currentWorkflow;
    }

    public function renderWorkflowSelector () {
        $workflowList = Workflow::getList();
        echo CHtml::dropDownList('workflowId',$this->getCurrentWorkflow (),$workflowList,    
            array(
                'ajax' => array(
                    'type'=>'GET', //request type
                    'url'=>CHtml::normalizeUrl( //url to call.
                        array(
                            '/workflow/workflow/getWorkflow',
                            'modelId'=>$this->model->id,
                            'type'=>$this->getAssociationType ()
                        )
                    ), 
                    'update'=>'#workflow-diagram', //selector to update
                    'data'=>array('workflowId'=>'js:$(this).val()')
                    //leave out the data key to pass all form values through
                ),
                'id'=>'workflowSelector',
                'class'=>'x2-select',
            )); 

    }

    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            $this->_setupScript = "
                $(function () {
                    x2.".$widgetClass.$this->widgetUID." = new x2.WorkflowStageDetailsWidget ({
                        'widgetClass': '".$widgetClass."',
                        'setPropertyUrl': '".Yii::app()->controller->createUrl (
                            '/profile/setWidgetSetting')."',
                        'cssSelectorPrefix': '".$this->widgetType."',
                        'widgetType': '".$this->widgetType."',
                        'widgetUID': '".$this->widgetUID."'
                    });
                });
            ";
        }
        return $this->_setupScript;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'currentWorkflow'=> $this->getCurrentWorkflow (),
                    'model' => $this->model,
                )
            );
        }
        return $this->_viewFileParams;
    } 

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Process',
                    'hidden' => false,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }


    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'WorkflowStageDetailsWorkflowJS' => array(
                        'baseUrl' => $this->module->assetsUrl,
                        'js' => array(
                            'js/WorkflowManagerBase.js',
                            'js/WorkflowManager.js',
                        ),
                        'depends' => array ('auxlib')
                    ),
                    'WorkflowStageDetailsWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/sortableWidgets/WorkflowStageDetailsWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                    'WorkflowStageDetailsWidgetCSS' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl (),
                        'css' => array(
                            'css/workflowFunnel.css',
                        )
                    ),
                )
            );
            if (AuxLib::isIE8 ()) {
                $this->_packages['WorkflowExcanvas'] = array (
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/jqplot/excanvas.js',
                    ),
                );
            }
        }
        return $this->_packages;
    }

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
                modelName: "'.$this->getAssociationType ().'",
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

    protected function getCss () {
        if (!isset ($this->_css)) {
            $this->_css = array_merge (
                parent::getCss (),
                array (
                'WorkflowStageDetailsWidgetCSS' => "

                    #workflowSelector {
                        margin-left: 13px;
                    }

                    #funnel-container {
                        position: relative;
                        width: auto;
                        margin-left: 12px;
                        margin-top: 9px;
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
                ")
            );
        }
        return $this->_css;
    }


}
