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

Yii::import('application.components.x2flow.X2FlowItem');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * @package application.controllers
 */
class StudioController extends x2base {
    // Declares class-based actions.

    public $modelClass = 'X2Flow';


    // public $layout = '//layouts/column1';
    public function filters() {
        return array(
            'setPortlets',
            //'accessControl',
        );
    }


    public function behaviors(){
        return array_merge (parent::behaviors () , 
            array(
                'ImportExportBehavior' => array('class' => 'ImportExportBehavior'),
            )
        );
    }


    public function actions() {
        if(file_exists(Yii::app()->getBasePath().'/components/FlowDesignerAction.php')) {
            return array(
                'flowDesigner'=>array(
                    'class'=>'FlowDesignerAction'
                ),
            );
        }
        return array();
    }


    public function actionFlowIndex() {
        $this->render('flowIndex');
    }

    public function actionTriggerLogs($pageSize=null) {
        $triggerLogsDataProvider = new CActiveDataProvider('TriggerLog', array(
                    'criteria' => array(
                        'order' => 'triggeredAt DESC'
                    ),
                    'pagination'=>array(
                        'pageSize' => !empty($pageSize) ?
                            $pageSize :
                            Profile::getResultsPerPage()
                    ),
                ));
        $viewParams['triggerLogsDataProvider'] = $triggerLogsDataProvider;
        $this->render('triggerLogs', array (
            'triggerLogsDataProvider' => $triggerLogsDataProvider
            )
        );
    }

    public function actionDeleteFlow($id) {
        $model = $this->loadModel($id);
        $model->delete();
        $this->redirect(array('flowIndex'));
    }

    public function actionTest() {
        echo CRYPT_SALT_LENGTH ;
        // var_dump($a instanceof X2Model);

        // $a = array();
        // $a = array(''=>Yii::t('studio','Custom')) + Docs::getEmailTemplates();
        // $a = Docs::getEmailTemplates();
        // var_dump($a);

        // $act = new X2FlowEmail;
        // $act->config = array (
            // 'type' => 'X2FlowEmail',
            // 'options' => array (
                // 'to' => 'me@x2engine.com',
                // 'from' => 'mpearson@x2engine.com',
                // 'template' => '',
                // 'subject' => 'Hey you!',
                // 'cc' =>'',
                // 'bcc' =>'',
                // 'body' => 'test test test test'
            // )
        // );
        // var_dump($act->execute($a));

        // $x = X2FlowTrigger::checkCondition(array('type'=>'time_of_day','operator'=>'<','value'=>'11:30'),$a);
        // var_dump($x);

        /* $triggerName = 'RecordDeleteTrigger';
        $params = array('model'=>new Contacts);

        $flowAttributes = array('triggerType'=>$triggerName);

        if(isset($params['model']))
            $flowAttributes['modelClass'] = get_class($params['model']);

        $results = array();

        // find all flows matching this trigger and modelClass
        foreach(CActiveRecord::model('X2Flow')->findAllByAttributes($flowAttributes) as $flow) {
            // file_put_contents('triggerLog.txt',"\n".$triggerName,FILE_APPEND);
            $flowData = CJSON::decode($flow->flow);    // parse JSON flow data


            if($flowData !== false && isset($flowData['trigger']['type'],$flowData['items'][0]['type'])) {

                $trigger = X2FlowTrigger::create($flowData['trigger']);

                if($trigger === null || !$trigger->validateRules($params) || !$trigger->check($params))
                    return;
                // var_dump($trigger->check($params));
                $results[] = array($flow->name,$flow->executeBranch($flowData['items'],$params));
            }
        }
        var_dump($results); */
    }

    public function actionGetParams($name,$type) {
        if($type === 'action') {
            $paramRules = X2FlowAction::getParamRules($name);    // X2Flow Actions
        } elseif($type === 'trigger') {
            $paramRules = X2FlowTrigger::getParamRules($name);    // X2Flow Triggers
        } elseif($type === 'condition') {
            // generic conditions (for triggers and switches)
            $paramRules = X2FlowTrigger::getGenericCondition($name); 
        } else {
            $paramRules = false;
        }

        if($paramRules !== false) {
            if($type === 'condition') {
                if(isset($paramRules['options']))
                    $paramRules['options'] = AuxLib::dropdownForJson($paramRules['options']);
            } else {
                foreach($paramRules['options'] as &$option) {    // find any dropdowns and reformat them
                    if(isset($option['options']))                // so the item order is preserved in JSON
                        $option['options'] = AuxLib::dropdownForJson($option['options']);
                }
                // do the same for suboptions, if they're present
                if (isset ($paramRules['suboptions'])) {
                    foreach($paramRules['suboptions'] as &$subOption) {
                        if(isset($subOption['options']))        
                            $subOption['options'] = AuxLib::dropdownForJson(
                                $subOption['options']);
                    }
                }
            }
        }
        echo CJSON::encode($paramRules);
    }

