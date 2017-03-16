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

Yii::app()->clientScript->registerCss('servicesView', "
	/*#contact-info-container {
		margin: -6px 5px 5px 5px !important;
	}*/
");

$authParams['X2Model'] = $model;
$menuOptions = array(
    'index', 'create', 'view', 'edit', 'delete', 'email', 'attach', 'quotes',
    'createWebForm', 'print', 'editLayout',
);
$this->insertMenu($menuOptions, $model, $authParams);
$themeUrl = Yii::app()->theme->getBaseUrl();

if ($model->contactId) {
    // Retrieve the associated contact: the contactId
    // field will be updated to only the model name
    // while rendering the _detailView
    $contact = $model->getLinkedModel('contactId');
}

$modelType = json_encode("Servces");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
?>
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title icon services">
            <?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;'));  ?>
            <?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
            <h2><?php echo Yii::t('services', 'Case {n}', array('{n}' => $model->id)); ?></h2>
            <?php //if(Yii::app()->user->checkAccess('ServicesUpdate',$authParams)){  ?>
            <a class="x2-button icon edit right" href="<?php echo $this->createUrl('update', array('id' => $model->id)); ?>"><span></span></a>
            <?php
            echo X2Html::emailFormButton();
            echo X2Html::inlineEditButtons();
            ?>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'services-form',
        'enableAjaxValidation' => false,
        'action' => array('saveChanges', 'id' => $model->id),
    ));
    $this->renderPartial(
            'application.components.views._detailView', array('model' => $model, 'form' => $form, 'modelName' => 'services'));
    ?>

    <?php $childCases = Services::model()->findAllByAttributes(array('parentCase' => $model->id)); ?>
    <?php if ($childCases) { ?>
        <div id="service-child-case-wrapper" class="x2-layout form-view">
            <div class="formSection showSection">
                <div class="formSectionHeader">
                    <span class="sectionTitle"><?php echo Yii::t('services', 'Child Cases'); ?></span>
                </div>
                <div id="parent-case" class="tableWrapper" style="min-height: 75px; padding: 5px;">
                    <?php
                    $comma = false;
                    foreach ($childCases as $c) {
                        if ($comma) { // skip the first comma
                            echo ", ";
                        } else {
                            $comma = true;
                        }
                        echo $c->createLink();
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php
    $this->endWidget();

    if (isset($contact) && $contact) { // every service case should have a contact associated with it
        ?>
        <div id='contact-info-container'>
            <?php
            $this->renderPartial(
                    'application.modules.contacts.views.contacts._detailViewMini', array(
                'model' => $contact,
                'serviceModel' => $model
            ));
            ?>
        </div>
        <?php
    }

    $to = null;
    if (isset($contact)) {
        $to = '"' . $contact->name . '" <' . $contact->email . '>, ';
    }

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => $to,
            'modelName' => 'Services',
            'modelId' => $model->id,
        ),
        'startHidden' => true,
    ));

    ?>

    <?php $this->widget('Attachments', array('associationType' => 'services', 'associationId' => $model->id, 'startHidden' => true)); ?>

    <?php
    ?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'contactId' => $model->getLinkedAttribute('contactId', 'id'),
            'recordId' => $model->id,
            'modelName' => X2Model::getModuleModelName()
        ));
        ?>
    </div>

</div>

<?php
$this->widget('X2WidgetList', array(
    'layoutManager' => $layoutManager,
    'block' => 'center',
    'model' => $model,
    'modelType' => 'services'
));
$this->widget(
        'CStarRating', array('name' => 'rating-js-fix', 'htmlOptions' => array('style' => 'display:none;')));
?>
