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

/**
 * 
 * @package application.tests.api
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class Api2TestBase extends CURLDbTestCase {

    public static function referenceFixtures() {
        return array(
            'user' => 'User',
        );
    }

    /**
     *
     * @param type $method
     * @param type $params
     * @param type $user
     * @param type $postData
     * @param type $options
     * @return type
     */
    public function getCurlResponse($method='GET',$params = array(),$user='admin',
            $postData = array(),$options = array()){
        $ch = $this->getCurlHandle($method,$params,$user,$postData,$options);
        return curl_exec();
    }

    /**
     * Obtains the cURL handle and adds authentication parameters
     *
     * @param type $method The request method type, i.e. GET, POST, PUT, DELETE
     * @param type $params Parameters with which to format the request URI
     * @param type $user Row alias in the users fixture to use for authentication
     * @param type $postData Optional post data array to send
     * @param type $options
     * @return type
     */
    public function getCurlHandle($method='GET',$params = array(),$user='admin',
            $postData = array(),$options = array()){
        // Set the request method
        if($method != 'GET')
            $options[CURLOPT_CUSTOMREQUEST] = $method;

        // Enable authentication
        if(!empty($user))
            foreach($this->authCurlOpts($user) as $opt => $optVal)
                $options[$opt] = $optVal;
        // Additional headers to set
        if(in_array($method, array('PATCH', 'POST', 'PUT'))
                && !isset($options[CURLOPT_HTTPHEADER])){
            // By default: post a JSON
            $postData = json_encode($postData);
            $options[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json; charset=utf-8'
            );
        }
        return parent::getCurlHandle($params, $postData, $options);
    }

    public function authCurlOpts($user='admin') {
        return array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->user($user)->username
                    .':'.$this->user($user)->userKey
        );
    }

}

?>
