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

$modTitles = array(
    'calendar' => Modules::displayName(),
    'actions' => Modules::displayName(true, "Actions"),
    'user' => Modules::displayName(false, "Users"),
    'users' => Modules::displayName(true, "Users"),
);

$menuOptions = array(
    'index', 'myPermissions',
);
if (Yii::app()->params->isAdmin)
    $menuOptions[] = 'userPermissions';
if (Yii::app()->settings->googleIntegration)
    $menuOptions[] = 'sync';
$this->insertMenu($menuOptions);
?>

<script type="text/javascript">
function giveSaveButtonFocus() {
$('#save-button')
    .css('background', '')
    .css('color', '');
$('#save-button')
    .css('background', '#579100')
    .css('color', 'white')
    .focus();
}
</script>

<?php

$users = User::model()->findAllByAttributes(array('status'=>User::STATUS_ACTIVE));
$thisUser = null;
$users = array_combine(array_map(function($u){return $u->fullName;},$users),$users);
ksort($users);

if(isset($id)) {

	$this->beginWidget('CActiveForm', array(
		'id'=>'user-permission-form',
		'enableAjaxValidation'=>false,
	));

	Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
	Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
	Yii::app()->clientScript->registerCss('userPermissionCss',"
	.user-permission {
		width: 460px;
		height: 200px;
	}
	#switcher {
		margin-top: 20px;
	}
	",'screen, projection');
	Yii::app()->clientScript->registerScript('userCalendarPermission', "
	$(function() {
		$('.user-permission').multiselect();
		$('.ui-icon').click(function() {
			giveSaveButtonFocus();
		});
	});
	",CClientScript::POS_HEAD);
	$names = array();
	foreach($users as $name => $user){
        if($user->id != $id){
            if(!Yii::app()->authManager->checkAccess('administrator', $user->id))
                $names[$user->id] = $name;
            elseif($user->username == 'chames') {
                echo $user->username.' '.$user->id;
                die();
            }
        } else{
            $thisUser = $user;
        }
    }
    
    $viewPermission = X2CalendarPermissions::getUserIdsWithViewPermission($id);
	$editPermission = X2CalendarPermissions::getUserIdsWithEditPermission($id);
	
	$fullname = $thisUser->fullName;
	
	echo CHtml::hiddenField('user-id', $id); // save user id for POST
	?>
	
	<div class="page-title"><h2><?php echo Yii::t('calendar', 'View Permission'); ?></h2></div>
	<div class="form">
        <?php echo Yii::t('calendar', "These {users} can view {fullname}'s {calendar}.", array (
            '{users}' => lcfirst($modTitles['users']),
            '{fullname}' => $fullname,
            '{calendar}' => $modTitles['calendar'],
        )); ?>
		<?php
		echo CHtml::listBox('view-permission', $viewPermission, $names, array(
			'class'=>'user-permission',
			'multiple'=>'multiple',
			'onChange'=>'giveSaveButtonFocus();',
		));
		?>
		<br>
	</div>
	<div class="page-title rounded-top"><h2><?php echo Yii::t('calendar', 'Edit Permission'); ?></h2></div>
	<div class="form">
        <?php echo Yii::t('calendar', "These {users} can edit {fullname}'s {calendar}.", array (
            '{users}' => lcfirst($modTitles['users']),
            '{fullname}' => $fullname,
            '{calendar}' => $modTitles['calendar'],
        )); ?>
		<?php
		echo CHtml::listBox('edit-permission', $editPermission, $names, array(
			'class'=>'user-permission',
			'multiple'=>'multiple',
			'onChange'=>'giveSaveButtonFocus();',
		));
		?>
		<br>
		<div class="row buttons">
			<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button', 'name'=>'save-button', 'tabindex'=>24)); ?>
            <?php echo CHtml::link(Yii::t('calendar', 'Back To {user} List', array(
                '{user}' => $modTitles['user'],
            )), $this->createUrl(''), array('class'=>'x2-button')); ?>
		</div>
	</div>
	<?php
$this->endWidget();
	?>

	<?php
} else {
	?>
    <div class="page-title"><h2>
        <?php echo Yii::t('calendar', '{user} {calendar} Permissions', array(
            '{calendar}' => $modTitles['calendar'],
            '{user}' => $modTitles['user'],
        )); ?>
    </h2></div>
	<div style="padding: 8px">
	<?php
	foreach($users as $user) {
			echo CHtml::link($user->fullName, $this->createUrl('', array('id'=>$user->id)));
			echo "<br>\n";
	}
	?>
	</div>
	<?php
}

?>
