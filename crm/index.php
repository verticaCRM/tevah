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
// change the following paths if necessary
$constants = __DIR__.DIRECTORY_SEPARATOR.'constants.php';
$yii = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'framework', 'yii.php'));
require_once($constants);
require_once($yii);
Yii::$enableIncludePath = false;
Yii::registerAutoloader(array('Yii', 'x2_autoload'));
if(!empty($_SERVER['REMOTE_ADDR'])){
    $matches = array();
    $indexReq = preg_match('/(.+)index.php/', $_SERVER["REQUEST_URI"], $matches);

    $filename = 'install.php';

    if(file_exists($filename)){
        header('Location: '.(!$indexReq ? $_SERVER['REQUEST_URI'] : $matches[1]).$filename);
        exit();
    }
    $config = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'protected', 'config', 'web.php'));
    Yii::createWebApplication($config)->run();
}

function printR($obj, $die = false){
    echo "<pre>".print_r($obj, true)."</pre>";
    if($die){
        die();
    }
}
