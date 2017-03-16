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

require_once('WebTestConfig.php');

/**
 * Non-database web request test case.
 *
 * This is hideously duplicated code from {@link CURLDbTestCase} because PHP
 * does not natively support mixins, and since we're working with child classes
 * of the PHPUnit framework classes, Yii behaviors are not an option. A distinct
 * class for doing non-database URL tests is necessary for performance's sake.
 *
 * Note, the one difference is that this uses TEST_WEBROOT_URL instead of
 * TEST_BASE_URL, to permit testing outside of X2Engine, i.e. ad-hoc scripts.
 *
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class CURLTestCase extends CTestCase {

    /**
     * Allows responses w/error codes, so that we can examine the contents of the response even if the request failed
     * @return type
     */
    public function getHttp200Aliases(){
        return array(
            201,
            204,
            304,
            400,
            401,
            402,
            403,
            404,
            405,
            410,
            415,
            422,
            429,
            500,
            501,
            503,
        );
    }

    public $outsideX2 = true;

    public static function webscriptsBasePath() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'tests','webscripts'));
    }

    /**
     * Run a request and check that a header field is present in the response
     * @param type $params
     * @param type $field
     * @param type $value
     * @param type $postData
     * @param type $options
     */
    public function assertHasHeaders($params,$fields,$postData=array()) {
        $ch = $this->getCurlHandle($params,$postData,array(
            CURLOPT_HEADER=>1,
        ));
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        foreach($fields as $field => $value){
            $this->assertRegExp(
                    sprintf('/^%s: %s/m', preg_quote($field,'/'), preg_quote($value,'/')),
                    $header,
                    "Header $field not found in response, or was not equal to \"$value\". Response body = $response");
        }
    }

    public function assertResponseCodeIs($code, $ch, $message = ''){
        $this->assertEquals($code, curl_getinfo($ch, CURLINFO_HTTP_CODE), $message);
    }

    public function getCurlResponse($params, $postData = array()){
        return curl_exec($this->getCurlHandle($params, $postData));
    }

    public function getCurlHandle($params, $postData = array(),$options = array()){
        $post = count($postData) > 0;
		$ch = curl_init(($this->outsideX2 ? TEST_WEBROOT_URL : TEST_BASE_URL). $this->url($params));
        $allOpts = array(
            CURLOPT_POST => $post,
            CURLOPT_RETURNTRANSFER => true, // Return the response data from curl_exec()
            CURLOPT_HTTP200ALIASES => $this->getHttp200Aliases(),
        );
        foreach($options as $const => $opt) {
            $allOpts[$const] = $opt;
        }
        curl_setopt_array($ch, $allOpts);
        if($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        return $ch;
    }

    public function url($params = array()){
        return strtr($this->urlFormat(), $params);
    }

    public abstract function urlFormat();
}

?>
