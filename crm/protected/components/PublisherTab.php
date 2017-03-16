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
abstract class PublisherTab extends X2Widget {
    
    /**
     * Path to 
     * @var String 
     */
    public $viewFile;

    /**
     * @var String 
     */
    public $title;

    /**
     * @var Publisher $publisher
     */
    public $publisher; 

    /**
     * @var bool If true, tab content container will be rendered with contents shown   
     */
    public $startVisible = false;

    /**
     * Id of tab content container
     * @var String 
     */
    public $tabId; 

    /**
     * Name of tab JS prototype 
     * @var String
     */
    public $JSClass = 'PublisherTab';

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    /**
     * @var string This script gets registered when the widget content gets rendered.
     */
    protected $_setupScript;

    /**
     * @param bool $onReady whether or not JS class should be instantiated after page is ready
     */
    public function instantiateJSClass ($onReady=true) {
        parent::instantiateJSClass ($onReady);
        if (isset ($this->publisher)) {
            Yii::app()->clientScript->registerScript (
                $this->namespace.get_class ($this).'AddTabJS', 
                ($onReady ? "$(function () {" : "").
                    $this->publisher->getJSObjectName ().".addTab (".
                        $this->getJSObjectName ().");".
                ($onReady ? "});" : ""), CClientScript::POS_END);
        }
    }


    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge (parent::getJSClassParams (), array (
                'id' => $this->tabId,
                'translations' => array ( 
                    'beforeSubmit' => Yii::t('actions', 'Please enter a description.'),
                    'startDateError' => Yii::t('actions', 'Please enter a start date.'),
                    'endDateError' => Yii::t('actions', 'Please enter an end date.'),
                ),
            ));
        }
        return $this->_JSClassParams;
    }


    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'PublisherTabJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/PublisherTab.js',
                    ),
                    'depends' => array ('auxlib')
                ),
            ));
        }
        return $this->_packages;
    }

    public function renderTab ($viewParams) {
        $this->registerPackages ();
        $this->instantiateJSClass (false);
        $this->render ($this->viewFile, array_merge (
            $viewParams, array ('startVisible' => $this->startVisible)));
    }

    public function renderTitle () {
        echo '<a href="#'.$this->resolveId ($this->tabId).'">'.$this->title.'</a>';
    }

}
