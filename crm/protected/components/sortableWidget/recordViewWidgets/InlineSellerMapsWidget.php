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
class InlineSellerMapsWidget extends GridViewWidget {

    public static $position = 5;

    public $viewFile = '_inlineSellerMapsWidget';

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

    private $sellerAddress;
    private $listingsAddresses;
    private $listingsDetails;
    private $infoWindowContent;


    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Maps',
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
            // $filterModel = new SellerMapsModel ('search');
            $filterModel = new Clistings ('search');
            // $filterModel = new RelationshipsGridModel ('search');
            //$filterModel->myModel = $model;
            $this->_filterModel = $filterModel;
        }
        return $this->_filterModel;
    }


    public function getDataProvider () {
        $model = $this->model;
        $filterModel = $this->getFilterModel ();

        $sellerAddress = '';
	$sellerState = ( isSet($model->c_state) && !empty($model->c_state) )?$model->c_state:'';
	$sellerState = ( isSet($model->state) && !empty($model->state) )?$model->state:$sellerState;
	$sellerCounty = ( isSet($model->c_county) && !empty($model->c_county) )?$model->c_county:'';
	$sellerCounty = ( isSet($model->county) && !empty($model->county) )?$model->county:$sellerCounty;
	$sellerAddressStr = ( isSet($model->c_address) && !empty($model->c_address) )?$model->c_address:'';
	$sellerAddressStr = ( isSet($model->address) && !empty($model->address) )?$model->address:$sellerAddressStr;

        if ($sellerAddressStr == '')
        {
            if ($sellerCounty != '')
            {
                $sellerAddress = $sellerCounty;
            }
            if ($sellerState != '')
            {
                $sellerAddress .= ' '.$sellerState;
            }

        }
        else
        {
            $sellerAddress = $sellerAddressStr. ' '.$sellerCounty. ' '.$sellerState;
        }

        if ($sellerAddress == '')
        {
            //$sellerAddress = '42 Broadway Suite 1815';
            $sellerAddress = '1600 Pennsylvania Avenue';
        }
        //for test
        //$sellerAddress = '42 Broadway Suite 1815';
        $this->sellerAddress = $sellerAddress;

        $criteria=new CDbCriteria;
        $criteria->with = array(
            //'Portfolio' => array('alias'=> 'Portfolio', 'together' => true, ),
            //'Contacts' => array('alias'=> 'Contacts', 'together' => true, ),
            // 'Clisings' => array('alias'=> 'Clisings', 'together' => true, ),
        );
        //  $criteria->addCondition("c_deleted != '1'");
        //$criteria->with = array('Contacts');
        //$criteria->with = array('Clisings');
        //$criteria->together = true;

        // $buyerPortfolios = X2Model::model('Portfolio')->findAll($criteria);
        $buyerPortfolios = X2Model::model('Clistings')->findAll($criteria);

        // printR($buyerPortfolios);
        // die();

        $gridModels_portfolio = array ();

        $listingsAddresses = array();
        $infoWindowContent = array();
        $listingsDetails = array();

        foreach ($buyerPortfolios as $Portfolio) {
            // printR($Portfolio->id);

            if (isset($Portfolio->id))
            {
                $listingAddress = '';
                if ($Portfolio->c_listing_address_c == '')
                {
                    if ($Portfolio->c_listing_city_c != '')
                    {
                        $listingAddress = $Portfolio->c_listing_city_c;
                    }
                    if ($Portfolio->c_listing_town_c != '')
                    {
                        $listingAddress .= ' '.$Portfolio->c_listing_town_c;
                    }
                    if ($Portfolio->c_listing_region_c != '')
                    {
                        $listingAddress .= ' '.$Portfolio->c_listing_region_c;
                    }
                }
                else
                {
                    $listingAddress = $Portfolio->c_listing_address_c.' '.$Portfolio->c_listing_city_c.' '.$Portfolio->c_listing_town_c.' '.$Portfolio->c_listing_region_c;
                }

                if ($listingAddress != '')
                {
                    $listingsAddresses[] = trim($listingAddress);
                }

                $listingDetailsInfo = array();
                $listingDetailsInfo['address'] = trim($listingAddress);
                $listingDetailsInfo['askingprice'] = $Portfolio->c_listing_askingprice_c;
                $listingDetailsInfo['ownerscashflow'] = $Portfolio->c_ownerscashflow;
                $listingDetailsInfo['downpayment'] = $Portfolio->c_listing_downpayment_c;
                $listingDetailsInfo['business_type'] = stripslashes($Portfolio->c_businesscategories);
                $listingsDetails[] = $listingDetailsInfo;

                $infoWindowContent[] = '<div class="info_content">
                    <h3>'.trim($listingAddress).'</h3>
                    <p>
                    Asking Price: '.$Portfolio->c_listing_askingprice_c.' <br/>
                    Owners Cash Flow: '.$Portfolio->c_ownerscashflow.' <br/>
                    Downpayment: '.$Portfolio->c_listing_downpayment_c.' <br/>
                    Business type: '.str_replace(array('[', ']', '"'), array('', '', ''), stripslashes($Portfolio->c_businesscategories)).' <br/>
                    </p>
                    </div>';

                $gridModels_portfolio[] = Yii::createComponent (array (
                    'class' => 'SellerMapsModel',
                    //'relatedModel' => $Portfolio,
                    // 'myModel' => $Portfolio,
                    'id' => $Portfolio->id
                ));

            }
        }
        $this->listingsDetails = $listingsDetails;
        $this->listingsAddresses = $listingsAddresses;
        $this->infoWindowContent = $infoWindowContent;
        // printR('gridModels_portfolio');
        // printR($gridModels_portfolio);
        // $gridModels_portfolio = $filterModel->filterModels ($gridModels_portfolio);

        // $relationshipsDataProvider = new CArrayDataProvider($gridModels_portfolio, array(
        $relationshipsDataProvider = new CArrayDataProvider($buyerPortfolios, array(
            'id' => 'buyerMaps-gridview',
            /* 'sort' => array(
                 'class' => 'SmartSort',
                 //'uniqueId' => 'SellerMaps',
                 'sortVar' => 'SellerMaps_sort',
                 'attributes'=>array('name','assignedTo', 'c_release_status', 'c_is_hidden', 'c_sales_stage', 'c_date_released', 'city', 'country','state','c_date_entered','c_name_dba_c', 'nameId', 'email', 'phone', 'phone2', 'c_seller', 'c_listing_date_approved_c', 'leadstatus', 'c_create_by_buyer', 'c_created_by_user', 'c_added_from', 'c_released_by'),
             ),
             */
            'pagination' => array('pageSize'=>$this->getWidgetProperty ('resultsPerPage'))
        ));
        return $relationshipsDataProvider;
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
            htmlspecialchars($label)."&nbsp(<span id='buyerMaps-count'>$relationshipCount</span>)</div>";
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
                        x2.inlineSellerMapsWidget = new x2.InlineSellerMapsWidget (".
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
                $this->_setupScript .= "
                    var address = '".$this->sellerAddress."';
                    var markers = ".json_encode($this->listingsAddresses, true).";
                    var listingsArray = ".json_encode($this->listingsDetails, true).";
                    var infoWindowContent = ".json_encode($this->infoWindowContent, true).";
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
                    'InlineSellerMapsJSExt' => array(
                        'baseUrl' => Yii::app()->getTheme ()->getBaseUrl ().'/css/gridview/',
                        'js' => array (
                            'jquery.yiigridview.js',
                        ),
                        'depends' => array ('auxlib')
                    ),
                    'InlineSellerMapsJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array (
                            'js/sortableWidgets/InlineSellerMapsWidget.js',
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

            $hasUpdatePermissions = $this->checkModuleUpdatePermissions ();

            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                    'modelName' => get_class ($this->model),
                    'default_address' => $this->sellerAddress,
                    'linkableModelsOptions' => $linkableModelsOptions,
                    'hasUpdatePermissions' => $hasUpdatePermissions,
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
