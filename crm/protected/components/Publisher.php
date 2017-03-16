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
 * Widget class for displaying all available inline actions.
 *
 * Displays tabs for "log a call","new action" and the like.
 *
 * @package application.components
 */
class Publisher extends X2Widget {

    public static $actionTypeToTab = array (
        'call' => 'PublisherCallTab',
        'time' => 'PublisherTimeTab',
        'action' => 'PublisherActionTab',
        'note' => 'PublisherCommentTab',
        'event' => 'PublisherEventTab',
        'products' => 'PublisherProductsTab',
    );

    public $JSClass = 'Publisher';
    public $model;
    public $associationType; // type of record to associate actions with
    public $associationId = ''; // record to associate actions with
    public $assignedTo = null; // user actions will be assigned to by default
    public $renderTabs = true;

    public $viewParams = array(
        'model',
        'associationId',
        'associationType',
    );

    protected $_packages;
    private $_tabs; // available tabs with tab titles
    private $_hiddenTabs;

    public function getTabs () {
        if (!isset ($this->_tabs)) {
            $visibleTabs = array_filter (Yii::app()->settings->actionPublisherTabs,
                function ($shown) {
                    return $shown; 
                });
            $this->_tabs = array ();
            foreach ($visibleTabs as $tabName => $shown) {
                $tab = new $tabName ();
                $tab->publisher = $this;
                $tab->namespace = $this->namespace;
                $this->_tabs[] = $tab;
            }
        }
        return $this->_tabs;
    }

    public function setTabs ($tabs) {
        $this->_tabs = $tabs;
        foreach ($this->_tabs as $tab) {
            $tab->publisher = $this;
        }
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'PublisherJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/Publisher.js',
                    ),
                    'depends' => array ('auxlib', 'MultiRowTabsJS')
                ),
                'MultiRowTabsJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/MultiRowTabs.js',
                    ),
                    'depends' => array ('jquery', 'jquery.ui')
                ),
            ));
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $selectedTab = $this->tabs[0]->tabId;
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'translations' => array (),
                'initTabId' => $selectedTab,
                'publisherCreateUrl' => 
                    Yii::app()->controller->createUrl ('/actions/actions/publisherCreate'),
                'isCalendar' => $this->calendar,
                'renderTabs' => $this->renderTabs,
            ));
        }
        return $this->_JSClassParams;
    }

    public function getTranslations () {
        if (!isset ($this->_translations)) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'View History Item' => Yii::t('app', 'View History Item')
            ));
        }
        return $this->_translations;
    }

    public function run() {
        $model = new Actions;
        $model->associationType = $this->associationType;
        $model->associationId = $this->associationId;
        if($this->assignedTo) {
            $model->assignedTo = $this->assignedTo;
        } else {
            $model->assignedTo = Yii::app()->user->getName();
        }
        $this->model = $model;
        $selectedTabObj = $this->tabs[0];
        $selectedTabObj->startVisible = true;

        $this->registerPackages ();
        $this->instantiateJSClass (false);

        Yii::app()->clientScript->registerScript('loadEmails', "
        $(document).on('ready',function(){
            $(document).on('click','.email-frame',function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'Email');
            });
            $(document).on ('click', '.quote-frame', function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'Quote');
            });

            $(document).on ('click', '.quote-print-frame', function(){
                var id=$(this).attr('id');
                x2.Publisher.loadFrame(id,'QuotePrint');
            });
        });
        ", CClientScript::POS_HEAD);

        Yii::app()->clientScript->registerCss('recordViewPublisherCss', '
            .action-event-panel {
                margin-top: 5px;
            }
            .action-duration {
                margin-right: 10px;
            }
            .action-duration .action-duration-display {
                font-size: 30px;
                font-family: Consolas, monaco, monospace;
            }
            .action-duration input {
                width: 50px;
            }
            .action-duration .action-duration-input {
                display:inline-block;
            }
            .action-duration label {
                font-size: 10px;
            }
        ');

        if ($this->renderTabs) {
            $that = $this;
            $this->render(
                'application.components.views.publisher.publisher',
                array_merge (
                    array_combine(
                        $this->viewParams,
                        array_map(function($p)use($that){return $that->$p;}, $this->viewParams)
                    ),
                    array (
                        'tabs' => $this->tabs, 
                    )
                )
            );
        }
    }

    //////////////////////////////////////////////////////////////
    // BACKWARDS COMPATIBILITY FUNCTIONS FOR OLD CUSTOM MODULES //
    //////////////////////////////////////////////////////////////

    /**
     * Old Publisher had "halfWidth" property
     */
    public function setHalfWidth($value) {
        $this->calendar = !$value;
    }
    public $calendar = false; 
    public $hideTabs = array ();
    public $selectedTab = '';


}
