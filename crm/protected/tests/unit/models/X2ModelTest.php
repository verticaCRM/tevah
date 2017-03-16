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

Yii::import('application.modules.accounts.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.opportunities.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * Test certain features specific to {@link X2Model}.
 *
 * Note, this will use subclasses. This is a shortcut that should probably in the
 * future be replaced with mocks, fake tables and special fixtures in order to
 * generally test {@link X2Model} without relying on ephemeral data.
 *
 * @package application.tests.unit.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2ModelTest extends X2DbTestCase {

    public $fixtures = array(
        'contact' => 'Contacts',
        'account' => 'Accounts',
    );

    private $_nameFields;

    public function nameFields() {
        if (!isset($this->_nameFields)) {
            $this->_nameFields = array();
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'firstName', 'modelName' => 'Contacts'));
            $this->_nameFields[] = Fields::model()->findByAttributes(array('fieldName' => 'lastName', 'modelName' => 'Contacts'));
        }
        return $this->_nameFields;
    }

    public function setDefaultName() {
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = 'Gustavo';
        $lastName->defaultValue = 'Fring';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function resetNameFields() {
        list($firstName, $lastName) = $this->nameFields();
        $firstName->defaultValue = '';
        $lastName->defaultValue = '';
        $firstName->save();
        $lastName->save();
        Yii::app()->cache->flush();
    }

    public function setUp() {
        parent::setUp();
        $this->setDefaultName();
    }

    public function tearDown() {
        $this->resetNameFields();
        parent::tearDown();
    }

    /**
     * Test setting default values in new records
     */
    public function testDefaultValues() {
        foreach (X2Model::model('Contacts')->getFields() as $field) {
            // Retrieve new values:
            $field->refresh();
        }

        // Setting default values in the constructor
        $contact = new Contacts;
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);

        // Setting default values in setX2Fields
        $contact->firstName = '';
        $contact->lastName = '';
        $input = array();
        $contact->setX2Fields($input);
        $this->assertEquals('Gustavo', $contact->firstName);
        $this->assertEquals('Fring', $contact->lastName);
    }

    public function testFindByEmail() {
        $c = Contacts::model()->findByEmail($this->contact('testAnyone')->email);
        $this->assertTrue((bool) $c);
        $this->assertEquals($this->contact('testAnyone')->id, $c->id);
    }

    /**
     * A cursory test of the auto-ref update for the link-type fields refactor.
     */
    public function testUpdateNameIdRefs() {
        $account = $this->account('testQuote');
        $contact = $this->contact('testAnyone');
        // Test name change:
        $account->refresh();
        $account->name = 'A smouldering crater left behind by the G-man';
        $account->save();
        $contact->refresh();
        $this->assertEquals(Fields::nameId($account->name, $account->id), $contact->company);
        // Test deletion:
        $account->delete();
        $contact->refresh();
        $this->assertEquals($account->name, $contact->company);
    }

    public function testMassUpdateNameId() {
        $contact = $this->contact('testAnyone');
        // First, need to break all the nameIds...
        Contacts::model()->updateAll(array('nameId' => null));
        // Try with the mass update method, one ID:
        X2Model::massUpdateNameId('Contacts', array($contact->id));
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        // Again, but with the "ids" parameter an int instead of an array
        X2Model::massUpdateNameId('Contacts', $contact->id);
        $contact->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        // Try again, multiple records:
        $contact2 = $this->contact('testUser');
        Contacts::model()->updateAll(array('nameId' => null));
        X2Model::massUpdateNameId('Contacts', array($contact->id, $contact2->id));
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name, $contact2->id), $contact2->nameId);
        // Try one last time, all records:
        Contacts::model()->updateAll(array('nameId' => null));
        X2Model::massUpdateNameId('Contacts');
        $contact->refresh();
        $contact2->refresh();
        $this->assertEquals(Fields::nameId($contact->name, $contact->id), $contact->nameId);
        $this->assertEquals(Fields::nameId($contact2->name, $contact2->id), $contact2->nameId);
    }

    /*
     * The following tests are all related to the "mergeRelatedRecords" function
     * which merges the all related record types (Actions, Events, Tags, etc.)
     * on two X2Model instances. The the undo merge is also tested in each
     * unit test. 
     */

    public function testMergeActions() {
        $contact = $this->contact('testAnyone');
        $action = new Actions;
        $action->actionDescription = "TEST";
        $action->visibility = 1;
        $action->associationType = "contacts";
        $action->associationId = $contact->id;
        $action->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());

        $mergeData = $model->mergeActions($contact, true);

        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());

        $model->unmergeActions($contact->id, $mergeData);

        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_actions')
                        ->where('associationType = "contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());
    }

    public function testMergeEvents() {
        $contact = $this->contact('testAnyone');

        $event = new Events;
        $event->type = 'record_updated';
        $event->associationType = 'Contacts';
        $event->associationId = $contact->id;
        $event->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(2, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());

        $mergeData = $model->mergeEvents($contact, true);

        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(3, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());

        $model->unmergeEvents($contact->id, $mergeData);

        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $contact->id))
                        ->queryScalar());
        $this->assertEquals(2, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_events')
                        ->where('associationType = "Contacts" AND associationId = :id', array(':id' => $model->id))
                        ->queryScalar());
    }

    public function testMergeNotifications() {
        $contact = $this->contact('testAnyone');

        $notif = new Notification;
        $notif->modelType = 'Contacts';
        $notif->modelId = $contact->id;
        $notif->type = 'weblead';
        $notif->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $contact->id))
                        ->queryScalar());

        $mergeData = $model->mergeNotifications($contact, true);

        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $contact->id))
                        ->queryScalar());

        $model->unmergeNotifications($contact->id, $mergeData);

        $this->assertEquals(0, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $model->id))
                        ->queryScalar());
        $this->assertEquals(1, Yii::app()->db->createCommand()->select('COUNT(*)')
                        ->from('x2_notifications')
                        ->where('modelType = "Contacts" AND modelId = :id', array(':id' => $contact->id))
                        ->queryScalar());
    }

    public function testMergeTags() {
        $contact = $this->contact('testAnyone');

        $contact->addTags(array('test'));

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();
        $this->assertEquals(0, count($model->getTags(true)));
        $this->assertEquals(1, count($contact->getTags(true)));

        $mergeData = $model->mergeTags($contact, true);

        $this->assertEquals(1, count($model->getTags(true)));
        $this->assertEquals(0, count($contact->getTags(true)));

        $model->unmergeTags($contact->id, $mergeData);

        $this->assertEquals(0, count($model->getTags(true)));
        $this->assertEquals(1, count($contact->getTags(true)));
    }

    public function testMergeRelationships() {
        $contact = $this->contact('testAnyone');
        $otherContact = $this->contact('testUser');
        $thirdContact = $this->contact('testUser_unsent');

        $rel = new Relationships;
        $rel->firstType = $rel->secondType = 'Contacts';
        $rel->firstId = $contact->id;
        $rel->secondId = $otherContact->id;
        $rel->save();
        $rel = new Relationships;
        $rel->firstType = $rel->secondType = 'Contacts';
        $rel->secondId = $contact->id;
        $rel->firstId = $thirdContact->id;
        $rel->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals(1, count($model->getRelatedX2Models(true)));
        $this->assertEquals(2, count($contact->getRelatedX2Models(true)));

        $mergeData = $model->mergeRelationships($contact, true);

        $this->assertEquals(0, count($contact->getRelatedX2Models(true)));
        $this->assertEquals(3, count($model->getRelatedX2Models(true)));

        $model->unmergeRelationships($contact->id, $mergeData);

        $this->assertEquals(2, count($contact->getRelatedX2Models(true)));
        $this->assertEquals(1, count($model->getRelatedX2Models(true)));
    }

    public function testMergeLinkFields() {
        $contact = $this->contact('testAnyone');
        $account = $this->account('testQuote');

        $account->primaryContact = $contact->nameId;
        $account->save();

        $model = new Contacts;
        foreach ($contact->attributes as $key => $val) {
            if ($key != 'id' && $key != 'nameId') {
                $model->$key = $val;
            }
        }
        $model->save();

        $this->assertEquals($contact->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);

        $mergeData = $model->mergeLinkFields($contact, true);
        $account = X2Model::model('Accounts')->findByPk($account->id);

        $this->assertEquals($model->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);

        $model->unmergeLinkFields($contact->id, $mergeData);
        $account = X2Model::model('Accounts')->findByPk($account->id);

        $this->assertEquals($contact->nameId, $account->primaryContact);
        $this->assertNotEquals($contact->nameId, $model->nameId);
    }

}

?>
