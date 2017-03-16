<?php
/***********************************************************************************
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
 **********************************************************************************/

/**
 * Standalone class with miscellaneous utility functions
 */
class AuxLib {

    /**
     * @param int $errCode php file upload error code 
     */
    public static function getFileUploadErrorMessage ($errCode) {
        switch ($errCode) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $errMsg = Yii::t('app', 'File exceeds the maximum upload size.');
                break;
            case UPLOAD_ERR_PARTIAL:
                $errMsg = Yii::t('app', 'File upload was not completed.');
                break;
            case UPLOAD_ERR_NO_FILE:
                $errMsg = Yii::t('app', 'Zero-length file uploaded.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                break;
            case UPLOAD_ERR_CANT_WRITE:
                break;
            case UPLOAD_ERR_EXTENSION:
                break;
            default: 
                $errMsg = Yii::t('app', 'Failed to upload file.');
        }
        return $errMsg;
    }

    /**
     * @return bool True if the file upload failed with errors, false otherwise
     */
    public static function checkFileUploadError ($name) {
        if (!isset ($_FILES[$name])) return false;
        if(empty($_FILES[$name]['tmp_name'])) 
            return true;
        return false;
    }

    /**
     * Registers a script which instantiates a dictionary of translations.
     * @param string $scriptName The name of the script which will be registered
     *   and which will be a property of the global JS object x2.
     * @param array $messages An associateive array (<message label> => <untranslated message>)
     * @param string $translationFile The first parameter to Yii::t
     * @param string $namespace The name of the JS object which will contain the translations
     *  dictiory
     */
    public static function registerTranslationsScript (
        $namespace, $messages, $translationFile='app', $scriptName='passMsgsToClientScript') {

        $passVarsToClientScript = "
            if (!x2.".$namespace.") x2.".$namespace." = {};
            x2.".$namespace.".translations = {};
        ";
        foreach ($messages as $key=>$val) {
            $passVarsToClientScript .= "x2.".$namespace.".translations['".
                $key. "'] = '" . addslashes (Yii::t($translationFile, $val)) . "';\n";
        }
        Yii::app()->clientScript->registerScript(
            $scriptName, $passVarsToClientScript,
            CClientScript::POS_HEAD);
    }

    /**
     * @param string $namespace The name of the JS object which will contain the translations
     *  dictionary. For nested namespaces, each namespace should be separated by a '.' character.
     * @param array $vars An associative array (<var name> => <var value>)
     * @param string $scriptName The name of the script which will be registered
     *   and which will be a property of the global JS object x2.
     */
    public static function registerPassVarsToClientScriptScript (
        $namespace, $vars, $scriptName='passVarsToClientScript') {

        $namespaces = explode ('.', $namespace);
        $rootNamespace = array_shift ($namespaces);

        // declare nested namespaces one at a time if they don't already exist, starting at the root
        $passVarsToClientScript = "
            (function () {
                if (typeof ".$rootNamespace." === 'undefined') ".$rootNamespace." = {};
                var namespaces = ".CJSON::encode ($namespaces).";
                var prevNameSpace = ".$rootNamespace.";

                for (var i in namespaces) {
                    if (typeof prevNameSpace[namespaces[i]] === 'undefined') {
                        prevNameSpace[namespaces[i]] = {};
                    }
                    prevNameSpace = prevNameSpace[namespaces[i]];
                }
            }) ();
        ";
        foreach ($vars as $key=>$val) {
            $passVarsToClientScript .= $namespace.".".$key." = ".$val.";";
        }
        Yii::app()->clientScript->registerScript(
            $scriptName, $passVarsToClientScript,
            CClientScript::POS_HEAD);
    }

    /**
     * Used by actions to return JSON encoded array containing error status and error message.
     * Used for testing purposes only.
     */
    public static function printTestError ($message) {
        if (YII_DEBUG) echo CJSON::encode (array ('error' => array (Yii::t('app', $message))));
    }

    /**
     * Used by actions to return JSON encoded array containing error status and error message.
     */
    public static function printError ($message) {
        echo CJSON::encode (array (false, $message));
    }

    /**
     * Used by actions to return JSON encoded array containing success status and success message.
     */
    public static function printSuccess ($message) {
        echo CJSON::encode (array (true, $message));
    }

    /**
     * Calls printError or printSuccess depending on the value of $success.
     *
     * @param bool $success 
     * @param string $successMessage
     * @param string $errorMessage
     * @return array (<bool>, <string>)
     */
    public static function ajaxReturn ($success, $successMessage, $errorMessage) {
        if ($success) {
            self::printSuccess ($successMessage);
        } else { // !$success
            self::printError ($errorMessage);
        }
    }

