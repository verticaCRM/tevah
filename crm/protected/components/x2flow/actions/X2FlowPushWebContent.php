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
 * Push Web Content Action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowPushWebContent  extends X2FlowAction {
	public $title = 'Push Web Content';
	public $info = 'Display custom web content to a contact visiting your website. This action terminates the flow.';

	public function paramRules() {
		return array(
			'title' => Yii::t('studio', $this->title),
			'info' => Yii::t('studio', $this->info),
			'options' => array(
				array(
                    'name' => 'content', 
                    'label' => Yii::t('studio', 'Message'), 
                    'optional' => 1,
                    'type' => 'richtext'
                ),
			)
        );
	}

    /**
     * Returns a JS script which inserts the specified content into the DOM by replacing the
     * targeted content script.
     * 
     * @param string $content the html content to place in the DOM
     * @param object $model the model with which to perform attribute replacement
     */
    public static function getPushWebContentScript ($content, $model=null, $flowId) {
        //AuxLib::debugLog ('getPushWebContentScript');
        if ($model) {
            $targetedContent = Formatter::replaceVariables (
                $content, $model);
        } else {
            $targetedContent = $content;
        }

        //AuxLib::debugLogR ($_COOKIE);

        $targetedContent = preg_replace ("/\n/", '', $targetedContent);
        $targetedContentScript = 'document.write ('.  
            //CJSON::encode (html_entity_decode ($targetedContent)) .  ');';
            CJSON::encode ($targetedContent) .  ');';
        return array (true, "", $targetedContentScript);

    }

	public function execute(&$params, $triggerLogId=null, $flow=null) {
        return self::getPushWebContentScript (
            $this->parseOption ('content', $params),
            $params['model'], $flow->id
        );
	}
}
