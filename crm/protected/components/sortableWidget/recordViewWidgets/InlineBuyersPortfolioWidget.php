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
 * Widget class for the relationships form.
 *
 * Relationships lists the relationships a model has with other models,
 * and provides a way to add existing models to the models relationships.
 *
 * @package application.components.sortableWidget
 */
class InlineBuyersPortfolioWidget extends GridViewWidget {

    public static $position = 5;

    public $viewFile = '_inlineBuyersPortfolioWidget';

    public $model;

    public $template = '<div class="submenu-title-bar widget-title-bar">{widgetLabel}{titleBarButtons}{closeButton}{minimizeButton}{settingsMenu}</div>{widgetContents}';

    /**
     * Used to prepopulate create relationship forms
     * @var array (<model class> => <array of default values indexed by attr name>)
     */
    public $defaultsByRelatedModelType = array ();

    protected $compactResultsPerPage = true;

    private $_relatedModels;

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Portfolio',
                    'hidden' => false,
                    'resultsPerPage' => 10,
                    'showHeader' => true,
                    'displayMode' => 'grid', // grid | graph
                    'height' => '200',
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    private $_filterModel;
    public function getFilterModel () {
        if (!isset ($this->_filterModel)) {
            $model = $this->model;
            $filterModel = new BuyersPortfolioModel ('search');
            // $filterModel = new RelationshipsGridModel ('search');
            $filterModel->myModel = $model;
            $this->_filterModel = $filterModel;
        }
        return $this->_filterModel;
    }

    public function getListingsData () {
        $model = $this->model;
        $filterModel = $this->getFilterModel ();

        $allListingsModel = new BuyersPortfolioModel ();
        $listingsDataProviderAll = $allListingsModel->getItems('list');

        return $listingsDataProviderAll;

    }
    public function getDataProvider () {
        $model = $this->model;
        $filterModel = $this->getFilterModel ();

        $criteria=new CDbCriteria;
$criteria->alias = 'Portfolio';

        $criteria->addCondition($criteria->alias.".c_buyer LIKE '%_".$model->id."'");
        $criteria->with = array('Clisings');
        $criteria->together = true;

        $buyerPortfolios = X2Model::model('Portfolio')->findAll($criteria);

        $gridModels_portfolio = array ();

        foreach ($buyerPortfolios as $Portfolio) {
           $gridModels_portfolio[] = Yii::createComponent (array (
                'class' => 'BuyersPortfolioModel',
                'relatedModel' => $Portfolio,
                'myModel' => $Portfolio,
                'id' => $Portfolio->id
            ));

        }
        $gridModels_portfolio = $filterModel->filterModels ($gridModels_portfolio);

        $relationshipsDataProvider = new CArrayDataProvider($gridModels_portfolio, array(
            'id' => 'buyersPortfolio-gridview',
            'sort' => array(
                'class' => 'SmartSort',
                //'uniqueId' => 'BuyersPortfolio',
                'sortVar' => 'BuyersPortfolio_sort',
                'attributes'=>array('name','assignedTo', 'c_release_status', 'c_is_hidden', 'c_sales_stage', 'c_date_released', 'c_listing_city_c', 'c_listing_town_c','c_listing_region_c','c_financial_net_cashflow_c','c_listing_askingprice_c','c_date_entered','c_name_dba_c', 'c_listing_id'),
            ),

            'pagination' => array('pageSize'=>$this->getWidgetProperty ('resultsPerPage'))
        ));
        return $relationshipsDataProvider;
    }

    public function renderTitleBarButtons () {
        echo '<div class="x2-button-group">';
        echo
            "<a class='x2-button rel-title-bar-button' id='new-buyersPortfolio-button'
              title='".CHtml::encode (Yii::t('app', 'Add and new business to the portfolio'))."'>".
            X2Html::fa ('fa-plus', array (), ' ', 'span').
            "</a>";

        echo '</div>';
    }

    public function renderWidgetLabel () {
        $label = $this->getWidgetLabel ();
        $entries_per_page =  count ($this->getDataProvider ()->getData ());
        $dp = $this->getDataProvider();
        $dp->pagination = false; // ALL fields
        $all_entries = count($dp->getData());
        // $relationshipCount = count ($this->model->getVisibleRelatedX2Models ());
        $relationshipCount = $all_entries;
        echo "<div class='widget-title'>".
            htmlspecialchars($label)."&nbsp(<span id='buyersPortfolio-count'>$relationshipCount</span>)</div>";
    }

