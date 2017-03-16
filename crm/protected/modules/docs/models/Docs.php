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

// Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_docs".
 *
 * @package application.modules.docs.models
 */
class Docs extends X2Model {

    public $supportsWorkflow = false;

    /**
     * Returns the static model of the specified AR class.
     * @return Docs the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_docs';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
                    'X2LinkableBehavior' => array(
                        'class' => 'X2LinkableBehavior',
                        'module' => 'docs',
                    ),
                    'ERememberFiltersBehavior' => array(
                        'class' => 'application.components.ERememberFiltersBehavior',
                        'defaults' => array(),
                        'defaultStickOnClear' => false
                    )
                ));
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    public function rules() {

        return array_merge(array(
            array('name','menuCheck','on'=>'menu')
        ),
                parent::rules()
                );
    }

    public function menuCheck($attr,$params=array()) {
        $this->$attr;
        $this->scenario = 'menu';

        if(sizeof(Modules::model()->findAllByAttributes(array('name'=>$this->name))) > 0)
        {
            $this->addError('name', 'That name is not available.');
        }
      }

    public function parseType() {
        if (!isset($this->type))
            $this->type = '';
        switch ($this->type) {
            case 'email':
                return Yii::t('docs', 'Template');
            default:
                return Yii::t('docs', 'Document');
        }
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        // $criteria->compare('id',$this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('subject', $this->subject, true);
        // $criteria->compare('text',$this->text,true);
        $criteria->compare('createdBy', $this->createdBy, true);
        $criteria->compare('createDate', $this->createDate);
        $criteria->compare('updatedBy', $this->updatedBy, true);
        $criteria->compare('lastUpdated', $this->lastUpdated);
        $criteria->compare('type', $this->type);

        if (!Yii::app()->params->isAdmin) {
            $condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy="' . Yii::app()->user->getName() . '" OR editPermissions LIKE "%' . Yii::app()->user->getName() . '%"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()
                ->select('groupId')
                ->from('x2_group_to_user')
                ->where('userId=' . Yii::app()->user->getId())
                ->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR createdBy IN (' . implode(',', $groupLinks) . ')';

            $condition .= 'OR (visibility=2 AND createdBy IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . ')))';
            $criteria->addCondition($condition);
        }
        // $criteria->compare('editPermissions',$this->editPermissions,true);

        $dateRange = X2DateUtil::partialDateRange($this->createDate);
        if ($dateRange !== false)
            $criteria->addCondition('createDate BETWEEN ' . $dateRange[0] . ' AND ' . $dateRange[1]);

        $dateRange = X2DateUtil::partialDateRange($this->lastUpdated);
        if ($dateRange !== false)
            $criteria->addCondition('lastUpdated BETWEEN ' . $dateRange[0] . ' AND ' . $dateRange[1]);

        return new SmartActiveDataProvider('Docs', array(
                    'pagination' => array(
                        'pageSize' => Profile::getResultsPerPage(),
                    ),
                    'sort' => array(
                        'defaultOrder' => 'lastUpdated DESC, id DESC',
                    ),
                    'criteria' => $criteria,
                ));
    }

    /**
     * Replace tokens with model attribute values.
     *
     * @param type $str Input text
     * @param X2Model $model Model to use for replacement
     * @param array $vars List of extra variables to replace
     * @param bool $encode Encode replacement values if true; use renderAttribute otherwise.
     * @return string
     */
    public static function replaceVariables($str,$model,$vars = array(),$encode = false,$renderFlag=true) {
        if($encode) {
            foreach(array_keys($vars) as $key)
                $vars[$key] = CHtml::encode($vars[$key]);
        }
        $str = strtr($str,$vars);    // replace any manually set variables

        if($model instanceof X2Model) {
            if(get_class($model) !== 'Quote') {
                $str = Formatter::replaceVariables($str, $model, '', $renderFlag, false);
            } else {
                // Specialized, separate method for quotes that can use details from
                // either accounts or quotes.
                // There may still be some stray quotes with 2+ contacts on it, so
                // explode and pick the first to be on the safe side. The most
                // common use case by far is to have only one contact on the quote.
                $accountId = $model->accountName;
                $staticModels = array('Contact' => Contacts::model(), 'Account' => Accounts::model(), 'Quote' => Quote::model());
                $models = array(
                    'Contact' => $model->contact,
                    'Account' => empty($accountId) ? null : $staticModels['Account']
                        ->findByAttributes(array('nameId' => $accountId)),
                    'Quote' => $model
                );
                $attributes = array();
                foreach($models as $name => $modelObj) {
                    $moduleTitle = Modules::displayName(false, $name."s");
                    if(empty($modelObj)) {
                        // Model will be blank
                        foreach ($staticModels[$name]->fields as $field) {
                            $attributes['{' . $moduleTitle . '.' . $field->fieldName . '}'] = '';
                        }
                    } else {
                        // Insert attributes
                        foreach($modelObj->attributes as $fieldName => $value) {
                            $attributes['{' . $moduleTitle. '.' . $fieldName . '}'] = $encode ? 
                                CHtml::encode($value) : 
                                $modelObj->renderAttribute($fieldName, false);
                        }
                    }
                }
                $quoteTitle = Modules::displayName(false, "Quotes");
                $quoteParams = array(
                    '{'.$quoteTitle.'.lineItems}' => $model->productTable(true),
                    '{'.$quoteTitle.'.dateNow}' => date("F d, Y", time()),
                    '{'.$quoteTitle.'.quoteOrInvoice}' => Yii::t('quotes',
                                $model->type=='invoice' ? 'Invoice' : $quoteTitle),
                );
                // Run the replacement:
                $str = strtr($str,array_merge($attributes,$quoteParams));
                return $str;
            }
        }
        return $str;
    }

