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

class X2PillBox extends X2Widget {

    /**
     * @var string $id html id for pill box element
     */
    public $id; 

    /**
     * @var string $name
     */
    public $name; 

    /**
     * @var array $options
     */
    public $options; 
      
    /**
     * @var string $optionsHeader will be displayed at the top of the options dropdown
     */
    public $optionsHeader; 

    /**
     * @var array $value; 
     */
    public $value = array (); 

    /**
     * @var array $translations
     */
    public $translations = array ();  

    /**
     * @var string $pillBoxJSClass
     */
    public $pillBoxJSClass = 'PillBox'; 

    /**
     * @var array $htmlOptions
     */
    public $htmlOptions = array (); 

    /**
     * @return arguments passed to $pillBoxJSClass constructor
     */
    public function getJSClassConstructorArgs () {
        return array (
            'element' => '#' . $this->id,
            'name' => $this->name,
            'options' => $this->options,
            'value' => $this->value,
            'translations' => array_merge (array (
                'helpText' => Yii::t('app', 'Click to add'),
                'optionsHeader' => $this->optionsHeader,
                'delete' => Yii::t('app', 'Delete'),
            ), $this->translations),
            'pillClass' => $this->pillJSClass,
        );
    }

    /**
     * @var string $pillJSClass
     */
    public $pillJSClass = 'Pill'; 

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages (), array (
                'X2PillBoxJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2PillBox.js',
                    ),
                    'depends' => array ('auxlib', 'X2Widget')
                ),
            ));
        }
        return $this->_packages;
    }

    public function init () {
        unset ($this->htmlOptions['id']);
        unset ($this->htmlOptions['name']);
        parent::init ();
    }

    public function run () {
        $this->registerPackages ();
        $this->render ('x2PillBox');
    }

}

?>
