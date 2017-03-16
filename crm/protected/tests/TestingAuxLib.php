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
 * Auxilliary Library for unit testing. A catch-all class for miscellaneous utility methods.
 * 
 * @package application.tests
 */
class TestingAuxLib  {

     
    /**
     * Method used by TestingAuxLibTest to test setPublic 
     */
    private function privateMethod ($arg1, $arg2) {
        return array ($arg1, $arg2);
    }

    /**
     * Updates timestamps of session records 
     */
    public static function setUpSessions ($sessions) {
        foreach ($sessions as $session) {
            $model = Session::model ()->findByAttributes ($session);
            $model->lastUpdated = time ();
            $model->save ();
        }
    }

    /**
     * Used to invoke methods which are protected or private.
     * @param string|object $classNameOrInstance
     * @param string $methodName 
     * @return function Takes an array of arguments as a parameter and calls
     *  the specified method with those arguments.
     */
    public static function setPublic ($classNameOrInstance, $methodName) {
        if (is_string ($classNameOrInstance)) {
            $class = new $classNameOrInstance ();
        } else {
            $class = $classNameOrInstance;
        }
        $method = new ReflectionMethod (get_class ($class), $methodName);
        $method->setAccessible (TRUE);
        return function ($arguments=array ()) use ($method, $class) {
            return $method->invokeArgs ($class, $arguments);
        };
    }

    public static function setPrivateProperty ($className, $propertyName, $value) {
        $relectionClass = new ReflectionClass ($className);
        $reflectionProperty = $relectionClass->getProperty ($propertyName);
        $reflectionProperty->setAccessible (true);
        $reflectionProperty->setValue ($propertyName, $value);
    }

    /**
     * Log in with the specified credentials .
     *
     * NOTE: in a non-web environment (i.e. command line, running PHPUnit)
     * this is not guaranteed to work, because Yii::app()->user is designed for
     * web sessions. To authenticate in the established web-or-console-agnostic
     * method, use {@link ApplicationConfigBehavior::setSuModel} (or
     * {@link suLogin}) instead.
     *
     * @return bool true if login was successful, false otherwise
     */
    public static function login ($username, $password) {
        $identity = new UserIdentity($username, $password);
        $identity->authenticate ();
		if($identity->errorCode === UserIdentity::ERROR_NONE) {
            if (Yii::app()->user->login ($identity, 2592000)) {
                if ($username === 'admin') {
                    Yii::app()->params->isAdmin = true;
                } else {
                    Yii::app()->params->isAdmin = false;
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Sets the substitute user model property of the application singleton
     *
     * This establishes a pseudo-session so that non-web-specific components'
     * methods that need userspace data to run properly can be executed from the
     * command line and do not need to depend on web-session-specific components.
     *
     * @param type $username
     */
    public static function suLogin($username) {
        $user = User::model()->findByAlias($username);
        if(!($user instanceof User))
            return false;
        $profile = $user->profile;
        Yii::app()->setSuModel($user);
        Yii::app()->params->profile = $profile;
        return true;
    }

    /**
     * Login with curl and return the PHP session id (which can be used to make curl requests to 
     * pages that require authentication)
     * @return string PHP session id
     */
    public function curlLogin ($username, $password) {
        // login and extract session id from response header
        $data = array (
            'LoginForm[username]' => $username,
            'LoginForm[password]' => $password,
            'LoginForm[rememberMe]' => 0,
        );
        $curlHandle = curl_init (TEST_BASE_URL.'site/login');
        curl_setopt ($curlHandle, CURLOPT_POST, true);
        curl_setopt ($curlHandle, CURLOPT_HEADER, true);
        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, http_build_query ($data));
        ob_start ();
        $result = curl_exec ($curlHandle);
        $matches = array ();
        preg_match_all ('/PHPSESSID=([^;]+);/', $result, $matches);
        //print_r ($matches);
        $sessionId = array_pop (array_pop ($matches)); // get the last match
        ob_clean ();
        return $sessionId;
    }

// not tested yet, might eventually be useful
//    public function curlLogout ($sessionId) {
//        $cookies = "PHPSESSID=$sessionId; path=/;";
//        $curlHandle = curl_init ('localhost/index.php/site/logout');
//        curl_setopt ($curlHandle, CURLOPT_HEADER, true);
//        curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt ($curlHandle, CURLOPT_COOKIE, $cookies);
//        ob_start ();
//        $result = curl_exec ($curlHandle);
//        ob_clean ();
//        //AuxLib::debugLogR ($result);
//        return $sessionId;
//
//    }

}

?>
