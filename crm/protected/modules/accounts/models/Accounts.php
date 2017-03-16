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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_accounts".
 *
 * @package application.modules.accounts.models
 */
class Accounts extends X2Model {

    /**
     * Returns the static model of the specified AR class.
     * @return Accounts the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_accounts';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'module' => 'accounts',
                'icon' => 'accounts_icon.png',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'InlineEmailModelBehavior' => array(
                'class' => 'application.components.InlineEmailModelBehavior',
            ),
            'X2AddressBehavior' => array(
                'class' => 'application.components.X2AddressBehavior',
            ),
            'X2DuplicateBehavior' => array(
                'class' => 'application.components.X2DuplicateBehavior',
            ),
        ));
    }

    public function duplicateFields() {
        return array_merge(array(
            'tickerSymbol',
            'website',
        ), parent::duplicateFields());
    }

    /**
     * Responds to {@link CModel::onBeforeValidate} event.
     * Fixes the revenue field before validating.
     *
     * @return boolean whether validation should be executed. Defaults to true.
     *//*
      public function beforeValidate() {
      $this->annualRevenue = Formatter::parseCurrency($this->annualRevenue,false);
      return parent::beforeValidate();
      } */

    public static function parseContacts($arr) {
        $str = "";
        foreach ($arr as $contact) {
            $str.=$contact . " ";
        }
        return $str;
    }

    public static function parseContactsTwo($arr) {
        $str = "";
        foreach ($arr as $id => $contact) {
            $str.=$id . " ";
        }
        return $str;
    }

    public static function editContactArray($arr, $model) {

        $pieces = explode(" ", $model->associatedContacts);
        unset($arr[0]);

        foreach ($pieces as $contact) {
            if (array_key_exists($contact, $arr)) {
                unset($arr[$contact]);
            }
        }

        return $arr;
    }

    public static function editUserArray($arr, $model) {

        $pieces = explode(', ', $model->assignedTo);
        unset($arr['Anyone']);
        unset($arr['admin']);
        foreach ($pieces as $user) {
            if (array_key_exists($user, $arr)) {
                unset($arr[$user]);
            }
        }
        return $arr;
    }

    public static function editUsersInverse($arr) {

        $data = array();

        foreach ($arr as $username)
            $data[] = CActiveRecord::model('User')->findByAttributes(array('username' => $username));

        $temp = array();
        foreach ($data as $item) {
            if (isset($item))
                $temp[$item->username] = $item->firstName . ' ' . $item->lastName;
        }
        return $temp;
    }

    public static function editContactsInverse($arr) {
        $data = array();

        foreach ($arr as $id) {
            if ($id != '')
                $data[] = CActiveRecord::model('Contacts')->findByPk($id);
        }
        $temp = array();

        foreach ($data as $item) {
            $temp[$item->id] = $item->firstName . ' ' . $item->lastName;
        }
        return $temp;
    }

    public static function getAvailableContacts($accountId = 0) {

        $availableContacts = array();

        $criteria = new CDbCriteria;
        $criteria->addCondition("accountId='$accountId'");
        $criteria->addCondition(array("accountId=''"), 'OR');


        $contactRecords = CActiveRecord::model('Contacts')->findAll($criteria);
        foreach ($contactRecords as $record)
            $availableContacts[$record->id] = $record->name;

        return $availableContacts;
    }

    public static function getContacts($accountId) {
        $contacts = array();
        $contactRecords = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId' => $accountId));
        if (!isset($contactRecords))
            return array();

        foreach ($contactRecords as $record)
            $contacts[$record->id] = $record->name;

        return $contacts;
    }

    public static function setContacts($contactIds, $accountId) {

        $account = CActiveRecord::model('Accounts')->findByPk($accountId);

        if (!isset($account))
            return false;

        // get all contacts currently associated
        $oldContacts = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId' => $accountId));
        foreach ($oldContacts as $contact) {
            if (!in_array($contact->id, $contactIds)) {
                $contact->accountId = 0;
                $contact->company = '';  // dissociate if they are no longer in the list
                $contact->save();
            }
        }

        // now set association for all contacts in the list
        foreach ($contactIds as $id) {
            $contactRecord = CActiveRecord::model('Contacts')->findByPk($id);
            $contactRecord->accountId = $account->id;
            $contactRecord->company = $account->name;
            $contactRecord->save();
        }
        return true;
    }

    public function search($pageSize = null, $uniqueId = null) {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria, $pageSize);
    }

    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('Accounts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC' // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else { //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

}
