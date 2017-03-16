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

class X2ActiveForm extends CActiveForm {

    /**
     * @var string $namespace
     */
    public $namespace = '';  

    public function resolveIds ($selector) {
        return preg_replace ('/#/', '#'.$this->namespace, $selector);
    }

    public function resolveId ($id) {
        return $this->namespace.$id;
    }

    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array()) {
		return X2Html::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

    public function richTextArea (CModel $model, $attribute, array $htmlOptions=array ()) {
        return X2Html::activeRichTextArea ($model, $attribute, $htmlOptions);
    }

    public function init () {
        $this->id = $this->resolveId ($this->id);
        parent::init ();
    }

    public function resolveHtmlOptions (CModel $model, $attribute, array $htmlOptions = array ()) {
        CHtml::resolveNameID ($model, $attribute, $htmlOptions);
        $htmlOptions['id'] = $this->resolveId ($htmlOptions['id']);
        return $htmlOptions;
    }

}
