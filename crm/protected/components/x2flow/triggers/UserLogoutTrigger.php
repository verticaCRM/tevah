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
 * X2FlowTrigger
 *
 * @package application.components.x2flow.actions
 */
class UserLogoutTrigger extends X2FlowTrigger {
	public $title = 'User Signed Out';
	public $info = 'Triggered when a user signs out of X2Engine.';

	public function paramRules() {
		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'options' => array(
				array(
                    'name'=>'user',
                    'label'=>Yii::t('studio','User'),
                    'type'=>'dropdown',
                    'multiple'=>1,
                    'options'=>array ('' => Yii::t('x2flow', 'Anyone')) + 
                        X2Model::getAssignmentOptions(false,false),
                    'operators'=>array(
                        '=',
                        '<>',
                        'list',
                        'notList'
                    ),
                    'optional'=>1
                ),
			));
	}
}
