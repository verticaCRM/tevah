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
 * This is the model class for table "x2_users".
 *
 * @property string $alias The user's alias, if set, or username otherwise. The
 *  user's alias is the "human-friendly" username that the user can configure to
 *  be whatever they choose. The username, however, cannot be changed, as there
 *  are references to it everywhere.
 * @property string $fullName The full name of the user, using the format defined
 *  in the general application settings.
 * @package application.modules.users.models
 */
class User extends CActiveRecord {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Full name (cached value)
     * @var type
     */
    private $_fullName;

    /**
     * @var bool If true, grid views displaying models of this type will have their filter and
     *  sort settings saved in the database instead of in the session
     */
    public $dbPersistentGridSettings = false;

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_users';
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'X2LinkableBehavior' => array(
                'class' => 'X2LinkableBehavior',
                'module' => 'users',
                'viewRoute' => '/profile',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            )
        ));
    }

    /**
     * Used to automatically set the user alias to the user name when a user is created.
     */
    public function beforeValidate () {
        if ($this->scenario === 'insert') {
            if ($this->userAlias === null)
                $this->userAlias = $this->username;
        }
        return parent::beforeValidate ();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('status', 'required'),
            array('password', 'required', 'on' => 'insert'),
            array('firstName, lastName, username', 'required'),
            array('userAlias', 'required'),
            array('status, lastLogin, login', 'numerical', 'integerOnly' => true),
            array('firstName, username, userAlias, title, updatedBy', 'length', 'max' => 20),
            array('lastName, department, officePhone, cellPhone, homePhone', 'length', 'max' => 40),
            array('password, address, emailAddress, recentItems, topContacts', 'length', 'max' => 100),
            array('lastUpdated', 'length', 'max' => 30),
            array('userKey', 'length', 'max' => 32, 'min' => 3),
            array('backgroundInfo', 'safe'),
            array('status', 'validateUserDisable'),
            array('username','in','not'=>true,'range'=>array('Guest','Anyone',Profile::GUEST_PROFILE_USERNAME),'message'=>Yii::t('users','The specified username is reserved for system usage.')),
            array('username', 'unique', 'allowEmpty' => false),
            array('userAlias', 'unique', 'allowEmpty' => false),
            array('userAlias', 'match', 'pattern' => '/^\s+$/', 'not' => true),
            array(
                'userAlias',
                'match',
                'pattern' => '/^((\s+\S+\s+)|(\s+\S+)|(\S+\s+))$/',
                'not' => true,
                'message' => Yii::t(
                    'users', 'Username cannot contain trailing or leading whitespace.'),
            ),
            array('username,userAlias','userAliasUnique'),
            array('username', 'match', 'pattern' => '/^\d+$/', 'not' => true), // No numeric usernames. That will break association with groups.
            array('username','match','pattern'=>'/^\w+$/'), // Username must be alphanumerics/underscores only
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, firstName, lastName, username, password, title, department, officePhone, cellPhone, homePhone, address, backgroundInfo, emailAddress, status, lastUpdated, updatedBy, recentItems, topContacts, lastLogin, login', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'profile' => array(self::HAS_ONE, 'Profile', 'id'),
        );
    }

    public function scopes () {
        return array (
            'active' => array (
                'condition' => 'status=1',
                'order' => 'lastName ASC',
            ),
        );
    }


    /**
     * Delete associated group to user records 
     */
    public function beforeDelete () {
        $adminUser = User::model()->findByPk(1);
        if (!$adminUser) {
            throw new CException (Yii::t('app', 'admin user could not be found'));
        }

        $params = array (
            ':username' => $this->username,
            ':adminUsername' => $adminUser->username
        );

        // reassign associated actions
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET completedBy=:adminUsername
            WHERE assignedTo=:username AND completedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute (array (
            ':username' => $this->username
        ));

        // reassign related contacts to anyone
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute (array (
            ':username' => $this->username
        ));

        return parent::beforeDelete ();
    }

    public function afterDelete () {
        // delete related social records (e.g. notes)
        $social=Social::model()->findAllByAttributes(array('user'=>$this->username));
        foreach($social as $socialItem){
            $socialItem->delete();
        }
        $social=Social::model()->findAllByAttributes(array('associationId'=>$this->id));
        foreach($social as $socialItem){
            $socialItem->delete();
        }

        X2CalendarPermissions::model()->deleteAllByAttributes (
            array(), 'user_id=:userId OR other_user_id=:userId', array (':userId' => $this->id)
        );

        // delete profile
        $prof=Profile::model()->findByAttributes(array('username'=>$this->username));
        if ($prof) $prof->delete();

        // delete associated events
        Yii::app()->db->createCommand()
            ->delete ('x2_events', 
                "user=:username OR (type='feed' AND associationId=".$this->id.")", 
                array (':username' => $this->username));

        // Delete associated group to user records 
        GroupToUser::model ()->deleteAll (array (
            'condition' => 'userId='.$this->id
        ));
        parent::afterDelete ();
    }


    public static function hasRole($user, $role){
        if(is_numeric($role)){
            $lookup = RoleToUser::model()->findByAttributes(array('userId' => $user, 'roleId' => $role));
            return isset($lookup);
        }else{
            $roleRecord = Roles::model()->findByAttributes(array('name' => $role));
            if(isset($roleRecord)){
                $lookup = RoleToUser::model()->findByAttributes(array('userId' => $user, 'roleId' => $roleRecord->id));
                return isset($lookup);
            }else{
                return false;
            }
        }
    }

    /**
     * Return ids of groups to which this user belongs 
     * @return array
     */
    public function getGroupIds () {
        $results = Yii::app()->db->createCommand ()
            ->select ('groupId')
            ->from ('x2_group_to_user')
            ->where ('userId=:id', array (':id' => $this->id))
            ->queryAll ();
        return array_map (function ($a) {
            return $a['groupId'];
        }, $results);
    }

    /**
     * Return model for current user 
     * @return object
     */
    public static function getMe () {
        return User::model()->findByPk (Yii::app()->getSuId());
    }

    public static function getUsersDataProvider () {
        $usersDataProvider = new CActiveDataProvider('User', array(
            'criteria' => array(
                'condition' => 'status=1',
                'order' => 'lastName ASC'
            )
        ));
        return $usersDataProvider;
    }

    /**
     * @return array (<username> => <full name>)
     */
    public static function getUserOptions () {
        $userOptions = Yii::app()->db->createCommand ("
            select username, concat(firstName, ' ', lastName) as fullName
            from x2_users
            where status=1
            order by lastName asc
        ")->queryAll ();
        return array_combine (
            array_map (function ($row) {
                return $row['username'];
            }, $userOptions),
            array_map (function ($row) {
                return $row['fullName'];
            }, $userOptions)
        );
    }


    /**
     * Populates an array of choices for an assignment dropdown menu
     * @return type
     */
    public static function getNames(){

        $userNames = array();
        $userModels = self::model()->findAllByAttributes(array('status' => 1));
        $userNames = array_combine(
                array_map(function($u){return $u->username;},$userModels),
                array_map(function($u){return $u->getFullName();},$userModels)
        );

        natcasesort($userNames);

        return array('Anyone' => Yii::t('app', 'Anyone')) + $userNames;
    }

    public static function getUserIds(){
        $userNames = array();
        $query = Yii::app()->db->createCommand()
                ->select('id, CONCAT(firstName," ",lastName) AS name')
                ->from('x2_users')
                ->where('status=1')
                ->order('name ASC')
                ->query();

        while(($row = $query->read()) !== false)
            $userNames[$row['id']] = $row['name'];
        natcasesort($userNames);

        return array('' => Yii::t('app', 'Anyone')) + $userNames;
    }

    public function getName(){
        return $this->firstName.' '.$this->lastName;
    }

    public static function getProfiles(){
        $arr = X2Model::model('User')->findAll('status="1"');
        $names = array('0' => Yii::t('app', 'All'));
        foreach($arr as $user){
            $names[$user->id] = $user->firstName." ".$user->lastName;
        }
        return $names;
    }

    public static function getTopContacts(){
        $userRecord = X2Model::model('User')->findByPk(Yii::app()->user->getId());

        //get array of IDs
        $topContactIds = empty($userRecord->topContacts) ? array() : explode(',', $userRecord->topContacts);
        $topContacts = array();
        //get record for each ID
        foreach($topContactIds as $contactId){
            $record = X2Model::model('Contacts')->findByPk($contactId);
            if(!is_null($record)) //only include contact if the contact ID exists
                $topContacts[] = $record;
        }
        return $topContacts;
    }

    public static function getRecentItems(){
        $userRecord = X2Model::model('User')->findByPk(Yii::app()->user->getId());

        //get array of type-ID pairs
        $recentItemsTemp = empty($userRecord->recentItems) ?
            array() : explode(',', $userRecord->recentItems);
        $recentItems = array();

        //get record for each ID/type pair
        foreach($recentItemsTemp as $item){
            $itemType = strtok($item, '-');
            $itemId = strtok('-');

            switch($itemType){
                case 'c': // contact
                    $record = X2Model::model('Contacts')->findByPk($itemId);
                    break;
                case 't': // action
                    $record = X2Model::model('Actions')->findByPk($itemId);
                    break;
                case 'a': // account
                    $record = X2Model::model('Accounts')->findByPk($itemId);
                    break;
                case 'p': // campaign
                    $record = X2Model::model('Campaign')->findByPk($itemId);
                    break;
                case 'o': // opportunity
                    $record = X2Model::model('Opportunity')->findByPk($itemId);
                    break;
                case 'w': // workflow
                    $record = X2Model::model('Workflow')->findByPk($itemId);
                    break;
                case 's': // service case
                    $record = X2Model::model('Services')->findByPk($itemId);
                    break;
                case 'd': // document
                    $record = X2Model::model('Docs')->findByPk($itemId);
                    break;
                case 'l': // x2leads object
                    $record = X2Model::model('X2Leads')->findByPk($itemId);
                    break;
                case 'm': // media object
                    $record = X2Model::model('Media')->findByPk($itemId);
                    break;
                case 'r': // product
                    $record = X2Model::model('Product')->findByPk($itemId);
                    break;
                case 'q': // product
                    $record = X2Model::model('Quote')->findByPk($itemId);
                    break;
                case 'g': // group
                    $record = X2Model::model('Groups')->findByPk($itemId);
                    break;
                case 'f': // x2flow
                    $record = X2Flow::model()->findByPk($itemId);
                    break;
                default:
                    printR('Warning: getRecentItems: invalid item type'.$itemType);
                    continue;
            }
            if(!is_null($record)) //only include item if the record ID exists
                array_push($recentItems, array('type' => $itemType, 'model' => $record));
        }
        return $recentItems;
    }

    private static $validRecentItemTypes = array(
        'a', // account
        'c', // contact
        'd', // doc
        'f', // x2flow
        'g', // group
        'l', // x2lead object
        'm', // media object
        'o', // opportunity
        'p', // campaign
        'q', // quote
        'r', // product
        's', // service case
        't', // action
        'w', // workflow
    );

    public static function addRecentItem($type, $itemId, $userId){
        if(in_array($type, self::$validRecentItemTypes)){ //only proceed if a valid type is given
            $newItem = $type.'-'.$itemId;

            $userRecord = X2Model::model('User')->findByPk($userId);
            //create an empty array if recentItems is empty
            $recentItems = ($userRecord->recentItems == '') ? 
                array() : explode(',', $userRecord->recentItems);
            $existingEntry = array_search($newItem, $recentItems); //check for a pre-existing entry
            if($existingEntry !== false)        //if there is one,
                unset($recentItems[$existingEntry]);    //remove it
            array_unshift($recentItems, $newItem);    //add new entry to beginning

            while(count($recentItems) > 10){ //now if there are more than 10 entries,
                array_pop($recentItems);  //remove the oldest ones
            }
            $userRecord->setAttribute('recentItems', implode(',', $recentItems));
            $userRecord->update();
        }
    }

    /**
     * Generate a link to a user or group.
     *
     * Creates a link or list of links to a user or group to be displayed on a record.
     * @param integer|array|string $users If array, links to a group; if integer, the group whose 
     *  ID is that value; if keyword "Anyone", not a link but simply displays "anyone".
     * @param boolean $makeLinks Can be set to False to disable creating links but still return the name of the linked-to object
     * @return string The rendered links
     */
    public static function getUserLinks($users, $makeLinks = true, $useFullName = true){
        if(!is_array($users)){
            /* x2temp */
            if(preg_match('/^\d+$/',$users)){
                $group = Groups::model()->findByPk($users);
                if(isset($group))
                //$link = $makeLinks ? CHtml::link($group->name, array('/groups/groups/view', 'id' => $group->id)) : $group->name;
                    $link = $makeLinks ? 
                        CHtml::link(
                            $group->name, 
                            Yii::app()->controller->createAbsoluteUrl(
                                '/groups/groups/view', array('id' => $group->id)),array('style'=>'text-decoration:none;')) : 
                        $group->name;
                else
                    $link = '';
                return $link;
            }
            /* end x2temp */
            if($users == '' || $users == 'Anyone')
                return Yii::t('app', 'Anyone');

            $users = explode(', ', $users);
        }
        $links = array();
        $userCache = Yii::app()->params->userCache;
        
        foreach($users as $user){
            if($user == 'Anyone' || $user == 'Email'){  // skip these, they aren't users
                continue;
            }else if(is_numeric($user)){  // this is a group
                if(isset($userCache[$user])){
                    $group = $userCache[$user];
                    //$links[] =  $makeLinks ? CHtml::link($group->name, array('/groups/groups/view', 'id' => $group->id)) : $group->name;
                    $links[] = $makeLinks ? CHtml::link($group->name, Yii::app()->controller->createAbsoluteUrl('/groups/groups/view', array('id' => $group->id)), array('style'=>'text-decoration:none;')) : $group->name;
                }else{
                    $group = Groups::model()->findByPk($user);
                    // $group = Groups::model()->findByPk($users);
                    if(isset($group)){
                        //$groupLink = $makeLinks ? CHtml::link($group->name, array('/groups/groups/view', 'id' => $group->id)) : $group->name;
                        $groupLink = $makeLinks ? CHtml::link($group->name, Yii::app()->controller->createAbsoluteUrl('/groups/groups/view', array('id' => $group->id)),array('style'=>'text-decoration:none;')) : $group->name;
                        $userCache[$user] = $group;
                        $links[] = $groupLink;
                    }
                }
            }else{
                if(isset($userCache[$user])){
                    $model = $userCache[$user];
                    $linkText = $useFullName ? $model->fullName : $user;
                    //$userLink = $makeLinks ? CHtml::link($linkText, array('/profile/view', 'id' => $model->id)) : $linkText;
                    $userLink = $makeLinks ? CHtml::link($linkText, Yii::app()->controller->createAbsoluteUrl('/profile/view', array('id' => $model->id)),array('style'=>'text-decoration:none;')) : $linkText;
                    $links[] = $userLink;
                }else{
                    $model = X2Model::model('User')->findByAttributes(array('username' => $user));
                    if(isset($model)){
                        $linkText = $useFullName ? $model->fullName : $user;
                        //$userLink = $makeLinks ? CHtml::link($linkText, array('/profile/view', 'id' => $model->id)) : $linkText;
                        $userLink = $makeLinks ? CHtml::link($linkText, Yii::app()->controller->createAbsoluteUrl('/profile/view', array('id' => $model->id)),array('style'=>'text-decoration:none;')) : $linkText;
                        $userCache[$user] = $model;
                        $links[] = $userLink;
                    }
                }
            }
        }
        Yii::app()->params->userCache = $userCache;
        return implode(', ', $links);
    }

    public static function getEmails(){
        $userArray = User::model()->findAllByAttributes(array('status' => 1));
        $emails = array('Anyone' => Yii::app()->params['adminEmail']);
        foreach($userArray as $user){
            $emails[$user->username] = $user->emailAddress;
        }
        return $emails;
    }

    /**
     * Returns the attribute labels.
     * @return array attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('users', 'ID'),
            'firstName' => Yii::t('users', 'First Name'),
            'lastName' => Yii::t('users', 'Last Name'),
            'username' => Yii::t('users', 'Username'),
            'userAlias' => Yii::t('users', 'Username'),
            'password' => Yii::t('users', 'Password'),
            'title' => Yii::t('users', 'Title'),
            'department' => Yii::t('users', 'Department'),
            'officePhone' => Yii::t('users', 'Office Phone'),
            'cellPhone' => Yii::t('users', 'Cell Phone'),
            'homePhone' => Yii::t('users', 'Home Phone'),
            'address' => Yii::t('users', 'Address'),
            'backgroundInfo' => Yii::t('users', 'Background Info'),
            'emailAddress' => Yii::t('users', 'Email'),
            'status' => Yii::t('users', 'Status'),
            'updatePassword' => Yii::t('users', 'Update Password'),
            'lastUpdated' => Yii::t('users', 'Last Updated'),
            'updatedBy' => Yii::t('users', 'Updated By'),
            'recentItems' => Yii::t('users', 'Recent Items'),
            'topContacts' => Yii::t('users', 'Top Contacts'),
            'userKey' => Yii::t('users', 'API Key'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search(){

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('firstName', $this->firstName, true);
        $criteria->compare('lastName', $this->lastName, true);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('password', $this->password, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('department', $this->department, true);
        $criteria->compare('officePhone', $this->officePhone, true);
        $criteria->compare('cellPhone', $this->cellPhone, true);
        $criteria->compare('homePhone', $this->homePhone, true);
        $criteria->compare('address', $this->address, true);
        $criteria->compare('backgroundInfo', $this->backgroundInfo, true);
        $criteria->compare('emailAddress', $this->emailAddress, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('lastUpdated', $this->lastUpdated, true);
        $criteria->compare('updatedBy', $this->updatedBy, true);
        $criteria->compare('recentItems', $this->recentItems, true);
        $criteria->compare('topContacts', $this->topContacts, true);
        $criteria->compare('lastLogin', $this->lastLogin);
        $criteria->compare('login', $this->login);
        $criteria->addCondition('(temporary=0 OR temporary IS NULL)');

        return new SmartActiveDataProvider(get_class($this), array(
                    'criteria' => $criteria,
                    'pagination'=>array(
                        'pageSize'=>Profile::getResultsPerPage(),
                    ),
                ));
    }

    /**
     * Validator for usernames and userAliases that enforces uniqueness across
     * both fields.
     *
     * @param type $attribute
     * @param type $params
     */
    public function userAliasUnique($attribute,$params=array()) {
        $otherAttribute = $attribute=='username'?'userAlias':'username';
        if(!empty($this->$attribute) && 
           self::model()->exists(
               (isset ($this->id) ? "id != $this->id AND " : '') . "`$otherAttribute` = BINARY :u",
               array(':u'=>$this->$attribute))) {

            $this->addError($attribute,Yii::t('users','That name is already taken.'));
        }
    }

    /**
     * Static instance method to find by username or userAlias
     *
     * @param string $name
     */
    public function findByAlias($name) {
        if(empty($name))
            return null;
        return self::model()->findBySql('SELECT * FROM `'.$this->tableName().'` '
                . 'WHERE `username` = BINARY :n1 OR `userAlias` = BINARY :n2',array(
            ':n1' => $name,
            ':n2' => $name
        ));
    }

    /**
     * Echoes the userAlias, if set, and the username otherwise.
     *
     * @param boolean $encode
     */
    public function getAlias() {
        if(empty($this->userAlias))
            return $this->username;
        else
            return $this->userAlias;
    }

    /**
     * Returns the full name of the user.
     */
    public function getFullName(){
        if(!isset($this->_fullName)){
            $this->_fullName = Formatter::fullName($this->firstName, $this->lastName);
        }
        return $this->_fullName;
    }

    public function getDisplayName ($plural=true) {
        return Yii::t('users', '{user}', array(
            '{user}' => Modules::displayName($plural, 'Users'),
        ));
    }

    // check if user profile has a list to remember which calendars the user has checked
    // if not, create the list
    public function initCheckedCalendars() {
        // calendar list not initialized?
        if (is_null($this->showCalendars)) {
            $showCalendars = array(
                'userCalendars' => array('Anyone', $this->username),
                'groupCalendars' => array(),
                'sharedCalendars' => array(),
                'googleCalendars' => array()
            );
            $this->showCalendars = CJSON::encode($showCalendars);

            $this->update();
        }
    }

    /**
     * Custom validation rule to ensure the primary admin account cannot be disabled
     */
    public function validateUserDisable() {
        if ($this->status === '0' && $this->id == X2_PRIMARY_ADMIN_ID) {
            $this->addError ('status', Yii::t('users',
                'The primary admin account cannot be disabled'));
        }
    }
}
