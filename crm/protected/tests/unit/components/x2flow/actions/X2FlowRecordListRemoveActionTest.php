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
Yii::import ('application.modules.contacts.models.*');

/**
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordListRemoveActionText extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowRecordListRemoveActionTest'),
        'lists' => 'X2List',
        'listItems' => 'X2ListItem',
        'contacts' => 'Contacts',
    );

    /**
     * Ensure that contact is correctly removed from list by flow
     */
    public function testListRemoval () {
        $contact = $this->contacts ('testUser');
        $list = $this->lists ('testUser');
        $this->assertTrue ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);

        VERBOSE_MODE && print_r ($retVal['trace']);

        // assert flow executed without errors
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $this->assertFalse ($list->hasRecord ($contact));
    }

    /**
     * Flow trace should include an error message since the record is not on the specified list 
     */
    public function testListRemovalError () {
        $contact = $this->contacts ('testAnyone');
        $list = $this->lists ('testUser');
        $this->assertFalse ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);

        VERBOSE_MODE && print_r ($retVal['trace']);

        // assert flow executed with errors
        $this->assertFalse ($this->checkTrace ($retVal['trace']));

        $this->assertFalse ($list->hasRecord ($contact));
    }

}

?>
