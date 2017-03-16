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
 * Form model for logging into the app.
 *
 * @package application.models
 * @property UserIdentity $identity The user identity component for the current
 *  login.
 * @propoerty User $user The user model corresponding to the current login; null
 *  if no match for username/alias was found.
 */
class LoginForm extends CFormModel {

    public $username;
    public $password;
    public $rememberMe;
    public $verifyCode;
    public $useCaptcha;
    private $_identity;

    /**
     * Validation rules for logins.
     * @return array
     */
    public function rules() {
	return array(
	    // username and password are required
	    array('username, password', 'required'),
	    // rememberMe needs to be a boolean
	    array('rememberMe', 'boolean'),
	    // password needs to be authenticated
	    array('password', 'authenticate'),
	    // captcha needs to be filled out
	    array('verifyCode', 'captcha', 'allowEmpty' => !(CCaptcha::checkRequirements()), 'on' => 'loginWithCaptcha'),
	    array('verifyCode', 'safe'),
	);
    }

    /**
     * Declares attribute labels.
     * @return array
     */
    public function attributeLabels(){
        return array(
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'Remember me'),
            'verifyCode' => Yii::t('app', 'Verification Code'),
        );
    }

    /**
     * Authenticates the password.
     * 
     * This is the 'authenticate' validator as declared in rules().
     * @param string $attribute Attribute name
     * @param array $params validation parameters
     */
	public function authenticate($attribute, $params) {
		if (!$this->hasErrors()) {
			if (!$this->identity->authenticate()) {
                if($this->identity->errorCode === UserIdentity::ERROR_DISABLED){
                    $this->addError('username',Yii::t('app','Login for that user account has been disabled.'));
                    $this->addError('password',Yii::t('app','Login for that user account has been disabled.'));
                }else{
                    $this->addError('username', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                    $this->addError('password', Yii::t('app', 'Incorrect username or password. Note, usernames are case sensitive.'));
                }
            }
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * 
	 * @param boolean $google Whether or not Google is being used for the login
	 * @return boolean whether login is successful
	 */
    public function login($google = false) {
        if(!isset($this->_identity))
            $this->getIdentity()->authenticate($google);
		if($this->getIdentity()->errorCode === UserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 2592000 : 0; //60*60*24*30 = 30 days
			Yii::app()->user->login($this->_identity, $duration);

			// update lastLogin time
			$user = User::model()->findByPk(Yii::app()->user->getId());
            Yii::app()->setSuModel($user);
			$user->lastLogin = $user->login;
			$user->login = time();
			$user->update(array('lastLogin','login'));
			
			Yii::app()->session['loginTime'] = time();
			
			return true;
		}
		
		return false;
	}

    /**
     * User identity component.
     * 
     * @return UserIdentity
     */
    public function getIdentity(){
        if(!isset($this->_identity)){
            $this->_identity = new UserIdentity($this->username, $this->password);
        }
        return $this->_identity;
    }

    /**
     * Returns the user model corresponding to the identity for the login
     *
     * @return User
     */
    public function getUser() {
        return $this->getIdentity()->getUserModel();
    }

    /**
     * Resolves the correct username to use for login form security and sessions
     *
     * @return type
     */
    public function getSessionUserName() {
        if((($user = $this->getUser()) instanceof User))
            return $user->username;
        return $this->username;
    }

}
