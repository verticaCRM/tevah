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

class RecordViewWidgetManager extends TwoColumnSortableWidgetManager {

    public $layoutManager;

    /**
     * @var CActiveRecord $model
     */
    public $model;

    /**
     * @var string $JSClass
     */
    public $JSClass = 'RecordViewWidgetManager';

    public $widgetLayoutName = 'recordViewWidgetLayout';

    public $namespace = 'RecordViewWidgetManager';

    /**
     * @var array (<widget name> => <array of parameters to pass to widget)
     */
    public $widgetParamsByWidgetName = array ();

    /**
     * Magic getter. Returns this widget's packages.
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'RecordViewWidgetManagerJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
				        'js/sortableWidgets/RecordViewWidgetManager.js',
                    ),
                    'depends' => array ('TwoColumnSortableWidgetManagerJS')
                ),
            ));
        }
        return $this->_packages;
    }

    protected function getTranslations () {
        if (!isset ($this->_translations )) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'Create' => Yii::t('app', 'Create'),
                'Cancel' => Yii::t('app', 'Cancel'),
            ));
        }
        return $this->_translations;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'connectedContainerSelector' => '.'.$this->connectedContainerClass,
                'setSortOrderUrl' =>
                    Yii::app()->controller->createUrl ('/profile/setWidgetOrder'),
                'showWidgetContentsUrl' => Yii::app()->controller->createUrl (
                    '/profile/view', array ('id' => 1)),
                'modelId' => $this->model->id,
                'modelType' => get_class ($this->model),
                'cssSelectorPrefix' => $this->namespace,
            ));
        }
        return $this->_JSClassParams;
    }

	public function displayWidgets ($containerNumber){
        $widgetLayoutName = $this->widgetLayoutName;
		$layout = Yii::app()->params->profile->$widgetLayoutName;

		foreach ($layout as $widgetClass => $settings) {
            if ($this->isExcluded ($widgetClass)) continue;

		    if ($settings['containerNumber'] == $containerNumber) {

                if (isset ($this->widgetParamsByWidgetName[$widgetClass])) {
                    $options = $this->widgetParamsByWidgetName[$widgetClass];
                } else {
                    $options = array ();
                }
                $options = array_merge (array (
                    'model' => $this->model,
                    'widgetManager' => $this,
                ), $options);
		        SortableWidget::instantiateWidget (
                    $widgetClass, Yii::app()->params->profile, 'recordView', $options);
		    }
		}
	}

    /**
     * @param bool $onReady whether or not JS class should be instantiated after page is ready
     */
    public function instantiateJSClass ($onReady=true) {
        Yii::app()->clientScript->registerScript (
            $this->namespace.get_class ($this).'JSClassInstantiation',
            ($onReady ? "$(function () {" : "").
                $this->getJSObjectName ()."=
                    x2.".lcfirst ($this->JSClass)."= new x2.$this->JSClass (".
                        CJSON::encode ($this->getJSClassParams ()).
                    ");".
            ($onReady ? "});" : ""), CClientScript::POS_END);
    }

    private function isExcluded ($name) {
        $modelType = get_class ($this->model);

        if ($modelType === 'Media' && (in_array ($name, array (
                'InlineTagsWidget',
                'WorkflowStageDetailsWidget',
                'ActionHistoryChartWidget',
                'ImageGalleryWidget',
                'EmailsWidget',
                'QuotesWidget',
                'InlineBuyersPortfolioWidget',
                'InlineListingBuyersWidget',
                'InlineListingMapsWidget',
                'InlineBuyerMapsWidget',
            ))) ||
            $modelType === 'Actions' && $name !== 'InlineTagsWidget' ||
            $modelType !== 'Campaign' && $name === 'CampaignChartWidget' ||
            ($modelType == 'BugReports' && $name!='WorkflowStageDetailsWidget') ||
            ($modelType == 'Quote' && in_array ($name, array (
                    'WorkflowStageDetailsWidget',
                    'QuotesWidget',
                     'InlineBuyersPortfolioWidget',
                     'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Opportunity' && in_array ($name, array (
                    'EmailsWidget',
                     'InlineBuyersPortfolioWidget',
                     'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
                    
            ))) ||
            ($modelType == 'Portfolio' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'ImageGalleryWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Clistings' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Accounts' && in_array ($name, array (
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineBuyerMapsWidget',

            ))) ||
            ($modelType == 'Contacts' && in_array ($name, array (
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
            ))) ||
            ($modelType == 'X2Leads' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Groups' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Seller' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineBuyerMapsWidget',
                    'InlineListingMapsWidget',
            ))) ||
            ($modelType == 'Brokers' && in_array ($name, array (
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType == 'Campaign' && in_array ($name, array (
                    'WorkflowStageDetailsWidget',
                    'InlineRelationshipsWidget',
                    'EmailsWidget',
                    'QuotesWidget',
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            ))) ||
            ($modelType === 'Product' && in_array ($name, array (
                    'WorkflowStageDetailsWidget',
                    'QuotesWidget',
                    'EmailsWidget',
                    'InlineBuyersPortfolioWidget',
                    'InlineListingBuyersWidget',
                    'InlineListingDetailsWidget',
                    'InlineListingMapsWidget',
                    'InlineBuyerMapsWidget',
            )))) {

            return true;
        } else {
            return false;
        }
    }
}

?>
