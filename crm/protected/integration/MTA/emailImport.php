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

/* @edition:pro */

/**
 * @file protected/integration/MTA/emailImport.php
 *
 * A script file that provides an alternate means to submit an email to X2Engine via
 * the API action.
 */

///////////////////////////
// Configuration details //
///////////////////////////
// Set this to the IP address or domain name of the server
$host = '';
// Set this to the protocol (use "https://" for an SSL-enabled web server)
$proto = 'http://';
// Set this to the URI on the web server of X2Engine, without the trailing slash.
// So, if the login URL is "http://example.com/X2Engine/index.php/site/login",
// this variable should be "/X2Engine"
$baseUri = '';
// Leave this null if the host specified by $host will resolve correctly.
// Otherwise, if in an environment where (for instance) the domain does not resolve
// properly, and the IP address must be used, but the CRM is on a specifically-named
// virtual host on a shared IP, set this to the domain name of that host, and set
// $host to the IP address of the web server.
$hostName = '';
$data = array(
	'user' => '',
	'userKey' => '',
);

// Obtain raw email as piped to this script from the MTA
$socket = fopen("php://stdin", 'r');
$data['email'] = stream_get_contents($socket);
fclose($socket);

// Run the CURL request to import the email:
$ch = curl_init("$proto$host$baseUri/index.php/api/dropbox");
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
if(!empty($hostName))
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Host: $hostName"));

curl_exec($ch);

?>
