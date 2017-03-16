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
$authParams['X2Model'] = $model;

$menuOptions = array(
    'index', 'invoices', 'create', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

$title = CHtml::tag(
    'h2',array('class' =>'quotes-section-title'), 
    $model->type === 'invoice' ? 
        Yii::t('quotes', 'Update Invoice:') : 
        Yii::t('quotes','Update {quote}:', array(
            '{quote}' => Modules::displayName(false),
        ))).CHtml::encode ($model->getName ());
echo $quick ? $title:CHtml::tag('div',array('class'=>'page-title icon quotes'),$title);

if(!$quick){ ?>
<a class="x2-button right" href="javascript:void(0);" 
    onclick="$('#quote-save-button').click();"><?php echo Yii::t('app','Update'); ?></a>
</div>
<?php 
} 


$form=$this->beginWidget('CActiveForm', array(
   'id'=>'quotes-form',
   'enableAjaxValidation'=>false,
));
	
echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'Quote',
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
		'scenario' => $quick ? 'Inline' : 'Default',
	)
);

if($model->type == 'invoice') { ?>
	<div class="x2-layout form-view" style="margin-bottom: 0;">
	
	    <div class="formSection showSection">
	    	<div class="formSectionHeader">
	    		<span class="sectionTitle" title="Invoice"><?php 
                    echo Yii::t('quotes', 'Invoice'); ?></span>
	    	</div>
	    	<div class="tableWrapper">
	    		<table>
	    			<tbody>
	    				<tr class="formSectionRow">
	    					<td style="width: 300px">
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Status'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceStatus'); ?>
	    							</div>
	    						</div>
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Created'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceCreateDate'); ?>
	    							</div>
	    						</div>
	    					</td>
	    					<td style="width: 300px">
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Issued'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoiceIssuedDate'); ?>
	    							</div>
	    						</div>
	    						<div class="formItem leftLabel">
	    							<label><?php echo Yii::t('media', 'Invoice Paid'); ?></label>
	    							<div class="formInputBox" style="width: 150px; height: auto;">
	    								<?php echo $model->renderInput('invoicePayedDate'); ?>
	    							</div>
	    						</div>
	    					</td>
	    				</tr>
	    			</tbody>
	    		</table>
	    	</div>
	    </div>
	    
	</div>
	<br />
<?php }

echo $this->renderPartial('_lineItems',
	array(
		'model'=>$model,
		'products'=>$products,
		'readOnly'=>false,
		'form'=>$form,
        'namespacePrefix' => 'quotes'
	)
);

echo $this->renderPartial('_sharedView', array (
    'quick' => $quick,
    'action' => 'Create',
    'model' => $model,
    'form' => $form,
));

$this->endWidget();
?>
