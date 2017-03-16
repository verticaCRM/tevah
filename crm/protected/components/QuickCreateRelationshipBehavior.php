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
 * Provides utility methods for handling quick creation of records and relationships. 
 * This class involves the use of two models:
 *  The model associated with the owner of this behavior (referred to as 'the first model') and 
 *  the model associated with the view from which the quick create ajax request was made 
 *  (referred to as 'the second model').
 *
 * @package application.components
 */
class QuickCreateRelationshipBehavior extends CBehavior {

    /**
     * Used to specify which attributes (for a given model type) should be updated to match
     * the first model's attribute values. 
     * @var array (<model type> => <array of attributes in second model indexed by attributes in 
     *  the first model>)
     */
    public $attributesOfNewRecordToUpdate = array ();

    protected $inlineFormPathAlias = 'application.components.views._form'; 

    private static $_modelsWhichSupportQuickCreate;

    /**
     * Returns an array of all model classes (associated with some module) which have this
     * behavior
     *
     * @return <array of strings>
     */
    public static function getModelsWhichSupportQuickCreate ($includeActions=false) {
        if (!isset (self::$_modelsWhichSupportQuickCreate)) {
            self::$_modelsWhichSupportQuickCreate = array_diff (
                array_keys (X2Model::getModelNames()), 
                    array ('Docs', 'Groups', 'Campaign', 'Media', 'Quote',
                        'BugReports'));
            self::$_modelsWhichSupportQuickCreate[] = 'Actions';
        }
        $modelNames = self::$_modelsWhichSupportQuickCreate;
        if (!$includeActions) {
            array_pop ($modelNames);
        }
        return $modelNames;
    }

    /**
     * @param array $models
     * @return array of urls for create actions of each model in $models 
     */
    public static function getCreateUrlsForModels ($models) {
        $createUrls = array_flip ($models);
        array_walk (
            $createUrls,
            function (&$val, $key) {
                $moduleName = lcfirst (X2Model::getModuleName ($key));
                $val = Yii::app()->controller->createUrl ("/$moduleName/$moduleName/create");
            });
        return $createUrls;
    }

    /**
     * Returns array of dialog titles to be used for quick create dialogs for each model 
     * @param array $models
     * @return array
     */
    public static function getDialogTitlesForModels ($models) {
        // get create relationship dialog titles for each linkable model
        $dialogTitles = array_flip ($models);
        array_walk (
            $dialogTitles,
            function (&$val, $key) {
                $val = Yii::t('app', 
                    'Create {relatedModelClass}', 
                    array ('{relatedModelClass}' => ucfirst (X2Model::getRecordName ($key))));
            });
        return $dialogTitles;
    }

    /**
     * Returns array of tooltips to be applied to quick create buttons for each model 
     * @param array $models
     * @param string $modelName
     * @return array
     */
    public static function getDialogTooltipsForModels ($models, $modelName) {
        $tooltips = array_flip ($models);
        array_walk (
            $tooltips,
            function (&$val, $key) use ($modelName) {
                $val = Yii::t('app', 
                    'Create a new {relatedModelClass} associated with this {modelClass}', 
                    array (
                        '{relatedModelClass}' => X2Model::getRecordName ($key), 
                        '{modelClass}' => 
                            X2Model::getRecordName (X2Model::getModelName ($modelName))
                    )
                );
            });
        return $tooltips;
    }

