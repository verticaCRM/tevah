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
 * This is the model class for table "x2_sessions".
 *
 * @package application.models
 * @property integer $id
 * @property string $user
 * @property integer $lastUpdated
 * @property string $IP
 * @property integer $status
 */
class Session extends CActiveRecord {

	/**
	 * Returns the static model of the specified AR class.
	 * @return Session the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_sessions';
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
			'id' => Yii::t('app','Sesesion ID'),
			'user' => Yii::t('app','User'),
			'lastUpdated' => Yii::t('app','Last Updated'),
			'IP' => Yii::t('app','IP Address'),
			'status' => Yii::t('app','Login Status'),
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
		$criteria->compare('user',$this->user,true);
		$criteria->compare('IP',$this->IP,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	public static function getOnlineUsers($useTimeout = false) {
		// $sessions = Session::model()->findAllByAttributes(array('status'=>1));
		// $temp = array();
		// foreach($sessions as $session)
			// $temp[] = $session->user;
		// return $temp;

		$query = Yii::app()->db->createCommand()
            ->selectDistinct('user')
            ->from('x2_sessions')
            ->where('status=1');
		if($useTimeout)
			$query = $query->where('lastUpdated > "'.(time()-900).'"');

		return $query->queryColumn();
	}

    /**
     * @param string $username
     * @return bool true if user has a recently updated session record, false otherwise
     */
	public static function isOnline ($username) {
		$record = Yii::app()->db->createCommand()
            ->select('*')
            ->from('x2_sessions')
            ->where('status=1 and user=:username and lastUpdated > "'.(time () - 900).
                '"', array ('username' => $username))
            ->queryAll ();

		return (!empty ($record));
    }

    /**
     * Clear session records which have timed out. Log the timeout.
     */
    public static function cleanUpSessions () {
        // Only select users with active sessions to clear out, in case there are
        // dozens of inactive users, to make things more efficient:
        $users = Yii::app()->db->createCommand()
                ->select('x2_users.id,x2_users.username')
                ->from('x2_users')
                ->rightJoin('x2_sessions', 'x2_sessions.user = x2_users.username')
                ->where('x2_users.username IS NOT NULL AND x2_users.username != ""')
                ->queryAll();
        foreach($users as $user){
            $timeout = Roles::getUserTimeout($user['id']);
            $sessions = X2Model::model('Session')->findAllByAttributes(
                    array('user' => $user['username']), 
                    'lastUpdated < :cutoff', 
                    array(':cutoff' => time() - $timeout));
            foreach($sessions as $session){
                SessionLog::logSession($session->user, $session->id, 'passiveTimeout');
                $session->delete();
            }
        }

        // check timeout on sessions not corresponding to any existing user
        $defaultTimeout = 900;
        self::model ()->deleteAll (
            array (
                'condition' => 'lastUpdated < :cutoff and 
                    user not in (select distinct (username) from x2_users)',
                'params' => array (':cutoff' => time () - $defaultTimeout)
            )
        );
    }
}
