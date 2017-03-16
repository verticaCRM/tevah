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
 * @package application.components
 */
abstract class GridViewWidget extends SortableWidget {

    public $sortableWidgetJSClass = 'GridViewWidget';

    protected $compactResultsPerPage = false; 

    private static $_JSONPropertiesStructure;

    /**
     * @var object 
     */
    protected $_dataProvider;

    /**
     * @var array the config array passed to widget ()
     */
    private $_gridViewConfig;

    abstract protected function getDataProvider ();

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'GridViewWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/GridViewWidget.js',
                    ),
                    'depends' => array ('SortableWidgetJS')
                ),
                'GridViewWidgetCSS' => array(
                    'baseUrl' => Yii::app()->theme->baseUrl,
                    'css' => array(
                        'css/components/sortableWidget/views/gridViewWidget.css',
                    )
                ),
            ));
        }
        return $this->_packages;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'resultsPerPage' => 10, 
                    'showHeader' => true, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (array( 
                'showHeader' => CPropertyValue::ensureBoolean (
                    $this->getWidgetProperty('showHeader')),
                'compactResultsPerPage' => $this->compactResultsPerPage,
                ), parent::getJSSortableWidgetParams ()
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    /**
     * Send the chart type to the widget content view 
     */
    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'gridViewConfig' => $this->gridViewConfig,
                )
            );
        }
        return $this->_viewFileParams;
    }

    public function getAjaxUpdateRouteAndParams () {
        $updateRoute = '/profile/view';
        $updateParams =  array (
            'widgetClass' => get_called_class (),        
            'widgetType' => $this->widgetType,
            'id' => $this->profile->id,
        );
        return array ($updateRoute, $updateParams);
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            list ($updateRoute, $updateParams) = $this->getAjaxUpdateRouteAndParams ();
            $this->_gridViewConfig = array (
                'ajaxUrl' => Yii::app()->controller->createUrl ($updateRoute, $updateParams),
                'showHeader' => CPropertyValue::ensureBoolean (
                    $this->getWidgetProperty('showHeader')),
            );
        }
        return $this->_gridViewConfig;
    }


    protected function getSettingsMenuContentEntries () {
        return 
            '<li class="hide-settings">'.
                X2Html::fa('fa-toggle-down').
                Yii::t('profile', 'Toggle Settings Bar').
            '</li>'.
            ($this->compactResultsPerPage ?
                '<li class="results-per-page-container">
                </li>' : '').
            parent::getSettingsMenuContentEntries ();
    }

    /**
     * @return array translations to pass to JS objects 
     */
    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (
                parent::getTranslations (), 
                array (
                    'Grid Settings' => Yii::t('profile', 'Widget Grid Settings'),
                    'Cancel' => Yii::t('profile', 'Cancel'),
                    'Save' => Yii::t('profile', 'Save'),
                ));
        }
        return $this->_translations;
    }

    public function init ($skipGridViewInit = false) {
        parent::init ();
        if (!$skipGridViewInit) {
            list ($updateRoute, $updateParams) = $this->getAjaxUpdateRouteAndParams ();
            $this->dataProvider->pagination->route = $updateRoute;
            $this->dataProvider->pagination->params = $updateParams;
            $this->dataProvider->sort->route = $updateRoute;
            $this->dataProvider->sort->params = $updateParams;
        }
    }


}
?>
