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

Yii::import ('application.modules.accounts.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class RecordUpdateTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.RecordUpdateTriggerTest'),
        'contacts' => 'Contacts',
    );

    /**
     * Ensure that the attribute changed condition works properly
     */
    public function testChangedCondition () {
        $this->clearLogs ();

        // value changed but doesn't equal expected value
        $contact = $this->contacts ('testAnyone');
        $contact->firstName = 'not test';
        $this->assertSaves ($contact);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertFalse ($this->checkTrace ($log)); 

        // value changed and equals expected value
        $contact->afterFind ();
        $this->clearLogs ();
        $contact->firstName = 'test';
        $this->assertSaves ($contact);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($log)); 

        // value didn't change
        $contact->afterFind ();
        $this->clearLogs ();
        $contact->lastName = 'test';
        $this->assertSaves ($contact);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        VERBOSE_MODE && print_r ($log);
        $this->assertFalse ($this->checkTrace ($log)); 
    }

}

?>
