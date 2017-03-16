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
 * Widget for record conversion links which leverage X2ModelConversionBehavior.
 * Designed to have multiple instances on the same page (which is useful if links for multiple
 * conversion targets are needed).
 */

class X2ModelConversionWidget extends X2Widget {

    /**
     * @var X2Model $model
     */
    public $model; 

    /**
     * @var string $targetClass
     */
    public $targetClass; 

    /**
     * @var string $element
     */
    public $element = '#model-conversion-widget'; 

    public $buttonSelector; 

    /**
     * @var string $JSClass
     */
    public $JSClass = 'X2ModelConversionWidget'; 

    protected $_JSClassParams;
    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $title = X2Model::getModelTitle (get_class ($this->model), true);
            $targetClass = $this->targetClass;
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'buttonSelector' => $this->buttonSelector,
                'translations' => array (
                    'conversionError' => Yii::t('app', '{model} conversion failed.', array (
                        '{model}' => $title,
                    )),
                    'conversionWarning' => Yii::t('app', '{model} Conversion Warning', array (
                        '{model}' => $title,
                    )),
                    'convertAnyway' => Yii::t('app', 'Convert Anyway'),
                    'Cancel' => Yii::t('app', 'Cancel'),
                ),
                'targetClass' => $this->targetClass,
                'modelId' => $this->model->id,
                'conversionFailed' => $this->model->conversionFailed,
                'conversionIncompatibilityWarnings' => 
                    $this->model->getConversionIncompatibilityWarnings ($this->targetClass),
                'errorSummary' => "
                    <div class='form'>".
                        CHtml::errorSummary (
                            $targetClass::model (), Yii::t('app', '{model} conversion failed.', 
                            array (
                                '{model}' => $title,
                            ))).
                    "</div>",

            ));
        }
        return $this->_JSClassParams;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'X2ModelConversionWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2ModelConversionWidget.js',
                    ),
                    'depends' => array (
                        'X2Widget',
                    ),
                ),
            ));
        }
        return $this->_packages;
    }

    public function run () {
        $this->registerPackages ();
        $this->instantiateJSClass ();
        $this->render ('application.components.views._x2ModelConversionWidget', array (
            'modelTitle' => lcfirst (X2Model::getModelTitle (get_class ($this->model), true)),
            'targetModelTitle' => lcfirst (X2Model::getModelTitle ($this->targetClass, true)),
        ));
    }

}

?>
