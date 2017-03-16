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
 * Handles parsing of attributes in reports attribute notation
 */

class ReportsAttributeParsingBehavior extends CBehavior {

    public function attach ($owner) {
        if (property_exists ($this, 'primaryModelType'))
            throw new CException ('owner must have property primaryModelType');
        parent::attach ($owner);
    }

    /**
     * @return CModel static model for specified primary model type 
     */
    private $_primaryModel;
    public function getPrimaryModel () {
        if (!isset ($this->_primaryModel)) {
            $primaryModelType = $this->owner->primaryModelType;
            $this->_primaryModel = $primaryModelType::model ();
        }
        return $this->_primaryModel;
    }

    /**
     * Separate date and aggregate functions from the attribute 
     */
    public function parseFns ($attr, array $fns=array ()) {
        $matches = array ();
        if (preg_match ('/^([^(]+)\(.*\)$/', $attr, $matches)) {
            $fns[] = $matches[1];
            $attr = preg_replace ('/[^(]+\(/', '', $attr);
            $attr = preg_replace ('/\)$/', '', $attr);
            return $this->parseFns ($attr, $fns);
        } else {
            return array ($attr, $fns);
        }
    }

    /**
     * Parses attribute specified with dot notation and returns model and attribute
     * @return array    
     */
    public function getModelAndAttr ($attr) {
        list ($attr, $fns) = $this->parseFns ($attr); 

        if ($attr === '*') return array ($this->getPrimaryModel (), $attr, $fns, null);

        $pieces = explode ('.', $attr);
        $linkField = null;
        if (count ($pieces) > 1) {
            $linkField = $pieces[0];
            $relatedModel = $this->_getRelatedModel ($linkField);
            $columnAttrModel = $relatedModel;
            $columnAttr = $pieces[1];
        } else {
            $columnAttrModel = $this->getPrimaryModel ();
            $columnAttr = $pieces[0];
        }
        return array ($columnAttrModel, $columnAttr, $fns, $linkField);
    }

    /**
     * @param string $linkField Name of the field whose corresponding model should be returned
     * @return CModel
     */
    private $_relatedModelsByLinkField = array ();
    public function _getRelatedModel ($linkField) {
        if (isset ($this->_relatedModelsByLinkField[$linkField])) {
            return $this->_relatedModelsByLinkField[$linkField];
        }
        if ($this->owner->primaryModelType === 'Actions') {
            $this->_relatedModelsByLinkField[$linkField] = $linkField::model ();
        } else {
            $model = $this->getPrimaryModel ();
            $field = $model->getField ($linkField);
            $linkType = $field->linkType;
            $this->_relatedModelsByLinkField[$linkField] = $linkType::model ();
        }
        return $this->_relatedModelsByLinkField[$linkField];
    }

}

?>
