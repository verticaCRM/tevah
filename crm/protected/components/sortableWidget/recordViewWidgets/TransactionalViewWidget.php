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
 * @package application.components.sortableWidget
 */
abstract class TransactionalViewWidget extends GridViewWidget {

    /**
     * @var CActiveRecord 
     */
	public $model;

    /**
     * @var TwoColumnSortableWidgetManager $widgetManager
     */
    public $widgetManager; 

    public static $position = 0; 

    public $viewFile = '_transactionalViewWidget';

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{createButton}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    public $sortableWidgetJSClass = 'x2.TransactionalViewWidget';

    protected $compactResultsPerPage = true; 

    protected $createButtonLabel = '';

    protected $labelIconClass = ''; 

    protected $historyType;

    protected $_dataProvider;

    protected $_gridViewConfig;

    protected $_searchModel;

    protected $containerClass = 
        'sortable-widget-container x2-layout-island transactional-view-widget';

    private static $_JSONPropertiesStructure;

    protected function getSearchModel () {
        if (!isset ($this->_searchModel)) {
            $this->_searchModel = new Actions ('search', $this->widgetKey);
        }
        return $this->_searchModel;
    }

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'showHeader' => false, 
                    'resultsPerPage' => 5, 
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'TransactionalViewWidgetJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/sortableWidgets/TransactionalViewWidget.js',
                    ),
                ),
            ));
        }
        return $this->_packages;
    }

    public function getSharedCssFileNames () {
        if (!isset ($this->_sharedCssFileNames)) {
            $this->_sharedCssFileNames = array_merge (parent::getSharedCssFileNames (), array (
                'components/sortableWidget/recordViewWidgets/TransactionalViewWidget.css',
            ));
        }
        return $this->_sharedCssFileNames;
    }

    public function getIcon () {
        return X2Html::fa ($this->labelIconClass, array (
            'class' => 'widget-label-icon',
        ));
    }

    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        $count = $this->dataProvider->totalItemCount;
        echo 
            "<div class='widget-title'>".
                $this->getIcon ().
                htmlspecialchars($label).
                "&nbsp(<span class='transaction-count'>".
                    $count.
                "</span>)</div>";
            "</div>";
    }

    public function getCreateButtonTitle () {
        return '';
    }

    public function getCreateButtonText () {
        return '';
    }

    public function renderCreateButton () {
        echo 
            "<button class='x2-button create-button'
              title='".CHtml::encode ($this->createButtonTitle)."'>
                <span class='fa fa-plus'></span>".
                CHtml::encode ($this->createButtonText)."</button>";
    }

    /**
     * @return object Data provider object to be used for the grid view
     */
    public function getDataProvider () {
        if (!isset ($this->_dataProvider)) {
            $resultsPerPage = $this->getWidgetProperty (
                'resultsPerPage');
            $this->_dataProvider = $this->getSearchModel ()->search (
                History::getCriteria (
                    $this->model->id, X2Model::getAssociationType (get_class ($this->model)),
                    $this->getWidgetProperty ('showRelatedRecords'), $this->historyType),
                $resultsPerPage
            );
            // clear order set by Actions::search and History::getCriteria
            $this->_dataProvider->criteria->order = '';
        }
        return $this->_dataProvider;
    }

    protected function getActionDescriptionHeader () {
        return Yii::t('actions','{action} Description', 
            array('{action}'=>Modules::displayName(false, 'Actions')));
    }

    public function getAjaxUpdateRouteAndParams () {
        list ($updateRoute, $updateParams) = parent::getAjaxUpdateRouteAndParams ();
        $updateParams = array_merge ($updateParams, array (
            'modelId' => $this->model->id,
            'modelType' => get_class ($this->model),
        ));
        return array ($updateRoute, $updateParams);
    }

    /**
     * @return array the config array passed to widget ()
     */
    public function getGridViewConfig () {
        if (!isset ($this->_gridViewConfig)) {
            $this->_gridViewConfig = array_merge (parent::getGridViewConfig (), array (
                'possibleResultsPerPage' => array (5, 10, 20, 30, 40, 50, 75, 100),
                'sortableWidget' => $this,
                'moduleName' => 'Actions', 
                'sortableWidget' => $this,
                'id'=>get_called_class ().'_'.$this->widgetUID,
                'fieldFormatter' => 'TransactionalViewFieldFormatter',
                'enableColDragging' => false,
                'evenPercentageWidthColumns' => true,
                'enableGridResizing' => false,
                'enableScrollOnPageChange' => false,
                'buttons'=>array('clearFilters','columnSelector','autoResize'),
                'template'=> '{summary}{items}{pager}',
                'fixedHeader'=>false,
                'dataProvider'=>$this->dataProvider,
                'filter'=>$this->model,
                'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
                'modelName'=> 'Actions',
                'viewName'=>'profile',
                'gvSettingsName'=> get_called_class ().$this->widgetUID,
                'enableControls'=>true,
                'filter' => new Actions ('search', $this->widgetKey),
                'fullscreen'=>false,
                'enableSelectAllOnAllPages' => false,
                'hideSummary' => true,
                'defaultGvSettings'=>array(
                    'actionDescription' => '38%',
                    'assignedTo' => '28%',
                    'createDate' => 60,
                ),
                'specialColumns'=>array(
                    'actionDescription'=>array(
                        'header'=>$this->getActionDescriptionHeader (),
                        'name'=>'actionDescription',
                        'value'=> '$data->frameLink ()',
                        'type'=>'raw',
                        'filter' => false,
                        'htmlOptions' => array (
                            'title' => 
                                'php:CHtml::encode(Formatter::trimText($data->actionDescription))',
                        )
                    ),
                ),
            ));
        }
        return $this->_gridViewConfig;
    }

    public function run () {
        $tabs = Yii::app()->settings->actionPublisherTabs;
        $actionTypeToTab = Publisher::$actionTypeToTab;

        if (isset ($this->widgetManager) && $this->widgetManager->layoutManager->staticLayout) {
            // don't display transactional view widgets on legacy record view pages (e.g. on old
            // custom modules)
            return;
        }

        // don't display widget if corresponding tab isn't enabled
        if ($tabs && isset ($actionTypeToTab[$this->historyType]) &&
            isset ($tabs[$actionTypeToTab[$this->historyType]]) &&
            !$tabs[$actionTypeToTab[$this->historyType]]) {

            return;
        }
        // hide widget if transactional view is disabled
        if (!Yii::app()->params->profile->miscLayoutSettings['enableTransactionalView']) {
            $this->registerSharedCss ();
            $this->render ('application.components.sortableWidget.views.'.$this->sharedViewFile,
                array (
                    'widgetClass' => get_called_class (),
                    'profile' => $this->profile,
                    'hidden' => true,
                    'widgetUID' => $this->widgetUID,
                ));
            return;
        }
        parent::run ();

    }

    protected function getJSSortableWidgetParams () {
        if (!isset ($this->_JSSortableWidgetParams)) {
            $this->_JSSortableWidgetParams = array_merge (
                parent::getJSSortableWidgetParams (), array (
                    'actionType' => $this->historyType,
                    'modelName' => get_class ($this->model),
                    'modelId' => $this->model->id,
                )
            );
        }
        return $this->_JSSortableWidgetParams;
    }

    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (parent::getTranslations(), array (
                'dialogTitle' => ucwords ($this->createButtonTitle),
            ));
        }
        return $this->_translations;
    }

}

?>
