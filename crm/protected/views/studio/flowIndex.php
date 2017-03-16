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

Yii::app()->clientScript->registerCss('flowIndexCss',"

#flow-grid {
    border-bottom: 1px solid rgb(219, 219, 219);
}

#create-flow-button {
    margin-left: 3px;
    margin-bottom: 3px;
}

");

$this->actionMenu = array(
	array('label'=>Yii::t('studio','Manage Flows')),
	array(
        'label'=>Yii::t('studio','Create Flow'),
        'url'=>array('flowDesigner'),
        'visible'=>Yii::app()->contEd('pro'),
    ),
    array (
        'label' => Yii::t('studio', 'All Trigger Logs'),
        'url' => array ('triggerLogs'),
        'visible' => Yii::app()->contEd('pro')
    ),
    /* x2plastart */ 
    array (
        'label' => Yii::t('studio', 'Import Flow'),
        'url' => array ('importFlow'),
        'visible' => Yii::app()->contEd('pro')
    ),
    /* x2plaend */ 
);

?>
<div class="flush-grid-view">
<?php

$this->widget('X2GridViewGeneric', array(
	'id'=>'flow-grid',
	'buttons'=>array('clearFilters','autoResize'),
	'baseScriptUrl'=>
        Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template'=>
        '<div class="page-title icon x2flow">'.
        '<h2>'.Yii::t('studio','X2Flow Automation Rules').'</h2>{buttons}'.
        '{summary}</div>{items}{pager}',
    'dataProvider'=>CActiveRecord::model('X2Flow')->search(),
    'defaultGvSettings' => array (
        'name' => 90,
        'active' => 90,
        'triggerType' => 90,
        'modelClass' => 90,
        'createDate' => 60,
        'lastUpdated' => 60,
    ),
    'gvSettingsName' => 'flow-grid',
    'viewName' => 'flowIndex',
	'columns'=>array(
		array(
			'name'=>'name',
			'headerHtmlOptions'=>array('style'=>'width:40%'),
			'value'=>'CHtml::link(CHtml::encode($data->name),array("/studio/flowDesigner","id"=>$data->id))',
			'type'=>'raw',
		),
		array(
			'name'=>'active',
			'headerHtmlOptions'=>array('style'=>'width:8%'),
			'value'=>'$data->active? Yii::t("app","Yes") : Yii::t("app","No")',
			'type'=>'raw',
		),
		array(
			'name'=>'triggerType',
			'headerHtmlOptions'=>array('style'=>'width:15%'),
			'value'=>'X2FlowTrigger::getTriggerTitle ($data->triggerType)',
			'type'=>'raw',
		),
		array(
			'name'=>'modelClass',
			'headerHtmlOptions'=>array('style'=>'width:10%'),
		),
		// 'flow',
		array(
			'name'=>'createDate',
			'header'=>Yii::t('admin','Create Date'),
            'headerHtmlOptions'=>array('style'=>'width:12%'),
			'value'=>'Formatter::formatDateTime($data->createDate)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
		array(
			'name'=>'lastUpdated',
			'header'=>Yii::t('admin','Last Updated'),
            'headerHtmlOptions'=>array('style'=>'width:12%'),
			'value'=>'Formatter::formatDateTime($data->lastUpdated)',
			'type'=>'raw',
			// 'htmlOptions'=>array('width'=>'20%'),
		),
	),
));
?>
</div>
<br>
<?php
if(Yii::app()->contEd('pro')) {
	echo CHtml::link(
        Yii::t('studio','Create New Flow'),
        array('/studio/flowDesigner'),
        array(
            'class'=>'x2-button',
            'id'=>'create-flow-button'
        ));
}
?>
