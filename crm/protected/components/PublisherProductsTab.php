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

/**
 * @package application.components
 */
class PublisherProductsTab extends PublisherTab {
    
    public $viewFile = 'application.components.views.publisher._productsForm';

    public $title = 'Products';

    public $tabId = 'products'; 

    public $JSClass = 'PublisherProductsTab';

    public $module = 'Quote';

    /**
     * Packages which will be registered when the widget content gets rendered.
     */
    protected $_packages;

    protected $_setupScript;

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'PublisherProductsTabJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/publisher/PublisherProductsTab.js',
                        ),
                        'depends' => array ('PublisherTabJS')
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function getTranslations () {
        if (!isset ($this->_translations)) {
            $this->_translations = array_merge (parent::getTranslations (), array (
                'beforeSubmit' => Yii::t('actions', 'Please enter a description.')
            ));
        }
        return $this->_translations;
    }

    public function instantiateJSClass ($onReady=true) {
        parent::instantiateJSClass ($onReady);
        Yii::app()->clientScript->registerScript(
            $this->namespace.get_class ($this).'JSClassInstantiation',"

            $(function () { // add line items manager object after it's available
                ".$this->getJSObjectName ().".lineItems = x2.".$this->namespace."lineItems;
            });
        ");
    }

    public function renderTab ($viewParams) {
        parent::renderTab (array_merge ($viewParams, array ('context' => $this)));
    }

}
