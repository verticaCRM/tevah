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
class RecordTagAddTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '_1'),
        'accounts' => array ('Accounts', '_1')
    );

    /**
     * Trigger config only contains tag condition 
     */
    public function testCheckWithTags () {
        $flow = $this->getFlow ($this,'flow1');
        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
        );
        $trigger = X2FlowItem::create ($flow['trigger']);
        $retVal = $trigger->check ($params);

        // tag condition fails
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $retVal = $trigger->check ($params);

        // tag condition succeeds
        $this->assertTrue ($retVal[0]);
    }

    /**
     * Trigger config contains tag condition (must have '#successful') and name='account1' condition
     */
    public function testCheckWithTagsAndConditions () {
        $flows = $this->getFlows ($this);
        $flow2 = $flows['flow2'];
        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account2']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $trigger = X2FlowItem::create ($flow2['trigger']);
        $retVal = $trigger->check ($params);

        // tag condition succeeds, name condition fails
        $this->assertFalse ($retVal[0]);

        $params = array (
            'model' => Accounts::Model ()->findByAttributes ($this->accounts['account1']),
            'modelClass' => 'Accounts',
            'tags' => '#successful'
        );
        $retVal = $trigger->check ($params);

        // tag condition succeeds, name condition succeeds
        $this->assertTrue ($retVal[0]);
    }

}

?>
