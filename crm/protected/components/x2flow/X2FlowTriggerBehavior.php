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
 * Causes flow triggers to be run as the result of events raised by the owner model
 *
 * @package application.components.x2flow
 */
class X2FlowTriggerBehavior extends CActiveRecordBehavior  {

    /**
     * @var bool Used to prevent update flows from being triggered when update () is called.
     */
    private $updateTriggerEnabled = false;

	public function events() {
		return array_merge(parent::events(),array(
			'onAfterInsert'=>'afterInsert',
			'onAfterUpdate'=>'afterUpdate',
		));
	}

	public function afterInsert($event) {
        X2Flow::trigger('RecordCreateTrigger',array('model'=>$this->getOwner ()));
	}

    public function enableUpdateTrigger () {
        $this->updateTriggerEnabled = true;
    }

    public function disableUpdateTrigger () {
        $this->updateTriggerEnabled = false;
    }

	public function afterUpdate($event) {
        if ($this->updateTriggerEnabled) {
            X2Flow::trigger('RecordUpdateTrigger',array(
                'model'=>$this->getOwner()
            ));
            $this->updateTriggerEnabled = false;
        }
	}


}
