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
Yii::import ('application.modules.users.models.*');

class SmartActiveDataProviderTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.SmartActiveDataProviderTest'),
        'users' => array ('User', '.SmartActiveDataProviderTest'),
    );

    public function testGetRecordIds () {
        $_GET['Contacts'] = array (
            'firstName' => 't',
            'assignedTo' => 'Chris',
        );
        $_GET['Contacts_sort'] = 'id.desc';
        $contact = new Contacts ('search');
        $dataProvider = $contact->search (PHP_INT_MAX);
        $dataProvider->calculateChecksum = true;
        $data = $dataProvider->getData ();
        $this->assertNotEquals (0, count ($data));
        $ids = array ();
        foreach ($data as $model) {
            $ids[] = $model->id;
        }
        $this->assertEquals ($dataProvider->getRecordIds (), $ids);

        $contact = new Contacts ('search');
        // lower page size so that record count is incorrect
        $dataProvider = $contact->search (10);
        $dataProvider->calculateChecksum = true;
        $data = $dataProvider->getData ();
        $this->assertNotEquals (0, count ($data));
        $ids = array ();
        foreach ($data as $model) {
            $ids[] = $model->id;
        }
        $this->assertNotEquals ($dataProvider->getRecordIds (), $ids);

        // ensure that default ordering gets applied in both cases (SmartActiveDataProvider applies
        // default id DESC ordering if /\bid\b/ isn't found in sort order)
        $_GET['Contacts'] = array (
            'firstName' => 't',
            'assignedTo' => 'Chris',
        );
        $_GET['Contacts_sort'] = 'dupeCheck.desc';
        $contact = new Contacts ('search');
        $dataProvider = $contact->search (PHP_INT_MAX);
        $dataProvider->calculateChecksum = true;
        $data = $dataProvider->getData ();
        $this->assertNotEquals (0, count ($data));
        $ids = array ();
        foreach ($data as $model) {
            $ids[] = $model->id;
        }
        $this->assertEquals ($dataProvider->getRecordIds (), $ids);

        // more filters, different sort order
        $_GET['Contacts'] = array (
            'firstName' => 't',
            'lastName' => '<>t',
            'assignedTo' => 'Chloe',
            'rating' => '<4',
        );
        $_GET['Contacts_sort'] = 'rating.desc';
        $contact = new Contacts ('search');
        $dataProvider = $contact->search (PHP_INT_MAX);
        $dataProvider->calculateChecksum = true;
        $data = $dataProvider->getData ();
        $this->assertNotEquals (0, count ($data));
        $ids = array ();
        foreach ($data as $model) {
            $ids[] = $model->id;
        }
        $this->assertEquals ($dataProvider->getRecordIds (), $ids);
    }

}

?>
