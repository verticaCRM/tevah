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

Yii::app()->clientScript->registerCss('workflowIndexCss',"

#workflow-grid .page-title.workflow .x2-grid-view-controls-buttons {
    position: relative;
    top: -4px;
}

");

$this->setPageTitle(Yii::t('workflow', '{process}', array(
    '{process}' => Modules::displayName(false)
)));

$menuOptions = array(
    'index', 'create',
);
$this->insertMenu($menuOptions);

?>
<div class='flush-grid-view'>
<?php

$this->widget('X2GridViewGeneric', array(
    'id' => 'workflow-grid',
	'dataProvider'=>$dataProvider,
    'baseScriptUrl'=>  
        Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'title'=>Yii::t('workflow','{processes}', array(
        '{processes}' => Modules::displayName())),
    'template'=> '<div class="page-title icon workflow">{title}'.
        '{buttons}{summary}</div>{items}{pager}',
	'summaryText' => Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'buttons' => array ('autoResize'),
	'enableSorting'=>false,
	'gvSettingsName'=>'workflowIndex',
    'defaultGvSettings' => array (
        'name' => 240,
        'isDefault' => 100,
        'stages' => 100,
    ),
	'columns'=>array(
		array(
			'name'=>'name',
			'value'=>'CHtml::link(CHtml::encode($data->name),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		array(
			'name'=>'isDefault',
			'value'=>'$data->isDefault? Yii::t("app","Yes") : ""',
			'type'=>'raw',
		),
		array(
			'header'=>Yii::t('workflow','Stages'),
			'name'=>'stages',
			'value'=>'X2Model::model("WorkflowStage")->countByAttributes(array("workflowId"=>$data->id))',
			'type'=>'raw',
		),
	),
)); ?>
</div>
