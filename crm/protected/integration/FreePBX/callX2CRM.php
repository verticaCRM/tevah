#!/usr/bin/php
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
 * An AGI script that makes use of X2Engine's API to produce call notifications.
 *
 * Requires PHPAGI: http://phpagi.sourceforge.net/ (should come standard in FreePBX Distro)
 */
$baseUrl = ''; // Set this to your CRM's URL, with the entry script ('index.php') appended
$user = '';  // Set this to a valid X2Engine user's username
$userKey = ''; // Set this to the user's API key

require_once "phpagi.php";
require_once "APIModel.php";

$agi = new AGI();
$cid = $agi->parse_callerid();

if($cid['username'] == '') // Can't do anything; caller ID is empty.
	exit(0); 

$defaultOpts = array(
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_HTTP200ALIASES => array(400,401,403,404,500),
	CURLOPT_CONNECTTIMEOUT => 3,
);

function newModel() {
	global $baseUrl,$user,$userKey;
	new APIModel($user, $userKey, $baseUrl);
}

// If the caller ID has name enabled, search for a preexisting contact with the same name
$contact = null;
if($cid['name']!='') {
	$agi->verbose('Caller has name enabled: '.$cid['name']);
	$lastFirst = false;
	if(strpos($cid['name'],',') !== false) {
		$fln = explode(',',$cid['name']);
		$lastFirst = true; // lastName, firstName
	} else
		$fln = explode(' ',$cid['name']); // firstName lastName
	$attr = array();
	if(count($fln) > 1) {
		$attr[($lastFirst ? 'lastName'  : 'firstName')] = trim($fln[0]);
		$attr[($lastFirst ? 'firstName' : 'lastName' )] = trim(implode(' ',array_slice($fln,1)));
		$contact = newModel();
		$contact->attributes = $attr;
		$contact->contactLookup();
		if($contact->responseCode == 404) { 
			$agi->verbose('Creating a new contact in X2Engine using the available data.');
			// Time to create a new contact!
			$attr['phone'] = $cid['username'];
			$attr['visibility'] = 1;
			$attr['backgroundInfo'] = 'Contact created via AGI; called in '.strftime('%h %e, %r');
			$attr['leadSource'] = 'Call-In';
			$contact->attributes = $attr;
			$contact->contactCreate();
		} else if($contact->responseCode == 200) {
			$agi->verbose('An existing contact was found in X2Engine matching the name.');
			// Create a call log (experimental/unfinished)
			//$action =  newModel();
			//$action->associationType = 'contacts';
			//$action->associationId = $contact->id;
			//$action->dueDate = time();
			//$action->completeDate = time();
		}
	}	
}

// First check to see if there's already a contact, and if this is
// a repeat caller. 
$ch = curl_init("$baseUrl/api/voip?data={$cid['username']}");
curl_setopt_array($ch,$defaultOpts);
$cr = curl_exec($ch);

if(empty($cr)) {
	$cr = array('error'=>true,'message'=>'Failed connecting to X2Engine.');
	$apiResponseCode = 0;
} else {
	$cr = json_decode($cr,1); 
	$apiResponseCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
}

$agi->verbose("($apiResponseCode) ".$cr['message']);

exit(0);
?>
