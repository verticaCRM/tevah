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

abstract class X2ReportFormModel extends CFormModel {
    public $primaryModelType = 'Contacts';
    public $allFilters = array ();
    public $anyFilters = array ();
    public $refreshForm = false;
    public $export = false;
    public $print = false;
    public $email = false;

    /**
     * @var bool $includeTotalsRow
     */
    public $includeTotalsRow; 

    /**
     * @var string $_reportType
     */
    private $_reportType; 

    public function behaviors () {
        return array (
            'ReportsAttributeParsingBehavior' => array (
                'class' => 'application.modules.reports.components.ReportsAttributeParsingBehavior'
            ),
        );
    }

    public function rules () {
        return array (
            array (
                'refreshForm, export, print, email, includeTotalsRow', 'boolean'
            ), 
            array (
                'primaryModelType', 'required',
            ),
            array (
                'primaryModelType', 'application.components.validators.ModuleModelNameValidator', 
                'throwExceptions' => true,
                'includeActions' => true,
            ),
            array (
                'allFilters,anyFilters',
                'application.components.validators.ArrayValidator',
                'throwExceptions' => true,
                'allowEmpty' => true,
            ),
            array (
                'allFilters,anyFilters',
                'validateFilters',
            ),
        );
    }

    public function getReportType () {
        if (!isset ($this->_reportType)) {
            $this->_reportType = lcfirst (
                preg_replace ('/ReportFormModel$/', '', get_class ($this)));
        }
        return $this->_reportType;
    }

    /**
     * Allows form refresh validation to be handled specially
     */
    public function validate ($attributes=null, $clearErrors=true) {
        if ($this->refreshForm) {
            return parent::validate (array ('refreshForm', 'primaryModelType'), $clearErrors);
        } else {
            return parent::validate ($attributes, $clearErrors);
        }
    }

    /**
     * Allows form refresh validation to be handled specially
     */
    public function setAttributes ($values, $safeOnly=true) {
        if (isset ($values['refreshForm'])) {
            $newValues = array ();
            foreach ($values as $name => $val) {
                if (in_array ($name, array ('refreshForm', 'primaryModelType'))) {
                    $newValues[$name] = $val;
                }
            }
            $values = $newValues;
        }
        return parent::setAttributes ($values, $safeOnly);
    }

    public function getSettings () {
        $settings = $this->getAttributes ();
        unset ($settings['refreshForm']);
        return $settings;
    }

    /**
     * @return array attributes to pass to {@link X2Report}
     */
    public function getReportAttributes () {
        $attributes = $this->getAttributes ();
        unset ($attributes['refreshForm']);
        return $attributes;
    }

    public function attributeLabels () {
        return array (
            'primaryModelType' => Yii::t('reports', 'Primary Record Type'),
            'allFilters' => Yii::t('reports', 'Records must pass all of these conditions:'), 
            'anyFilters' => Yii::t('reports', 'Records must pass any of these conditions:'), 
            'includeTotalsRow' => Yii::t('reports', 'Include totals row?'), 
        );
    }

    /**
     * Validates 'any' and 'all' filters 
     */
    public function validateFilters ($attribute) {
        $value = &$this->$attribute;
        $valid = true;
        foreach ($value as &$arr) {
            if (array_keys ($arr) !== array ('name', 'operator', 'value') ||
                !in_array ($arr['operator'], array ('=', '>', '<', '>=', '<=', '<>', 'notEmpty',
                    'empty', 'list', 'notList', 'noContains', 'contains'), true)) {

                $valid = false;
                break;
            }
            $this->_validateAttrs (array ($arr['name']));
            list ($model, $attr, $fns, $linkField) = $this->getModelAndAttr ($arr['name']);
            $field = $model->getField ($attr);
            $arr['value'] = $field->parseValue ($arr['value']); 
        }

        if (!$valid) {
            throw new CHttpException (
                400, Yii::t('reports', 'Invalid report filter'));
        }
        return true;
    }

