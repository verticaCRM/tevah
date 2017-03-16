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

/* @edition:pro */

/**
 * Create Record action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowRecordEmail extends BaseX2FlowEmail {

    public $title = 'Email Contact';
    // Uses the assignee\'s email unless specified.';
    public $info = 'Send a template or custom email to a relevant contact\'s email address. This action will attempt to find the correct contact associated with the triggering record.';

	public function paramRules() {
        $parentRules = parent::paramRules ();
        $parentRules['options'] = array_merge (
            $parentRules['options'],
            array (
                array(
                    'name' => 'template',
                    'label' => Yii::t('studio', 'Template'),
                    'type' => 'dropdown',
                    'defaultVal' => '',
                    'options' => array('' => Yii::t('studio', 'Custom')) + 
                        Docs::getEmailTemplates('email', 'Contacts')
                ),
                array(
                    'name' => 'subject',
                    'label' => Yii::t('studio', 'Subject'),
                    'optional' => 1
                ),
                array(
                    'name' => 'cc',
                    'label' => Yii::t('studio', 'CC:'),
                    'optional' => 1,
                    'type' => 'email'
                ),
                array(
                    'name' => 'bcc', 
                    'label' => Yii::t('studio', 'BCC:'),
                    'optional' => 1,
                    'type' => 'email'
                ),
                array(
                    'name' => 'logEmail', 
                    'label' => 
                        Yii::t('studio', 'Log email?').'&nbsp;'.
                        X2Html::hint2 (
                        Yii::t('studio', 'Checking this box will cause the email to be attached '.
                            'to the recipient contact\'s record and enables email tracking.')),
                    'optional' => 1,
                    'defaultVal' => 1,
                    'type' => 'boolean',
                ),
                array(
                    'name' => 'doNotEmailLink', 
                    'label' => 
                        Yii::t('studio', 'Add "Do Not Email" link to email body?') . '&nbsp;'.
                        X2Html::hint2 (
                        Yii::t('studio', 'Checking this box will cause a link to be appended to '.
                            'the email body which, when clicked, causes the contact\'s '.
                            '"Do Not Email" field to be checked. Note that any '.
                            'recipients specified in the Bcc or Cc lists will also be able to '.
                            'click this link.')),
                    'optional' => 1,
                    'type' => 'boolean',
                ),
                array(
                    'name' => 'body', 
                    'label' => Yii::t('studio', 'Message'),
                    'optional' => 1,
                    'type' => 'richtext'
                ),

            )
        );
        return $parentRules;
    }

    public function execute(&$params){
        $eml = new InlineEmail;
        $contact = $params['model'];
        $options = $this->config['options'];
        if(isset($options['cc']['value'])) {
            $eml->cc = $this->parseOption('cc', $params);
        }

        if(isset($options['bcc']['value'])) {
            $eml->bcc = $this->parseOption('bcc', $params);
        }
        if($params['model'] instanceof Contacts){
            if(!$contact->hasAttribute('email') || empty($contact->email))
                return array(false, Yii::t('app', "Email could not be sent"));
            $eml->to = $contact->email;
        }elseif($params['model'] instanceof Actions && 
            strcasecmp($params['model']->associationType, 'contacts') == 0){

            $lookup = X2Model::model('Contacts')->findByPk($params['model']->associationId);
            if(isset($lookup)){
                $contact = $lookup;
                if(!$contact->hasAttribute('email') || empty($contact->email))
                    return array(false, Yii::t('app', "Email could not be sent"));
                $eml->to = $contact->email;
            }else{
                return array(false, Yii::t('app', 'No valid Contact found.'));
            }
        }elseif($params['model'] instanceof X2Model){
            $failure=true;
            foreach($params['model']->getFields() as $field){
                if($field->type == 'link' && $field->linkType=='Contacts'){
                    // Use the relation established via X2Model.relations()
                   $lookup = $params['model']->getRelated("{$field->fieldName}Model");

//                    $lookup = $params['model']->getRelated("{$field->linkType}Model");
                    if($lookup instanceof Contacts){
                        $failure=false;
                        $contact = $lookup;
                        if(!$contact->hasAttribute('email') || empty($contact->email))
                            return array(false, Yii::t('app', "Email could not be sent"));
                        $eml->to = $contact->email;
                        break;
                    }
                }
            }
            if($failure){
                return array(false, Yii::t('app', 'No valid Contact found.'));
            }
        }else{
            return array(false, Yii::t('app', 'No valid Contact found.'));
        }

        if(empty($options['from']['value'])){
            $profile = CActiveRecord::model('Profile')
                ->findByAttributes(array('username' => $params['model']->assignedTo));
            if($profile === null)
                return array(false, Yii::t('app', "Email could not be sent"));
            $eml->setUserProfile($profile);
        } else{
            //$eml->from = array('address'=>$this->parseOption('from',$params),'name'=>'');
            $eml->credId = $this->parseOption('from', $params);
            if ($eml->credentials && $eml->credentials->user)
                $eml->setUserProfile($eml->credentials->user->profile);
        }
        $eml->subject = Formatter::replaceVariables(
            $this->parseOption('subject', $params), $contact);
        $eml->targetModel = $contact;

        // "body" option (deliberately-entered content) takes precedence over template
        if(isset($options['body']['value']) && !empty($options['body']['value'])){ 
            $eml->scenario = 'custom';
            $eml->message = InlineEmail::emptyBody(
                Formatter::replaceVariables($this->parseOption('body', $params), $contact));
            $eml->prepareBody();
            // $eml->insertSignature(array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>'));
        }elseif(!empty($options['template']['value'])){
            $eml->scenario = 'template';
            $eml->template = $this->parseOption('template', $params);
            $eml->prepareBody();
        }

        list ($success, $message) = $this->checkDoNotEmailFields ($eml);
        if (!$success) {
            return array ($success, $message);
        }

        if ($options['doNotEmailLink']['value'])
            $eml->appendDoNotEmailLink ($contact);

        // die(var_dump($eml->send(false)));
        $result = $eml->send($options['logEmail']['value']);
        if(isset($result['code']) && $result['code'] == 200){
            if (YII_DEBUG && YII_UNIT_TESTING) {
                return array(true, $eml->message);
            } else {
                return array(true, "");
            }
        }else{
            return array(false, Yii::t('app', "Email could not be sent"));
        }
    }

}