    /**
     * Used to log debug messages
     */
    public static function debugLog ($message) {
        if (!YII_DEBUG) return;
        Yii::log ($message, 'error', 'application.debug');
    }

    public static function debugLogR ($arr) {
        if (!YII_DEBUG) return;
        $logMessage = print_r ($arr, true);
        Yii::log ($logMessage, 'error', 'application.debug');
    }

    public static function debugLogExport ($arr) {
        if (!YII_DEBUG) return;
        $logMessage = var_export ($arr, true);
        Yii::log ($logMessage, 'error', 'application.debug');
    }

    public static function isIE8 () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie 8/i', $userAgentStr);
    }

    public static function isIE () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        return preg_match('/msie/i', $userAgentStr);
    }

    /**
     * @return mixed The IE version if available, otherwise infinity 
     */
    public static function getIEVer () {
        $userAgentStr = strtolower(Yii::app()->request->userAgent);
        preg_match('/msie ([0-9]+)/', $userAgentStr, $matches);
        if (sizeof ($matches) === 2) {
            $ver = (int) $matches[1];
        } else {
            $ver = INF;
        }
        return $ver;
    }

    /**
     * @return bool returns true if user is using mobile app, false otherwise 
     */
    public static function isMobile () {
        return (Yii::app()->request->cookies->contains('x2mobilebrowser') && 
                Yii::app()->request->cookies['x2mobilebrowser']->value);
    }

    public static function setCookie ($key, $val, $time) {
        if (YII_DEBUG) { // workaround which allows chrome to set cookies for localhost
            $serverName = Yii::app()->request->getServerName() === 'localhost' ? '' :
                Yii::app()->request->getServerName();
        } else {
            $serverName = Yii::app()->request->getServerName();
        }
        setcookie($key,$val,time()+$time,dirname(Yii::app()->request->getScriptUrl()), $serverName);
    }

    public static function clearCookie ($key){
        if(YII_DEBUG){ // workaround which allows chrome to set cookies for localhost
            $serverName = Yii::app()->request->getServerName() === 'localhost' ? '' :
                    Yii::app()->request->getServerName();
        }else{
            $serverName = Yii::app()->request->getServerName();
        }
        unset($_COOKIE[$key]);
        setcookie(
            $key, '', time() - 3600, dirname(Yii::app()->request->getScriptUrl()), $serverName);
    }

    /**
     * Generates parameter binding placeholders for each element in array
     * @param array $arr parameter values to be bound in a SQL query
     * @param string $prefix prefix to use for paramater names
     * @return array parameter values indexed by parameter name
     */
    public static function bindArray ($arr, $prefix='X2') {
        $placeholders = array ();
        $arrLen = sizeof ($arr);
        for ($i = 0; $i < $arrLen; ++$i) {
            $placeholders[] = ':' . $prefix . $i;
        }
        if ($arrLen === 0) {
            return array ();
        } 
        return array_combine ($placeholders, $arr);
    }

    public static function arrToStrList ($arr) {
        return '('.implode (',', $arr).')';
    }

    public static function coerceToArray (&$arr) {
        if (!is_array ($arr)) {
            $arr = array ($arr);
        } 
    }

    /**
     * Prints stack trace 
     * @param int $limit If set, only the top $limit items on the call stack will get printed. 
     *  debug_backtrace does have an optional limit argument, but it wasn't introduced until php
     *  5.4.0.
     */
    public static function trace ($limit=null) {
        if ($limit !== null) {
            /**/AuxLib::debugLogR (
                array_slice (debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS), 0, $limit));
        } else {
            /**/AuxLib::debugLogR (debug_backtrace (DEBUG_BACKTRACE_IGNORE_ARGS));
        }
    }

    /**
     * Reformats and translates dropdown arrays to preserve sorting in {@link CJSON::encode()}
     * @param array an associative array of dropdown options ($value => $label)
     * @return array a 2-D array of values and labels
     */
    public static function dropdownForJson($options) {
        $dropdownData = array();
        foreach($options as $value => &$label)
            $dropdownData[] = array($value,$label);
        return $dropdownData;
    }

    public static function println ($message) {
        /**/print ($message."\n");
    }

    public static function issetIsArray ($param) {
        return (isset ($param) && is_array ($param));
    }

    public static function captureOutput ($fn) {
        ob_start();
        ob_implicit_flush(false);
        $fn ();
        return ob_get_clean();
    }

}
