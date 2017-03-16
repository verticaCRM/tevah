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
 * This is the model class for table "x2_contacts".
 *
 * @package application.modules.contacts.models
 */
class Contacts extends X2Model {

    public $name;

    /**
     * Returns the static model of the specified AR class.
     * @return Contacts the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge(parent::relations(), array(
            /* x2plastart */
            'fingerprint' => array(self::BELONGS_TO, 'Fingerprint', 'fingerprintId'),
            /* x2plaend */
        ));
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_contacts';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'module' => 'contacts',
            ),
            /* x2plastart */
            'FingerprintBehavior' => array(
                'class' => 'FingerprintBehavior',
            ),
            /* x2plaend */
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'X2AddressBehavior' => array(
                'class' => 'application.components.X2AddressBehavior',
            ),
            'X2DuplicateBehavior' => array(
                'class' => 'application.components.X2DuplicateBehavior',
            ),
            'ContactsNameBehavior' => array(
                'class' => 'application.components.ContactsNameBehavior',
            ),
        ));
    }

    public function rules() {
        $parentRules = parent::rules();
        $parentRules[] = array(
            'firstName,lastName', 'required', 'on' => 'webForm');
        return $parentRules;
    }

    public function duplicateFields() {
        return array_merge(parent::duplicateFields(), array(
            'email',
        ));
    }

    public function afterFind() {
        parent::afterFind();
        if ($this->trackingKey === null && self::$autoPopulateFields) {
            $this->trackingKey = self::getNewTrackingKey();
            $this->update(array('trackingKey'));
        }
    }

    /**
     * @return boolean whether or not to save
     */
    public function beforeSave() {
        if ($this->trackingKey === null) {
            $this->trackingKey = self::getNewTrackingKey();
        }

        return parent::beforeSave();
    }

    /**
     * Responds when {@link X2Model::afterUpdate()} is called (record saved, but
     * not a new record). Sends a notification to anyone subscribed to this contact.
     *
     * Before executing this, the model must check whether the contact has the
     * "changelog" behavior. That is because the behavior is disabled
     * when checking for duplicates in {@link ContactsController}
     */
    public function afterUpdate() {
        if (!Yii::app()->params->noSession && $this->asa('changelog') &&
                $this->asa('changelog')->enabled) {//$this->scenario != 'noChangelog') {
            // send subscribe emails if anyone has subscribed to this contact
            $result = Yii::app()->db->createCommand()
                    ->select('user_id')
                    ->from('x2_subscribe_contacts')
                    ->where('contact_id=:id', array(':id' => $this->id))
                    ->queryColumn();

            $datetime = Formatter::formatLongDateTime(time());
            $modelLink = CHtml::link($this->name, Yii::app()->controller->createAbsoluteUrl('/contacts/' . $this->id));
            $subject = 'X2Engine: ' . $this->name . ' updated';
            $message = "Hello,<br>\n<br>\n";
            $message .= 'You are receiving this email because you are subscribed to changes made to the contact ' . $modelLink . ' in X2Engine. ';
            $message .= 'The following changes were made on ' . $datetime . ":<br>\n<br>\n";

            foreach ($this->getChanges() as $attribute => $change) {
                if ($attribute != 'lastActivity') {
                    $old = $change[0] == '' ? '-----' : $change[0];
                    $new = $change[1] == '' ? '-----' : $change[1];
                    $label = $this->getAttributeLabel($attribute);
                    $message .= "$label: $old => $new<br>\n";
                }
            }

            $message .="<br>\nYou can unsubscribe to these messages by going to $modelLink and clicking Unsubscribe.<br>\n<br>\n";

            $adminProfile = Yii::app()->params->adminProfile;
            foreach ($result as $subscription) {
                $subscription = array();
                if (isset($subscription['user_id'])) {
                    $profile = X2Model::model('Profile')->findByPk($subscription['user_id']);
                    if ($profile && $profile->emailAddress && $adminProfile && $adminProfile->emailAddress) {
                        $to = array('to' => array(array($profile->fullName, $profile->emailAddress)));
                        Yii::app()->controller->sendUserEmail($to, $subject, $message, null, Credentials::$sysUseId['systemNotificationEmail']);
                    }
                }
            }
        }


        parent::afterUpdate();
    }

    public static function getNames() {

        $criteria = $this->getAccessCriteria();

        // $condition = 'visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
        // /* x2temp */
        // $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
        // if(!empty($groupLinks))
        // $condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';
        // $condition .= 'OR (visibility=2 AND assignedTo IN
        // (SELECT username FROM x2_group_to_user WHERE groupId IN
        // (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
        $contactArray = X2Model::model('Contacts')->findAll($condition);
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    /**
     *    Returns all public contacts.
     *    @return $names An array of strings containing the names of contacts.
     */
    public static function getAllNames() {
        $contactArray = X2Model::model('Contacts')->findAll($condition = 'visibility=1');
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    public static function getContactLinks($contacts) {
        if (!is_array($contacts))
            $contacts = explode(' ', $contacts);

        $links = array();
        foreach ($contacts as &$id) {
            if ($id != 0) {
                $model = X2Model::model('Contacts')->findByPk($id);
                if (isset($model))
                    $links[] = CHtml::link($model->name, array('/contacts/contacts/view', 'id' => $id));
                //$links.=$link.', ';
            }
        }
        //$links=substr($links,0,strlen($links)-2);
        return implode(', ', $links);
    }

    public static function getMailingList($criteria) {

        $mailingList = array();

        $arr = X2Model::model('Contacts')->findAll();
        foreach ($arr as $contact) {
            $i = preg_match("/$criteria/i", $contact->backgroundInfo);
            if ($i >= 1) {
                $mailingList[] = $contact->email;
            }
        }
        return $mailingList;
    }

    /**
     * An alias for search ()
     */
	public function searchAll($pageSize=null) {
        return $this->search ($pageSize);
    }

    public function searchMyContacts() {
        $criteria = new CDbCriteria;

        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        // $condition = 'assignedTo="'.Yii::app()->user->getName().'"';
        // $parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
        // $parameters['condition']=$condition;
        // $criteria->scopes=array('findAll'=>array($parameters));

        return $this->searchBase($criteria);
    }

    public function searchNewContacts() {
        $criteria = new CDbCriteria;
        // $condition = 'assignedTo="'.Yii::app()->user->getName().'" AND createDate > '.mktime(0,0,0);
        $condition = 't.createDate > ' . mktime(0, 0, 0);
        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        $parameters = array('limit' => ceil(Profile::getResultsPerPage()));

        $parameters['condition'] = $condition;
        $criteria->scopes = array('findAll' => array($parameters));

        return $this->searchBase($criteria);
    }

    /**
     * Adds tag filtering to search base 
     */
    public function search($pageSize=null) {
        $criteria = new CDbCriteria;
		if(isset($_GET['tagField']) && !empty($_GET['tagField'])) {	// process the tags filter
            
            //remove any spaces around commas, then explode to array
            $tags = explode(',',preg_replace('/\s?,\s?/',',',trim($_GET['tagField'])));    
            $inQuery = array ();
            $params = array ();
            for($i=0; $i<count($tags); $i++) {
                if(empty($tags[$i])) {
                    unset($tags[$i]);
                    $i--;
                    continue;
                } else {
                    if($tags[$i][0] != '#') {
                        $tags[$i] = '#'.$tags[$i];
                    }
                    $inQuery[] = 'b.tag LIKE BINARY :'.$i;
                    $params[':'.$i] = $tags[$i];
                    //$tags[$i] = 'b.tag = "'.$tags[$i].'"';
                }
            }
            // die($str);
            //$tagConditions = implode(' OR ',$tags);
            $tagConditions = implode(' OR ',$inQuery);

            $criteria->distinct = true;
            $criteria->join .= ' JOIN x2_tags b ON (b.itemId=t.id AND b.type="Contacts" '.
                'AND ('.$tagConditions.'))';
            $criteria->params = $params;
        }
        return $this->searchBase($criteria, $pageSize);
    }

    public function searchAdmin() {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria);
    }

    public function searchAccount($id) {
        $criteria = new CDbCriteria;
        $criteria->compare('company', $id);

        return $this->searchBase($criteria);
    }

    /**
     * Returns a DataProvider for all the contacts in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('Contacts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

    /**
     * Generates a random tracking key and guarantees uniqueness
     * @return String $key a unique random tracking key
     */
    public static function getNewTrackingKey() {

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // try up to 100 times to guess a unique key
        for ($i = 0; $i < 100; $i++) {
            $key = '';
            for ($j = 0; $j < 32; $j++)    // generate a random 32 char alphanumeric string
                $key .= substr($chars, rand(0, strlen($chars) - 1), 1);

            // check if this key is already used
            if (X2Model::model('Contacts')->exists('trackingKey="' . $key . '"'))
                continue;
            else
                return $key;
        }
        return null;
    }

    /* x2plastart */

    /**
     * Sets values of attributes with values of corresponding attributes in the anon contact record.
     * Also migrates over actions and notifications associated with the anon contact. Finally,
     * the anonymous contact is deleted.
     * @param AnonContact $anonContact The anonymous contact record whose attributes will be
     *  merged in with this contact
     */
    public function mergeWithAnonContact(AnonContact $anonContact) {
        $fingerprintRecord = $anonContact->fingerprint;

        // Migrate over existing AnonContact data
        if (!isset($this->leadscore)) {
            $this->leadscore = $anonContact->leadscore;
        }
        if (!isset($this->email)) {
            $this->email = $anonContact->email;
        }
        if (!isset($this->reverseIp)) {
            $this->reverseIp = $anonContact->reverseIp;
        }
        $fingerprintRecord->anonymous = false;
        $fingerprintRecord->update('anonymous');
        $this->mergeRelatedRecords($anonContact);
        $this->fingerprintId = $fingerprintRecord->id;
        // Update the fingerprintId so that the Fingerprint is not deleted
        // by afterDelete() when the AnonContact is deleted.
        $this->update(array('fingerprintId'));
        $anonContact->delete();
    }

    /* x2plaend */

}
