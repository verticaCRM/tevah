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

Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.marketing.models.*');
Yii::import('application.modules.marketing.models.*');

/**
 * @package application.tests.unit.modules.contacts.models
 */
class FingerprintBehaviorTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.FingerprintTest'),
        'anonContacts' => array ('AnonContact', '.FingerprintTest'),
        'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
    );

    public function testAfterDelete () {
        // test Contacts afterDelete
        $contact = $this->contacts ('contact1');
        $fingerprint = $contact->fingerprint;
        $this->assertTrue ($contact->fingerprint instanceof Fingerprint);
        $this->assertTrue ($contact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

        // test AnonContact afterDelete
        $anonContact = $this->anonContacts ('anonContact2');
        $fingerprint = $anonContact->fingerprint;
        $this->assertTrue ($anonContact->fingerprint instanceof Fingerprint);
        $this->assertTrue ($anonContact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

        // ensure that fingerprint isn't deleted if it's shared by an anonymous contact
        $anonContact = $this->anonContacts ('anonContact3');
        $contact = $this->contacts ('contact2');
        $fingerprint = $anonContact->fingerprint;
        $this->assertTrue ($fingerprint instanceof Fingerprint);
        $contact->fingerprintId = $fingerprint->id;
        $this->assertTrue ($contact->save ());
        $this->assertTrue ($anonContact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertTrue ($fingerprint instanceof Fingerprint);

        // ensure that fingerprint isn't deleted if it's shared by a contact
        $contact = $this->contacts ('contact2');
        $anonContact = $this->anonContacts ('anonContact1');
        $fingerprint = $contact->fingerprint;
        $this->assertTrue ($fingerprint instanceof Fingerprint);
        $anonContact->fingerprintId = $fingerprint->id;
        $this->assertTrue ($anonContact->save ());
        $this->assertTrue ($contact->delete ());
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertTrue ($fingerprint instanceof Fingerprint);
    }

    public function testAfterDeleteByPk () {
        $anonContact = $this->anonContacts ('anonContact2');
        $fingerprint = $anonContact->fingerprint;
        X2Model::model('AnonContact')->findByPk ($anonContact->id)->delete ();
        $fingerprint = Fingerprint::model ()->findByPk ($fingerprint->id);
        $this->assertEquals (null, $fingerprint);

    }

}

?>