    /**
     * For controllers implementing this behavior, this method should be called if the GET parameter
     * 'x2ajax' is set to '1' after the model is created and fields are set. 
     * 
     * If called from the record create page:
     *  No record exists yet for the second model. An array is echoed containing values of the 
     *  first model which should be used to populate fields in the create form of the second model.
     *
     * If called from the record view page:
     *  Attempts to create a new relationship between first and second models.
     *  If creation of new record is successful and if the second model has been updated, 
     *  an updated detailView of the second model is returned.
     *
     *  If the first record could not be created, the create form is rendered again with errors.
     * 
     * @return bool true if errors were encountered, false otherwise
     */
    public function quickCreate ($model) {
        Yii::app()->clientScript->scriptMap['*.css'] = false;

        $errors = false;

        if (isset ($_POST['validateOnly'])) return;

        if ($model->save ()) {
            if (isset ($_POST['ModelName'])) {
                $secondModelName = $_POST['ModelName']; 
            }
            if (!empty ($_POST['ModelId'])) {
                $secondModelId = $_POST['ModelId']; 
            }

            if (isset ($secondModelName) && !empty ($secondModelId)) {
                $secondModel = $this->quickCreateRelationship (
                    $model, get_class ($model), $model->id, $secondModelName, $secondModelId);
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'data' => ($secondModel ? $this->owner->getDetailView ($secondModel) : ''),
                        'name' => $model->name,
                        'id' => $model->id,
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else if (isset ($secondModelName)) {
                $data = $this->getValuesOfNewRecordToUpdate ($model, $secondModelName);
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'data' => $data,
                        'name' => $model->name,
                        'id' => $model->id,
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else if (isset ($_POST['quickCreateOnly']) && $_POST['quickCreateOnly']) {
                $model->refresh ();
                $modelClass = get_class ($model);
                $modelLink = ($modelClass === 'Actions' ? $model->getLink (30, false) : $model->getLink());
                echo CJSON::encode (
                    array (
                        'status' => 'success',
                        'message' => Yii::t('app', '{recordType} created: {link}', array (
                            '{recordType}' => $modelClass,
                            '{link}' => $modelLink,
                        )),
                        'attributes' => $model->getVisibleAttributes (),
                    ));
            } else {
                throw new CHttpException (400, Yii::t ('app', 'Bad Request'));
            }

            Yii::app()->end();
        } else {
            $errors = true;
        }

        return $errors;
    }

    /**
     * Renders an inline record create/update form
     * @param object $model 
     * @param bool $hasErrors
     */
    public function renderInlineForm ($model, $hasErrors, array $viewParams = array ()) {
        if ($hasErrors) {
            $page = $this->owner->renderPartial(
                $this->inlineFormPathAlias,
                array_merge (array(
                    'model' => $model,
                    'modelName' => strtolower (get_class ($model)),
                    'suppressQuickCreate' => true,
                ), $viewParams), true, true);
            echo json_encode(
                array(
                    'status' => 'userError',
                    'page' => $page,
                ));
        } else {
            $this->owner->renderPartial(
                $this->inlineFormPathAlias,
                array_merge (array(
                    'model' => $model, 
                    'modelName' => strtolower (get_class ($model)),
                    'suppressQuickCreate' => true,
                ), $viewParams), false, true);
        }

    }

    /**
     * Returns an associative array of values of the first model indexed by attribute
     * names in the second model.
     * @return array (<name of attribute to modify => <value of attribute in new record>)
     */
    private function getValuesOfNewRecordToUpdate ($firstModel, $secondModelName) {
        $attributesToUpdate = (isset ($this->attributesOfNewRecordToUpdate[$secondModelName]) ? 
            $this->attributesOfNewRecordToUpdate[$secondModelName] : array ());

        $data = array ();
        foreach ($attributesToUpdate as $firstModelAttr => $secondModelAttr) {
            if (isset ($firstModel->$firstModelAttr)) {
                $data[$secondModelAttr] = $firstModel->$firstModelAttr;
            }
        }

        return $data;
    }

    /**
     * Creates a new relationship and then, based on the value of attributesOfNewRecordToUpdate,
     * sets values of the second model using values of the first model.
     * Returns an array of the values that were changed indexed by the attribute name.
     * @param object $firstModel 
     * @param string $firstModelNamethe class name of the first model
     * @param string $firstModelId the id of the first model
     * @param string $secondModelName the class name of the second model
     * @param string $secondModelId the id of the second model 
     * @return mixed false if the second model isn't updated, the second model otherwise
     */
    private function quickCreateRelationship (
        $firstModel, $firstModelName, $firstModelId, $secondModelName, $secondModelId) {

        $success = Relationships::create (
            $firstModelName, $firstModelId, $secondModelName, $secondModelId);

        $attributesToUpdate = (isset ($this->attributesOfNewRecordToUpdate[$secondModelName]) ? 
            $this->attributesOfNewRecordToUpdate[$secondModelName] : array ());

        $secondModel = $secondModelName::model ()->findByPk ($secondModelId);

        if ($secondModel) {
            $changed = false;

            /* 
            Set values of existing record to values of newly created record based on mapping
            configured in $attributesOfNewRecordToUpdate
            */
            foreach ($attributesToUpdate as $firstModelAttr => $secondModelAttr) {
                
                if (isset ($firstModel->$firstModelAttr) &&
                    (!isset ($secondModel->$secondModelAttr) || 
                     $secondModel->$secondModelAttr === '')) {

                    $secondModel->$secondModelAttr = $firstModel->$firstModelAttr;

                    $changed = true;
                }
            }

            if ($changed) {
                $secondModel->update ();
            }
        }

        if ($secondModel && $changed) return $secondModel;
        else return false;
    }

    /**
     * Alias for {@link renderInlineForm} preserved for backwards compatibility with 
     * TemplatesController.
     */
    public function renderInlineCreateForm ($model, $hasErrors) {
        $this->renderInlineForm ($model, $hasErrors);
    }

}
?>
