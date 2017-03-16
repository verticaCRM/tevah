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
 * Class for displaying the "Quick Contact" widget
 * 
 * @package application.components 
 */
class QuickContact extends X2Widget {

	public $visibility;
	public function init() {
		parent::init();
	}

    public function renderContactFields($model) {
        $defaultFields = X2Model::model('Fields')->findAllByAttributes(
            array('modelName' => 'Contacts'),
            array(
                'condition' => "fieldName IN ('firstName', 'lastName', 'email', 'phone')",
            )
        );
        $requiredFields = X2Model::model('Fields')->findAllByAttributes(
            array(
                'modelName' => 'Contacts',
                'required' => 1,
            ), array(
                'condition' => "fieldName NOT IN ('firstName', 'lastName', 'phone', 'email', 'visibility')"
            ));
        $i = 0;
        $fields = array_merge($requiredFields, $defaultFields);
        foreach ($fields as $field) {
            if ($field->type === 'boolean') {
                $class = "";
                echo "<div>";
            } else {
                $class = (($field->fieldName === 'firstName' || $field->fieldName === 'lastName') ?
                    'quick-contact-narrow' : 'quick-contact-wide');
            }

            $htmlAttr = array(
                'class' => $class,
                'tabindex'=>100 + $i,
                'title'=>$field->attributeLabel,
                'id'=>'quick_create_'.$field->modelName.'_'.$field->fieldName,
            );

            if ($field->type === 'boolean') {
                echo CHtml::label($field->attributeLabel, $htmlAttr['id']);
            }
            echo X2Model::renderModelInput ($model, $field, $htmlAttr);
            if ($field->type === 'boolean')
                echo "</div>";

            ++$i;
        }
    }

	public function run() {
        $model = new Contacts;
		$this->render('quickContact', array (
            'model' => $model
        ));
	}
}

