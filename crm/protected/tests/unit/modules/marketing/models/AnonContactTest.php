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

/* @edition:pla */

Yii::import('application.modules.actions.models.*');

class AnonContactTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.FingerprintTest'),
        'anonContacts' => array ('AnonContact', '.FingerprintTest'),
        'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
    );

    public function testBeforeSave () {
        $lastModifiedId = Yii::app()->db->createCommand()
            ->select('id')
            ->from('x2_anon_contact')
            ->order('lastUpdated ASC')
            ->queryScalar();

        $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
        $this->assertNotEquals (null, $anonContact);
        $action = new Actions;
        $action->setAttributes (array (
            'id' => 1000000000,
            'associationType' => 'anoncontact',
            'associationId' => $lastModifiedId,
            'createDate' => time (),
            'lastUpdated' => time (),
            'completeDate' => time (),
        ), false);
        $this->assertSaves ($action);

        // set max to 0 to check if anon contact gets deleted on before save event
        Yii::app()->settings->maxAnonContacts = 0;
        $anonContact = new AnonContact;
        $anonContact->setAttributes (array (
            'trackingKey' => 'test',
            'createDate' => time (),
            'fingerprintId' => 20000,
        ), false);
        $this->assertSaves ($anonContact);

        $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
        $action = X2Model::model('Actions')->findByPk($action->id);
        // should have deleted this anon contact before the new anon contact was saved
        $this->assertEquals (null, $anonContact);
        // should also have deleted action associated with deleted anon contact
        $this->assertEquals (null, $action);
    }

    public function testAfterDelete () {
        $anonContact = $this->anonContacts ('anonContact1');
        $action = new Actions;
        $action->setAttributes (array (
            'subject' => 'test',
            'associationName' => $anonContact->id,
            'associationId' => $anonContact->id,
            'associationType' => 'anoncontact',
        ), false);
        $this->assertSaves ($action);

        $this->assertTrue ($anonContact->delete ());
        // ensure that associated action gets deleted with the anon contact
        $this->assertEquals (null,  Actions::model ()->findByPk ($action->id));
    }


}

?>
