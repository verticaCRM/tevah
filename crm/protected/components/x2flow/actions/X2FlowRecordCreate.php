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
 * X2FlowAction that creates a new record
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordCreate extends X2FlowAction {
	public $title = 'Create Record';
	public $info = '';

	public function paramRules() {
		return array(
			'title' => $this->title,
			'modelClass' => 'modelClass',
			'options' => array(
				array('name'=>'attributes'),
				array(
                    'name'=>'modelClass','label'=>Yii::t('studio','Record Type'),
                    'type'=>'dropdown',
                    'options'=>X2Flow::getModelTypes(true)
                ),
				array(
                    'name'=>'createRelationship',
                    'label' => 
                        Yii::t('studio', 'Create Relationship'). 
                        '<span class="x2-hint" title="'.
                        Yii::t('app', 'Check this box if you want a new relationship to be '.
                        'established between the record created by this action and the record that'.
                        ' triggered the flow.').'">&nbsp;[?]</span>', 
                    'type'=>'boolean',
                    'defaultVal' => false,
                ),
            ),
            /*'suboptions' => array (
                array (
                    'name' => 'dependency',
                    'dependentOn' => 'createRelationship',
                ),
				array(
                    'name'=>'relatedModelClass',
                    'label'=>Yii::t('studio','Related Record Type'),
                    'type'=>'dropdown',
                    'optional' => true,
                    'defaultVal' => 'Accounts',
                    'options'=>X2Model::getModelTypesWhichSupportRelationships (true),
                ),
				array(
                    'name'=>'relatedModelName',
                    'label'=>Yii::t('studio', 'Related Record Name'),
                    'type'=>'dependentAutocomplete',
                    'linkSource'=>Yii::app()->createUrl(
					    CActiveRecord::model('Accounts')->autoCompleteSource),
                    'getAutoCompleteUrl'=>Yii::app()->createUrl(
                        '/studio/studio/ajaxGetModelAutocomplete', 
                        array ('modelType' => 'Accounts')
                    ),  
                    'dependency' => 'relatedModelClass',
                    'optional'=>1,
                ),
			),*/
		);
	}

	public function execute(&$params) {
        // make sure this is a valid model type
		if(!is_subclass_of($this->config['modelClass'],'X2Model'))	
			return array (false, "");
		if(!isset($this->config['attributes']) || empty($this->config['attributes']))
			return array (false, "");

        // verify that if create relationship option was set, that a relationship can be made
        if($this->parseOption('createRelationship', $params)) {
            $acceptedModelTypes = X2Model::getModelTypesWhichSupportRelationships ();

            if (!in_array ($this->config['modelClass'], $acceptedModelTypes)) {
                return array (false, Yii::t('x2flow', 'Relationships cannot be made with records '.
                    'of type {type}.', array ('{type}' => $this->config['modelClass'])));
            }
            if (!isset ($params['model'])) { // no model passed to trigger
                return array (false, '');
            }
            if (!in_array (get_class ($params['model']), $acceptedModelTypes)) {
                return array (false, Yii::t('x2flow', 'Relationships cannot be made with records '.
                    'of type {type}.', array ('{type}' => get_class ($params['model']))));
            }
        }

		$model = new $this->config['modelClass'];
        $model->setScenario ('X2FlowCreateAction');
        if ($this->setModelAttributes($model,$this->config['attributes'],$params) && 
            $model->save()) {

            if($this->parseOption('createRelationship', $params)) {
                Relationships::create (
                    get_class($params['model']), $params['model']->id, 
                    get_class ($model), $model->id);
            }

            return array (
                true,
                Yii::t('studio', 'View created record: ').$model->getLink ());
        } else {
            $errors = $model->getErrors ();
            return array(false, array_shift($errors));
        }
	}
}
