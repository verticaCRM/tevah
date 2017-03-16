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

abstract class X2ReportActiveFormBase extends X2ActiveForm {

    /**
     * @var $id
     */
    public $id = 'x2-form'; 

    /**
     * @var string $JSClass 
     */
    public $JSClass = 'X2Form'; 

    /**
     * @var CFormModel $formModel
     */
    public $formModel; 

    protected $_packages;

    /**
     * @param array 
     */
    public function getJSClassConstructorArgs () {
        return array (
            'formSelector' => '#'.$this->id,
            'submitUrl' => '',
            'formModelName' => get_class ($this->formModel),
            'translations' => array (),
        );
    }

    public function registerPackages () {
        Yii::app()->clientScript->registerPackages ($this->getPackages (), true);
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array(
                'X2FormJS' => array(
                    'baseUrl' => Yii::app()->baseUrl,
                    'js' => array(
                        'js/X2Form.js',
                    ),
                    'depends' => array ('auxlib'),
                ),
            );
        }
        return $this->_packages;
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array()) {
		return X2Html::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

    protected function registerJSClassInstantiationScript () {
        Yii::app()->clientScript->registerScript(
            $this->getId ().'registerJSClassInstantiationScript', "
        
        (function () {     
            x2.".lcfirst($this->JSClass)." = new x2.$this->JSClass (".
                CJSON::encode ($this->getJSClassConstructorArgs ()).
            ");
        }) ();
        ", CClientScript::POS_END);
    }

    public function init () {
        if(!isset($this->formModel)){
            $formModel = get_class($this)."Model";
            $this->formModel = new $formModel;
        }
        $this->registerPackages (); 
        $this->registerJSClassInstantiationScript ();
        parent::init ();
    }


}

?>
