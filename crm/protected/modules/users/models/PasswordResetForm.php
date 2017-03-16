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

/**
 * The "Enter a New Password" form model for password resetting.
 *
 * @package application.modules.users.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class PasswordResetForm extends CFormModel {

    const N_CHAR_CLASS_SECURE = 2;
    const N_CHAR_TOTAL_SECURE = 5;

    public $password;
    public $confirm;

    /**
     * User active record to be updated
     * 
     * @var User
     */
    public $userModel;

    public function attributeLabels(){
        return array(
            'password' => Yii::t('users','Password'),
            'confirm' => Yii::t('users','Confirm Password'),
        );
    }

    public function attributeNames(){
        return array('password','confirm');
    }

    public function rules() {
        return array(
            array('password,confirm','required'),
            array('password','securePassword'),
            array('confirm','compareAttr','against'=>'password','message'=>Yii::t('users','Passwords do not match.')),
        );
    }

    public function __construct(User $userModel,$scenario = ''){
        $this->userModel = $userModel;
        parent::__construct($scenario);
    }

    /**
     * Validator that checks equality between attributes
     * 
     * @param type $attribute
     * @param type $params
     */
    public function compareAttr($attribute,$params=array()) {
        if($this->$attribute != $this->{$params['against']}) {
            $this->addError($attribute,$params['message']);
        }
    }

    /**
     * Save the associated user model
     *
     * Also, this clears out all password resets associated with the given user,
     * if successful.
     * @return type
     */
    public function save() {
        if($this->validate()) {
            $this->userModel->password = md5($this->password);
            PasswordReset::model()->deleteAllByAttributes(array('userId'=>$this->userModel->id));
            return $this->userModel->update(array('password'));
        }
        return false;
    }

    /**
     * Validation rule that prompts user for a more secure password
     *
     * @param type $attribute
     * @param type $params
     */
    public function securePassword($attribute,$params=array()) {
        $nClass = 0;
        if(strlen($this->$attribute) < self::N_CHAR_TOTAL_SECURE) {
            $this->addError($attribute,Yii::t('users','{attribute} is not secure enough (minimum length: {l})', array(
                        '{attribute}' => $this->getAttributeLabel($attribute),
                        '{l}' => self::N_CHAR_TOTAL_SECURE
            )));
        }
        foreach(array('[0-9]','[a-z]','[A-Z]','\W','\s') as $characterClass) {
            if(preg_match('/'.$characterClass.'/',$this->$attribute)) {
                $nClass++;
            }
        }
        if($nClass < self::N_CHAR_CLASS_SECURE){
            $this->addError($attribute, Yii::t('users', '{attribute} is not secure enough; it must contain at least {n} types of characters (upper case, lower case, number, etc)', array(
                        '{attribute}' => $this->getAttributeLabel($attribute),
                        '{n}' => self::N_CHAR_CLASS_SECURE
            )));
        }
    }
}

?>
