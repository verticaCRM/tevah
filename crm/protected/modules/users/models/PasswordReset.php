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
 * Password recovery active record model.
 *
 * @property boolean $limitReached Whether or not the requests per hour limit was reached
 * @package application.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordReset extends CActiveRecord {

    /**
     * Password reset requests expire in one hour:
     */
    const EXPIRE_S = 3600;

    const MAX_REQUESTS = 5;

    private $_limitReached;
    private $_user;

    public function tableName() {
        return 'x2_password_reset';
    }

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO,'User','userId')
        );
    }

    public function getIpAddr(){
        if(empty($this->ip)){
            $this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        return $this->ip;
    }

    public function getIsExpired() {
        return $this->requested < time()-self::EXPIRE_S;
    }
    
    /**
     * Returns whether the maximum number of requests for the current IP address
     * has already been reached.
     * @return type
     */
    public function getLimitReached() {
        return $this->_limitReached = (((int)  self::model()->countByAttributes(array('ip'=>$this->ipAddr))) >= self::MAX_REQUESTS);
    }

    /**
     * Creates a password reset request.
     * 
     * Assigns a secure/unique ID to the request.
     * @param type $attributes
     */
    public function beforeSave(){
        // Clean out old requests:
        Yii::app()->db->createCommand('DELETE FROM `'.$this->tableName().'`'
                . ' WHERE requested < '.(time()-self::EXPIRE_S))
                ->execute();
        $user = $this->resolveUser();
        if($user instanceof User){
            $this->userId = $user->id;
        }
        return !$this->limitReached && parent::beforeSave();
    }

    public function insert($attributes = null){
        $this->id = EncryptUtil::secureUniqueIdHash64();
        $this->requested = time();
        $this->getIpAddr();
        return parent::insert($attributes);
    }

    public function rules() {
        return array(
            array('email','required'),
            array('email','email'),
            array('email','validUserId','on'=>'afterSave'),
        );
    }

    /**
     * Validator for checking if a user was found
     * @param type $attribute
     * @param type $params
     */
    public function validUserId($attribute,$params = array()) {
        if(empty($this->userId)) {
            $user = $this->resolveUser();
            if($user instanceof User)
                $this->userId = $user->id;
        }
        if(empty($this->userId)) {
            $this->addError('email',Yii::t('users','No user corresponding to that email address could be found.'));
        }
    }

    /**
     * Finds the user either by user or profile record (this is a sort of kludge
     * -y safeguard that can be removed when those tables are merged)
     * @return type
     */
    public function resolveUser() {
        $user = User::model()->findByAttributes(array('emailAddress' => $this->email));
        if(!($user instanceof User)){
            $profile = Profile::model()->findByAttributes(array('emailAddress' => $this->email));
            if($profile instanceof Profile) {
                $user = $profile->user;
            }
        }
        return $user;
    }
}

?>
