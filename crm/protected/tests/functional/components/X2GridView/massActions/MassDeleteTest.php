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

class MassDeleteTest extends X2DbTestCase {

    public $fixtures = array (
        'contacts' => array ('Contacts', '.MassDeleteTest'),
        'authItems' => array (':x2_auth_item', '.MassDeleteTest'),
        'authItemChildren' => array (':x2_auth_item_child', '.MassDeleteTest'),
        'roles' => array ('Roles', '.MassDeleteTest'),
        'roleToUser' => array (':x2_role_to_user', '.MassDeleteTest'),
        'authAssignment' => array (':x2_auth_assignment', '.MassDeleteTest'),
    );

    /**
     * Ensure that a user without delete access cannot mass delete records
     */
    public function testSuperExecutePermissions () {
        $contacts = Contacts::model ()->updateByPk (
            array (1, 2, 3, 4), array ('assignedTo' => 'testUser'));
// this could be useful if we ever enable mass deletion for users with private delete access
//        $expectedFailures = intval (Yii::app ()->db->createCommand ("
//            SELECT count(*)
//            FROM x2_contacts
//            WHERE id < 20 and assignedTo!='testuser'
//            ORDER by firstName desc
//        ")->queryScalar ());
//        $expectedSuccesses = intval (Yii::app ()->db->createCommand ("
//            SELECT count(*)
//            FROM x2_contacts
//            WHERE id < 20 and assignedTo='testuser'
//            ORDER by firstName desc
//        ")->queryScalar ());
//        $this->assertNotEquals ($expectedFailures, $expectedSuccesses);
//        $this->assertNotEquals (0, $expectedSuccesses);
//        $this->assertNotEquals (0, $expectedFailures);

        $sessionId = TestingAuxLib::curlLogin ('testuser', 'password');
        $cookies = "PHPSESSID=$sessionId;";

        // perform mass deletion
        $data = array (
            'modelType' => 'Contacts',
            'massAction' => 'delete',
            'gvSelection' => range (1, 19),
        );
        $curlHandle = curl_init (TEST_BASE_URL.'contacts/x2GridViewMassAction');
        curl_setopt ($curlHandle, CURLOPT_POST, true);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt ($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, http_build_query ($data));
        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
        ob_start ();
        $response = CJSON::decode (curl_exec ($curlHandle));
        ob_clean ();
        //println ('response = ');
        //print_r ($response);
        $this->assertEquals ('0 records deleted', $response['success'][0]);
        $this->assertEquals ('You were not authorized to delete 19 records', $response['error'][0]);
    }

}

?>
