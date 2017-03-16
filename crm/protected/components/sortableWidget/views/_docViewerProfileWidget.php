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

?>
<iframe src="<?php 
if ($docId !== '') {
    echo Yii::app()->controller->createUrl('/docs/docs/fullView',array('id'=>$docId)); 
} else {
    echo '';
}
?>" frameBorder="0" height="<?php echo $height; ?>" width="100%" style="background:#fff;"></iframe>
<?php
if ($docId === '') {
?>
<div class='default-text-container' style='display: none;'>
<a href='#'><?php echo Yii::t('app', '-Click Here to Upload a {Doc}-', array(
    '{Doc}' => Modules::displayName(false, 'Docs'),
)); ?></a>
</div>
<?php
}
