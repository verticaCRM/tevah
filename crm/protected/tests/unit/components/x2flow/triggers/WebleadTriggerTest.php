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

Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.components.*');
Yii::import ('application.components.x2flow.*');
Yii::import ('application.components.x2flow.triggers.*');
Yii::import ('application.components.permissions.*');

/**
 * @package application.tests.unit.components.x2flow.triggers
 */
class WebleadTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '_1'),
        'contacts' => array ('Contacts', '_2')
    );

    /**
     * Trigger config contains leadSource = 'Google' condition
     */
    public function testCheckWithNoTagsAndWithConditions () {
        $flows = $this->getFlows ($this);
        $flow3 = $flows['flow3'];

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact1']),
            'modelClass' => 'Contacts',
        );
        $trigger = X2FlowItem::create ($flow3['trigger']);
        $retVal = $trigger->check ($params);

        // lead source condition succeeds
        $this->assertTrue ($retVal[0]);

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact2']),
            'modelClass' => 'Contacts',
        );
        $retVal = $trigger->check ($params);

        // lead source condition fails
        $this->assertFalse ($retVal[0]);
    }

    /**
     * Trigger config contains leadSource = 'Google' condition #successful tag condition
     */
    public function testCheckWithTagsAndWithConditions () {
        $flows = $this->getFlows ($this);
        $flow4 = $flows['flow4'];

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact1']),
            'modelClass' => 'Contacts',
        );
        $trigger = X2FlowItem::create ($flow4['trigger']);
        $retVal = $trigger->check ($params);

        // lead source condition succeeds, tag condition fails  10
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact2']),
            'modelClass' => 'Contacts',
        );
        $retVal = $trigger->check ($params);

        // lead source condition fails, tag condition fails  00
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact1']),
            'modelClass' => 'Contacts',
            'tags' => '#successful'
        );
        $trigger = X2FlowItem::create ($flow4['trigger']);
        $retVal = $trigger->check ($params);

        // lead source condition succeeds, tag condition succeeds  11
        $this->assertTrue ($retVal[0]);

        $params = array (
            'model' => Contacts::Model ()->findByAttributes ($this->contacts['contact2']),
            'modelClass' => 'Contacts',
            'tags' => '#successful'
        );
        $retVal = $trigger->check ($params);

        // lead source condition fails, tag condition succeeds   01
        $this->assertFalse ($retVal[0]);
    }

}

?>
