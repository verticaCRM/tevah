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

require '../protected/models/APIModel.php';
$attributes = $_POST['Contacts'];
$contact = new APIModel(
    'admin','21232f297a57a5a743894a0e4a801fc3','localhost/index-test.php');
$fieldMap = array( // This map should be of the format 'your_fieldname'=>'x2_fieldname',
    'firstName'=>'firstName',
    'lastName'=>'lastName',
    'email'=>'email',
    'phone'=>'phone',
    'backgroundInfo'=>'backgroundInfo',
);
foreach($attributes as $key=>$value){
   if(isset($fieldMap[$key])){
        $contact->{$fieldMap[$key]}=$value; // Found in field map, used mapped attribute
    }else{
        $contact->$key=$value; // No match anywhere, assume it's a Contact attribute
    }
}
if(isset($_POST['x2_key'])){
    $contact->trackingKey=$_POST['x2_key'];
}
if ((int)$contact->contactCreate()) {
?>
<div id="success">success<?php echo $contact->trackingKey; ?></div>
<div id="success">success<?php echo $_POST['x2_key']; ?></div>
<?php
} else {
?>
<div id="failure">failure</div>
<?php
}
