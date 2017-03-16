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

class MassAddToListTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
    );

    /**
     * Create new list from selection then mass add to newly created list
     */
    public function testExecute () {
        X2List::model ()->deleteAllByAttributes (array ('name' => 'test'));
        $newList = new NewListFromSelection;
        $addToList = new MassAddToList;

        // create new list with 2 records
        $_POST['modelType'] = 'Contacts';
        $_POST['listName'] = 'test';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));
        $gvSelection = range (1, 2);
        $newList->execute ($gvSelection);
        $list = X2List::model ()->findByAttributes (array ('name' => 'test'));
        $itemIds = $list->queryCommand (true)->select ('id')->queryColumn ();
        $this->assertEquals (array (1, 2), $itemIds);

        //  add the rest of the contacts to the newly created list
        unset ($_POST['modelType']);
        unset ($_POST['listName']);
        $_POST['listId'] = $list->id;
        $gvSelection = range (3, 24);
        $addToList->execute ($gvSelection);
        $itemIds = $list->queryCommand (true)->select ('id')->queryColumn ();
        $this->assertEquals (range (1, 24), $itemIds);
    }

     /**
      * Test new list + add to list super mass actions
      */
    public function testSuperExecute () {
        X2List::model ()->deleteAllByAttributes (array ('name' => 'test'));
        $_SESSION = array ();
        $newList = new NewListFromSelection;
        $addToList = new MassAddToList;

        TestingAuxLib::suLogin ('admin');
        Yii::app()->user; // initializes $_SESSION superglobal
        Yii::app()->controller = new ContactsController (
            'contacts', new ContactsModule ('contacts', null));

        $idChecksum = SmartActiveDataProvider::calculateChecksumFromIds (
            Yii::app()->db->createCommand ("
                SELECT id
                FROM x2_contacts
                ORDER BY lastUpdated DESC, id DESC
            ")->queryColumn ()
        );

        // perform super mass actions in batches, ensuring that after each batch, the id queue
        // in the session matches the remaining records to be updated. Call the new list from
        // selection mass action on the first batch and the mass add to list on all subsequent
        // batches to simulate behavior of grid view
        $_POST['modelType'] = 'Contacts';
        $_POST['listName'] = 'test';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['SERVER_NAME'] = 'localhost';

        $updated = 0;
        $uid = null;
        $listId = null;
        while (true) {
            ob_start ();
            if (!isset ($listId)) {
                $newList->superExecute ($uid, 24, $idChecksum);
            } else {
                $_POST['listId'] = $listId;
                $addToList->superExecute ($uid, 24, $idChecksum);
            }
            $retVal = CJSON::decode (ob_get_contents ());
            ob_clean ();
            $this->assertTrue (!isset ($retVal['errorCode']));
            $uid = $retVal['uid'];
            if (isset ($retVal['listId'])) {
                $listId = $retVal['listId'];
            }

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
