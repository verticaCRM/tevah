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

class RecordAliases extends CActiveRecord {

    /**
     * @var array $types
     */
    public static $types = array (
        'email', 'phone', 'skype', 'googlePlus', 'linkedIn', 'twitter', 'facebook', 'other');

    public static function model ($className=__CLASS__) {
        return parent::model ($className);
    }

    public static function getActions () {
        return array (
            'createRecordAlias' => 
                'application.components.recordAliases.RecordAliasesCreateAction',
            'deleteRecordAlias' => 
                'application.components.recordAliases.RecordAliasesDeleteAction',
        );
    }

    public static function getAliases (X2Model $model, $aliasType = null) {
        $params =  array (
            ':type' => get_class ($model),
            ':recordId' => $model->id,
        );
        if ($aliasType) {
            $params[':aliasType'] = $aliasType;
        }
        $aliases = RecordAliases::model ()->findAll (array (
            'condition' => 'recordType=:type AND recordId=:recordId'.
                ($aliasType ? ' AND aliasType=:aliasType' : ''),
            'group' => 'aliasType, alias',
            'params' => $params,
        ));
        return $aliases;
    }

    public function tableName () {
        return 'x2_record_aliases';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules () {
        return array(
            array('recordId, aliasType, alias', 'required'),
            array('aliasType', 'validateAliasType'),
            array('recordId', 'validateRecordId'),
            array('alias', 'validateRecordAlias'),
            array('alias', 'validateAliasUniqueness', 'on' => 'insert'),
        );
    }

    public function getModel () {
        $recordType = $this->recordType;
        return $recordType::model ()->findByPk ($this->recordId);
    }

    public function validateAliasType ($attribute) {
        $value = $this->$attribute;
        if (!in_array ($value, self::$types)) {
            throw new CHttpException (400, Yii::t('app', 'Invalid alias type'));
        }
    }

    public function validateRecordId () {
        if (!$this->getModel ()) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record id or type')) ;
        }
    }

    public function renderAlias () {
        switch ($this->aliasType) {
            case 'email':
                return X2Html::renderEmailLink ($this->alias);
            case 'phone':
                return X2Html::renderPhoneLink ($this->alias);
            default:
                return CHtml::encode ($this->alias);
        }
    }

    public function validateAliasUniqueness () {
        if (self::findByAttributes (array_diff_key ($this->getAttributes (), array (
            'id' => true)))) {

            $this->addError (
                'alias', 
                Yii::t('app', 'This record already has an {aliasType} alias with the '.
                    'name "{alias}"', array (
                    '{aliasType}' => $this->aliasType,
                    '{alias}' => $this->alias,
                )));
        }
    }

    public function validateRecordAlias ($attribute) {
        $value = $this->$attribute;
        switch ($this->aliasType) {
            case 'email':
                $emailValidator = CValidator::createValidator ('email', $this, 'alias');
                $emailValidator->validate ($this, 'email');
                break;
        }
    }

    public function attributeLabels () {
        return array (
            'aliasType' => Yii::t('app', 'Alias Type'),
            'alias' => Yii::t('app', 'Alias'),
        );
    }

    public function getAllIcons () {
        $icons = array ();
        foreach (self::$types as $type) {
            $icons[$type] = $this->getIcon (false, false, $type);
        }
        return $icons;
    }

    public function getIcon ($includeTitle=false, $large=false, $aliasType=null) {
        if ($aliasType === null) {
            $aliasType = $this->aliasType;
        }
        
        $class = '';
        switch ($aliasType) {
            case 'email':
                $class = 'fa-at';
                break;
            case 'phone':
                $class = 'fa-phone';
                break;
            case 'skype':
                $class = 'fa-skype';
                break;
            case 'googlePlus':
                $class = 'fa-google-plus';
                break;
            case 'linkedIn':
                $class = 'fa-linkedin';
                break;
            case 'twitter':
                $class = 'fa-twitter';
                break;
            case 'facebook':
                $class = 'fa-facebook';
                break;
        }
        if ($includeTitle) $aliasOptions = $this->getAliasTypeOptions ();
        if ($large) $class .= ' fa-lg';
        return 
            '<span '.($includeTitle ? 
                'title="'.CHtml::encode ($aliasOptions[$aliasType]).'" ' : '')
            .'class="fa '.$class.'"></span>';
    }

    private $_aliasTypeOptions;
    public function getAliasTypeOptions () {
        if (!isset ($this->_aliasTypeOptions)) {
            $this->_aliasTypeOptions = array ( 
                'email' => Yii::t('app', 'email'),
                'phone' => Yii::t('app', 'phone'),
                'skype' => 'Skype',
                'googlePlus' => 'Google+',
                'linkedIn' => 'LinkedIn',
                'twitter' => 'Twitter',
                'facebook' => 'Facebook',
                'other' => 'Other',
            );
        }
        return $this->_aliasTypeOptions;
    }

}

?>
