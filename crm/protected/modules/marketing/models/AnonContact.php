<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/* @edition:pla */

/**
 * Model for an anonymous contact. This records information about a lead
 * before they register and are converted to a contact.
 *
 * @package application.models
 */
class AnonContact extends X2Model {

    public $supportsWorkflow = false;

    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_anon_contact';
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge (parent::relations (), 
                array (
                    'fingerprint' => array(self::BELONGS_TO, 'Fingerprint', 'fingerprintId'),
                )
            );
    }

    /**
     * Ensure a valid tracking key is set
     * @return boolean whether or not to save
     */
    public function beforeSave() {
        if($this->trackingKey === null) {
            $this->trackingKey = X2Model::model('Contacts')->getNewTrackingKey();
        }

        $maxAnonContacts = Yii::app()->settings->maxAnonContacts;
        $count = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_anon_contact')
                ->queryScalar();
        if ($count > $maxAnonContacts) {
            // Remove the last modified AnonContact and its associated Actions
            // if the limit has been reached.
            $lastModifiedId = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_anon_contact')
                    ->order('lastUpdated ASC')
                    ->queryScalar();
            $actions = X2Model::model('Actions')->deleteAllByAttributes(array(
                'associationType' => 'anoncontact',
                'associationId' => $lastModifiedId,
            ));
            // find and then delete so that the onAfterDelete event gets triggered
            $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
            if ($anonContact) {
                $anonContact->disableBehavior ('changelog');
                $anonContact->delete ();
            }
        }
        return parent::beforeSave();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = parent::rules();
        return $rules;
    }

    public function behaviors() {
        return array_merge (parent::behaviors (), array(
            'tags' => array('class' => 'TagBehavior'),
            'FingerprintBehavior'=>array(
                'class'=>'FingerprintBehavior',
            ),
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'module' => 'marketing',
                'autoCompleteSource' => null,
                'viewRoute' => '/marketing/marketing/anonContactView'
            ),
            'ERememberFiltersBehavior' => array(
                'class'=>'application.components.ERememberFiltersBehavior',
                'defaults'=>array(),
                'defaultStickOnClear'=>false
            ),
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('createDate', $this->createDate);
        $criteria->compare('lastUpdated', $this->lastUpdated);
        $criteria->compare('trackingKey', $this->trackingKey);
        $criteria->compare('email', $this->email);
        $criteria->compare('leadscore', $this->leadscore);

        if (!Yii::app()->user->isGuest) {
            $pageSize = Profile::getResultsPerPage();
        } else {
            $pageSize = 20;
        }

        return new SmartActiveDataProvider(get_class($this), array(
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'criteria' => $criteria,
        ));
    }

    /**
     * Tries to find an anonymous contact by fingerprint 
     * @param int $fingerprint 
     * @return null|AnonContact
     */
    public function findByFingerprint ($fingerprint, $attributes) {
        $anonContact = null;

        $fingerprintRecord = X2Model::model('Fingerprint')
            ->findByAttributes(array(
                'fingerprint'=>$fingerprint,
                'anonymous'=>1,
            ));

        if (!isset($fingerprintRecord)) {
            // Try a partial match in case the fingerprint has changed
            list ($contact, $bits) = Fingerprint::partialMatch($attributes);
            if ($contact !== null && $contact instanceof AnonContact) {
                $fingerprintRecord = X2Model::model('Fingerprint')
                    ->findByPk($contact->fingerprintId);
            }
        }
        if (isset($fingerprintRecord)) {
            $anonContact = X2Model::model('AnonContact')
                ->findByAttributes(
                    array('fingerprintId'=>$fingerprintRecord->id));
        }
        return $anonContact;
    }

}
