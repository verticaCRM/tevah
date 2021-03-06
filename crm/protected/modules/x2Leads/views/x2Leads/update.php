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

$this->pageTitle = CHtml::encode (
    Yii::app()->settings->appName . ' - '.Yii::t('x2Leads', 'Edit Lead'));


$authParams['assignedTo'] = $model->assignedTo;

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

?>
<div class="page-title icon x2Leads">
	<h2><span class="no-bold"><?php echo Yii::t('module','Update'); ?>:</span> <?php echo CHtml::encode($model->name); ?></h2>
	<a class="x2-button highlight right" href="javascript:void(0);" onclick="$('#save-button').click();"><?php echo Yii::t('app','Save'); ?></a>
</div>
<?php echo $this->renderPartial('application.components.views._form', array('model'=>$model, 'modelName'=>'X2Leads')); ?>
