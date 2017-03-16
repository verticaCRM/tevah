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

Yii::import ('application.modules.reports.components.views.*');

class ChartForm extends X2ReportActiveFormBase {

    /**
     * @var string $JSClass The js Class to be instantiated
     */
    public $JSClass; // ChartForm

    /**
     * @var string View file to render
     */
    public $viewFile; // chartForm

    /**
     * @var CActiveForm Form object to render attributes for 
     */
    public $formModel; // ChartFormModel

    /**
     * @var string type of chart this form is for
     */
    public $chartType; // bar, timeSeries

    /**
     * @var Reports Report object this is tied to 
     */
    public $report;

    /**
     * @var string assetsUrl to retrive assets for
     */
    protected $_assetsUrl;

    /**
     * @see getPackages()
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'ChartFormJS' => array(
                    'baseUrl' => $this->_assetsUrl,
                    'js' => array(
                        'js/ChartForm.js',
                    ),
                    'css' => array(
                        'css/ChartForm.css',
                    ),
                    'depends' => array ( 'X2FormJS'),
                ),
                $this->JSClass.'JS' => array(
                    'baseUrl' => $this->_assetsUrl,
                    'js' => array(
                        'js/'.$this->JSClass.'.js',
                    ),
                    'depends' => array ('ChartFormJS')
                ),
            ));
        }
        return $this->_packages;
    }


    /**
     * Retrieved a help item from the formModel
     * @param string $field Attribute name to get help from
     * @return string Help item text
     */
    public function getHelp($field) {
        $helpItems = $this->formModel->getHelpItems();
        if (isset($helpItems[$field])) {
            return $helpItems[$field];
        }

        return '';
    }

    /**
     * @see init()
     */
    public function init(){
        $this->_assetsUrl = Yii::app()->getModule('reports')->assetsUrl;

        $this->id = $this->formName;
        $this->JSClass = $this->formName;
        $this->viewFile = lcfirst($this->formName);

        $formModel = $this->getFormModelName();
        $this->formModel = new $formModel($this->report->type);
        $this->formModel->reportId = $this->report->id;
        parent::init();
    }

    /**
     * @see run()
     */
    public function render($viewFile, $data=null, $return=false) {
        if (!$viewFile) {
            $viewFile = $this->viewFile;
        }

        parent::render($viewFile, $data, $return);

        echo $this->hiddenField($this->formModel, 'reportId');
        echo X2Html::tag('span', 
            array(
                'id' => 'submit-button',
                'class' => 'x2-button'
            )
            , Yii::t('charts', 'submit')) ;

    }

    /**
     * Renders HTMl for this models errors
     * @return string HTML of errors
     */
    public function printErrors() {
        $errors = '';
        $errorsArray = $this->formModel->getErrors();
        foreach ($errorsArray as $value) {
             foreach($value as $error){
                $errors .= X2Html::openTag('div', array(
                        'class' =>'row error' )
                ).$error.'</div>';
             }       
        }

        return $errors;
    }

    /**
     * Helper method to retrieve the formModel name of this instances
     * Chart Type
     * @return string This formModels class name
     */
    public function getFormModelName() {
        return Charts::toFormModelName($this->chartType);
    }

    /**
     * Helper method to retrieve the form name of this instances
     * Chart Type
     * @return string This forms class name
     */
    public function getFormName() {
        return Charts::toFormName($this->chartType);
    }

    /**
     * Generates an axis selector. 
     * An axis selector has a hidden field and a visible text field
     * @param string $field  Field name of formModel to supply input for
     * @param string $axis  What type of selection it is, currently only 'column'
     * @see ChartCreator
     */
    public function axisSelector($field, $axis = 'column') {
        $attr = $this->formModel->attributes;

        $content = X2Html::textField ($field, '', 
            array (
                'class' => 'axis-selector',
                'axis' => $axis,
                'placeholder' => Yii::t('charts','click to select'),
                'readonly' => true,
            )
        );

        $content .= X2Html::fa('fa-times-circle clear-field');

        // Help icon with tips
        $content .= X2Html::hint($this->getHelp($field));

        // Hidden field for the actual attributeName of the report
        $content .= $this->hiddenField($this->formModel, $field, 
            array (
                'class' => 'axis-selector-hidden'
            )
        );
        return $this->row ($content, $field);

    }

    /**
     * Helper function to generate a row with label on the form
     * @param string $content HTML string to be rendered on the row
     * @param field $field to generate a label for 
     * @param array $htmlOptions Array of extra options 
     * @return String string of generated HTML 
     */
    public function row($content, $field=null, $htmlOptions=null) {
        $row = CHtml::openTag('div', array('class' => 'row'));

        if ($field != null) {
            $label = $this->label ($this->formModel, $field);
            $row .= $label;
        }

        $row .= $content;
        $row .= '</div>';
        return $row;
    }

    
}

?>
