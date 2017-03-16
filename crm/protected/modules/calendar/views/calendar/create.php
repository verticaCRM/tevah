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

$modTitle = Modules::displayName();

$menuItems = array(
    array('label'=>Yii::t('calendar','{module}', array('{module}'=>$modTitle)), 'url'=>array('index')),
    array(
        'label'=>Yii::t('calendar', 'My {module} Permissions',  array(
            '{module}' => $modTitle,
        )),
        'url'=>array('myCalendarPermissions')
    ),
    array('label'=>Yii::t('calendar','List'),'url'=>array('list')),
    array('label'=>Yii::t('calendar','Create')),
);
if (Yii::app()->settings->googleIntegration) {
    $menuItems[] = array(
        'label'=>Yii::t('calendar', 'Sync My {actions} To Google Calendar', array(
            '{actions}' => Modules::displayName(true, "Actions"),
        )),
        'url'=>array('syncActionsToGoogleCalendar')
    );
}

$this->actionMenu = $this->formatMenu($menuItems);
?>
<h2>
    <?php echo Yii::t('calendar','Create Shared {module}', array(
        '{module}' => $modTitle,
    )); ?>
</h2>


<?php 
$form=$this->beginWidget('CActiveForm', array(
   'id'=>'calendar-form',
   'enableAjaxValidation'=>false,
));

$users = User::getNames();
unset($users['Anyone']);
unset($users['admin']);
	
echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'calendar',
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
	)
);
?>


<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
		</div>
	</div>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
	<table frame="border">
		<td>
			<?php if($googleIntegration) { ?>
				<?php if ($client->getAccessToken()) { ?>
					<?php echo $form->labelEx($model, 'googleCalendar'); ?>
					<?php echo $form->checkbox($model, 'googleCalendar'); ?>
					<?php echo $form->labelEx($model, 'googleCalendarName'); ?>
					<?php echo $form->dropDownList($model, 'googleCalendarId', $googleCalendarList); ?>
					<br />
					<?php echo CHtml::link(Yii::t('calendar', "Don't link to Google Calendar"), $this->createUrl('') . '?unlinkGoogleCalendar'); ?>
				<?php } else { ?>
					<?php echo CHtml::link(Yii::t('calendar', "Link to Google Calendar"), $client->createAuthUrl()); ?>
				<?php } ?>
			<?php } else { ?>
					<?php echo $form->labelEx($model, 'googleCalendar'); ?>
					<?php echo $form->checkbox($model, 'googleCalendar'); ?>
					<?php echo $form->labelEx($model, 'googleFeed'); ?>
					<?php echo $form->textField($model, 'googleFeed', array('size'=>75)); ?>
			<?php } ?>
		</td>
	</table>
</div>

<?php
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton(Yii::t('app','Create'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";
$this->endWidget();

?>
