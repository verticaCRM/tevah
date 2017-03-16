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

$this->pageTitle = $model->name;

$authParams['X2Model'] = $model;
$menuOptions = array(
    'all', 'create', 'view', 'edit', 'delete', 'lists', 'newsletters',
    'weblead', 'webtracker', 'x2flow',
);
$this->insertMenu($menuOptions, $model, $authParams);

$form = $this->beginWidget('CActiveForm', array(
	'id'=>'campaign-form',
	'enableAjaxValidation'=>false
));
?>
<div class="page-title icon marketing">
	<h2><?php echo CHtml::encode($model->name); ?></h2>
	<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button highlight right')); ?>
</div>
<?php
$this->renderPartial('_form', array('model'=>$model, 'modelName'=>'Campaign','form'=>$form));

$this->endWidget();
