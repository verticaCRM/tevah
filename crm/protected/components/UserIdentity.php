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
 * The data needed to identity a user.
 * 
 * It contains the authentication method that checks if the provided data can 
 * identity the user.
 * @package application.components
 */
class UserIdentity extends CUserIdentity {

    const ERROR_DISABLED = 3;

	private $_id;
	private $_name;
    private $_userModel;

	public function authenticate($google=false) {
		$user = $this->getUserModel();
        $isRealUser = $user instanceof User;
        
		if($isRealUser){
            $this->username = $user->username;
            if((integer) $user->status === User::STATUS_INACTIVE){
                $this->errorCode = self::ERROR_DISABLED;
                return false;
            }
        }

        if (!$isRealUser) { // username not found
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		} elseif($google) { // Completely bypasses password-based authentication
			$this->errorCode = self::ERROR_NONE;
			$this->_id = $user->id;
			return true;
		} else {
            if($user->status == 0) {
                // User has been disabled
                $this->errorCode = self::ERROR_DISABLED;
                return false;
            }
			$isMD5 = (strlen($user->password) == 32);
			if($isMD5)
				$isValid = ($user->password == md5($this->password));	// if 32 characters, it's an MD5 hash
			else
				$isValid = (crypt($this->password,'$5$rounds=32678$'.$user->password) == '$5$rounds=32678$'.$user->password);	// otherwise, 2^15 rounds of sha256
		
			if($isValid) {
				$this->errorCode = self::ERROR_NONE;
				$this->_id = $user->id;
				$nonce = '';
                for($i = 0; $i < 16; $i++) // generate a random 16 character nonce with the Mersenne Twister
                    $nonce .= substr('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./', mt_rand(0, 63), 1);
                
                $user->password = substr(crypt($this->password, '$5$rounds=32678$'.$nonce), 16);
                $user->update(array('password'));
            } else {
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
			}
		}

		return $this->errorCode===self::ERROR_NONE;
	}
	
	public function getId() {
		return $this->_id;
	}

    public function getUserModel() {
        if(!isset($this->_userModel)) {
            $this->_userModel = User::model()->findByAlias($this->username);
        }
        return $this->_userModel;
    }

}
