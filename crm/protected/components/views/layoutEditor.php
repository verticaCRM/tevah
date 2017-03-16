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

$dragMe = Yii::t('app', 'Drag me!');
$close = Yii::t('app', 'Close');
$reset= Yii::t('app', 'Reset');
$screenWidth = Yii::t('app', 'Increase screen width to adjust columns');

?>


<div id='<?php echo $namespace; ?>-layout-editor' class='x2-layout-island layout-editor'>

	<div class='drag-me-label'>
		<!-- <h4>Drag me!</h4> -->
	</div>

	<div class="column-adjuster">
		<span class='screen-too-small'><?php echo $screenWidth ?></span>
		<span id='<?php echo $namespace; ?>-section-1' class='section-1'></span>
		<span class='indicator portlet-title'>
			<span><?php echo $dragMe?></span>
		</span>
		<span class='close-button x2-minimal-button'><?php echo $close ?></span>
		<span class='reset-button x2-minimal-button'><?php echo $reset ?></span>
		<span class='clear'></span>
	</div>
</div>