    /**
     * Like {@link validateAttrs} but for a single attribute 
     */
    public function validateAttr ($attribute, $params=array ()) {
        $value = $this->$attribute;
        if (isset ($params['empty']) && $params['empty'] && empty ($value)) return;
        $this->_validateAttrs (array ($value));
    }

    /**
     * Ensure that attributes are either names of attributes of primary model type or are of the
     * form <link field name>.<link type attribute name>
     * @throws CHttpException
     */
    public function validateAttrs ($attribute, $params=array ()) {
        $value = $this->$attribute;
        if (!is_array ($value)) return true;
        return $this->_validateAttrs ($value, $attribute, isset ($params['unique']) ? 
            $params['unique'] : false);
    }

    /**
     * Validates attributes and sort directions
     */
    public function validateOrderBy ($attribute, $params=array ()) {
        $value = $this->$attribute;
        $attributes = array_map (function ($entry) {
                return $entry[0];
            }, $value);

        $this->_validateAttrs ($attributes, $attribute, isset ($params['unique']) ? 
            $params['unique'] : false);
        $sortDirections = array_map (function ($entry) {
                return $entry[1];
            }, $value);
        $valid = $this->_validateSortDirections ($sortDirections);
        if (!$valid) {
            throw new CHttpException (400, Yii::t('reports', 'Invalid order by attribute name'));
        }
    }

    protected function _validateSortDirections ($sortDirections) {
        $valid = true;
        foreach ($sortDirections as $fn) {
            if (!in_array ($fn, array ('asc', 'desc'))) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    /**
     * Ensure that attributes are either names of attributes of primary model type or are of the
     * form <link field name>.<link type attribute name>
     * @throws CHttpException
     */
    protected function _validateAttrs (array $value, $attribute=null, $uniqueConstraint=false) {
        $valid = true;
        $primaryModelType = $this->primaryModelType;
        if ($uniqueConstraint && array_unique ($value) !== $value) {
            $this->addError ($attribute, Yii::t('reports', '{attribute} must be unique', array (
                '{attribute}' => ucfirst ($this->getAttributeLabel ($attribute)),
            )));
        }

        foreach ($value as $name) {
            $matches = array ();

            // check for date function
            $fnName = null;
            if (preg_match ('/^(year|month|day|hour|minute|second)\(.*\)$/', $name, $matches)) {
                $fnName = $matches[1];
                $name = preg_replace ('/[^(]+\(/', '', $name);
                $name = preg_replace ('/\)$/', '', $name);
            }

            // parse dot notation
            $pieces = explode ('.', $name);
            if (count ($pieces) > 2) {
                $valid = false;
                break;
            }
            if (count ($pieces) > 1) { // link field

                $relatedField = $pieces[1];
                $linkFieldName = $pieces[0];
                if ($primaryModelType === 'Actions' &&
                    in_array ($linkFieldName, array_keys (X2Model::getModelNames ()))) {

                    // actions link fields can also be of the form 
                    // <model class A>.<attribute of model class A>
                    $linkFieldType = $pieces[0];
                } else {
                    if (!($linkField = $primaryModelType::model ()->getField ($linkFieldName))) {
                        $valid = false;
                        break;
                    }
                    $linkFieldType = $linkField->linkType;
                }

                $field = $linkFieldType::model ()->getField ($relatedField);
                if (!$field) {
                    $valid = false;
                    break;
                }
            } else { // field of primary model
                $field = $primaryModelType::model ()->getField ($name);
                if (!$field) {
                    $valid = false;
                    break;
                }
            }

            if ($fnName) { 
                // validate date function
                if ($field->type === 'date' && 
                    in_array ($fnName, array ('hour', 'minute', 'second'))) {

                    $valid = false;
                    break;
                }
            }
        }
        if (!$valid) {
            throw new CHttpException (400, Yii::t('reports', 'Invalid columns'));
        }
        return true;
    }

}
?>
