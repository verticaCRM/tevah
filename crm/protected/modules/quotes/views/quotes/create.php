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

$menuOptions = array(
    'index', 'invoices', 'create',
);
$this->insertMenu($menuOptions);

$title = CHtml::tag(
    'h2',array('class' =>'quotes-section-title'),Yii::t('quotes','Create {quote}', array(
    '{quote}' => Modules::displayName(false),
)));
echo $quick?$title:CHtml::tag('div',array('class'=>'page-title icon quotes'),$title);

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
));

if($model->hasLineItemErrors) { ?>
<div class="errorSummary">
    <h3>
        <?php echo Yii::t('quotes','Could not save {quote} due to line item errors:',  array(
            '{quote}' => lcfirst(Modules::displayName(false)),
        )); ?>
    </h3>
	<ul>
	<?php foreach($model->lineItemErrors as $error) { ?>
		<li><?php echo CHtml::encode($error); ?></li>
	<?php } ?>
	</ul>
</div>
<?php 
}

echo $this->renderPartial('application.components.views._form',
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'Quote',
		'suppressForm'=>true, // let us create the CActiveForm in this file
		'scenario' => $quick ? 'Inline' : 'Default',
	)
);

echo $this->renderPartial('_lineItems', array(
	'model' => $model,
	'products' => $products,
	'readOnly' => false,
    'namespacePrefix' => 'quotes'
));

echo $this->renderPartial('_sharedView', array (
    'quick' => $quick,
    'action' => 'Create',
    'model' => $model,
    'form' => $form,
));

$this->endWidget();
?>
