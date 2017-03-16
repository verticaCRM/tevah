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
 * Authentication data for using an Outlook account to send email.
 *
 * Similar to EmailAccount but with certain details already filled in
 * @package application.models.embedded
 */
class OutlookEmailAccount extends EmailAccount {

    public $email = '';
    public $imapNoValidate = false;
    public $imapPort = 993;
    public $imapSecurity = 'ssl';
    public $imapServer = 'imap-mail.outlook.com';
    public $password = '';
    public $port = 25;
    public $security = 'tls';
    public $senderName = '';
    public $server = 'smtp-mail.outlook.com';
    public $user = '';

    public function modelLabel() {
        return Yii::t('app','Outlook Email Account');
    }

}

?>
