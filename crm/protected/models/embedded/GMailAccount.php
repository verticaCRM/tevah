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

Yii::import('application.models.embedded.*');

/**
 * Authentication data for using a Google account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package application.models.embedded
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class GMailAccount extends EmailAccount {

    public $email = '';
    public $imapNoValidate = false;
    public $imapPort = 993;
    public $imapSecurity = 'ssl';
    public $imapServer = 'imap.gmail.com';
    public $password = '';
    public $port = 587;
    public $security = 'tls';
    public $senderName = '';
    public $server = 'smtp.gmail.com';
    public $user = '';

    public function attributeLabels(){
        return array(
            'senderName' => Yii::t('app','Sender Name'),
            'email' => Yii::t('app','Google ID'),
            'password' => Yii::t('app','Password'),
            'imapPort' => Yii::t('app','IMAP Port'),
            'imapServer' => Yii::t('app','IMAP Server'),
            'imapSecurity' => Yii::t('app','IMAP Security'),
            'imapNoValidate' => Yii::t('app','Disable SSL Validation'),
        );
    }

    public function modelLabel() {
        return Yii::t('app','Google Email Account');
    }

    public function renderInput ($attr) {
        switch($attr){
            case 'email':
                echo '<p class="fieldhelp-thin-small">'.Yii::t('app', '(example@gmail.com)').
                    '</p>';
                echo CHtml::activeTextField($this, $attr, $this->htmlOptions($attr));
                break;
            case 'password':
                echo CHtml::activePasswordField($this, $attr, $this->htmlOptions($attr));
                echo CHtml::label(Yii::t('app', 'Visible?'), 'visible', array('style' => 'display: inline'));
                echo CHtml::checkBox('visible', false, array(
                    'id' => 'password-visible',
                    'onchange' => 'js: x2.credManager.swapPasswordVisibility("#Credentials_auth_password")',
                ));
                break;
            default:
                parent::renderInput ($attr);
        }
    }

    public function renderInputs(){
        $this->password = null;
        echo CHtml::activeLabel($this, 'senderName');
        $this->renderInput ('senderName');
        echo CHtml::activeLabel($this, 'email');
        $this->renderInput ('email');
        echo CHtml::activeLabel($this, 'password');
        $this->renderInput ('password');
        echo '<br/>';
        echo '<br/>';
        echo CHtml::label(Yii::t('app', 'IMAP Configuration'), false);
        echo '<hr/>';
        echo CHtml::activeLabel($this, 'imapPort');
        $this->renderInput ('imapPort');
        echo CHtml::activeLabel($this, 'imapSecurity');
        $this->renderInput ('imapSecurity');
        echo CHtml::activeLabel($this, 'imapNoValidate');
        $this->renderInput ('imapNoValidate');
        echo CHtml::activeLabel($this, 'imapServer');
        $this->renderInput ('imapServer');
        echo CHtml::errorSummary($this);
    }

    public function rules(){
        return array(
            array('email','email'),
            array('senderName,email,password', 'required'),
            array('senderName,email,password', 'safe'),
        );
    }

}

?>
