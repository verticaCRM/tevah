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

class X2ReportForm extends X2ReportActiveFormBase {

    /**
     * @var $id
     */
    public $id = 'report-settings'; 

    /**
     * Id of report container element
     * @var string $reportContainerSelector
     */
    public $reportContainerId = 'report-container'; 

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'ReportForm'; 

    /**
     * @var string $_primaryModelTypeDropdownId
     */
    private $_primaryModelTypeDropdownId = 'primary-model-type'; 

    /**
     * @param array 
     */
    public function getJSClassConstructorArgs () {
        return array_merge(parent::getJSClassConstructorArgs (), array(
            'reportContainerSelector' => '#'.$this->reportContainerId,
            'settingsFormSelector' => '#'.$this->id,
            'primaryModelTypeDropDownSelector' => '#'.$this->_primaryModelTypeDropdownId,
            'type' => $this->formModel->getReportType (),
            'translations' => array (
                'savedSettingsDialogTitle' => Yii::t('reports', 'Save Report'),
                'copyReportDialogTitle' => Yii::t('reports', 'Copy Report'),
                'cancel' => Yii::t('reports', 'Cancel'),
                'saveButton' => Yii::t('reports', 'Save'),
                'copy' => Yii::t('reports', 'Copy'),
                'proceedAnyway' => Yii::t('reports', 'Proceed Anyway'),
                'unsavedSettingsWarning' => 
                    Yii::t('reports', 'You have unsaved report settings which will be lost.'),
                ),
            )
        );
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge( parent::getPackages(), array(
                'X2ReportFormJS' => array(
                    'baseUrl' => Yii::app()->controller->module->assetsUrl,
                    'js' => array(
                        'js/X2ReportForm.js',
                    ),
                    'depends' => array ('X2FormJS'),
                ),
                // 'ChartCreatorJS' => array(
                //     'baseUrl' => Yii::app()->controller->module->assetsUrl,
                //     'js' => array(
                //         'js/ChartCreator.js',
                //     ),
                //     'depends' => array ('X2ReportForm'),
                // ),
            ));
        }
        return $this->_packages;
    }

    protected function registerJSClassInstantiationScript () {

        Yii::app()->clientScript->registerScript(
            $this->getId ().'registerJSClassInstantiationScript', "
        
        (function () {     
            x2.reportForm = new x2.$this->JSClass (".
                CJSON::encode ($this->getJSClassConstructorArgs ()).
            ");
        }) ();
        ", CClientScript::POS_END);
    }

    /**
     * @param CModel $model 
     */
    public function primaryModelTypeDropDown (CModel $model) {
        $criteria = new CDbCriteria;
        $criteria->addCondition ('name="actions"', 'OR');
        $primaryModelNames = X2Model::getModelNames ($criteria);
        return $this->dropDownList ($model, 'primaryModelType', $primaryModelNames, array (
            'id' => $this->_primaryModelTypeDropdownId
        ));
    }

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param X2Model $model used to populate condition list attribute options
     */
    public function filterConditionList (
        CModel $formModel, $name, array $htmlOptions = array (), $attributes=null) {
        CHtml::resolveNameID ($formModel, $name, $htmlOptions);

        $primaryModelType = $formModel->primaryModelType;
        $primaryModel = $primaryModelType::model ();
        $value = $formModel->$name;
        foreach ($value as &$val) {
            list ($model, $attr, $fns, $linkField) = $formModel->getModelAndAttr ($val['name']);
            $field = $model->getField ($val['name']);
            if ($field) {
                if ($field->type === 'date') {
                    $val['value'] = Formatter::formatDate ($val['value'], 'medium');
                } elseif ($field->type === 'dateTime') {
                    $val['value'] = Formatter::formatDateTime  ($val['value']);
                } 
            }
        }

        return $this->widget ('X2ConditionList', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'value' => $value,
            'model' => X2Model::model ($formModel->primaryModelType),
            'useLinkedModels' => true,
            'attributes' => $attributes,
        ), true);
    }

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param array $options attribute options for pill box dropdown
     */
    public function attributePillBox (CModel $formModel, $name, array $options,
        array $htmlOptions = array ()) {

        if ($formModel->hasErrors($name)) X2Html::addErrorCss ($htmlOptions);

        CHtml::resolveNameID ($formModel, $name, $htmlOptions);
        return $this->widget ('X2PillBox', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'htmlOptions' => $htmlOptions,
            'optionsHeader' => Yii::t('reports', 'Select an attribute:'),
            'value' => $formModel->$name,
            'translations' => array (
                'delete' => Yii::t('reports', 'Delete attribute'),
            ),
            'options' => $options,
        ), true);
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array())
	{
        /* x2modstart */ 
        // swapped CHtml for X2Html
		return X2Html::activeDropDownList($model,$attribute,$data,$htmlOptions);
        /* x2modend */ 
	}

    /**
     * @param CModel $formModel
     * @param string $name name of input
     * @param array $options attribute options for pill box dropdown
     */
    public function sortByAttrPillBox (CModel $formModel, $name, array $options,
        array $htmlOptions = array ()) {

        if ($formModel->hasErrors($name)) X2Html::addErrorCss ($htmlOptions);

        CHtml::resolveNameID ($formModel, $name, $htmlOptions);
        return $this->widget ('SortByPillBox', array (
            'id' => $htmlOptions['id'],
            'name' => $htmlOptions['name'],
            'optionsHeader' => Yii::t('reports', 'Select an attribute:'),
            'value' => $formModel->$name,
            'htmlOptions' => $htmlOptions,
            'translations' => array (
                'delete' => Yii::t('reports', 'Delete attribute'),
                'ascending' => Yii::t('reports', 'ascending'),
                'descending' => Yii::t('reports', 'descending'),
            ),
            'options' => $options,
        ), true);
    }

    /**
     * Renders report generation submit button 
     */
    public function generateReportButton () {
        echo "<button type='submit' class='x2-button'>".
            Yii::t('reports', 'Generate')."</button>";
    }


}

?>
