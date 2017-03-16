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
 * Model that assumes any attributes given to it.
 *
 * Intended for handling special data validation in Fields input.
 *
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class AmorphousModel extends CModel {

    private $_attributes = array();

    private $_mockFields = array();

    private $_tableName;

    public function __get($name){
        if($this->hasAttribute($name)){
            return $this->_attributes[$name];
        } else
            return parent::__get($name);
    }

    public function __set($name, $value){
        if($this->hasAttribute($name, $value)){
            $this->setAttribute($name, $value);
        } else
            parent::__set($name, $value);
    }

    public function addField(Fields $field,$name=null){
        $name = empty($name) ? $field->fieldName : $name;
        $this->_mockFields[$name] = $field;
        if(!isset($this->_attributes[$name])){
            $this->_attributes[$name] = '';
        }
    }

    public function attributeNames(){
        return array_keys($this->_attributes);
    }

    public function getAttribute($name){
        return $this->_attributes[$name];
    }

    public function getAttributeLabel($name){
        if(isset($this->_mockFields[$name])){
            return $this->_mockFields[$name]->attributeLabel;
        } else
            return null;
    }

    public function getAttributes($names = null){
        if($names == null)
            return $this->_attributes;
        else
            parent::getAttributes($names);
    }

    public function hasAttribute($name){
        return array_key_exists($name,$this->_attributes);
    }

    /**
     * Automatically generate rules from X2Model.
     *
     * The "required" validator is excluded because, when validating input for a
     * default value for a field that is required, blank should be a valid value
     * because otherwise having the field be required would force the user to
     * specify a non-blank default value.
     * @return array
     */
    public function rules(){
        $rules = X2Model::modelRules($this->_mockFields);
        foreach(array_keys($rules) as $ind) {
            if(in_array($rules[$ind][1],array('required','unique','application.components.ValidLinkValidator')))
                unset($rules[$ind]);
        }
        return $rules;
    }

    public function setAttribute($name, $value){
        $this->_attributes[$name] = $value;
    }

    public function setTableName($value){
        $this->_tableName = $value;
    }

    public function tableName(){
        return $this->_tableName;
    }

}

?>
