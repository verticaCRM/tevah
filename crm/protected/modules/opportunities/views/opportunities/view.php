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
Yii::app()->clientScript->registerCss('recordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
");
Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');

$modelType = json_encode("Opportunities");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");


$authParams['X2Model'] = $model;
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));
$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));

$menuOptions = array(
    'index', 'create', 'view', 'edit', 'share', 'delete', 'attach', 'quotes', 'import', 'export',
    'editLayout',
);
if ($contactModule->visible && $accountModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions, $model, $authParams);


$themeUrl = Yii::app()->theme->getBaseUrl();
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon opportunities">
	<?php //echo CHtml::link('['.Yii::t('contacts','Show All').']','javascript:void(0)',array('id'=>'showAll','class'=>'right hide','style'=>'text-decoration:none;')); ?>
	<?php //echo CHtml::link('['.Yii::t('contacts','Hide All').']','javascript:void(0)',array('id'=>'hideAll','class'=>'right','style'=>'text-decoration:none;')); ?>
	<h2><span class="no-bold"><?php echo Yii::t('opportunities','{opportunity}:', array('{opportunity}'=>Modules::displayName(false))); ?> </span><?php echo CHtml::encode($model->name); ?></h2>
	<?php echo X2Html::editRecordButton($model); ?>
    <?php if ((bool) $model->contactName) {
        echo X2Html::emailFormButton();        
    }
    echo X2Html::inlineEditButtons();
    ?>
</div>
</div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>

<?php
$this->beginWidget('CActiveForm', array(
    'id'=>'contacts-form',
    'enableAjaxValidation'=>false,
    'action'=>array('saveChanges','id'=>$model->id),
));

$this->renderPartial('application.components.views._detailView',array('model'=>$model,'modelName'=>'Opportunity'));
$this->endWidget();

$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'modelName' => 'Opportunities',
        'modelId' => $model->id,
        'targetModel' => $model,
    ),
    'startHidden' => true,
));


// $this->widget('InlineTags', array('model'=>$model));

// render workflow box
// $this->renderPartial('application.components.views._workflow',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
// $this->widget('WorkflowStageDetails',array('model'=>$model,'modelName'=>'opportunities','currentWorkflow'=>$currentWorkflow));
?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'account' => $model->getLinkedAttribute('accountName', 'name'),
            'modelName' => X2Model::getModuleModelName ()
        ));
        ?>
    </div>

<?php 
if((bool) $model->contactName){ // if associated contact exists, setup inline email form
    $contact = $model->getLinkedModel('contactName');
    if ($contact) {
        $this->widget('InlineEmailForm', array(
            'attributes' => array(
                'to' => '"'.$contact->name.'" <'.$contact->email.'>, ',
                'modelName' => 'Opportunity',
                'modelId' => $model->id,
            ),
            'startHidden' => true,
        ));
    }
}

$this->widget(
    'Attachments',
    array(
        'associationType'=>'opportunities','associationId'=>$model->id,
        'startHidden'=>true
    )
); 

?>
</div>
<?php
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block'=>'center',
        'model'=>$model,
        'modelType'=>'opportunities'
    ));
