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

Yii::import ('X2GridView');

/**
 * X2GridView for CActiveRecord models
 *
 * @package application.components
 */
class X2GridViewLess extends X2GridView {

    /**
     * @var array The columns that can be added with the column selector menu 
     */
    protected $_modelAttrColumnNames;

    public function getIsAdmin() {
        if(!isset($this->_isAdmin)) {
            $this->_isAdmin = Yii::app()->params->isAdmin;
        }
        return $this->_isAdmin;
    }

    protected function getSpecialColumnName ($columnName) {
        return $this->getModel ()->getAttributeLabel ($columnName);
    }

    public function setModelAttrColumnNames ($val) {
        $this->_modelAttrColumnNames = $val;
    }

    public function getModelAttrColumnNames () {
        if (!isset ($this->_modelAttrColumnNames)) { // use model attributes if none specified
            $attrs = array_keys ($this->getModel ()->getAttributes ());
            $this->_modelAttrColumnNames = $attrs;
        }
        return $this->_modelAttrColumnNames;
    }

    protected function addFieldNames () {
        $this->addSpecialFieldNames ();
        $attrs = $this->modelAttrColumnNames;
        foreach ($attrs as $name) {
            $this->allFieldNames[$name] = $this->getModel ()->getAttributeLabel ($name);
        }
    }

    protected function createDefaultStyleColumn ($columnName, $width) {
        $isCurrency = in_array($columnName,array('annualRevenue','quoteAmount'));
    
        $newColumn = array ();

        $newColumn['name'] = $columnName;
        $newColumn['id'] = $this->namespacePrefix.'C_'.$columnName;
        $newColumn['header'] = $this->getModel ()->getAttributeLabel ($columnName);
        $newColumn['headerHtmlOptions'] = array('style'=>'width:'.$width.'px;');

        if($isCurrency) {
            $newColumn['value'] = 'Yii::app()->locale->numberFormatter->'.
                'formatCurrency($data["'.$columnName.'"],Yii::app()->params->currency)';
            $newColumn['type'] = 'raw';
        } else if($columnName == 'assignedTo' || $columnName == 'updatedBy') {
            $newColumn['value'] = 'empty($data["'.$columnName.'"])?'.
                'Yii::t("app","Anyone"):User::getUserLinks($data["'.$columnName.'"])';
            $newColumn['type'] = 'raw';
        } else {
            $newColumn['value'] = '$data["'.$columnName.'"]';
            $newColumn['type'] = 'raw';
        }

        if(Yii::app()->language == 'en') {
            $format =  "M d, yy";
        } else {

            // translate Yii date format to jquery
            $format = Yii::app()->locale->getDateFormat('medium');

            $format = str_replace('yy', 'y', $format);
            $format = str_replace('MM', 'mm', $format);
            $format = str_replace('M','m', $format);
        }

        return $newColumn;
    }

    protected function handleFields () {}

}
?>
