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
 * Create Record action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowEmail extends BaseX2FlowEmail {
	public $title = 'Email';
	public $info = 'Send a template or custom email to the specified address.';


	public function paramRules() {
        $parentRules = parent::paramRules ();
        $parentRules['options'] = array_merge (
            $parentRules['options'],
            array (
                array('name'=>'to','label'=>Yii::t('studio','To:'),'type'=>'email'),
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
                    'name' => 'body', 
                    'label' => Yii::t('studio', 'Message'),
                    'optional' => 1,
                    'type' => 'richtext'
                ),

            )
        );
        return $parentRules;
    }

	public function execute(&$params) {
		$eml = new InlineEmail;
		$options = &$this->config['options'];
		$eml->to = Formatter::replaceVariables ($this->parseOption('to',$params), $params['model']);
        
        $historyFlag = false;
        if(isset($params['model'])){
            $historyFlag = true;
            $eml->targetModel=$params['model'];
        }
		if(isset($options['cc']['value']))
			$eml->cc = $this->parseOption('cc',$params);
		if(isset($options['bcc']['value'])){
			$eml->bcc = $this->parseOption('bcc',$params);
        }

		//$eml->from = array('address'=>$this->parseOption('from',$params),'name'=>'');
        $eml->credId = $this->parseOption('from',$params);
        if ($eml->credentials && $eml->credentials->user)
            $eml->setUserProfile($eml->credentials->user->profile);

        //printR ($eml->from, true);
		$eml->subject = Formatter::replaceVariables($this->parseOption('subject',$params),$params['model']);

        // "body" option (deliberately-entered content) takes precedence over template
		if(isset($options['body']['value']) && !empty($options['body']['value'])) {	
            $eml->scenario = 'custom';
			$eml->message = InlineEmail::emptyBody(Formatter::replaceVariables($this->parseOption('body',$params),$params['model']));
			$eml->prepareBody();
			// $eml->insertSignature(array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>'));
		} elseif(!empty($options['template']['value'])) {
			$eml->scenario = 'template';
			$eml->template = $this->parseOption('template',$params);
			$eml->prepareBody();
		}

        list ($success, $message) = $this->checkDoNotEmailFields ($eml);
        if (!$success) {
            return array ($success, $message);
        }

		$result = $eml->send($historyFlag);
		if (isset($result['code']) && $result['code'] == 200) {
            if (YII_DEBUG && YII_UNIT_TESTING) {
                return array(true, $eml->message);
            } else {
                return array(true, "");
            }
        } else {
            return array (false, Yii::t('app', "Email could not be sent"));
        }
	}
}
