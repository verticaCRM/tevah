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

Yii::import('application.modules.reports.components.reports.*');

/**
 * This is the model class for table "x2_reports".
 *
 * The followings are the available columns in table 'x2_reports':
 */
class Reports extends X2Model {

    /**
     * @var string $settings JSON-encoded report form model attributes
     */
    public $settings; 

    /**
     * @var string $type 'rowsAndColumns'|'grid'|'summation'
     */
    public $type; 

    /**
     * @var string $version the version of X2Engine in which the report was saved
     */
    public $version; 

    public static function model ($className=__CLASS__) {
        return parent::model ($className);
    }

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'x2_reports_2';
	}

    public function rules () {
        $rules = parent::rules ();
        return array_merge (array (
            array (
                'settings',
                'application.components.validators.ArrayValidator',
                'throwExceptions' => true,
                'allowEmpty' => false,
            ),
            array (
                'settings', 'validateSettings'
            ),
        ), $rules);
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array (
            'WidgetLayoutJSONFieldsBehavior' => array(
                'class' => 'application.components.WidgetLayoutJSONFieldsBehavior', 
                'transformAttributes' => array (
                    'dataWidgetLayout' => SortableWidget::DATA_WIDGET_PATH_ALIAS
                ) 
            ) ,
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
        )); 
    }

    public function relations() {
        $rules = parent::relations ();
        return array_merge (array (
            'charts' => array(self::HAS_MANY, 'Charts', 'reportId'),
        ), $rules);

    }

    public function validateSettings ($attribute) {
        $value = $this->$attribute;
        if (count ($value) > 1) {
            return false;
        }
        $keys = array_keys ($value);
        $formModelName = array_pop ($keys);
        if (!in_array ($formModelName, 
            array ('SummationReportFormModel', 'RowsAndColumnsReportFormModel', 
                'GridReportFormModel'))) {

            return false;
        }
        $formModel = new $formModelName;
        $formModel->setAttributes ($value[$formModelName]);
        if (!$formModel->validate ()) {
            //AuxLib::debugLogR ($formModel->getErrors ());
            $this->addError ($attribute, Yii::t('reports', 'Invalid report settings')) ;
            return false;
        }
        $this->type = $formModel->getReportType ();
        $this->$attribute = CJSON::encode ($formModel->getSettings ());
    }

    public function getFormModelName () {
        return ucfirst ($this->type).'ReportFormModel';
    }

    public function changeSetting($key, $value) {
        $settings = CJSON::decode ($this->settings);
        $settings[$key] = $value;
        $this->settings = CJSON::encode ($settings);
    }

    public function setting($key) {
        $settings = CJSON::decode ($this->settings);
        return $settings[$key];
    }

    /**
     * Returns whether a widget supports a this 
     * By checking if the widget contains the get{reportType}Data() function
     * @param string $chartType the chart type to test support for 
     */
    public function chartSupports($chartType) {
        return method_exists($chartType.'Widget', 'get'.ucfirst($this->type).'Data');
    }


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
	 */
	public function search ($pageSize=null) {
		$criteria = new CDbCriteria;
        return $this->searchBase ($criteria, $pageSize);
	}

    /**
     * @return string 
     */
    public function getPrettyType () {
        return self::prettyType ($this->type);
    }

    /**
     * Retrieves as setting such as column field or rowField,
     * And returns the field label
     * @param $setting string the key name in settings
     */
    public function getAttrLabel($fieldName) {

        list($model, $attr) = $this->instance->getModelAndAttr($fieldName);
        $field = $model->getField ($attr);
        if ($field) {
            return $field->attributeLabel;
        }

        return ucfirst($fieldName);

    }



    public static function prettyType ($type) {
        return preg_replace ('/And/', 'and', ucfirst (Formatter::deCamelCase ($type)));
    }

    public function getClassName() {
        if ($this->type == 'grid')
            return 'X2GridReport';
        else if ($this->type == 'summation') {
            return 'X2SummationReport';
        } else if ($this->type == 'rowsAndColumns') {
            return 'X2RowsAndColumnsReport';
        }

    }

    public function getInstance () {
        $reportName = $this->getClassName();

        $report = new $reportName;
        $settings = CJSON::decode($this->settings);

        foreach($settings as $key => $value) {
            $report->$key = $value;
        }

        return $report;
    }

    public function afterDelete () {
        foreach($this->charts as $chart) {
            $chart->delete();
        }
        return parent::afterDelete ();
    }

}
