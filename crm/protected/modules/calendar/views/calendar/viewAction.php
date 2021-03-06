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
$users = User::getNames();
$form=$this->beginWidget('CActiveForm', array(
    'enableAjaxValidation'=>false,
));
?>

<style type="text/css">

.dialog-label {
	font-weight: bold;
	display: block;
}

.cell {
	float: left;
}

.dialog-cell {
	padding: 5px;
}

</style>

<div class="row">
	<div class="cell dialog-cell" style="float: none;">
		<?php echo $model->actionDescription; ?>
	</div>
</div>

<div class="row">
	<div class="cell dialog-cell">
		<?php 
        echo $form->label($model,($isEvent?'startDate':'dueDate'), array('class'=>'dialog-label'));
		echo Formatter::formatDateTime($model->dueDate);	//format date from DATETIME

		if($isEvent) {
			echo $form->label($model, 'endDate', array('class'=>'dialog-label'));
			echo Formatter::formatDateTime($model->completeDate);	//format date from DATETIME
		}

		echo $form->label($model, 'allDay', array('class'=>'dialog-label')); 
		echo $form->checkBox(
            $model, 'allDay', array('onChange'=>'giveSaveButtonFocus();', 'disabled'=>'disabled')); 
        ?>
	</div>

	<div class="cell dialog-cell">
		<?php 
        echo $form->label($model,'priority', array('class'=>'dialog-label')); 
		$priorityArray = Actions::getPriorityLabels();
		echo isset($priorityArray[$model->priority])?$priorityArray[$model->priority]:""; 
        ?>
	</div>
	<div class="cell dialog-cell">
		<?php
        // assigned to calendar instead of user?
		if($model->assignedTo == null && is_numeric($model->calendarId)) { 
		    $model->assignedTo = $model->calendarId;
		}
		echo $form->label($model,'assignedTo', array('class'=>'dialog-label')); 
        echo $model->renderAttribute (
            'assignedTo');
        ?>
    </div>
	<div class="cell dialog-cell">
        <?php
        if ($model->type === 'event') {
            if (!empty ($model->eventSubtype)) {
                echo $form->label ($model, 'eventSubtype', array ('class' => 'dialog-label'));
                echo $model->renderAttribute ('eventSubtype');
            }
            if (!empty ($model->eventStatus)) {
                echo $form->label ($model, 'eventStatus', array ('class' => 'dialog-label'));
                echo $model->renderAttribute ('eventStatus');
            }
        }
		?>
</div>

<?php $this->endWidget(); ?>
