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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_groups".
 * @package application.modules.groups.models
 */
class Groups extends X2Model {

    public $supportsWorkflow = false;

	/**
	 * Returns the static model of the specified AR class.
	 * @return Groups the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_groups'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'groups'
			)
		));
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>259),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
		);
	}

	public static function getNames() {

		$groupNames = array();
		$data = Yii::app()->db->createCommand()
            ->select('id,name')->from('x2_groups')->order('name ASC')->queryAll(false);
        foreach($data as $row){
			$groupNames[$row[0]] = $row[1];
        }

		return $groupNames;

		// $groupArray = X2Model::model('Groups')->findAll();
		// $names = array();
		// foreach ($groupArray as $group) {
			// $names[$group->id] = $group->name;
		// }
		// return $names;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
            'users' => array (
                self::MANY_MANY, 'User', 'x2_group_to_user(groupId, userId)'
            ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('groups','ID'),
			'name' => Yii::t('groups','Name'),
		);
	}

    /**
     * Delete associated group to user records 
     */
    public function afterDelete () {
        GroupToUser::model ()->deleteAll (array (
            'condition' => 'groupId='.$this->id
        ));
        parent::afterDelete ();
    }


	// public static function getLink($id) {
		// $groupName = Yii::app()->db->createCommand()->select('name')->from('x2_groups')->where('id='.$id)->queryScalar();

		// if(isset($groupName))
			// return CHtml::link($groupName,array('/groups/'.$id));
		// else
			// return '';
	// }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider('Groups', array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'name DESC'	// true = ASC
			),
		));
	}

    /**
     * @return bool True if group has online users, false otherwise 
     */
    public function hasOnlineUsers () {
        return count ($this->getOnlineUsers ()) > 0;
    }

    /**
     * @return <array of objects> An array of user models where each user has an active session
     */
    public function getOnlineUsers () {
		$onlineUserUsernames = Session::getOnlineUsers();

        $onlineUsers = array_filter($this->users,function ($a) use ($onlineUserUsernames) {
            return in_array($a->username, $onlineUserUsernames);
        });
        return $onlineUsers;
    }

	/**
	 * Find out if a user belongs to a group
	 */
	public static function inGroup($userId, $groupId) {
        $groups = self::getUserGroups($userId);
        return in_array($groupId,$groups);
	}

	/** 
     * Looks up groups to which the specified user belongs.
	 * Uses cache to lookup/store groups.
	 *
	 * @param integer $userId user to look up groups for
	 * @param boolean $cache whether to use cache
	 * @return Array array of groupIds
	 */
	public static function getUserGroups($userId,$cache=true) {
        if($userId === null)
            return array();
		// check the app cache for user's groups
		if($cache === true && ($userGroups = Yii::app()->cache->get('user_groups')) !== false) {
			if(isset($userGroups[$userId]))
				return $userGroups[$userId];
		} else {
			$userGroups = array();
		}
        
		$userGroups[$userId] = Yii::app()->db->createCommand()	// get array of groupIds
			->select('groupId')
			->from('x2_group_to_user')
			->where('userId=:userId', array (':userId' => $userId))->queryColumn();

		if($cache === true) {
            // cache user groups for 3 days
			Yii::app()->cache->set('user_groups',$userGroups,259200); 
        }

		return $userGroups[$userId];
	}

        /**
     * Gets a list of names of all users having a group in common with a user.
     *
     * @param integer $userId User's ID
     * @param boolean $cache Whether to cache or not
     * @return array
     */
    public static function getGroupmates($userId,$cache=true) {
       	if($cache === true && ($groupmates = Yii::app()->cache->get('user_groupmates')) !== false){
            if(isset($groupmates[$userId]))
                return $groupmates[$userId];
        } else{
            $groupmates = array();
        }

        $userGroups = self::getUserGroups($userId,$cache);
        $groupmates[$userId] = array();
        if(!empty($userGroups)) {
            $groupParam = AuxLib::bindArray($userGroups,'gid_');
            $inGroup = AuxLib::arrToStrList(array_keys($groupParam));

            $groupmates[$userId] = Yii::app()->db->createCommand()
                    ->select('DISTINCT(gtu.username)')
                    ->from(GroupToUser::model()->tableName().' gtu')
                    ->join(User::model()->tableName().' u',
                            'gtu.userId=u.id AND gtu.groupId IN '.$inGroup, $groupParam)
                    ->queryColumn();
        }
        if($cache === true)
            Yii::app()->cache->set('user_groupmates',$groupmates,259200);
        return $groupmates[$userId];
    }
}
