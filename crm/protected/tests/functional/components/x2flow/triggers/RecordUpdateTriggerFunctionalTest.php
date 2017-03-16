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

Yii::import('application.tests.functional.components.x2flow.X2FlowFunctionalTestBase');
Yii::import('application.modules.contacts.models.*');

class RecordUpdateTriggerFuntionalTest extends X2FlowFunctionalTestBase {

    public $fixtures = array (
        'flows' => array ('X2Flow', '.RecordUpdateTriggerFunctionalTest'),
        'lists' => 'X2List',
    );

    public function testNoTriggerOnView () {
        $flow = $this->flows ('flow1');
        TriggerLog::model ()->deleteAllByAttributes (array (
            'flowId' => $flow->id
        ));
		$this->openX2 ('contacts/list/id/30');

        // ensure that flow isn't triggered when list is viewed
        $this->assertEquals (0, sizeof (TriggerLog::model ()->findByAttributes (array (
            'flowId' => $flow->id
        ))));
    }

    public function testTriggerOnUpdate () {
        $flow = $this->flows ('flow1');
        TriggerLog::model ()->deleteAllByAttributes (array (
            'flowId' => $flow->id
        ));
        $this->openX2 ('contacts/updateList/id/30');
        $this->clickAndWait ('css=#save-button');
        $log = TriggerLog::model ()->findByAttributes (array (
            'flowId' => $flow->id
        ));
        $this->checkTrace (CJSON::decode ($log->triggerLog));

    }


}
