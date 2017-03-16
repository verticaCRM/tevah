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
 * 
 * @package application.tests.unit.components.x2flow.triggers
 * @author Demitri Morgan <demitri@x2engine.com>
 * @author Derek Mueller <derek@x2engine.com>
 */
class X2FlowTriggerTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowTriggerTest'),
        'lists' => 'X2List',
        'listItems' => 'X2ListItem',
        'contacts' => 'Contacts',
    );

    public function testGetTriggerInstances() {
        $this->assertGetInstances(
            $this, 'Trigger',array(
                'X2FlowTrigger',
                'X2FlowSwitch',
                'BaseTagTrigger',
                'BaseWorkflowStageTrigger',
                'BaseWorkflowTrigger'
            ));
    }

    /**
     * Ensure that on list condition works properly
     */
    public function testOnListCondition () {
        $contact = $this->contacts ('testUser');
        $list = $this->lists ('testUser');
        $this->assertTrue ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flowOnListCondition'), $params);

        VERBOSE_MODE && print_r ($retVal['trace']);

        // assert flow executed without errors since contact is on list
        $this->assertTrue ($this->checkTrace ($retVal['trace']));


        $contact = $this->contacts ('testAnyone');
        $this->assertFalse ($list->hasRecord ($contact));

        $params = array (
            'model' => $contact,
            'modelClass' => 'Contacts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flowOnListCondition'), $params);

        VERBOSE_MODE && print_r ($retVal['trace']);

        // assert flow executed with errors since contact is not on list
        $this->assertFalse ($this->checkTrace ($retVal['trace']));
    }

    
}

?>
