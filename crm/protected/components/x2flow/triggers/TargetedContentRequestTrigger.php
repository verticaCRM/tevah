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
 * X2FlowTrigger 
 * 
 * @package application.components.x2flow.actions
 */
class TargetedContentRequestTrigger extends X2FlowTrigger {
	public $title = 'Targeted Content Requested';
	public $info = 'Triggered when a contact visits a page with embedded targeted content.';
	
	public function paramRules() {
		return array(
			'title' => Yii::t('studio', $this->title),
			'info' => Yii::t('studio', $this->info),
			'modelClass' => 'Contacts',
			'options' => array(
				array(
                    'name' => 'url', 
                    'label' => Yii::t ('studio', 'URL'), 
                    'optional' => 1,
                    'operators' => array ('=', '<>', 'list', 'notList', 'contains', 'noContains')
                ),
				array(
                    'name' => 'content', 
                    'comparison' => false,
                    'label' => 
                        Yii::t('studio', 'Default Web Content') . 
                        '<span class="x2-hint" title="'.
                        Yii::t('app', 'This web content gets displayed if the visitor doesn\'t '.
                        'have an associated contact record or if your flow terminates without '.
                        'pushing web content').'">&nbsp;[?]</span>', 
                    'optional' => 1,
                    'type' => 'richtext',
                    'htmlOptions' => array (
                        'class' => 'default-web-content-fieldset'
                    )
                ),

			)
        );
	}

    public function getDefaultReturnVal ($flowId) {
        $retValArr = X2FlowPushWebContent::getPushWebContentScript (
            $this->parseOption ('content', $params), null, $flowId);
        return $retValArr[2];
    }

    public function afterValidate (&$params, $defaultErrMsg='', $flowId) {
        if (!isset($params['model'])) { // no contact record available;
            return X2FlowPushWebContent::getPushWebContentScript (
                $this->parseOption ('content', $params), null, $flowId);
        }
    }
}
