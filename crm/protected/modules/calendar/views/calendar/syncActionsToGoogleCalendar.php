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

<?php

$actionsTitle = Modules::displayName(true, "Actions");

$menuOptions = array(
    'index', 'myPermissions', 'sync',
);
$this->insertMenu($menuOptions);

?>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'calendar-form',
    'enableAjaxValidation' => false,
        ));
?>

<?php
Yii::app()->clientScript->registerScript('syncSaveChanges', "
$(function() {
	$('#calendar-form').find('select').change(function() {
		$('#save-button').addClass('highlight'); // highlight button when user changes a field in the form
	});
});
", CClientScript::POS_HEAD);
?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
    <div class="formSection">
        <div class="formSectionHeader">
            <span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
        </div>
    </div>
</div>
<div class="errorSummary">
    <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message){
        echo '<div class="flash-'.$key.'">'.$message."</div>\n";
    }
    ?>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
    <table frame="border">
        <td>
            <?php if($googleIntegration){ 
                if(empty($errors) && !$auth->getErrors() && $auth->getAccessToken()){ 
                    echo $form->labelEx($model, 'googleCalendarName');
                    echo $form->dropDownList($model, 'syncGoogleCalendarId', $googleCalendarList); 
                    ?>
                    <br />
                    <?php 
                    if(isset($syncGoogleCalendarName) && $syncGoogleCalendarName){ 
                        echo Yii::t(
                            'calendar',
                            'Your {actions} are being synced to the Google Calendar '.
                            '"{calendarName}".',
                            array(
                                '{calendarName}' => $syncGoogleCalendarName,
                                '{actions}' => lcfirst($actionsTitle),
                            )); 
                    ?> <br />
                    <?php } 
                    echo CHtml::link(
                        Yii::t('calendar', "Don't Sync My {actions} To Google Calendar", array(
                            '{actions}' => $actionsTitle,
                        )),
                        $this->createUrl('').'?unlinkGoogleCalendar'); 
                }else{ 
                    echo CHtml::link(
                        Yii::t('calendar', "Sync My {actions} To Google Calendar", array(
                            '{actions}' => $actionsTitle,
                        )),
                        $auth->getAuthorizationUrl(null)); 
                } 
            }else{
                echo Yii::t('calendar', 'Google Integration is not configured on this server.');
            }
            ?>
        </td>
    </table>
</div>

<?php
if(empty($errors) && $googleIntegration && !$auth->getErrors() && $auth->getAccessToken()){
    echo '	<div class="row buttons">'."\n";
    echo '		'.CHtml::submitButton(
        Yii::t('app', 'Sync'), 
        array('class' => 'x2-button', 'id' => 'save-button', 'tabindex' => 24))."\n";
    echo "	</div>\n";
}
$this->endWidget();
?>
