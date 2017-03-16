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

Yii::import ('application.modules.contacts.controllers.*');
Yii::import ('application.modules.contacts.*');
Yii::import ('application.components.X2GridView.massActions.*');

class NewListFromSelectionTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
    );

    /**
     * Mass update firstName and lastName for fixture records 
     */
    public function testExecute () {
        X2List::model ()->deleteAllByAttributes (array ('name' => 'test'));
        $_POST['modelType'] = 'Contacts';
        $_POST['listName'] = 'test';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $gvSelection = range (1, 24);
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $newList = new NewListFromSelection;
        $newList->execute ($gvSelection);
        $list = X2List::model ()->findByAttributes (array ('name' => 'test'));
        $itemIds = $list->queryCommand (true)->select ('id')->queryColumn ();
        $contactIds = Yii::app ()->db->createCommand (" 
            SELECT id 
            FROM x2_contacts
        ")->queryColumn ();
        $this->assertEquals ($contactIds, $itemIds);
    }

    /**
     * Super mass update firstName and lastName for fixture records 
     */
    public function testSuperExecute () {
        X2List::model ()->deleteAllByAttributes (array ('name' => 'test'));
        $_SESSION = array ();
        $newList = new NewListFromSelection;
        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be updated
        $_POST['modelType'] = 'Contacts';
        $_POST['listName'] = 'test';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app ()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        $updated = 0;
        $uid = null;
        while (true) {
            ob_start ();
            $newList->superExecute ($uid, 24, $idChecksum);
            $retVal = CJSON::decode (ob_get_contents ());
            $this->assertTrue (!isset ($retVal['errorCode']));
            $uid = $retVal['uid'];
            ob_clean ();

            // get ids of contacts not in new list
            $remainingIds = Yii::app()->db->createCommand ('
                SELECT t.id
                FROM x2_contacts AS t
                WHERE t.id NOT IN (
                    SELECT contactId 
                    FROM x2_list_items AS t2
                    JOIN x2_lists AS t3 ON t3.id = t2.listId
                    WHERE t.id = t2.contactId AND t3.name="test"
                )
            ')->queryColumn ();
            $storedIds = $_SESSION[MassAction::SESSION_KEY_PREFIX.$uid];
            sort ($storedIds);
            $this->assertEquals ($remainingIds, $storedIds);

            // new list from selection mass action should only ever get run on the first batch.
            // subsequent batches get added to the list (mass action swapping is handled 
            // client-side)
            break;
        }
    }
}

?>
