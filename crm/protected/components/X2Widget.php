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
 * Base widget class for all of X2Engine's widgets
 *
 * @property X2WebModule $module
 * @package application.components
 */
abstract class X2Widget extends CWidget {

    protected $_module;

    protected $_packages;

    /**
     * @var string $JSClass
     */
    public $JSClass = 'Widget'; 

    /**
     * @var string $element
     */
    public $element; 

    /**
     * @var string $namespace
     */
    public $namespace = ''; 

	/**
	 * Constructor.
	 * @param CBaseController $owner owner/creator of this widget. It could be either a widget or a 
     *  controller.
	 */
	public function __construct($owner=null)
	{
        parent::__construct ($owner);
        $this->attachBehaviors($this->behaviors());
	}

    public function behaviors () {
        return array ();
    }

    protected $_translations;
    protected function getTranslations () {
        if (!isset ($this->_translations)) {
            $this->_translations = array ();
        }
        return $this->_translations;
    }

    protected $_JSClassParams;
    public function getJSClassParams () {
        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array (
                'element' => $this->element,
                'namespace' => $this->namespace,
                'translations' => $this->getTranslations (),
            );
        }
        return $this->_JSClassParams;
    }

    public function resolveIds ($selector) {
        return preg_replace ('/#/', '#'.$this->namespace, $selector);
    }

    public function resolveId ($id) {
        return $this->namespace.$id;
    }

    public function getJSObjectName () {
        return "x2.".$this->namespace.lcfirst ($this->JSClass);
    }

    /**
     * @param bool $onReady whether or not JS class should be instantiated after page is ready
     */
    public function instantiateJSClass ($onReady=true) {
        Yii::app()->clientScript->registerScript (
            $this->namespace.get_class ($this).'JSClassInstantiation', 
            ($onReady ? "$(function () {" : "").
                $this->getJSObjectName ()."= new x2.$this->JSClass (".
                        CJSON::encode ($this->getJSClassParams ()).
                    ");".
            ($onReady ? "});" : ""), CClientScript::POS_END);
    }

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'X2Widget' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2Widget.js',
                    ),
                ),
            );
        }
        return $this->_packages;
    }

	/**
	 * Renders a view file.
	 * Overrides {@link CBaseController::renderFile} to check if the requested view 
	 * has a version in /custom, and uses that if it exists.
	 *
	 * @param string $viewFile view file path
	 * @param array $data data to be extracted and made available to the view
	 * @param boolean $return whether the rendering result should be returned instead of being 
     *  echoed
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view file does not exist
	 */
	public function renderFile($viewFile,$data=null,$return=false) {
		$viewFile = Yii::getCustomPath($viewFile);
		return parent::renderFile($viewFile,$data,$return);
	}

    /**
     * Runs an arbitrary function inside a partial view. All scripts registered get processed.
     * Allows scripts associated with a widget to be returned in AJAX response.
     * 
     * @param function $function
     */
    public static function ajaxRender ($function) {
        Yii::app()->controller->renderPartial (
            'application.components.views._ajaxWidgetContents',
            array (
                'run' => $function
            ), false, true);
    }

    /**
     * Getter for {@link module}.
     *
     * Can automatically recognize when a component is a member of a module's
     * collection of components.
     * @return type
     */
    public function getModule(){
        if(!isset($this->_module)){
            // Ascertain the module to which the widget belongs by virtue of its
            // location in the file system:
            $rc = new ReflectionClass(get_class($this));
            $path = $rc->getFileName();
            $ds = preg_quote(DIRECTORY_SEPARATOR,'/');
            $pathPattern = array(
                'protected',
                'modules',
                '(?P<module>[a-z0-9]+)',
                'components',
                '\w+\.php'
            );
            if(preg_match('/'.implode($ds,$pathPattern).'$/',$path,$match)) {
                // The widget is part of a module:
                $this->_module = Yii::app()->getModule($match['module']);
            } else {
                // Assume the widget's module is the currently-requested module:
                $this->_module = Yii::app()->controller->module;
            }
        }
        return $this->_module;
    }

    public function setModule ($moduleName) {
        $this->_module = Yii::app()->getModule($moduleName);
    }

}
?>
