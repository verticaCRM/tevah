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

 Yii::import ('application.components.sortableWidget.ProfileGridViewWidget');

/**
 * Grid summary widget for custom modules
 *
 * @package application.components
 */
class TemplatesGridViewProfileWidget extends ProfileGridViewWidget {

    public $canBeDeleted = true;

    public $defaultTitle = '{Templates} Summary';

    public $relabelingEnabled = true;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';
    
    protected $_viewFileParams;

    private static $_JSONPropertiesStructure;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    protected function getModel () {
        if (!isset ($this->_model)) {
            $modelType = self::getJSONProperty (
                $this->profile, 'modelType', $this->widgetType, $this->widgetUID);
            $this->_model = new $modelType ('search',
                $this->widgetKey,
                $this->getWidgetProperty ('dbPersistentGridSettings'));
            $this->afterGetModel ();
        }
        return $this->_model;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => '{Templates} Summary',
                    'hidden' => true,
                    'modelType' => null,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    /**
     * Override parent method to prevent widget content from rendering if custom module has been
     * deleted
     */
    public function renderWidgetContents () {
        $modelType = self::getJSONProperty (
            $this->profile, 'modelType', $this->widgetType, $this->widgetUID);
        if (!class_exists ($modelType)) { // custom module was deleted
        } else {
            parent::renderWidgetContents ();
        }
    }

    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = self::getJSONProperty (
                $this->profile, 'resultsPerPage', $this->widgetType, $this->widgetUID);
            $this->_dataProvider = $this->model->search (
                $resultsPerPage, $this->widgetKey);
        }
        return $this->_dataProvider;

    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (
                parent::getGridViewConfig (),
                array (
                    'moduleName' => strtolower (get_class ($this->model)),
                    'defaultGvSettings'=>array(
                        'gvCheckbox' => 30,
                        'name'=>257,
                        'description'=>132,
                        'assignedTo'=>105,
                        'gvControls' => 73,
                    ),
                    'specialColumns'=>array(
                        'name'=>array(
                            'name'=>'name',
                            'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
                            'type'=>'raw',
                        ),
                        'description'=>array(
                            'name'=>'description',
                            'header'=>Yii::t('app','Description'),
                            'value'=>'Formatter::trimText($data->description)',
                            'type'=>'raw',
                        ),
                    ),
                )
            );
        }
        return $this->_gridViewConfig;
    }

    public function init ($skipGridViewInit = false) {
        $modelType = self::getJSONProperty (
            $this->profile, 'modelType', $this->widgetType, $this->widgetUID);

        if (class_exists ($modelType)) { // custom module was deleted
            parent::init (false);
        } else {
            parent::init (true);
        }
    }


}
?>
