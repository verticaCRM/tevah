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
class WebActivityTrigger extends X2FlowTrigger {
	public $title = 'Contact Web Activity';
	public $info = 'Triggered when a contact visits a webpage';
	
	public function paramRules() {
        $options = array (
            array(
                'name'=>'url',
                'label'=>Yii::t('studio','URL'),
                'optional'=>1,
                'operators'=>array('=','<>','list','notList','contains','noContains')
            ),
        );

        /* x2plastart */ 
        $options[] = array(
            'name'=>'probability',
            'label'=>Yii::t('studio','Match Probability'),
            'optional'=>1,
            'operators'=>array('=','<','>')
        );
        /* x2plaend */  

		return array(
			'title' => Yii::t('studio',$this->title),
			'info' => Yii::t('studio',$this->info),
			'modelClass' => 'Contacts',
			'options' => $options
        );
	}
}
