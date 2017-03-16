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

/*
Iframe-less version of the webtracker 
The legacy version of webtracker is in webListener.php.

This script generates a key as soon as someone visits the page. The key as well as the referring
url get sent to the server where, if the visitor is a contact, they'll be tracked.
*/

?>

/* @edition:pro */

var x2WebTracker = {};



<?php
/**
 * Send key and url to server by creating a new script node which points to the desired action.
 * If x2GETKey is set, send that to the server as well.
 *
 * @param url string The url of the server on which the CRM is running
 * @param string x2GETKey The key (tracking link or campaign click) sent in the GET parameters.
 */
?>

/**
 * Constructs the weblistener url 
 * @return string
 */
x2WebTracker.getSendKeyUrl = function () {
    return '<?php 
        $protocol = !empty ($_SERVER['HTTPS']) ? 'https' : 'http';
		$baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];

		$path = preg_replace ("/\/webTracker\.php/", '', $_SERVER['REQUEST_URI']);
		if ($path) $baseUrl .= $path;
		echo $baseUrl;
		?>/<?php echo (defined ('FUNCTIONAL_TEST') && constant ('FUNCTIONAL_TEST') ? 
            'index-test.php' : 'index.php'); 
        ?>/api/webListener';

};

/**
 * Structures the parameters which will get sent to the weblistener action, either as a query
 * string or as a dictionary
 * @param object args
 * @param string method ('GET'|'POST')
 * @return object|string 
 */
x2WebTracker.getSendKeyParams = function (args, method) {
    var x2GETKey = args['x2KeyGetParam']; 
    var url = args['url'];
    /* x2plastart */ 
    var attributes;
    var fingerprint = args['fingerprint']
    if (typeof fingerprint !== 'undefined') {
        // Ensure fingerprint data has valid keys: these will not be set
        // at this point if the client has DNT set
        if (typeof fingerprint['attributes'] !== 'undefined')
            attributes = fingerprint['attributes'];
        if (typeof fingerprint['fingerprint'] !== 'undefined')
            fingerprint = fingerprint['fingerprint'];
    }
    /* x2plaend */      

    var params = {
        url: (method === 'GET') ? encodeURIComponent (url) : url/* x2plastart */,
        fingerprint: fingerprint,
        /*attributes: (method === 'GET') ? encodeURIComponent (fingerprint['attributes']) :
            fingerprint['attributes'],*/
        /* x2plaend */ 
    };
    if (x2GETKey !== null)
        params['get_key'] = (method === 'GET') ? encodeURIComponent (x2GETKey) : x2GETKey

    /* x2plastart */         
    for (var fingerprintAttr in attributes) {
        params[fingerprintAttr] = (method === 'GET' ? 
            encodeURIComponent (attributes[fingerprintAttr]) : 
            attributes[fingerprintAttr]);
    }
    /* x2plaend */ 

    if (method === 'GET') {
        var queryString = '';
        var i = 0;
        for (var paramName in params) {
            if (i++ === 0) {
                queryString += '?';
            } else {
                queryString += '&';
            }
            queryString += paramName + '=' + params[paramName];
        }
        return queryString;
    } else {
        return params;
    }
};

/**
 * Sends the tracking information to the server using a script tag
 */
x2WebTracker.sendKeyNoCORS = function (args) {

    var sendKeyScript = document.createElement ('script');
    sendKeyScript.setAttribute (
        'src', x2WebTracker.getSendKeyUrl () + x2WebTracker.getSendKeyParams (args, 'GET'),
         true);
    document.getElementsByTagName("head")[0].appendChild(sendKeyScript);
};

/**
 * Sends tracking information to the server using an AJAX request if CORS is supported by browser.
 * Falls back on sendKey ().
 */
x2WebTracker.sendKey = function (args) {
    x2WebTracker.sendKeyNoCORS (args);
};

<?php
/**
 * Generate and return a 32 character key
 */
