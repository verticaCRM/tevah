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

Yii::import('application.tests.functional.components.x2flow.X2FlowFunctionalTestBase');

/**
 * Used to test interaction with link type field inputs in the X2Flow UI 
 */
class X2FlowLinkTypeFieldInputTest extends X2FlowFunctionalTestBase {


    /**
     * Ensure that text inputted into link type field inputs will persist after navigating away
     * from the config menu
     */
    public function testAutocompleteValuePersistence () {
        $this->initiateFlowCreation ();
        $this->selectTrigger ('ActionCompleteTrigger'); // opens the trigger config menu
        $this->appendActionToFlow ('X2FlowRecordListAdd'); 
        $this->pause (500);
        $this->inputValueIntoConfigMenu ('listId', 'Big Ticket Sales');

        // open a different config menu and then come back to the action config menu and assert 
        // that the inputted text is still there
        $this->pause (500);
        $this->openTriggerConfigMenu ('ActionCompleteTrigger'); // switches to the trigger menu
        $this->pause (500);
        $this->openConfigMenu ('X2FlowRecordListAdd'); // switches to the flow action menu
        $this->assertValue (
            "dom=document.querySelector ('[name=\"listId\"] input')", 'Big Ticket Sales');

        // save the flow and then got to the action config menu and assert 
        // that the inputted text is still there
        $this->saveFlow ();
        $this->pause (500);
        $this->openConfigMenu ('X2FlowRecordListAdd'); // switches to the flow action menu
        $this->assertValue (
            "dom=document.querySelector ('[name=\"listId\"] input')", 'Big Ticket Sales');

    }

}
