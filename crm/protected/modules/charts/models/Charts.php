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

/** 
* Charts model 
* 
* How to create a new chart type: 
*     - Add a new entry to the static chartTypes list in this class
*     - extend the ChartFormModel class that specifies the settings of the chart  
*     - extend the ChartForm class that specifies the behaviors and helper methods for the creation form
*          - add a corresponding javascript class extending ChartForm.js
*          - add css rules to the already exisitng chartForm.scss
*     - extend the dataWidget class that specificies the actual chart widget, front and back end  
*          - add a corresponding javascript class extending dataWidget.js
*          - add css rules either to dataWidget.scss or create your own
*     - add a thumbnail image to reports/assets/images
*/
class Charts extends X2Model {

    /**
     * List of avaliable chart types. This list is used in various places and is the only static list
     * of chart types. Hence when a new chart type is created this is the only place it needs to be appended to.
     */
    public static $chartTypes = array(
        'Bar',
        'TimeSeries',
    );    

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

    /**
     * We dont want a chart in a relationships table
     */
    public $supportsRelationships = false;

	/**
	 * @return string the associated database table name
	 */
	public function tableName () {
		return 'x2_charts';
	}

    public static function model ($className=__CLASS__) {
        return parent::model ($className);
    }


    /**
     * Replaces the default Linkable behavior with the charts specific behavior
     */
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['X2LinkableBehavior']['class'] = 'ChartsLinkableBehavior';
        return $behaviors;
    }

    public function rules () {
        $rules = parent::rules ();
        return array_merge (array (
            array (
                'settings',
                'application.components.validators.ArrayValidator',
                'throwExceptions' => true,
                'allowEmpty' => false,
                'except' => 'clone'
            ),
            array (
                'settings', 'validateSettings', 'except' => 'clone'
            ),
            array(
                'reportId', 'validateReportId'
            ),
            array(
                'id', 'unique'
            ),
        ), $rules);
    }

    public function init() {
        parent::init();
        $this->version = Yii::app()->params->version;
        $this->createdBy = Yii::app()->user;
    }

    public function relations()
    {
        $rules = parent::relations ();
        return array_merge (array (
            'report' => array(self::BELONGS_TO, 'Reports', 'reportId'),
        ), $rules);

    }


    /**
     * This is an important function that formats the settings send to it
     * @param string called by the validator only for 'settings' 
     */
    public function validateSettings ($attribute) {
        $value = $this->$attribute;
        $keys = array_keys ($value);
        $formModelName = array_shift ($keys);

        $reportId = $value[$formModelName]['reportId'];
        $report = X2Model::model('Reports')->findByPk($reportId);

        if (!$report) {
            $this->addError($attribute,Yii::t('app', 'The associated report was not found'));
            return false;
        }
        $this->reportId = $reportId;
        $this->name = $report->name;

        // new form model with the scenario as the report type
        $formModel = new $formModelName($report->type);
        $formModel->setAttributes ($value[$formModelName], false);
        $formModel->validate ();

        if ( $formModel->hasErrors()) {
            $this->addErrors(
                $formModel->errors
            );
        }

        $this->type = $formModel->chartType;
        $attributes = $formModel->attributes;
        unset($attributes['reportId']);

        $this->$attribute = CJSON::encode ($attributes);
    }

    public function validateReportId($attribute) {
        $value = $this->$attribute;
        $report = new Reports;

        $report = $report->findByPk($value);

        if(count($report) == 0) {
            $this->reportId = null;
            $this->addError($attribute, "Report id $value does not exist" );
            return false;
        }

        $this->reportId = $report->id;
    }

    public function getSettingsArr() {
        return CJSON::decode($this->settings);
    }

    public function setting($key) {
        if (isset($this->settingsArr[$key])) {
            return $this->settingsArr[$key];
        } 
    }

    public function setSetting($key, $value) {
        $settings = $this->settingsArr;
        $settings[$key] = $value;
        $this->settings = CJSON::encode($settings);
    }

    /**
     * These functions aid in generating the names of the various classes that a chart requires to be made. 
     * for a Bar chart for example the following classes are required: 
     *      BarForm
     *      BarFormModel
     *      BarWidget
     */

    public static function getForms() {
        return array_map( array('Charts','toFormName'), self::$chartTypes);
    }

    public static function getFormModels() {
        return array_map( array('Charts','toFormModelName'), self::$chartTypes);
    }

    public static function getWidgets() {
        return array_map( array('Charts','toWidgetName'), self::$chartTypes);
    }

    public static function toFormName($chartType) {
        return $chartType."Form";
    }

    public static function toFormModelName($chartType) {
        return $chartType."FormModel";
    }

    public static function toWidgetName($chartType) {
        return $chartType."Widget";
    }

    public function getFormName() {
        return $this->toFormName($this->type);
    }

    public function getFormModelName() {
        return $this->toFormModelName($this->type);
    }

    public function getWidgetName() {
        return $this->toWidgetName($this->type);
    }

}
