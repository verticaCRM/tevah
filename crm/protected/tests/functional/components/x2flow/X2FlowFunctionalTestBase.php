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
 * Contains utility methods to for X2Flow testing 
 * Disclaimer: Placing your cursor over the test window while tests are executing may prevent
 *  automated drag and drop from functioning properly.
 */

abstract class X2FlowFunctionalTestBase extends X2WebTestCase {

    public function checkTrace ($trace) {
        $flowTestBase = new X2FlowTestBase;
        $flowTestBase->checkTrace ($trace);
    }

    /**
     * Navigate to the flow designer page and enter a flow name
     */
    protected function initiateFlowCreation () {
        $this->openX2 ('studio/flowDesigner');
        $this->waitForCondition ("window.document.querySelector ('[name=\"X2Flow[name]\"]')");
        $this->type("name=X2Flow[name]", 'testFlow');
    }

    /**
     * @return string the label for the given flow trigger class 
     */
    protected function getTriggerLabel ($triggerClassName) { 
        $this->storeEval (
            "window.document.querySelector (
                '#trigger-selector [value=\"".$triggerClassName."\"]').innerHTML", 
            'triggerLabel');
        return $this->getExpression ('${triggerLabel}');
    }

    /**
     * @return string the label for the given flow action class 
     */
    protected function getActionLabel ($actionClassName) {
        // get the label for the action
        $this->storeEval (
            "window.document.querySelector ('#item-box .$actionClassName > span').innerHTML", 
            'actionLabel');
        return $this->getExpression ('${actionLabel}');
    }

    /**
     * Selects a trigger from the trigger options menu 
     * @param string $triggerLabel the label the trigger option
     */
    protected function selectTrigger ($triggerClassName) {
        $this->select ('id=trigger-selector', 'value='.$triggerClassName);
        $triggerLabel = $this->getTriggerLabel ($triggerClassName);
        $this->waitForCondition (
            "((configTitle = window.document.querySelector ('#x2flow-main-config h2')) && 
                configTitle.innerHTML === '$triggerLabel')");
    }

    /**
     * Calculates the offset between the action menu item and the last empty node. 
     * @return string A coordinate string which can be used by Selenium mouse interaction methods 
     */
    private function getOffsetBetweenItemBoxAndEmptyNode ($actionClassName) {
        $this->storeEval (
            "window.$('.x2flow-node.x2flow-empty').last ().offset ().left - 
                window.$('#item-box .$actionClassName').offset ().left", 
            'offsetX');
        $offsetX = $this->getExpression ('${offsetX}');
        $this->storeEval (
            "window.$('.x2flow-node.x2flow-empty').last ().offset ().top - 
                window.$('#item-box .$actionClassName').offset ().top", 
            'offsetY');
        $offsetX = $this->getExpression ('${offsetX}');
        $offsetY = $this->getExpression ('${offsetY}');
        VERBOSE_MODE && println ($offsetX);
        VERBOSE_MODE && println ($offsetY);

        return ($offsetX + 50).','.($offsetY + 25);
    }

    /**
     * Drag the specified flow action to the first available empty node
     * @param string $actionClassName The flow action class name of the flow action which should
     *  be dragged into the flow
     */
    protected function appendActionToFlow ($actionClassName) {
        $this->waitForCondition ("window.document.querySelector ('#item-box .$actionClassName')");
        $this->assertElementPresent ('css=#item-box');
        $this->assertElementPresent ("css=#item-box .$actionClassName");
        $this->assertElementPresent ("css=.x2flow-node.x2flow-empty");

        //$this->setMouseSpeed (1);
        $offset = $this->getOffsetBetweenItemBoxAndEmptyNode ($actionClassName);

        // simulate drag from action menu to empty node
        $this->mouseDown ("dom=document.querySelector ('#item-box .$actionClassName')");
        //$this->pause (1000);
        $this->mouseMoveAt ("dom=document.querySelector ('#item-box .$actionClassName')", $offset);
        $this->pause (500); 
        $this->mouseUpAt ("dom=document.querySelector ('#item-box .$actionClassName')", $offset);
        $this->pause (500);

        $actionLabel = $this->getActionLabel ($actionClassName);
        $this->assertConfigMenuOpened ($actionLabel);
    }

    /**
     * Assert that the config menu has the correct title displayed 
     */
    protected function assertConfigMenuOpened ($label) {
        $this->waitForCondition (
            // wait until the config title exists and has the correct text
            "((configTitle = window.document.querySelector ('#x2flow-main-config h2')) && 
                configTitle.innerHTML === '$label')");
        $this->assertText (
            "dom=document.querySelector ('#x2flow-main-config h2')", $label);
    }

    /**
     * Save the flow and assert that there aren't any errors
     */
    protected function saveFlow () {
        $this->clickAndWait ('css=#save-button');
        $this->storeEval (
            // retrieve the error message or null, if there isn't one
            "(elem = window.document.querySelector ('.errorSummary')) && elem.innerHTML", 
            'errorMessage');
        VERBOSE_MODE && println ($this->getExpression ('${errorMessage}'));
        $this->assertElementNotPresent ('css=.errorSummary');
    }

    /**
     * Opens the trigger config menu and asserts that the menu loaded correctly
     */
    protected function openTriggerConfigMenu ($triggerClass) {
        $this->click ("dom=document.querySelector('.x2flow-node.$triggerClass')");
        $this->assertConfigMenuOpened ($this->getTriggerLabel ($triggerClass));
    }

    /**
     * Opens the flow action config menu and asserts that the menu loaded correctly
     */
    protected function openConfigMenu ($actionClass) {
        $this->click (
            // convert the node list into an array and take the last node
            "dom=Array.prototype.slice.call (
                document.querySelectorAll ('.x2flow-node.$actionClass')).pop ()");
        $this->assertConfigMenuOpened ($this->getActionLabel ($actionClass));
    }

    /**
     * Types the given value into the config input corresponding to the field with the given name 
     * @param string $optionName
     * @param string $inputVal
     */
    protected function inputValueIntoConfigMenu ($optionName, $inputVal) {
        $this->assertElementPresent (
            "dom=document.querySelector ('[name=\"$optionName\"] input')");
        $this->type(
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        $this->typeKeys(
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        $this->assertValue (
            "dom=document.querySelector ('[name=\"$optionName\"] input')", $inputVal);
        /*$this->fireEvent (
            "dom=document.querySelector ('[name=\"$optionName\"] input')", 'blur');*/
    }

}
