<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerCss('recordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");
Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');


// quotes can be locked meaning they can't be changed anymore
Yii::app()->clientScript->registerScript('LockedQuoteDialog', "
function dialogStrictLock() {
var denyBox = $('<div></div>')
    .html('This quote is locked.')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'OK': function() {
    			$(this).dialog('close');
    		}
    	}
    });

denyBox.dialog('open');
}

function dialogLock() {
var confirmBox = $('<div></div>')
    .html('This quote is locked. Are you sure you want to update this quote?')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'Yes': function() {
    			window.location = '" . Yii::app()->createUrl('/quotes/quotes/update', array('id' => $model->id)) . "';
    			$(this).dialog('close');
    		},
    		'No': function() {
    			$(this).dialog('close');
    		}
    	}
    });
confirmBox.dialog('open');
}

", CClientScript::POS_HEAD);
$modelType = json_encode("Quotes");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
if ($contactId) {
    $contact = Contacts::model()->findByPk($contactId); // used to determine if 'Send Email' menu item is displayed
} else {
    $contact = false;
}

$authParams['X2Model'] = $model;
$strict = Yii::app()->settings->quoteStrictLock;
$themeUrl = Yii::app()->theme->getBaseUrl();

$menuOptions = array(
    'index', 'invoices', 'create', 'view', 'email', 'delete', 'attach', 'print', 'convert', 
    'duplicate', 'editLayout',
);
if ($contact)
    $menuOptions[] = 'email';
if ($model->locked)
    if ($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess'))
        $menuOptions[] = 'editStrictLock';
    else
        $menuOptions[] = 'editLock';
else
    $menuOptions[] = 'edit';
if ($model->type !== 'invoice')
    $menuOptions[] = 'convert';
$this->insertMenu($menuOptions, $model, $authParams);
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="responsive-page-title page-title icon quotes">
            <h2><span class="no-bold"><?php echo ($model->type == 'invoice' ? Yii::t('quotes', 'Invoice:') : Yii::t('quotes', '{module}:', array('{module}' => Modules::displayName(false)))); ?></span> <?php echo $model->name == '' ? '#' . $model->id : CHtml::encode($model->name); ?></h2>
            <?php
            echo ResponsiveHtml::gripButton();
            ?>
            <div class='responsive-menu-items'>
                <?php if ($model->locked) { ?>
                    <?php if ($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess')) { ?>
                        <a class="x2-button icon edit right" href="#" onClick="dialogStrictLock();"><span></span></a>
                    <?php } else { ?>
                        <a class="x2-button icon edit right" href="#" onClick="dialogLock();"><span></span></a>
                    <?php
                    }
                } else {
                    echo X2Html::editRecordButton($model);
                }
                echo X2Html::emailFormButton();
                echo X2Html::inlineEditButtons();


                if ($model->type !== 'invoice') {
                    ?>
                    <a class="x2-button right" href="<?php echo $this->createUrl('convertToInvoice', array('id' => $model->id)); ?>">
                    <?php echo Yii::t('quotes', 'Convert To Invoice'); ?>
                    </a>
                       <?php
                       }
                       ?>
            </div>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'quotes-form',
    'enableAjaxValidation' => false,
    'action' => array('saveChanges', 'id' => $model->id)
        ));

$this->renderPartial('application.components.views._detailView', array('model' => $model, 'modelName' => 'Quote'));
?>
    <?php if ($model->type == 'invoice') { ?>
        <div class="x2-layout form-view">
            <div class="formSection showSection">
                <div class="formSectionHeader">
                    <span class="sectionTitle" title="Invoice"><?php echo Yii::t('quotes', 'Invoice'); ?></span>
                </div>
                <div class="tableWrapper">
                    <table>
                        <tbody>
                            <tr class="formSectionRow">
                                <td style="width: 300px">
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Status'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceStatus'); ?>
                                        </div>
                                    </div>
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Created'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceCreateDate'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 300px">
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Issued'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceIssuedDate'); ?>
                                        </div>
                                    </div>
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Paid'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoicePayedDate'); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<?php
}

$productField = Fields::model()->findByAttributes(array('modelName' => 'Quote', 'fieldName' => 'products'));
?>
    <div class="x2-layout form-view">
        <div class="formSection showSection">
            <div class="formSectionHeader">
                <span class="sectionTitle"><?php echo $productField->attributeLabel; ?></span>
            </div>
            <div class="tableWrapper">
<?php
$this->renderPartial('_lineItems', array(
    'model' => $model, 'readOnly' => true, 'namespacePrefix' => 'quotesView'
));
?>
            </div>

        </div>
    </div>
<?php
/*
  $this->renderPartial('_detailView',
  array(
  'model'=>$model,
  'form'=>$form,
  'currentWorkflow'=>$currentWorkflow,
  'dataProvider'=>$dataProvider,
  'total'=>$total
  )
  );
 */
$this->endWidget();

$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'to' => !empty($contact) && $contact instanceof Contacts ? '"' . $contact->name . '" <' . $contact->email . '>, ' : '',
        // 'subject'=>'hi',
        // 'redirect'=>'contacts/'.$model->id,
        'modelName' => 'Quote',
        'modelId' => $model->id,
        'message' => $this->getPrintQuote($model->id, true),
        'subject' => $model->type == ('invoice' ? Yii::t('quotes', 'Invoice') : Yii::t('quotes', '{quote}', array('{quote}' => Modules::displayName(false)))) . '(' . Yii::app()->settings->appName . '): ' . $model->name,
    ),
    'startHidden' => true,
    'templateType' => 'quote',
        )
);
?>

    <?php $this->widget('Attachments', array('associationType' => 'quotes', 'associationId' => $model->id, 'startHidden' => true)); ?>

</div>
<?php 
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block' => 'center',
        'model' => $model,
        'modelType' => 'Quote'
    )); 
?>