    // reports TODO
    public function actionGetFields($model) {
        if(!class_exists($model)) {
            echo 'false';
            return;
        }
        $fieldModels = X2Model::model($model)->getFields();
        $fields = array();

        foreach($fieldModels as &$field) {
            if($field->isVirtual)
                continue;
            $data = array(
                'name' => $field->fieldName,
                'label' => $field->attributeLabel,
                'type' => $field->type,
            );

            if($field->required)
                $data['required'] = 1;
            if($field->readOnly)
                $data['readOnly'] = 1;
            if($field->type === 'assignment' || $field->type === 'optionalAssignment' ) {
                $data['options'] = AuxLib::dropdownForJson(X2Model::getAssignmentOptions(true, true));
            } elseif($field->type === 'dropdown') {
                $data['linkType'] = $field->linkType;
                $data['options'] = AuxLib::dropdownForJson(Dropdowns::getItems($field->linkType));
            }

            if($field->type === 'link') {
                $staticLinkModel = X2Model::model($field->linkType);
                if(array_key_exists('X2LinkableBehavior', $staticLinkModel->behaviors())) {
                    $data['linkType'] = $field->linkType;
                    $data['linkSource'] = Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource);
                }
            }


            $fields[] = $data;
        }
        echo CJSON::encode($fields);
    }

    public function actionDeleteAllTriggerLogs ($flowId) {
        if (isset ($flowId)) {
            $triggerLogs = TriggerLog::model()->findAllByAttributes (array (
                'flowId' => $flowId
            ));
            foreach ($triggerLogs as $log) {
                $log->delete ();
            }
            echo "success";
        } else {
            echo "failure";
        }
    }

    public function actionDeleteAllTriggerLogsForAllFlows () {
        $triggerLogs = TriggerLog::model()->findAll ();
        foreach ($triggerLogs as $log) {
            $log->delete ();
        }
        echo "success";
    }

    public function actionDeleteTriggerLog ($id) {
        if (isset ($id)) {
            $triggerLog = TriggerLog::model()->findByAttributes (array (
                'id' => $id
            ));
            if (!empty ($triggerLog)) {
                $triggerLog->delete ();
                echo "success";
                return;
            }
        }
        echo "failure";
    }

    /* x2plastart */ 
    /**
     * Simple validation function for imported flow. A more sophisticated validation method
     * is needed but this, at least, ensures that the flow can be saved.
     * @param string $flow Decoded imported flow file contents
     * @return bool
     */
    public function validateImportedFlow ($flow) {
        if (!is_array ($flow) ||
            !isset ($flow['flowName']) ||
            !isset ($flow['trigger']) ||
            !is_array ($flow['trigger']) ||
            !isset ($flow['trigger']['type']))  {

            return false;
        }
        return true;
    }

    /**
     * Import a flow which was exported with the flow export tool 
     */
    public function actionImportFlow () {
        $model = null;
        if (isset ($_FILES['flowImport'])) {
            if (AuxLib::checkFileUploadError ('flowImport')) {
                throw new CException (
                    AuxLib::getFileUploadErrorMessage ($_FILES['flowImport']['error']));
            }
            $fileName = $_FILES['flowImport']['name'];
            $ext = pathinfo ($fileName, PATHINFO_EXTENSION);
            if ($ext !== 'json') {
                throw new CException (Yii::t('studio', 'Invalid file type'));
            }
            $data = file_get_contents($_FILES['flowImport']['tmp_name']);
           
            $flow = CJSON::decode ($data);
            if ($this->validateImportedFlow ($flow)) {
                $model = new X2Flow;
                $model->name = $flow['flowName'];
                $model->triggerType = $flow['trigger']['type'];
                $model->flow = CJSON::encode ($flow);
                $model->active = false;
                if ($model->save ()) {
                    $this->redirect(
                        $this->createUrl ('/studio/flowDesigner', array ('id' => $model->id)));
                } 
            }
            Yii::app()->user->setFlash ('error', Yii::t('studio', 'Invalid file contents'));
        }

        $this->render ('importFlow', array (
            'model' => $model,
        ));
    }

    /**
     * Exports flow json as .json file and provides a download link 
     */
    public function actionExportFlow ($flowId) {
        $flowId = $_GET['flowId'];
        $flow = X2Flow::model()->findByPk ($flowId);
        $download = false;
        $_SESSION['flowExportFile'] = '';
        if (isset ($_GET['export'])) {
            $flowJSON = $flow->flow; 
            $file = 'flow.json'; 
            $filePath = $this->safePath($file);
            file_put_contents ($filePath, $flowJSON);
            $_SESSION['flowExportFile'] = $file;
            $download = true;
        } 
        $this->render ('exportFlow', array (
            'flow' => $flow,
            'download' => $download
        ));
    }
    /* x2plaend */ 

}
