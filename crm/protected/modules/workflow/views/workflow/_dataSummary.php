<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
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

/*
Per-stage data summary which gets displayed when stage name links to the left of the funnel are
clicked.
*/
?>
<div id='stage-data-summary'>
<h3><?php echo Yii::t(
    'charts','{stageName} Stage Summary', array ('{stageName}' => $stageName)); ?></h3>
<span><?php echo Yii::t('charts','Total Records'); ?>:<b><?php echo $count; ?></b></span>
<span><?php echo Yii::t('charts','Total Value'); ?>:<b> <?php echo $totalValue; ?></b></span>
<span><?php echo Yii::t('charts','Projected Value'); ?>:<b> <?php echo $projectedValue; ?></b></span>
<span><?php echo Yii::t('charts','Current Value'); ?>:<b> <?php echo $currentAmount; ?></b></span>
</div>
