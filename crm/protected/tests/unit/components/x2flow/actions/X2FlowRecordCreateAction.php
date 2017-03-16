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

/**
 * @package application.tests.unit.components.x2flow.actions
 */
class X2FlowRecordCreateAction extends X2FlowTestBase {

    public static function referenceFixtures () {
        return array (
            'x2flow' => array ('X2Flow', '.X2FlowRecordCreateAction'),
            'accounts' => array ('Accounts', '_1'),
        );
    }

    /**
     * The flow creates a contact with a company field pointing to the account that
     * triggered the flow.
     */
    public function testCreateContactWithLinkTypeFieldSet () {
        $flow = $this->getFlow ($this,'flow1');
        Yii::app()->params->isAdmin = false;
        $account = $this->accounts ('account1');
        $params = array (
            'model' => $account,
            'modelClass' => 'Accounts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow1'), $params);
        VERBOSE_MODE && print_r ($retVal['trace']);


        // assert flow executed without errors
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $createdContact = Contacts::model ()->findByAttributes (array (
                'firstName' => 'test',
                'lastName' => 'test'
            ));

        // assert that contact with correct first name and last name was created by flow
        $this->assertTrue ($createdContact !== null);

        /*print_r ($createdContact->getAttributes ());
        print_r ($account->getAttributes ());*/

        $relatedX2Models = $createdContact->getRelatedX2Models ();

        // assert that relationship was created from link type field
        $this->assertTrue (sizeof ($relatedX2Models) !== 0);

        // assert that correct relationship was created from link type field
        $this->assertTrue (in_array ($account->id, array_map (function ($elem) {
            return $elem->id; 
        }, $relatedX2Models)));

    }

    /**
     * Tests the create relationship option 
     */
    public function testCreateRelationship () {
        $params = array (
            'user' => 'admin'
        );
        $account = $this->accounts ('account1');
        $params = array (
            'model' => $account,
            'modelClass' => 'Accounts',
        );
        $retVal = $this->executeFlow ($this->x2flow ('flow2'), $params);

        VERBOSE_MODE && print_r ($retVal['trace']);
        $this->assertTrue ($this->checkTrace ($retVal['trace']));

        $lead = X2Leads::model()->findByAttributes (array ('name' => 'test'));
        $this->assertTrue ($lead !== null);

        // assert that lead is related to account
        $relatedModels = $lead->getRelatedX2Models ();
        $this->assertTrue (in_array ($account->id, array_map (function ($elem) {
            return $elem->id; 
        }, $relatedModels)));
    }
}

?>
