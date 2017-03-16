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
 * @file email.php Backwards-compatible email capture script.
 * 
 * This was once a standalone PHP script with lots of ad-hoc SQL for importing
 * emails into the database. The functionality has been more or less entirely
 * refactored into a Yii console app command to make it more manageable and 
 * easier to work with. This script now essentially just pipes the email to that
 * new command.
 */

$command = dirname(__FILE__).implode(DIRECTORY_SEPARATOR,array('','protected','yiic emaildropbox'));

$descriptorspec = array(
	0 => array("pipe", "r")
);

$process = proc_open($command, $descriptorspec, $pipes);

$socket = fopen("php://stdin", 'r');
while (!feof($socket)) {
	$line = fread($socket, 1024);
	fwrite($pipes[0],$line);
}
proc_close($process);

?>
