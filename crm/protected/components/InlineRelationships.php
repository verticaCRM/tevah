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
 * Widget class for the relationships form.
 *
 * Relationships lists the relationships a model has with other models,
 * and provides a way to add existing models to the models relationships.
 *
 * @package application.components 
 */
class InlineRelationships extends X2Widget {

	public $model = null;
	public $startHidden = false;
	public $modelName = "";
    public $moduleName = "";

    /**
     * Used to prepopulate create relationship forms
     * @var array (<model class> => <array of default values indexed by attr name>)
     */
    public $defaultsByRelatedModelType = array ();

	private $_relatedModels;

	public function init(){
		parent::init();
	}

    private function checkModuleUpdatePermissions () {
        $moduleName = '';
        if (is_object (Yii::app()->controller->module)) {
            $moduleName = Yii::app()->controller->module->name;
        } 
        $actionAccess = ucfirst($moduleName).'Update';
        $authItem = Yii::app()->authManager->getAuthItem($actionAccess);
        return (!isset($authItem) || Yii::app()->user->checkAccess($actionAccess, array(
            'X2Model' => $this->model
        )));
    }

	public function run(){
        $linkableModels = X2Model::getModelTypesWhichSupportRelationships(true);
        /* x2plastart */ 
		if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
            unset ($linkableModels['AnonContact']);
        }
        /* x2plaend */ 

        // used to instantiate html dropdown
        $linkableModelsOptions = $linkableModels;
        //array_walk ($linkableModelsOptions, function (&$val, $key) { $val = $key; });

        $modelsWhichSupportQuickCreate = 
            QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate ();

        // get create action urls for each linkable model
        $createUrls = QuickCreateRelationshipBehavior::getCreateUrlsForModels (
            $modelsWhichSupportQuickCreate);

        // get create relationship tooltips for each linkable model
        $tooltips = QuickCreateRelationshipBehavior::getDialogTooltipsForModels (
            $modelsWhichSupportQuickCreate, $this->modelName);

        // get create relationship dialog titles for each linkable model
        $dialogTitles = QuickCreateRelationshipBehavior::getDialogTitlesForModels (
            $modelsWhichSupportQuickCreate);

        $hasUpdatePermissions = $this->checkModuleUpdatePermissions ();

		$this->render('inlineRelationships', array(
			'model' => $this->model,
			'modelName' => $this->model->myModelName,
			'startHidden' => $this->startHidden,
            'moduleName' => $this->moduleName,
            'linkableModelsOptions' => $linkableModelsOptions,
            'dialogTitles' => $dialogTitles,
            'tooltips' => $tooltips,
            'createUrls' => $createUrls,
            'defaultsByRelatedModelType' => $this->defaultsByRelatedModelType,
            'modelsWhichSupportQuickCreate' => $modelsWhichSupportQuickCreate,
            'hasUpdatePermissions' => $hasUpdatePermissions
		));
	}


}

?>
