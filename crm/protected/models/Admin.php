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

Yii::import('application.components.JSONEmbeddedModelFieldsBehavior');

/**
 * This is the model class for table "x2_admin".
 * @package application.models
 */
class Admin extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public static function getDoNotEmailLinkDefaultText () {
        return Yii::t('admin', 'I do not wish to receive these emails.');
    }

    public static function getDoNotEmailDefaultPage () {
        $message = Yii::t(
            'admin', 'You will no longer receive emails from this sender.');
        return '<html><head><title>'.$message.
            '</title></head><body>'.$message.'</body></html>';
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_admin';
    }

    public function behaviors(){
        $behaviors = array(
            'JSONFieldsBehavior' => array (
                'class' => 'application.components.JSONFieldsBehavior',
                'transformAttributes' => array (
                    'twitterRateLimits',
                ),
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'actionPublisherTabs' => array(
                        'PublisherCallTab' => true,
                        'PublisherTimeTab' => true,
                        'PublisherActionTab' => true,
                        'PublisherCommentTab' => true,
                        'PublisherEventTab' => false,
                        'PublisherProductsTab' => false,
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
        );
        /* x2prostart */
        $behaviors['JSONEmbeddedModelFieldsBehavior'] = array(
            'class' => 'application.components.JSONEmbeddedModelFieldsBehavior',
            'fixedModelFields' => array('emailDropbox' => 'EmailDropboxSettings'),
            'transformAttributes' => array('emailDropbox'),
        );
        /* x2plastart */
        $behaviors['JSONEmbeddedModelFieldsBehavior']['fixedModelFields']['api2'] = 'Api2Settings';
        $behaviors['JSONEmbeddedModelFieldsBehavior']['transformAttributes'][] = 'api2';
        /* x2plaend */

        /* x2proend */
        return $behaviors;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('emailType,emailFromName, emailFromAddr', 'requiredIfSysDefault', 'field' => 'emailBulkAccount'),
            array('serviceCaseFromEmailName, serviceCaseFromEmailAddress', 'requiredIfSysDefault', 'field' => 'serviceCaseEmailAccount'),
            array('serviceCaseEmailSubject, serviceCaseEmailMessage', 'required'),
            array('timeout, webTrackerCooldown, chatPollTime, ignoreUpdates, rrId, onlineOnly, emailBatchSize, emailInterval, emailPort, installDate, updateDate, updateInterval, workflowBackdateWindow, workflowBackdateRange', 'numerical', 'integerOnly' => true),
            // accounts, sales,
            array('chatPollTime', 'numerical', 'max' => 10000, 'min' => 100),
            array('currency', 'length', 'max' => 3),
            array('emailUseAuth, emailUseSignature', 'length', 'max' => 10),
            array('emailType, emailSecurity,gaTracking_internal,gaTracking_public', 'length', 'max' => 20),
            array('webLeadEmail, leadDistribution, emailFromName, emailFromAddr, emailHost, emailUser, emailPass,externalBaseUrl,externalBaseUri', 'length', 'max' => 255),
            // array('emailSignature', 'length', 'max'=>512),
            array('batchTimeout', 'numerical', 'integerOnly' => true),
            array(
                'massActionsBatchSize',
                'numerical',
                'integerOnly' => true,
                'min' => 5,
                'max' => 100,
            ),
            array('emailBulkAccount,serviceCaseEmailAccount', 'safe'),
            /* x2prostart */
            array('emailDropbox' /* x2plastart */ .',api2' /* x2plaend */,'safe'),
            /* x2proend */
            array('emailBulkAccount', 'setDefaultEmailAccount', 'alias' => 'bulkEmail'),
            array('serviceCaseEmailAccount', 'setDefaultEmailAccount', 'alias' => 'serviceCaseEmail'),
            array('webLeadEmailAccount','setDefaultEmailAccount','alias' => 'systemResponseEmail'),
            array('emailNotificationAccount','setDefaultEmailAccount','alias'=>'systemNotificationEmail'),
            array('emailSignature', 'length', 'max' => 4096),
            array('externalBaseUrl','url','allowEmpty'=>true),
            array('externalBaseUrl','match','pattern'=>':/$:','not'=>true,'allowEmpty'=>true,'message'=>Yii::t('admin','Value must not include a trailing slash.')),
            array('enableWebTracker, quoteStrictLock, workflowBackdateReassignment', 'boolean'),
            array('gaTracking_internal,gaTracking_public', 'match', 'pattern' => "/'/", 'not' => true, 'message' => Yii::t('admin', 'Invalid property ID')),
            array ('appDescription', 'length', 'max' => 255),
            array (
                'appName,x2FlowRespectsDoNotEmail,doNotEmailPage,doNotEmailLinkText',
                'safe'
            ),
            /* x2prostart */
            array('imapPollTimeout', 'numerical', 'max' => 30, 'min' => 5),
            /* x2proend */
            /* x2plastart */
            array('maxFailedLogins,failedLoginsBeforeCaptcha', 'numerical', 'min' => 1, 'max' => 100),
            array('maxLoginHistory', 'numerical', 'min' => 10, 'max' => 10000),
            array('loginTimeout', 'numerical', 'min' => 5, 'max' => 1440),
            array('failedLoginsBeforeCaptcha', 'compare', 'compareAttribute' => 'maxFailedLogins',
                'operator' => '<=', 'message' => Yii::t('admin', 'Failed logins before CAPTCHA '.
                'must be less than the maximum number of failed logins.'),
            ),
            /* x2plaend */
                // The following rule is used by search().
                // Please remove those attributes that should not be searched.
                // array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            // 'accounts' => Yii::t('admin','Accounts'),
            // 'sales' => Yii::t('admin','Opportunities'),
            'timeout' => Yii::t('admin', 'Session Timeout'),
            'webLeadEmail' => Yii::t('admin', 'Web Lead Email'),
            'enableWebTracker' => Yii::t('admin', 'Enable Web Tracker'),
            'webTrackerCooldown' => Yii::t('admin', 'Web Tracker Cooldown'),
            'currency' => Yii::t('admin', 'Currency'),
            'chatPollTime' => Yii::t('admin', 'Notification Poll Time'),
            'ignoreUpdates' => Yii::t('admin', 'Ignore Updates'),
            'rrId' => Yii::t('admin', 'Round Robin ID'),
            'leadDistribution' => Yii::t('admin', 'Lead Distribution'),
            'onlineOnly' => Yii::t('admin', 'Online Only'),
            'emailBulkAccount' => Yii::t('admin', 'Send As (when sending bulk email)'),
            'emailFromName' => Yii::t('admin', 'Sender Name'),
            'emailFromAddr' => Yii::t('admin', 'Sender Email Address'),
            'emailBatchSize' => Yii::t('admin', 'Batch Size'),
            'emailInterval' => Yii::t('admin', 'Interval (Minutes)'),
            'emailUseSignature' => Yii::t('admin', 'Email Signatures'),
            'emailSignature' => Yii::t('admin', 'Default Signature'),
            'emailType' => Yii::t('admin', 'Method'),
            'emailHost' => Yii::t('admin', 'Hostname'),
            'emailPort' => Yii::t('admin', 'Port'),
            'emailUseAuth' => Yii::t('admin', 'Authentication'),
            'emailUser' => Yii::t('admin', 'Username'),
            'emailPass' => Yii::t('admin', 'Password'),
            'emailSecurity' => Yii::t('admin', 'Security'),
            'enableColorDropdownLegend' => Yii::t('admin', 'Colorize Dropdown Options?'),
            'installDate' => Yii::t('admin', 'Installed'),
            'updateDate' => Yii::t('admin', 'Last Update'),
            'updateInterval' => Yii::t('admin', 'Version Check Interval'),
            'googleClientId' => Yii::t('admin', 'Google Client ID'),
            'googleClientSecret' => Yii::t('admin', 'Google Client Secret'),
            'googleAPIKey' => Yii::t('admin', 'Google API Key'),
            'googleIntegration' => Yii::t('admin', 'Activate Google Integration'),
            'inviteKey' => Yii::t('admin', 'Invite Key'),
            'workflowBackdateWindow' => Yii::t('admin', 'Process Backdate Window'),
            'workflowBackdateRange' => Yii::t('admin', 'Process Backdate Range'),
            'workflowBackdateReassignment' => Yii::t('admin', 'Process Backdate Reassignment'),
            'serviceCaseEmailAccount' => Yii::t('admin', 'Send As (to service requesters)'),
            'serviceCaseFromEmailName' => Yii::t('admin', 'Sender Name'),
            'serviceCaseFromEmailAddress' => Yii::t('admin', 'Sender Email Address'),
            'serviceCaseEmailSubject' => Yii::t('admin', 'Subject'),
            'serviceCaseEmailMessage' => Yii::t('admin', 'Email Message'),
            'gaTracking_public' => Yii::t('admin', 'Google Analytics Property ID (public)'),
            'gaTracking_internal' => Yii::t('admin', 'Google Analytics Property ID (internal)'),
            'serviceDistribution' => Yii::t('admin', 'Service Distribution'),
            'serviceOnlineOnly' => Yii::t('admin', 'Service Online Only'),
            'eventDeletionTime' => Yii::t('admin', 'Event Deletion Time'),
            'eventDeletionTypes' => Yii::t('admin', 'Event Deletion Types'),
            'properCaseNames' => Yii::t('admin', 'Proper Case Names'),
            'corporateAddress' => Yii::t('admin', 'Corporate Address'),
            'contactNameFormat' => Yii::t('admin', 'Contact Name Format'),
            'webLeadEmailAccount' => Yii::t('admin','Send As (to web leads)'),
            'emailNotificationAccount' => Yii::t('admin','Send As (when notifying users)'),
            'batchTimeout' => Yii::t('app','Time limit on batch actions'),
            'massActionsBatchSize' => Yii::t('app','Batch size for grid view mass actions'),
            'externalBaseUrl' => Yii::t('app','External / Public Base URL'),
            'externalBaseUri' => Yii::t('app','External / Public Base URI'),
            'appName' => Yii::t('app','Application Name'),
            'x2FlowRespectsDoNotEmail' => Yii::t(
                'app','Respect contacts\' "Do not email" settings?'),
            'doNotEmailLinkText' => Yii::t('app','"Do not email" Link Text'),
            'doNotEmailLinkPage' => Yii::t('app','"Do not email" Page'),
            /* x2prostart */ 
            'imapPollTimeout' => Yii::t('app','Email Polling Timeout'),
            'ipBlacklist' => Yii::t('app','IP Blacklist'),
            'ipWhitelist' => Yii::t('app','IP Whitelist'),
            /* x2proend */ 
        );
    }

    public function requiredIfSysDefault($attribute, $params){
        if(empty($this->$attribute) && $this->{$params['field']} == Credentials::LEGACY_ID)
            $this->addError($attribute, Yii::t('yii', '{attribute} cannot be blank.', array('{attribute}' => $this->getAttributeLabel($attribute))));
    }

    public function setDefaultEmailAccount($attribute, $params){
        if($this->$attribute != Credentials::LEGACY_ID){
            $cred = Credentials::model()->findByPk($this->$attribute);
            if($cred)
                $cred->makeDefault(Credentials::$sysUseId[$params['alias']], 'email', false);
        } else{
            Yii::app()->db->createCommand()->delete('x2_credentials_default', 'userId=:uid AND serviceType=:st', array(':uid' => Credentials::$sysUseId[$params['alias']], ':st' => 'email'));
        }
    }

    /**
     * Record that a number of emails have been sent, to avoid going over the
     * bulk email batch size per interval.
     * 
     * @param integer $nEmail Number of emails that will have been sent
     */
    public function countEmail($nEmail = 1) {
        $now = time();
        if(empty($this->emailStartTime))
            $this->emailStartTime = $now;
        if($now-$this->emailStartTime > $this->emailInterval) {
            // Reset
            $this->emailStartTime = $now;
            $this->emailCount = 0;
        }
        $this->emailCount += $nEmail;
        $this->update(array('emailCount','emailStartTime'));
        return $this->emailCount;
    }

    /**
     * Returns true or false based on whether a number of emails to be sent will
     * exceed the batch maximum.
     *
     * @param integer $nEmail Number of emails to be sent
     */
    public function emailCountWillExceedLimit($nEmail=1) {
        $now = time();
        if($now-$this->emailStartTime > $this->emailInterval) {
            $this->emailStartTime = $now;
            $this->emailCount = 0;
        }
        return $this->emailCount + $nEmail > $this->emailBatchSize;
    }

    /**
     * @param array $value This should match the structure of the actionPublisherTabs property
     *  specified in the JSONFieldsDefaultValuesBehavior configuration
     */
    public function setActionPublisherTabs ($value) {
        $this->actionPublisherTabs = $value;
        $this->save ();
    }

    /* x2prostart */
    /**
     * Render button for the advanced security failed logins grid to ban or whitelist
     * an IP address or disable a user account
     */
    public static function renderACLControl ($type, $target) {
        // If this is a disable user button, Just render the disable user button and return
        if ($type === 'disable') {
            $user = $target;
            $active = Yii::app()->db->createCommand()
                ->select ('status')
                ->from ('x2_users')
                ->where ('username = :user', array(
                    ':user' => $user,
                ))->queryScalar();
            if ($active === '1') {
                return CHtml::link (Yii::t('admin', 'Disable'),
                    array('/admin/disableUser?username='.$target),
                    array('class' => 'x2-button')
                );
            } else {
                return '<div class="x2-button disabled">'.
                    Yii::t('admin', 'Disable').
                    '</div>';
            }
        }

        // Otherwise render a whitelist or ban button
        if ($type === 'blacklist') {
            $buttonText = Yii::t('admin', 'Ban');
            $actionUrl = array(
                '/admin/admin/banIp',
                'ip' => $target
            );
        } else {
            $buttonText = Yii::t('admin', 'Whitelist');
            $actionUrl = array(
                '/admin/admin/whitelistIp',
                'ip' => $target
            );
        }
        $method = Yii::app()->settings->accessControlMethod;
        $class = 'x2-button';
        if ($method !== $type) {
            return '<a class="x2-button disabled">'.$buttonText.'</a>';
        } else {
            return CHtml::link ($buttonText, $actionUrl , array(
                'class' => 'x2-button',
            ));
        }
    }
    /* x2proend */
}
