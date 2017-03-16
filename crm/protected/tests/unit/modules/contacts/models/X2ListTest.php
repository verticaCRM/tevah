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

/**
 * 
 * @package application.tests.unit.modules.contacts.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2ListTest extends X2DbTestCase {

    public $fixtures = array(
        'contacts' => array('Contacts', '.list'),
        'lists' => 'X2List',
        'listItems' => 'X2ListItem',
        'listCriteria' => 'X2ListCriterion',
    );

    public function testStaticDuplicate_dynamic() {
        // Static clone of a dynamic list:
        $that = $this;
        $expectedContactEmailAddresses = array_map(function($i)use($that){
            return $that->contacts("listTest$i")->email;
        },array(1,2,3));
        
        $dyn = $this->lists('staticDuplicateDynamic');
        $modelName = $dyn->modelName;
        $dynClone = $dyn->staticDuplicate();
        $this->assertNotEmpty($dynClone);
        $this->assertTrue($dynClone instanceof X2List);
        $this->assertEquals($dyn->count,$dynClone->count);
        $this->assertEquals($dyn->modelName,$dynClone->modelName);
        // A cursory check that the criteria generation works:
        $expectedContacts = $dyn->queryCommand()->queryAll(true);
        $this->assertEquals($expectedContactEmailAddresses, array_map(function($c){
            return $c['email'];
        }, $expectedContacts));
        $this->assertEquals(3,count($expectedContacts));
        // Test that the static clone has all the correct list items.
        //
        // The reference to contacts happens in a roundabout way because the
        // "contact" relation in X2ListItem is expected to some day be
        // removed, when lists can be used for more than just contacts.
        $this->assertEquals($expectedContactEmailAddresses, array_map(function($i)use($modelName){
            return X2Model::model($modelName)->findByPk($i->contactId)->email;
        }, $dynClone->listItems));

        // Static clone of a static list:
        $expectedContactEmailAddresses = array_map(function($i)use($that){
            return $that->contacts("listTest$i")->email;
        },array(1,2,3));

        $static = $this->lists('staticDuplicateStatic');
        $modelName = $static->modelName;
        $staticClone = $static->staticDuplicate();
        $modelName = $static->modelName;
        $this->assertNotEmpty($staticClone);
        $this->assertEquals(3,count($staticClone->listItems));
        $this->assertTrue($staticClone instanceof X2List);
        $this->assertEquals($static->count,$staticClone->count);
        $this->assertEquals($static->modelName,$staticClone->modelName);
        $emailsInList = function($i)use($modelName){
                    return X2Model::model($modelName)->findByPk($i->contactId)->email;
                };
        $this->assertEquals(array_map($emailsInList, $static->listItems)
                , array_map($emailsInList, $staticClone->listItems));

        $expectedEmailAddresses = array_map(function($i)use($that){
            return $that->listItems("subscriber$i")->emailAddress;
        },array(4,5,6,7));

        // Static clone of a newsletter list:
        $static = $this->lists('staticDuplicateNewsletter');

        $modelName = $static->modelName;
        $staticClone = $static->staticDuplicate();
        $staticClone->refresh ();
        $modelName = $static->modelName;
        $this->assertNotEmpty($staticClone);
        $this->assertEquals(4,count($staticClone->listItems));
        $this->assertTrue($staticClone instanceof X2List);
        $this->assertEquals($static->count,$staticClone->count);
        $this->assertEquals($static->modelName,$staticClone->modelName);
        $emailsInList = function($i)use($modelName){
                    return $i->emailAddress;
                };
        $this->assertEquals(array_map($emailsInList, $static->listItems)
                , array_map($emailsInList, $staticClone->listItems));
    }
    

}

?>
