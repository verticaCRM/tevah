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
 * Helper web script for testing API hooks (for data pull requests)
 * 
 * The only thing this script really needs to do is save the incoming data in 
 * the testing output folder for examination and testing.
 */
$testsDir = implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    'protected',
    'tests',
));

$users = require(implode(DIRECTORY_SEPARATOR,array(
    $testsDir,
    'fixtures',
    'x2_users.php'
)));

$requestData = array();
$requestData['body'] = json_decode(file_get_contents('php://input'),1);

$hookName = isset($_GET['name'])?$_GET['name']:null;

// Saving to files:
$outPath = implode(DIRECTORY_SEPARATOR,array(
    $testsDir,
    'data',
    'output',
));
file_put_contents($outPath.DIRECTORY_SEPARATOR."hook_$hookName.json", json_encode($requestData));

if(isset($requestData['body']['resource_url'])) {
    // Make a GET request to retrieve the data that X2Engine requested us to retrieve:
    $ch = curl_init($requestData['body']['resource_url']);
    $options = array(
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "{$users['admin']['username']}:{$users['admin']['userKey']}",
        CURLOPT_RETURNTRANSFER => true,
    );
    curl_setopt_array($ch,$options);
    file_put_contents($outPath.DIRECTORY_SEPARATOR."hook_pulled_$hookName.json",curl_exec($ch));
}