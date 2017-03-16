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

Yii::import('application.modules.contacts.models.*');

/**
 * @package application.tests.unit.modules.contacts.models
 */
class ContactsTest extends X2DbTestCase {

    public static function referenceFixtures () {
        return array(
/* x2plastart */
            'contacts' => array ('Contacts', '.FingerprintTest'),
            'anonContacts' => array ('AnonContact', '.FingerprintTest'),
            'fingerprints' => array ('Fingerprint', '.FingerprintTest'),
/* x2plaend */
        );
    }
    
/* x2plastart */
    public function testRelations() {
        $contact = $this->contacts ('contact1');
        $this->assertTrue ($contact->fingerprint instanceof Fingerprint);
    }
/* x2plaend */

}

?>
