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

// uncomment the following to define a path alias
// Yii::setPathOfAlias('custom','custom');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
if (YII_DEBUG && YII_UNIT_TESTING) {
    include "X2Config-test.php";
} else {
    include "X2Config.php";
}

$defaultLogRoutes = array(
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.api',
        'logFile' => 'api.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'exception.*,php',
        'logFile' => 'errors.log'
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.update',
        'logFile' => 'updater.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),

);

$debugLogRoutes = array(
    array(
        'class' => 'CWebLogRoute',
        'categories' => 'translations',
        'levels' => 'missing',
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.debug',
        'logFile' => 'debug.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
);

if (YII_DEBUG_TOOLBAR) {
    $debugLogRoutes[] = array (
        'class' => 'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
        'ipFilters' => array('127.0.0.1'),
    );
}

$noSession = php_sapi_name()=='cli';
if (!$noSession || YII_UNIT_TESTING) {
    $userConfig = array(
        'class' => 'X2WebUser',
        // enable cookie-based authentication
        'allowAutoLogin' => true,
    );
} else {
    $userConfig = array(
        'class' => 'X2NonWebUser',
    );
}

$config = array(
    'basePath' => dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name' => $appName,
    'theme' => 'x2engine',
    'sourceLanguage' => 'en',
    'language' => $language,
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.components.ApplicationConfigBehavior',
        'application.components.X2UrlRule',
        'application.components.ThemeGenerator.ThemeGenerator',
    // 'application.controllers.x2base',
    // 'application.models.*',
    // 'application.components.*',
    // 'application.components.ERememberFiltersBehavior',
    // 'application.components.EButtonColumnWithClearFilters',
    ),
    'modules' => array(
//		 'gii'=>array('class'=>'system.gii.GiiModule',
//            'password'=>'admin',
//            // If removed, Gii defaults to localhost only. Edit carefully to taste.
//            'ipFilters'=>false,
//        ),
        'mobile',
    ),
    'behaviors' => array('ApplicationConfigBehavior'),
    // application components
    'components' => array(
        'user' => $userConfig,
        'file' => array(
            'class' => 'application.extensions.CFile',
        ),
        // uncomment the following to enable URLs in path-format

        'urlManager' => array(
            'urlFormat' => 'path',
            'urlRuleClass' => 'X2UrlRule',
            'showScriptName' => !isset($_SERVER['HTTP_MOD_REWRITE']),
            //'caseSensitive'=>false,
            'rules' => array(
                'api/tags/<model:[A-Z]\w+>/<id:\d+>/<tag:\w+>' => 'api/tags/model/<model>/id/<id>/tag/<tag>',
                'api/tags/<model:[A-Z]\w+>/<id:\d+>' => 'api/tags/model/<model>/id/<id>',
                'x2touch' => 'mobile/site/home',
                '<module:(mobile)>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:(mobile)>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:(mobile)>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<id:\d+>' => '<controller>/view',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:(site|admin|profile|search|notifications|studio|gallery|relationships)>/<action:\w+>/id/<id:\d+>' => '<controller>/<action>',
                '<controller:(site|admin|profile|api|search|notifications|studio|gallery|relationships)>/<action:\w+>' => '<controller>/<action>',

                // REST-ful 2nd-gen API URLs
                //
                // Note, all "reserved" GET parameters begin with an underscore.
                // This is to avoid name conflict when querying records by
                // attributes, because column names might interfere with 
                // parameters (i.e. "class" might be a column name, whereas it
                // would need to be used to specify the active record class).
                //
                // The URL formatting rules are listed in ascending generality and
                // decreasing specificity, so that when using CController.createUrl
                // to create URLs, the "prettiest" format will be chosen first
                // (because it will match before one of the more general URL
                // formats, and thus be chosen)
                //
                // Working with actions associated with a model:
                'api2/<associationType:[A-Z]\w+>/<associationId:\d+>/<_class:Actions>/<_id:\d+>.json' => 'api2/model',
                'api2/<associationType:[A-Z]\w+>/<associationId:\d+>/<_class:Actions>' => 'api2/model',
                // Relationships manipulation:
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/relationships/<_relatedId:\d+>.json' => 'api2/relationships',
                // Tags manipulation:
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/tags/<_tagName:.+>.json' => 'api2/tags',
                // Special fields URL format:
                'api2/<_class:[A-Z]\w+>/fields/<_fieldName:\w+>.json'=>'api2/fields',
                // REST hooks:
                'api2/<_class:[A-Z]\w+>/hooks/:<_id:\d+>' => 'api2/hooks',
                'api2/<_class:[A-Z]\w+>/hooks' => 'api2/hooks',
                'api2/hooks/:<_id:\d+>' => 'api2/hooks',
                // Directly access an X2Model instance
                // ...By attributes
                // Example: api2/Contacts/by:firstName=John;lastName=Doe.json
                'api2/<_class:[A-Z]\w+>/by:<_findBy:.+>.json'=>'api2/model',
                // ...By ID
                // Example: api2/Contacts/1121.json = Contact #1121
                'api2/<_class:[A-Z]\w+>/<_id:\d+>.json'=>'api2/model',
               // Run the "model" action, with class parameter (required); the
                // base URI for the "model" function
                'api2/<_class:[A-Z]\w+>'=>'api2/model',
                // Run an action "on" a class with a record ID for that class
                // Example: api2/Contacts/1121/relationships = query
                // relationships for contact #1121
                'api2/<_class:[A-Z]\w+>/<_id:\d+>/<action:[a-z]\w+>'=>'api2/<action>',
                // Run an action "on" a class (run action with class parameter)
                // but without any ID specified, i.e. for metadata
                // Example: api2/Contacts/fields = query fields for Contacts model
                'api2/<_class:[A-Z]\w+>/<action:[a-z]\w+>.json'=>'api2/<action>',
                'api2/<_class:[A-Z]\w+>/<action:[a-z]\w+>'=>'api2/<action>',
                // Tag searches:
                'api2/tags/<_tags:[^\/]+>/<_class:[A-Z]\w+>' => 'api2/model',
                // Run a generic action with an ID:
                'api2/<action:[a-z]\w+>/<_id:\d+>.json' => 'api2/<action>',
                // Run a generic action with no additional parameters
                'api2/<action:[a-z]\w+>.json' => 'api2/<action>',
                // Everything else:
                'api2/<action:[a-z]\w+>' => 'api2/<action>',
                // End REST API URL rules

                '<module:calendar>/<action:ical>/<user:\w+>:<key:[^\/]+>.ics' => '<module>/<module>/<action>',

                'weblist/<action:\w+>' => 'marketing/weblist/<action>',
                '<module:\w+>' => '<module>/<module>/index',
                '<module:\w+>/<id:\d+>' => '<module>/<module>/view',
                '<module:\w+>/id/<id:\d+>' => '<module>/<module>/view',
                '<module:\w+>/<action:\w+>/id/<id:\d+>' => '<module>/<module>/<action>',
                '<module:\w+>/<action:\w+>' => '<module>/<module>/<action>',
                '<module:\w+>/<action:\w+>/<id:\d+>' => '<module>/<module>/<action>',
                '<module:\w+>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
            ),
        ),
        'zip' => array(
            'class' => 'application.extensions.EZip',
        ),
        'session' => array(
            'timeout' => 3600,
        ),
        // 'db'=>array(
        // 'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
        // ),
        'db' => array(
            'connectionString' => "mysql:host=$host;dbname=$dbname",
            'emulatePrepare' => true,
            'username' => $user,
            'password' => $pass,
            'charset' => 'utf8',
            'enableProfiling'=>true,
            'enableParamLogging' => true,
            'schemaCachingDuration' => 84600
        ),
        'authManager' => array(
            'class' => 'X2AuthManager',
            'connectionID' => 'db',
            'defaultRoles' => array('guest', 'authenticated', 'admin'),
            'itemTable' => 'x2_auth_item',
            'itemChildTable' => 'x2_auth_item_child',
            'assignmentTable' => 'x2_auth_assignment',
        ),
        // 'clientScript'=>array(
        // 'class' => 'X2ClientScript',
        // ),
        'clientScript'=>array(
            'class' => 'X2ClientScript',
            'mergeJs' => false,
            'mergeCss' => false,
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => '/site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => (YII_DEBUG && YII_LOGGING 
                ? array_merge($defaultLogRoutes, $debugLogRoutes)
                : (YII_LOGGING ? $defaultLogRoutes : array()))
        ),
        'messages' => array(
            'class' => 'X2MessageSource',
//			 'forceTranslation'=>true,
//             'logBlankMessages'=>false,
//			 'onMissingTranslation'=>create_function('$event', 'Yii::log("[".$event->category."] ".$event->message,"missing","translations");'),
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache',
        ),
        // cache which doesn't get cleared when admin index is visited
        'cache2' => array(
            'class' => 'X2FileCache',
            'cachePath' => 'application.runtime.cache2',
        ),
        'authCache' => array(
            'class' => 'application.components.X2AuthCache',
            'connectionID' => 'db',
            'tableName' => 'x2_auth_cache',
        // 'autoCreateCacheTable'=>false,
        ),
        'sass' => array(
            'class' => 'SassHandler',
            'enableCompass' => true
        )
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // this is used in contact page
        'adminEmail' => $email,
        'adminModel' => null,
        'profile' => null,
        'adminProfile' => null,
        'roles' => array(),
        'groups' => array(),
        'userCache' => array(),
        'isAdmin' => false,
        'sessionStatus' => 0,
        'logo' => "uploads/logos/yourlogohere.png",
        'webRoot' => __DIR__.DIRECTORY_SEPARATOR.'..',
        'trueWebRoot' => substr(__DIR__, 0, -17),
        'registeredWidgets' => array(
            'OnlineUsers' => 'Active Users',
            'TimeZone' => 'Clock',
            'ChatBox' => 'Activity Feed',
            'TagCloud' => 'Tag Cloud',
            'ActionMenu' => 'My Actions',
            'MessageBox' => 'Message Board',
            'QuickContact' => 'Quick Contact',
            'SmallCalendar' => 'Calendar',
            //'TwitterFeed'=>'Twitter Feed',
            'NoteBox' => 'Note Pad',
            'MediaBox' => 'Files',
            'DocViewer' => 'Doc Viewer',
            'TopSites' => 'Top Sites',
            'HelpfulTips' => 'Helpful Tips'
        ),
        'currency' => '',
        'version' => $version,
        'edition' => '',
        'buildDate' => $buildDate,
        'faviconURL' => $faviconURL,
        'noSession' => $noSession,
        'automatedTesting' => false,
        'supportedCurrencies' => array('USD', 'EUR', 'GBP', 'CAD', 'JPY', 'CNY', 'CHF', 'INR', 'BRL', 'VND'),
        'supportedCurrencySymbols' => array(),
    ),
);

if (YII_DEBUG && YII_UNIT_TESTING)
    $config['components']['urlManager']['rules'] = array_merge (
        array ('profileTest/<action:\w+>' => 'profileTest/<action>'),
        $config['components']['urlManager']['rules']);

if(file_exists('protected/config/proConfig.php')){
    $proConfig = include('protected/config/proConfig.php');
    foreach($proConfig as $attr => $proConfigData){
        if(isset($config[$attr])){
            $config[$attr] = array_merge($config[$attr], $proConfigData);
        }else{
            $config[$attr] = $proConfigData;
        }
    }
}
return $config;
