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
<div class="view top-level">
	<div class="deleteButton">
		<?php
		$parent=Events::model()->findByPk($data->associationId);
		if($data->user==Yii::app()->user->getName() || $parent->associationId==Yii::app()->user->getId() || Yii::app()->params->isAdmin)
			echo CHtml::link(
                '',
                array(
                    '/profile/deletePost',
                    'id'=>$data->id,
                    'profileId'=>$profileId,
                ),
                array(
                	'class'=>'fa fa-close'
                )
            ); //,array('class'=>'x2-button') ?>
	</div>
	<?php echo User::getUserLinks($data->user);
	echo ' ';
	echo X2Html::tag('span', array(
		'class' => 'comment-age x2-hint',
		'id' => "-$data->timestamp",
		'title' => Formatter::formatFeedTimestamp($data->timestamp),
		), Formatter::formatFeedTimestamp($data->timestamp));

	?> 
	<br/>
	<?php echo $data->text; ?>
</div>


<?php /*
<div class="view">
	<div class="deleteButton">
		<?php echo CHtml::link('[x]',array('deleteNote','id'=>$data->id)); //,array('class'=>'x2-button') ?>
		<?php //echo CHtml::link("<img src='".Yii::app()->request->baseUrl."/images/deleteButton.png' />",array("deleteNote","id"=>$data->id)); ?>
	</div>

	<b><?php echo CHtml::encode($data->getAttributeLabel('createdBy')); ?>:</b>
	<?php echo CHtml::encode($data->createdBy); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('createDate')); ?>:</b>
	<?php echo CHtml::encode($data->createDate); ?>
	<br /><br />
	<b><?php echo CHtml::encode($data->getAttributeLabel('note')); ?>:</b>
	<?php echo CHtml::encode($data->note); ?>
	<br />
</div>
*/
?>
