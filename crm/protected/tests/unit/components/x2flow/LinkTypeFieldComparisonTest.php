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
 * Used to ensure that link type field conditions in X2Flow are functional
 * @package application.tests.unit.components.x2flow.triggers
 */
class LinkTypeFieldComparisonTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '_1'),
        'contacts' => array ('Contacts', '.LinkTypeFieldComparisonTest'),
        'accounts' => 'Accounts'
    );

    /**
     * Trigger config asserts that contact account field points to 
     */
    public function testCheckConditions () {
        $contact = Contacts::model()
            ->findByAttributes ($this->contacts['contact2']);
        $params = array (
            'model' => $contact
        );

        $retVal = $this->executeFlow (
            X2Flow::model ()->findByAttributes ($this->x2flow['flow8']), $params);

        // contact has incorrect company name
        $this->assertFalse ($this->checkTrace ($retVal['trace']));

        $contact = Contacts::model()
            ->findByAttributes ($this->contacts['contact1']);
        $params = array (
            'model' => $contact
        );

        $retVal = $this->executeFlow (
            X2Flow::model ()->findByAttributes ($this->x2flow['flow8']), $params);

        // contact has correct company name
        $this->assertTrue ($this->checkTrace ($retVal['trace']));
    }

}

?>