    public function getSetupScript () {
        if (!isset ($this->_setupScript)) {
            $widgetClass = get_called_class ();
            if (isset ($_GET['ajax'])) {
                $this->_setupScript = "";
            } else {
                $modelsWhichSupportQuickCreate =
                    QuickCreateRelationshipBehavior::getModelsWhichSupportQuickCreate ();

                // get create action urls for each linkable model
                $createUrls = QuickCreateRelationshipBehavior::getCreateUrlsForModels (
                    $modelsWhichSupportQuickCreate);

                // get create relationship tooltips for each linkable model
                $tooltips = QuickCreateRelationshipBehavior::getDialogTooltipsForModels (
                    $modelsWhichSupportQuickCreate, get_class ($this->model));

                // get create relationship dialog titles for each linkable model
                $dialogTitles = QuickCreateRelationshipBehavior::getDialogTitlesForModels (
                    $modelsWhichSupportQuickCreate);
                $this->_setupScript = "
                    $(function () {
                        x2.inlineBuyersPortfolioWidget = new x2.InlineBuyersPortfolioWidget (".
                    CJSON::encode (array_merge ($this->getJSSortableWidgetParams (), array (
                        'displayMode' => $this->getWidgetProperty ('displayMode'),
                        'widgetClass' => $widgetClass,
                        'setPropertyUrl' => Yii::app()->controller->createUrl (
                                '/profile/setWidgetSetting'),
                        'cssSelectorPrefix' => $this->widgetType,
                        'widgetType' => $this->widgetType,
                        'widgetUID' => $this->widgetUID,
                        'enableResizing' => true,
                        'height' => $this->getWidgetProperty ('height'),
                        'recordId' => $this->model->id,
                        'recordType' => get_class ($this->model),
                        'defaultsByRelatedModelType' =>
                            $this->defaultsByRelatedModelType,
                        'createUrls' => $createUrls,
                        'dialogTitles' => $dialogTitles,
                        'tooltips' => $tooltips,
                        'modelsWhichSupportQuickCreate' =>
                            array_values ($modelsWhichSupportQuickCreate),
                        'ajaxGetModelAutocompleteUrl' =>
                            Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete'),
                        'createRelationshipUrl' =>
                            Yii::app()->controller->createUrl ('/site/addPortfolioItems'),
                        'hasUpdatePermissions' => $this->checkModuleUpdatePermissions (),
                    )))."
                        );
                    });
                ";
            }
        }
        return $this->_setupScript;
    }

    // **
    // * overrides parent method. Adds JS file necessary to run the setup script.
    // *
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'InlineBuyersPortfolioJSExt' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl ().'/css/gridview/',
                        'js' => array (
                            'jquery.yiigridview.js',
                        ),
                        'depends' => array ('auxlib')
                    ),
                    'InlineBuyersPortfolioJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array (
                            'js/sortableWidgets/InlineBuyersPortfolioWidget.js',
                        ),
                        'depends' => array ('SortableWidgetJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $linkableModels = X2Model::getModelTypesWhichSupportRelationships(true);
            // * x2plastart
            if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
                unset ($linkableModels['AnonContact']);
            }
            // * x2plaend

            // used to instantiate html dropdown
            $linkableModelsOptions = $linkableModels;

             //list of all listings that can be added as a relation to current buyer
            $linkableListingsOptions = $this-> getListingsData();

            $hasUpdatePermissions = $this->checkModuleUpdatePermissions ();

            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'modelName' => get_class ($this->model),
                    'linkableModelsOptions' => $linkableModelsOptions,
                    'hasUpdatePermissions' => $hasUpdatePermissions,
                    'linkableListingsOptions' => $linkableListingsOptions,
                    // * x2prostart
                    'displayMode' => $this->getWidgetProperty ('displayMode'),
                    // * x2proend
                    'height' => $this->getWidgetProperty ('height'),
                )
            );
        }
        return $this->_viewFileParams;
    }

    private function checkModuleUpdatePermissions () {
        $moduleName = '';
        if (is_object (Yii::app()->controller->module)) {
            $moduleName = Yii::app()->controller->module->name;
        }
        $actionAccess = ucfirst($moduleName).'Update';
        $authItem = Yii::app()->authManager->getAuthItem($actionAccess);
        return (!isset($authItem) || Yii::app()->user->checkAccess($actionAccess, array(
                'X2Model' => $this->model
            )));
    }

    public function init ($skipGridViewInit=false) {
        return parent::init (true);
    }


}

?>
