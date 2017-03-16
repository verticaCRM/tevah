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
 * This is the model class for table "x2_roles".
 *
 * @package application.models
 * @property integer $id
 * @property string $name
 * @property string $users
 */
class Roles extends CActiveRecord {

    private static $_authNames;

    /**
     * Runtime storage array of user roles indexed by user ID
     * @var type
     */
    private static $_userRoles;

    /**
     * Retrieves a list of restricted (non-permissible) role names.
     */
    public static function getAuthNames() {
        if(!isset(self::$_authNames)) {
            $x2Roles = Yii::app()->db->createCommand()
                    ->select('name')
                    ->from('x2_roles')
                    ->queryColumn();
            $authRoles = Yii::app()->db->createCommand()
                    ->select('name')
                    ->from('x2_auth_item')
                    ->queryColumn();
            self::$_authNames = array_diff($authRoles, $x2Roles);
        }
        return self::$_authNames;
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @return Roles the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_roles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>250),
			array('name','match',
                'not'=>true,
                'pattern'=> '/^('.implode('|',array_map(function($n){return preg_quote($n);},self::getAuthNames())).')/i',
                'message'=>Yii::t('admin','The name you entered is reserved or belongs to the system.')),
            array('timeout', 'numerical', 'integerOnly' => true, 'min' => 5),
			array('users', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, users', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'name' => Yii::t('admin','Name'),
			'users' => Yii::t('admin','Users'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('users',$this->users,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

    /**
     * Get roles from cache 
     */
    public static function getCachedUserRoles ($userId) {
		// check the app cache for user's roles
		return Yii::app()->cache->get(self::getUserCacheVar ($userId));
    }

    /**
     * Clear role cache for specified user 
     */
    public static function clearCachedUserRoles ($userId) {
        if(isset(self::$_userRoles[$userId]))
            unset(self::$_userRoles[$userId]);
        Yii::app()->cache->delete (self::getUserCacheVar ($userId));
    }

	/**
     * Determines roles of the specified user, including group-inherited roles.
     *
	 * Uses cache to lookup/store roles.
	 *
	 * @param integer $userId user for which to look up roles. Note, null user ID
     *  implies guest.
	 * @param boolean $cache whether to use cache
	 * @return Array array of roleIds
	 */
	public static function getUserRoles($userId,$cache=true) {
        if(isset(self::$_userRoles[$userId]))
            return self::$_userRoles[$userId];
		// check the app cache for user's roles
		if($cache === true
                && ($userRoles = self::getCachedUserRoles ($userId)) !== false) {
			self::$_userRoles[$userId] = $userRoles;
            return $userRoles;
		}
        $userRoles = array();

        if($userId !== null){ // Authenticated user
            $userRoles = Yii::app()->db->createCommand() // lookup the user's roles
                    ->select('roleId')
                    ->from('x2_role_to_user')
                    ->where('`type`="user" AND `userId`=:userId')
                    ->queryColumn(array(':userId' => $userId));

            $groupRoles = Yii::app()->db->createCommand() // lookup roles of all the user's groups
                    ->select('rtu.roleId')
                    ->from('x2_group_to_user gtu')
                    ->join('x2_role_to_user rtu', 'rtu.userId=gtu.groupId '
                            .'AND gtu.userId=:userId '
                            .'AND type="group"')
                    ->queryColumn(array(':userId' => $userId));
        }else{ // Guest
            $groupRoles = array();
            $userRoles = array();
            $guestRole = self::model()->findByAttributes(array('name' => 'Guest'));
            if(!empty($guestRole))
                $userRoles = array($guestRole->id);
        }

        // Combine all the roles, remove duplicates:
        $userRoles = array_unique($userRoles + $groupRoles);

        // Cache/store:
        self::$_userRoles[$userId] = $userRoles;
		if($cache === true)
			Yii::app()->cache->set(self::getUserCacheVar ($userId),$userRoles,259200); // cache user groups for 3 days

		return $userRoles;
	}

    /**
     * Returns the timeout of the current user.
     *
     * Selects and returns the maximum timeout between the timeouts of the
     * current user's roles and the default timeout.
     * @return Integer Maximum timeout value
     */
    public static function getUserTimeout($userId, $cache = true){
        $cacheVar = 'user_roles_timeout'.$userId;
        if($cache === true && ($timeout = Yii::app()->cache->get($cacheVar)) !== false)
            return $timeout;


        $userRoles = Roles::getUserRoles($userId);
        $availableTimeouts = array();
        foreach($userRoles as $role){
            $timeout = Yii::app()->db->createCommand()
                    ->select('timeout')
                    ->from('x2_roles')
                    ->where('id=:role', array(':role' => $role))
                    ->queryScalar();
            if(!is_null($timeout))
                $availableTimeouts[] = (integer) $timeout;
        }

        $availableTimeouts[] = Yii::app()->settings->timeout;
        $timeout = max($availableTimeouts);
        if($cache === true)
            Yii::app()->cache->set($cacheVar, $timeout, 259200);
        return $timeout;
    }

    private static function getUserCacheVar ($userId) {
		return 'user_roles_'.($userId===null?'guest':$userId);
    }

}