?>
x2WebTracker.generateKey = function () {
    var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var key = '';
    for(var i = 0; i < 32; ++i) { 
        key += chars.charAt (parseInt (Math.floor (Math.random () * chars.length)));
    }
    //console.log ('key = ' + key); 
    return key;
};

<?php
/**
 * Set the key cookie with an expiration date 1 year in the future
 */
?>
x2WebTracker.setKeyCookie = function (key) {
    // Remove a subdomain if present
    if (document.domain.split('.').length > 2)
        var domain = document.domain.replace (/^[^.]*/, '');
    else
        var domain = document.domain;

    document.cookie = "x2_key=" + key + 
        ";expires=" + (new Date (+new Date () + 31556940000)).toGMTString () +
        ";path=/;domain=" + domain;
};

<?php
/**
 * Detects a hidden key cookie input fields and populates it with the cookie key. The purpose of
 * this function is to enable tracking for users who have custom web lead forms that submit via
 * the web lead form API.
 */
?>
x2WebTracker.setKeyCookieHiddenField = function (key) {
    document.onreadystatechange = function () {
        if (document.readyState === 'complete') {
            var hiddenFields = document.getElementsByName ('x2_key');
            if (hiddenFields.length > 0) {
                var hiddenField = hiddenFields[0];
                hiddenField.setAttribute ('value', key);
            }
        }
    }
};

(x2WebTracker.main = function () {
    var url = window.location.href;

    <?php /* x2plastart */ ?>
    var fingerprint;
    <?php if (!isset ($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] != 1) {
        require(__DIR__.'/js/fontdetect.js');
        require(__DIR__.'/js/X2Identity.js'); ?>
        fingerprint = x2Identity.fingerprint();
    <?php } ?>
    <?php /* x2plaend */ ?>

    <?php 
    /*
    Tracking campaign clicks and tracking links take precedence. If its GET key is set, generate 
    a new key for tracking and send the GET key to the server for lookup.
    */
    ?>
    var getParamRegex = /(?:^[?]|[?].*[?&])x2_key=([^&]+)/;
    if (window.location.search && window.location.search.match (getParamRegex)) {
        var x2KeyGetParam = window.location.search.replace (getParamRegex, '$1');
        if (x2KeyGetParam.match (/[a-zA-Z0-9]/)) {
            var x2KeyCookie = x2WebTracker.generateKey ();
            x2WebTracker.setKeyCookie (x2KeyCookie);
            x2WebTracker.sendKey ({
                url: url, 
                x2KeyGetParam: x2KeyGetParam <?php /* x2plastart */ ?>, 
                fingerprint: fingerprint<?php /* x2plaend */ ?>
            });
            x2WebTracker.setKeyCookieHiddenField (x2KeyGetParam);
            return;
        }
    }

    <?php // generate cookie key if there isn't one ?>
    var cookieRegex = /(?:^|.*;)\s*x2_key\s*=\s*([^;]*)(?:.*$|$)/;
    if (!document.cookie.match (cookieRegex)) {
        //console.log ('no cookie');
        var x2KeyCookie = x2WebTracker.generateKey ();
        x2WebTracker.setKeyCookie (x2KeyCookie);
        x2WebTracker.setKeyCookieHiddenField (x2KeyCookie);
        <?php /* x2plastart */ ?>
        x2WebTracker.sendKey ({
            url: url, 
            x2KeyGetParam: null,
            fingerprint: fingerprint
        });
        <?php /* x2plaend */ ?>
        return; 
    }

    <?php // there's a cookie key, request content ?>
    var x2KeyCookie = document.cookie.replace (cookieRegex, '$1');
    //console.log ('x2KeyCookie = ');
    //console.log ('1' + x2KeyCookie + '1');
    if (x2KeyCookie.match (/[a-zA-Z0-9]/)) {
        x2WebTracker.sendKey ({
            url: url, 
            x2KeyGetParam: null <?php /* x2plastart */ ?>, 
            fingerprint: fingerprint<?php /* x2plaend */ ?>
        });
        x2WebTracker.setKeyCookieHiddenField (x2KeyCookie);
    }
}) ();


