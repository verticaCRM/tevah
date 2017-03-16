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

Yii::app()->clientScript->registerCss('inviteUsersCss',"

#invitation-email-list {
    width: 80%;
    max-width: 600px;
    height: 150px;
}

");

$menuOptions = array(
    'feed', 'admin', 'create', 'invite',
);
$this->insertMenu($menuOptions);

?>
<div class="page-title icon users"><h2>
    <?php echo Yii::t('users','Invite {users} to X2Engine', array(
        '{users}' => Modules::displayName(),
    )); ?>
</h2></div>

<form method="POST">
<div class="form">
<h2><?php echo Yii::t('users','Instructions'); ?></h2>
<?php echo Yii::t('users','Please enter a list of e-mails separated by commas.'); ?>
	<div class="row"><textarea id='invitation-email-list' name="emails"></textarea></div>
	<div class="row"><input type="submit" value="<?php echo Yii::t('app','Submit');?>" class="x2-button"></div>
</div>
</form>
