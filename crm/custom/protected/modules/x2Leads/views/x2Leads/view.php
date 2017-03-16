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

$this->pageTitle = CHtml::encode(
                Yii::app()->settings->appName . ' - ' . Yii::t('x2Leads', 'View Lead'));

$authParams['assignedTo'] = $model->assignedTo;

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'delete', 'attach', 'quotes',
    'convertToContact', 'convert', 'print', 'editLayout',
);
$this->insertMenu($menuOptions, $model, $authParams);


Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');
Yii::app()->clientScript->registerCss('leadViewCss', "

#content {
    background: none !important;
    border: none !important;
}

#conversion-warning-dialog ul {
    padding-left: 25px !important;
}

");

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl() . '/js/Relationships.js');

Yii::app()->clientScript->registerScript('leadsJS', "

// widget data
$(function() {
	$('body').data('modelType', 'x2Leads');
	$('body').data('modelId', $model->id);
});

");

$themeUrl = Yii::app()->theme->getBaseUrl();
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">

        <div class="page-title icon x2Leads">
            <h2><span class="no-bold"><?php echo Yii::t('x2Leads', 'Leads:'); ?> </span><?php echo CHtml::encode($model->name); ?></h2>
            <?php
            echo X2Html::editRecordButton($model);
            echo X2Html::inlineEditButtons();
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <?php
    $this->beginWidget('CActiveForm', array(
        'id' => 'contacts-form',
        'enableAjaxValidation' => false,
        'action' => array('saveChanges', 'id' => $model->id),
    ));

    $this->renderPartial('application.components.views._detailView', array('model' => $model, 'modelName' => 'X2Leads'));
    $this->endWidget();

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'modelName' => 'X2Leads',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));

    ?>
    <div id="quote-form-wrapper">
    <?php
    $this->widget('InlineQuotes', array(
        'startHidden' => true,
        'recordId' => $model->id,
        'account' => $model->getLinkedAttribute('accountName', 'name'),
        'modelName' => X2Model::getModuleModelName()
    ));
    ?>
    </div>

<?php
$this->widget(
        'Attachments', array(
    'associationType' => 'x2Leads', 'associationId' => $model->id,
    'startHidden' => true
        )
);
?>
</div>

<?php
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block' => 'center',
        'model' => $model,
        'modelType' => 'x2Leads'
    ));
$this->widget('CStarRating', array('name' => 'rating-js-fix', 'htmlOptions' => array('style' => 'display:none;')));

$this->widget('X2ModelConversionWidget', array(
    'buttonSelector' => '#convert-lead-button',
    'targetClass' => 'Seller',
    'namespace' => 'Seller',
    'model' => $model,
));

$this->widget('X2ModelConversionWidget', array(
    'buttonSelector' => '#convert-lead-to-contact-button',
    'targetClass' => 'Contacts',
    'namespace' => 'Contacts',
    'model' => $model,
));
?>
