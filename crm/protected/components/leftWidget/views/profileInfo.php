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

<div id='profile-badge'>
	<span id='profile-avatar'>
	<?php Profile::renderFullSizeAvatar ($this->model->id, 45); ?>
	</span>

	<span id='info'>
	<div id='profile-name'>
		<?php echo X2Html::link(
			$this->model->fullName	,
			Yii::app()->controller->createUrl('view', array(
					'id' => $this->model->id,
					'publicProfile'=> true))
			 );
		?>
	</div>

	<!--div id='profile-edit'>
		<!?php echo X2Html::link(
			Yii::t('profile','Edit Profile'),
			Yii::app()->controller->createUrl('update', array(
					'id' => $this->model->id 
				))
			);
		?!>
	</div-->
	</span>
	<div class='clear'></div>
</div>

<div id='profile-actions'>
	<?php echo
	X2Html::ul($actionList, array(
		'id' => 'profile-widget-action-menu')
	, 'x2-minimal-button');
	?>
</div>
