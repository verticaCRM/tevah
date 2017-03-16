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
 * Generic condition list form which enables user specification of conditions on model properties.
 * User specified conditions can be retrieved through the front-end X2ConditionList API (see 
 * X2ConditionList.js).
 */

class X2ConditionList extends X2Widget {

    /**
     * @var string $id id of container element
     */
    public $id;

    /**
     * @var string $name condition list input name
     */
    public $name; 

    /**
     * @var array $value conditions already added
     */
    public $value; 

    /**
     * @var X2Model $model model whose attributes should be used to populate attribute list
     */
    public $model;

    /**
     * @var bool $useLinkedModels if true, add field options for related models
     */
    public $useLinkedModels = false;

    /**
     * @var array (optional) Used to instantiate JS X2ConditionList class. If not specified, this
     *  value will default to return value of {@link X2Model::getFieldsForDropdown}
     */
    public $attributes;

    /**
     * @var array $_packages
     */
    protected $_packages;

    public static function listOption ($attributes, $name) {
        if ($attributes instanceof Fields) {
            $attributes = $attributes->getAttributes ();
        }
        $data = array(
            'name' => $name,
            'label' => $attributes['attributeLabel'],
        );

        if(isset ($attributes['type']) && $attributes['type'])
            $data['type'] = $attributes['type'];
        if(isset ($attributes['required']) && $attributes['required'])
            $data['required'] = 1;
        if(isset ($attributes['readOnly']) && $attributes['readOnly'])
            $data['readOnly'] = 1;
        if(isset ($attributes['type'])) {
           if (($attributes['type'] === 'assignment' || 
                $attributes['type'] === 'optionalAssignment')) {
               $data['options'] = AuxLib::dropdownForJson(
                   X2Model::getAssignmentOptions(true, true));
            } elseif ($attributes['type'] === 'dropdown' && isset ($attributes['linkType'])) {
                $data['linkType'] = $attributes['linkType'];
                $data['options'] = AuxLib::dropdownForJson(
                    Dropdowns::getItems($attributes['linkType']));
            } elseif ($attributes['type'] === 'link' && isset ($attributes['linkType'])) {
                $staticLinkModel = X2Model::model($attributes['linkType']);
                if(array_key_exists('X2LinkableBehavior', $staticLinkModel->behaviors())) {
                    $data['linkType'] = $attributes['linkType']; 
                    $data['linkSource'] = Yii::app()->controller->createUrl(
                        $staticLinkModel->autoCompleteSource);
                }
            }
        }

        return $data;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'X2Fields' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2Fields.js',
                        'js/X2FieldsGeneric.js',
                    ),
                ),
                'X2ConditionListJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/X2ConditionList.js',
                    ),
                    'depends' => array ('auxlib', 'X2Fields')
                ),
            );
        }
        return $this->_packages;
    }

    public function init () {
        if (!$this->attributes) {
            $this->attributes = $this->model->getFieldsForDropdown ($this->useLinkedModels);
        }
    }

    public function run () {
        $this->registerPackages ();
        $this->render ('x2ConditionList');
    }

}