    /**
     * Returns a list of email available email templates.
     *
     * Email and quote are the only two types of supported templates;
     * no design has yet been done to completely generalize templating to
     * accomodate generic models. Part of the challenge will lie in how,
     * for multiple associated contacts (i.e. an account) any reference
     * to a contact is ambiguous unless it is distinguished (i.e.
     * primary contact, secondary contact, etc.)
     *
     * Current solution to this problem: Templates only contain insertable attributes for one type
     *
     * @param type $type
     * @param string associationType Type associated with template (used for attribute replacement).
     *  If the empty string is passed, templates of all association types will be retrieved. 
     * @return type
     */
    public static function getEmailTemplates($type = 'email', $associationType=''){
        $templateLinks = array();
        if(in_array($type, array('email', 'quote'))){
            // $criteria = new CDbCriteria(array('order'=>'lastUpdated DESC'));
            $condition = 'TRUE';
            $params = array ();
            if(!Yii::app()->params->isAdmin){
                $params[':username'] = Yii::app()->user->getName();
                $condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy=:username ';

                /* x2temp */
                $uid = Yii::app()->getSuID();
                if(empty($uid)){
                    if(Yii::app()->params->noSession)
                        $uid = 1;
                    else
                        $uid = Yii::app()->user->id;
                }
                $groupLinks = Yii::app()->db->createCommand()
                    ->select('groupId')
                    ->from('x2_group_to_user')
                    ->where('userId='.$uid)
                    ->queryColumn();

                if(!empty($groupLinks))
                    $condition .= ' OR createdBy IN ('.implode(',', $groupLinks).')';

                $condition .= 
                    'OR (visibility=2 AND createdBy IN
                        (SELECT username FROM x2_group_to_user WHERE groupId IN
                            (SELECT groupId FROM x2_group_to_user WHERE userId='.$uid.')))';

                // $criteria->addCondition($condition);
            }

            // for email templates, retrieve only templates with given association type.
            // if associationType is empty, get templates of all association types
            if ($type === 'email' && $associationType !== '') {
                $condition .= ' AND (associationtype=:associationType)';
                $params[':associationType'] = $associationType;
            }
            // $templates = 
                //X2Model::model('Docs')->findAllByAttributes(array('type'=>'email'),$criteria);
            $params[':type'] = $type;

            $templateData = Yii::app()->db->createCommand()
                    ->select('id,name')
                    ->from('x2_docs')
                    ->where('type=:type AND ('.$condition.')', $params)
                    ->order('name ASC')
                    // ->andWhere($condition)
                    ->queryAll(false);
            foreach($templateData as &$row)
                $templateLinks[$row[0]] = $row[1];
        }
        return $templateLinks;
    }

    /**
     * @return bool true if user has edit permissions, false otherwise 
     */
    public function checkEditPermission () {
        $perm = $this->editPermissions;
        $pieces = explode(", ",$perm);
        if (Yii::app()->user->checkAccess('DocsUpdate') && 
            (Yii::app()->user->checkAccess('DocsAdmin') || 
             Yii::app()->user->getName()==$this->createdBy || 
             array_search(Yii::app()->user->getName(),$pieces)!==false || 
             Yii::app()->user->getName()==$perm)) {

             return true;
        } else {
            return false;
        }
    }

    /**
     * @return array names of models which support email templates 
     */
    public static function modelsWhichSupportEmailTemplates () {
        // get all x2model types not in blacklist
        return array_diff_key (X2Model::getModelNames (), array_flip (array (
            'Actions', 'Quote', 'Product', 'Opportunity','Campaign', 
        )));
    }

}
