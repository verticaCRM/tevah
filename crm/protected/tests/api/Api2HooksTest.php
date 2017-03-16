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

Yii::import('application.tests.api.Api2TestBase');

/**
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Api2HooksTest extends Api2TestBase {

    public static $scriptPath;

    public static function setUpBeforeClass() {
        copy(self::webscriptsBasePath().DIRECTORY_SEPARATOR.'api2HooksTest.php',
             self::$scriptPath = implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','api2HooksTest.php')));
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(){
        parent::tearDownAfterClass();
        if(file_exists(self::$scriptPath)) {
            unlink(self::$scriptPath);
        }
    }
    
    public function urlFormat() {
        return 'api2/Contacts/hooks{suffix}';
    }


    /**
     * Test subscribing and then receiving data:
     */
    public function testSubscription() {
        // 1: Request to subscribe:
        $hookName = 'ContactsCreate';
        $hook = array(
            'event' => 'RecordCreateTrigger',
            'target_url' => TEST_WEBROOT_URL.'/api2HooksTest.php?name='.$hookName
        );
        $ch = $this->getCurlHandle('POST',array('{suffix}'=>''),'admin',$hook,array(CURLOPT_HEADER=>1));
        $response = curl_exec($ch);
        $this->assertResponseCodeIs(201, $ch,VERBOSE_MODE?$response:'');
        $trigger = ApiHook::model()->findByAttributes($hook);

        // 2. Create a contact
        $contact = array(
            'firstName' => 'Walter',
            'lastName' => 'White',
            'email' => 'walter.white@sandia.gov',
            'visibility' => 1
        );
        $ch = curl_init(TEST_BASE_URL.'api2/Contacts');
        $options = array(
            CURLOPT_POSTFIELDS => json_encode($contact),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true
        );
        foreach($this->authCurlOpts() as $opt=>$optVal)
            $options[$opt] = $optVal;
        curl_setopt_array($ch,$options);
        $response = curl_exec($ch);
        $c = Contacts::model()->findByAttributes($contact);
        $this->assertResponseCodeIs(201, $ch);
        $this->assertNotEmpty($c);

        // 3. Test that the receiving end got the payload
        $outputDir = implode(DIRECTORY_SEPARATOR, array(
            Yii::app()->basePath,
            'tests',
            'data',
            'output'
        ));
        $this->assertFileExists($outputDir.DIRECTORY_SEPARATOR."hook_$hookName.json");
        $contactPulled = json_decode(file_get_contents($outputDir.
                DIRECTORY_SEPARATOR."hook_pulled_$hookName.json"),1);
        foreach($contact as $field=>$value) {
            $this->assertEquals($value,$contactPulled[$field]);
        }
    }

}

?>
