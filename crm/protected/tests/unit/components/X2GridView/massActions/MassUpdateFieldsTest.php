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
Yii::import ('application.modules.contacts.controllers.*');
Yii::import ('application.modules.contacts.*');
Yii::import ('application.components.X2GridView.massActions.*');

class MassUpdateFieldsTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
    );

    /**
     * Mass update firstName and lastName for fixture records 
     */
    public function testExecute () {
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_POST['fields'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massUpdate = new MassUpdateFields;
        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" and lastName="test"
        ')->queryScalar ());
        $massUpdate->execute ($gvSelection);
        $this->assertEquals (24, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" and lastName="test"
        ')->queryScalar ());
    }

    /**
     * Super mass update firstName and lastName for fixture records 
     */
    public function testSuperExecute () {
        $_SESSION = array ();
        $massUpdate = new MassUpdateFields;
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));

        $this->assertEquals (0, Yii::app()->db->createCommand ('
            SELECT count(*)
            FROM x2_contacts
            WHERE firstName="test" AND lastName="test"
        ')->queryScalar ());

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be updated
        $_POST['modelType'] = 'Contacts';
        $_POST['fields'] = array (
            'firstName' => 'test',
            'lastName' => 'test',
        );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            ob_start ();
            $massUpdate->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ());
            $this->assertTrue (!isset ($retVal['errorCode']));
            $uid = $retVal['uid'];
            ob_clean ();

            $remainingIds = array_map (
                function ($a) { return $a['id']; }, 
                Yii::app()->db->createCommand ('
                    SELECT id
                    FROM x2_contacts
                    WHERE firstName!="test" OR lastName!="test"
                ')->queryAll ());
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                $storedIds = $_SESSION[MassAction::SESSION_KEY_PREFIX.$uid];
                sort ($storedIds);
                $this->assertEquals ($remainingIds, $storedIds);
            }
        }
    }
}

?>
