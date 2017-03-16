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

class X2FlowBasicFlowInteractionTest extends X2FlowFunctionalTestBase {

    /**
     * Select a trigger from the trigger options menu and assert that the config menu changes to
     * the selected trigger's config menu
     */
    public function testTriggerSelection () {
        $this->initiateFlowCreation ();
        $this->selectTrigger ('ActionCompleteTrigger');
    }

    /**
     * Drag an action into the flow and assert that the config menu changes to the newly added
     * action's config menu
     */
    public function testActionDragAndDrop () {
        $this->initiateFlowCreation ();
        $this->appendActionToFlow ('X2FlowRecordListAdd');
    }

    /**
     * Selects a trigger, adds an action, and saves the flow 
     */
    public function testCreateNewFlow () {
        // should fail to save since we haven't added a trigger and an action
        $this->initiateFlowCreation ();
        $this->clickAndWait ('css=#save-button');
        $this->waitForElementPresent ('css=.errorSummary', 5000);

        // now add a trigger and an action and save again
        $this->initiateFlowCreation ();
        $this->selectTrigger ('ActionCompleteTrigger');
        $this->appendActionToFlow ('X2FlowRecordListAdd');
        $this->inputValueIntoConfigMenu ('listId', 1);
        $this->saveFlow ();
    }

    /**
     * Add a trigger and action to the flow and assert that both config menus are accessible
     */
    public function testFlowConfigChange () {
        $this->initiateFlowCreation ();
        $this->selectTrigger ('ActionCompleteTrigger'); // opens the trigger config menu
        $this->appendActionToFlow ('X2FlowRecordListAdd'); // opens the flow action config menu
        $this->openTriggerConfigMenu ('ActionCompleteTrigger'); // switches to the trigger menu
        $this->openConfigMenu ('X2FlowRecordListAdd'); // switches to the flow action menu
    }

    /**
     * Input a value into a field in one of the config menus 
     */
    public function testConfigInput () {
        $this->initiateFlowCreation ();
        $this->appendActionToFlow ('X2FlowRecordListAdd'); 
        $this->inputValueIntoConfigMenu ('listId', 'Big Ticket Sales');
    }

}
