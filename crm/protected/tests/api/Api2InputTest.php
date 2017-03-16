<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

Yii::import('application.tests.api.Api2TestBase');

/**
 * Really basic data-saving and deletion tests.
 *
 * These tests are currently really shallow (as of 4.1) because time is running 
 * out. There may still thus be bugs lurking in the depths of {@link Api2Controller}
 *
 * @todo Expand upon and generalize the tests for completeness/thoroughness
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2InputTest extends Api2TestBase {

    public $action;

    public $fixtures = array(
        'contacts' => 'Contacts',
        'accounts' => 'Accounts',
        'relationships' => 'Relationships',
        'tags' => 'Tags'
    );

    public function urlFormat(){
        $urlFormats = array(
            'model' => 'api2/{modelAction}',
            'relationships' => 'api2/{_class}/{_id}/relationships',
            'relationships_get' => 'api2/{_class}/{_id}/relationships/{_relatedId}.json',
            'tags' => 'api2/{_class}/{_id}/tags',
            'tags_get' => 'api2/{_class}/{_id}/tags/{tagname}.json',
        );
        return $urlFormats[$this->action];
    }

    /**
     * Really rudimentary test: contact
     */
    public function testContacts() {
        $this->action = 'model';
        // Create
        $contact = array(
            'firstName' => 'Walt',
            'lastName' => 'White',
            'email' => 'walter.white@sandia.gov',
            'visibility' => 1
        );
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts'),'admin',$contact);
        $response = json_decode(curl_exec($ch),1);
        $id = $response['id'];
        $this->assertResponseCodeIs(201, $ch);
        $this->assertTrue((bool) ($newContact = Contacts::model()->findBySql(
                'SELECT * FROM x2_contacts '
                . 'WHERE firstName="Walt" '
                . 'AND lastName="White" '
                . 'AND email="walter.white@sandia.gov"')));

        // Update
        $contact['firstName'] = 'Walter';
        $ch = $this->getCurlHandle('PUT',array('{modelAction}'=>"Contacts/$id.json"),'admin',$contact);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $newContact->refresh();
        $this->assertEquals($contact['firstName'],$newContact['firstName']);

        // Update by attributes:
        $contact['firstName'] = 'Walter "Heisenberg"';
        $ch = $this->getCurlHandle('PUT',
                array(
                    '{modelAction}'=>"Contacts/by:email={$contact['email']}.json"
                ),
                'admin',
                $contact);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(200, $ch);
        $newContact->refresh();
        $this->assertEquals($contact['firstName'],$newContact['firstName']);


        // Delete
        $ch = $this->getCurlHandle('DELETE',array('{modelAction}'=>"Contacts/$id.json"),'admin');
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
        $this->assertFalse(Contacts::model()->exists('id='.$id));

        // Validation error (missing visibility):
        $contact = array(
            'firstName' => 'Hank',
            'lastName' => 'Schrader',
        );
        $ch = $this->getCurlHandle('POST', array('{modelAction}' => 'Contacts'), 'admin', $contact);
        $response = json_decode(curl_exec($ch), 1);
        $this->assertResponseCodeIs(422, $ch);

        // Incorrect method of creating new contact (should respond with 400)
        $contact = array(
            'firstName' => 'Hank',
            'lastName' => 'Schrader',
        );
        $ch = $this->getCurlHandle('PUT', array('{modelAction}' => 'Contacts'), 'admin', $contact);
        $response = json_decode(curl_exec($ch), 1);
        $this->assertResponseCodeIs(400, $ch);
    }

    /**
     * Tests the special alternate way of managing actions
     * (through "api2/{_class}/{_id}/Actions")
     */
    public function testActions() {
        $this->action = 'model';
        $action = array(
            'actionDescription' => 'Lunch meeting',
            'type' => 'event',
            'associationType' => 'contacts',
            'associationId' => $this->contacts('testFormula')->id,
            'dueDate' => 1398987130,
            'complete' => 'No',
        );
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts/'.$this->contacts('testFormula')->id.'/Actions'),'admin',$action);
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch,VERBOSE_MODE?$response:'');
        $response = json_decode($response,1);
        $this->assertEquals($action['actionDescription'],$response['actionDescription']);
        $this->assertEquals($action['type'],$response['type']);
        $this->assertEquals($action['complete'],$response['complete']);
        $this->assertEquals($this->contacts('testFormula')->id,$response['associationId']);
        $this->assertEquals('contacts',$response['associationType']);
        // Do it again but with bad ID to test the actions association check kludge
        $ch = $this->getCurlHandle('POST',array('{modelAction}'=>'Contacts/242424242/Actions'),'admin',$action);
        curl_exec($ch);
        $this->assertResponseCodeIs(404, $ch);
        // Delete through a similar URL:
        $ch = $this->getCurlHandle('DELETE',array('{modelAction}'=>'Contacts/'.$this->contacts('testFormula')->id.'/Actions/'.$response['id'].'.json'));
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
    }

    public function testRelationships() {
        // Delete a relationship from the fixture data:
        $this->action = 'relationships_get';
        $ch = $this->getCurlHandle('DELETE',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id,
            '{_relatedId}' => $this->relationships('blackMesaContact')->id
        ),'admin');
        $oldRelationship = $this->relationships('blackMesaContact')->getAttributes(array(
            'id',
            'secondType',
            'secondId',
        ));
        $oldRelationId = $this->relationships('blackMesaContact')->id;
        $response = curl_exec($ch);
        $this->assertEmpty($response);
        $this->assertResponseCodeIs(204, $ch);
        $this->assertFalse((bool)Relationships::model()->findByPk($oldRelationId));

        // Re-create it from fixture data:
        $this->action = 'relationships';
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch);

        // Create it again just to test that validation works (don't set w/same ID)
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(201, $ch);

        // Create it once more but with a nonexistent ID to test that validation works
        $oldRelationship['secondId'] = 2424242;
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $this->contacts('testFormula')->id
        ),'admin',$oldRelationship);
        $response = json_decode(curl_exec($ch),1);
        $this->assertResponseCodeIs(422, $ch);
        
    }

    /**
     * Test saving tags
     */
    public function testTags() {
        $this->action = 'tags';
        // The following contact should start with no tags on it:
        $contact = $this->contacts('testFormula');
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id
        ),'admin',$tagsPut = array('#not-a-talker','#carries-a-crowbar','#enemy-of-vortigaunts'));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(200, $ch);
        $tags = $contact->getTags();
        $this->assertEquals($tagsPut,$tags);
        // Add some more tags
        $ch = $this->getCurlHandle('POST',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id
        ),'admin',$moreTagsPut = array('#enemy-of-combine'));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(200, $ch);
        $contact = Contacts::model()->findByPk($contact->id);
        $tags = $contact->getTags();
        $this->assertEquals($allTags=array_merge($tagsPut,$moreTagsPut),$tags);
        // Delete a tag:
        $this->action = 'tags_get';
        $ch = $this->getCurlHandle('delete',array(
            '{_class}'=>'Contacts',
            '{_id}' => $contact->id,
            '{tagname}' => 'enemy-of-vortigaunts'
        ));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(204, $ch);
        $contact = Contacts::model()->findByPk($contact->id);
        $tags = $contact->getTags();
        $this->assertEquals(array_values(array_diff($allTags,array('#enemy-of-vortigaunts'))),$tags);
    }

}

?>
