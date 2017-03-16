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
Yii::import ('application.components.X2GridView.massActions.*');
Yii::import ('application.modules.contacts.ContactsModule');
Yii::import ('application.modules.contacts.controllers.*');

class MassTagTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
        'tags' => array ('Tags', '.MassActionTest'),
    );

    /**
     * Attempt to mass delete range of records in fixture file
     */
    public function testExecute () {
        $_POST['modelType'] = 'Contacts';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_POST['tags'] = array ('#test1', '#test2');
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massTag = new MassTag;
        $this->assertEquals (19, Yii::app()->db->createCommand ('
            SELECT COUNT(DISTINCT(t.id))
            FROM x2_contacts AS t
            JOIN x2_tags ON itemId=t.id
            WHERE type="Contacts" AND tag LIKE binary "#test1" or tag like binary "#test2" 
        ')->queryScalar ());
        $massTag->execute ($gvSelection);
        $this->assertEquals (24, Yii::app()->db->createCommand ('
            SELECT COUNT(DISTINCT(t.id))
            FROM x2_contacts AS t
            JOIN x2_tags ON itemId=t.id
            WHERE type="Contacts" AND tag LIKE binary "#test1" or tag like binary "#test2" 
        ')->queryScalar ());
    }

    /**
     * Attempt to super mass delete range of records in fixture file
     */
    public function testSuperExecute () {
        $_SESSION = array ();
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal

        // confirm super mass deletion via password
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $massTag = new MassTag;

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be tagged
        $_POST['modelType'] = 'Contacts';
        $_POST['tags'] = array ('#test3', '#test4');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $uid = null;
        while (true) {
            ob_start ();
            $massTag->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ());
            $uid = $retVal['uid'];
            ob_clean ();
            $this->assertTrue (!isset ($retVal['errorCode']));
            print_r ($retVal);
            $remainingIds = Yii::app ()->db->createCommand ("
                SELECT DISTINCT(t.id)
                FROM x2_contacts AS t
                WHERE t.id NOT in (
                    SELECT itemId AS id
                    FROM x2_tags
                    WHERE itemId=t.id and tag like binary '#test3'
                ) and t.id not in (
                    SELECT itemId AS id
                    FROM x2_tags
                    WHERE itemId=t.id and tag like binary '#test4'
                )
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ();
            print_r ($remainingIds);
            print_r ($_SESSION);
            if (isset ($retVal['complete'])) {
                $this->assertEquals (0, count ($remainingIds));
                $this->assertTrue (!isset ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
                break;
            } else {
                $this->assertEquals (
                    $remainingIds, array_reverse ($_SESSION[MassAction::SESSION_KEY_PREFIX.$uid]));
            }
        }
    }

}

?>
