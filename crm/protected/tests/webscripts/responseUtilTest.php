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

require_once(implode(DIRECTORY_SEPARATOR,array(__DIR__,'protected','components','util','ResponseUtil.php')));

function throwException(){
    throw new Exception("I'm dyin'. Here's how.");
}

if(!isset($_GET['case'])) {
    die('Test case ("case") is a required parameter.');
}

$c = $_GET['case'];
$case = substr($c,0,strpos($c,'.'));
if($case == '')
    $case = $c;
$subcase = substr($c,strpos($c,'.')+1);
switch ($case) {
    case 'isCli':
        header('Content-type: text/plain');
        echo (integer) !ResponseUtil::isCli();
        break;
    case 'respond':
        ResponseUtil::$errorCode = 400;
        switch($subcase) {
            case 'errTrue':
                ResponseUtil::respond($subcase,true);
                break;
            case 'errFalse':
                ResponseUtil::respond($subcase,false);
                break;
            case 'property':
                $r = new ResponseUtil;
                $r['property'] = 'value';
                ResponseUtil::respond($subcase,false);
                break;
        }
        break;
    case 'respondWithError':
        set_error_handler('ResponseUtil::respondWithError');
        switch($subcase) {
            case 'nonFatalFalse':
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                ResponseUtil::respond('All clear!',false);
                break;
            case 'nonFatalTrue':
                ResponseUtil::$exitNonFatal = true;
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                break;
            case 'longErrorTrace':
                ResponseUtil::$exitNonFatal = true;
                ResponseUtil::$longErrorTrace = true;
                trigger_error('Ad-hoc error',E_USER_NOTICE);
                break;
        }
        break;
    case 'respondFatalErrorMessage':
        register_shutdown_function('ResponseUtil::respondFatalErrorMessage');
        switch($subcase){
            case 'parse':
                $e = 'a';
                // Trigger a parse error:
                eval('return $e"bc";');

                break;
            case 'class':
                $odysseus = new NoMan;
                break;
        }
        break;
    case 'respondWithException':
        set_exception_handler('ResponseUtil::respondWithException');
        switch($subcase) {
            case 'normal';
                throw new Exception("I'm dyin' here.");
                break;
            case 'long':
                ResponseUtil::$longErrorTrace = true;
                throwException();
                break;
        }
        break;
    case 'catchDouble':
        set_exception_handler('ResponseUtil::respondWithException');
        $response = new ResponseUtil();
        $response = new ResponseUtil();
        break;
    case 'sendHttp':
        $r = new ResponseUtil;
        if(ctype_digit($subcase) || is_int($subcase)){
            $r->sendHttp($subcase);
        }
        $r['message'] = 'The response';
        $r['error'] = false;
        switch($subcase) {
            case 'badCode':
                $r->sendHttp(666);
                break;
            case 'extraHeader':
                $r->httpHeader['Content-MD5'] = base64_encode(md5('not the content'));
                $r->sendHttp();
                break;
            case 'raw':
                $r->body = 'The message in plain text.';
                $r->httpHeader['Content-Type'] = 'text/plain';
                $r->sendHttp();
                break;
        }
        break;
    case 'setProperties':
        $r = new ResponseUtil();
        $r->setProperties(array('foo'=>'bar','message'=>'ni'));
        $r->sendHttp();
        break;
    default:
        die('Unknown test case.');
}

?>
