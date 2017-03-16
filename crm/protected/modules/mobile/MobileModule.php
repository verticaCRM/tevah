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

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

/**
 * @package application.modules.mobile
 */
class MobileModule extends CWebModule {

    /**
     * @var string the path of the assets folder for this module. Defaults to 'assets'.
     */
    public $packages = array();
    
    private $_assetsUrl;
 
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null)
            $this->_assetsUrl = Yii::app()->getAssetManager()->publish(
                Yii::getPathOfAlias('mobile.assets'), false, -1, true );
        return $this->_assetsUrl;
    }


    public function init() {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application
        // import the module-level models and components
        $this->setImport(array(
            'mobile.models.*',
            'mobile.components.*',
        ));

        // Set module specific javascript packages
        $this->packages = array(
            'jquery-migrate' => array(
                'baseUrl' => Yii::app()->baseUrl,
                'js' => array(
                    'js/lib/jquery-migrate-1.2.1.js',
                ),
                'depends' => array('jquery')
            ),
            'jquerymobile' => array(
                'basePath' => $this->getBasePath(),
                'baseUrl' => $this->assetsUrl,
                'css' => array(
                    'css/x2MobileTheme.css',
                    'css/jquery.mobile.structure-1.3.2.css'
                ),
                'js' => array(
                    'js/x2mobile-init.js',
                    'js/jquery.mobile-1.3.2.js',
                ),
                'depends' => array('jquery', 'jquery-migrate'),
            ),
            'yiiactiveform' => array(
                'js' => array('jquery.yiiactiveform.js'),
                'depends' => array('jquerymobile'),
            )
        );
        Yii::app()->clientScript->packages = $this->packages;

        // set module layout
        $this->layout = 'main';
    }

    public function beforeControllerAction($controller, $action) {
        if (parent::beforeControllerAction($controller, $action)) {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
            return false;
    }

}
