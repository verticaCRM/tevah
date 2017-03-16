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

/* @edition:pro */

class MassUpdateFields extends MassAction {

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        $editableFieldsFieldInfo = FormLayout::model ()->getEditableFieldsInLayout ($modelName);
        asort ($editableFieldsFieldInfo, SORT_STRING);
        echo "
            <div class='mass-action-dialog x2-gridview-update-field-dialog' 
            id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span class='dialog-help-text'>".
                    Yii::t('app', 'Select a field and enter a field value')."
                </span><br/>
                <div class='update-fields-inputs-container'>";
        if (sizeof ($editableFieldsFieldInfo) !== 0) {
            echo "
                <select class='update-field-field-selector left'>";
            foreach ($editableFieldsFieldInfo as $fieldName=>$attrLabel) {
                echo "
                    <option value='".CHtml::encode ($fieldName)."'>".
                        CHtml::encode ($attrLabel)."</option>";
            }
            echo "
                </select>
                <span class='update-fields-field-input-container'>";
            $fieldNames = array_keys ($editableFieldsFieldInfo);
            echo X2Model::model ($modelName)->renderInput ($fieldNames[0]);
            echo "
                <br/><br/>
                </span>";
        }
        echo "
                </div>
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Update fields of selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2UpdateFields' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassUpdateFields.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if (!isset ($_POST['fields'])) {
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $fields = $_POST['fields'];

        $modelType = X2Model::Model (Yii::app()->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated.', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? 
                    Yii::t('app','The record could not be found.') : 
                    Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }

            if (isset($fields['associationType']) && isset($fields['associationName']) && 
                $fields['associationType'] !== 'none') {

                // If we are setting an association, lookup the association id
                $attributes = array('name' => $fields['associationName']);
                $associatedModel = X2Model::Model($fields['associationType'])
                    ->findByAttributes($attributes);
                $fields['associationId'] = $associatedModel->id;
            }

            $model->setX2Fields($fields);
            if (!$model->save()) {
                $errors = $model->getAllErrorMessages();
                foreach ($errors as $err) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be updated: '.$err,
                        array ('{recordId}' => $recordId)
                    );
                }
                continue;
            }
            $updatedRecordsNum++;
        }
        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                    ' updated', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        }

        return $updatedRecordsNum;

    }

}
