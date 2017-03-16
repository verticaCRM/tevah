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
include("protected/modules/bugReports/bugReportsConfig.php");

$this->actionMenu = $this->formatMenu(array(
    array('label'=>Yii::t('module','{X} List',array('{X}'=>Modules::itemDisplayName()))),
    array('label'=>Yii::t('module','Create {X}',array('{X}'=>Modules::itemDisplayName())), 'url'=>array('create')),
    array('label'=>Yii::t('module','Import {X}', array('{X}'=>Modules::itemDisplayName())),
        'url'=>array('admin/importModels', 'model'=>ucfirst($moduleConfig['moduleName'])), 'visibility'=>Yii::app()->params->isAdmin),
    array('label'=>Yii::t('module','Export {X}', array('{X}'=>Modules::itemDisplayName())),
        'url'=>array('admin/exportModels', 'model'=>ucfirst($moduleConfig['moduleName'])), 'visibility'=>Yii::app()->params->isAdmin),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('opportunities-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php

$this->widget('X2GridView', array(
	'id'=>'bugReports-grid',
	'title'=>$moduleConfig['title'],
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=> '<div class="page-title">{title}{buttons}{filterHint}{summary}</div>{items}{pager}',
	'dataProvider'=>$model->searchWithStatusFilter(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'bugReports',
	'viewName'=>'bugReports',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
		'subject'=>100,
		'severity'=>65,
		'status'=>65,
        'type'=>65,
        'description'=>180,
        'assignedTo'=>100,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
        'subject'=>array(
			'name'=>'subject',
			'value'=>'CHtml::link($data->renderAttribute("subject"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'description'=>array(
			'name'=>'description',
			'header'=>Yii::t('app','Description'),
			'value'=>'Formatter::trimText($data->renderAttribute("description"))',
			'type'=>'raw',
		),
        'severity'=>array(
            'name'=>'severity',
            'header'=>Yii::t('app','Severity'),
            'value'=>'X2Model::model("Dropdowns")->getDropdownValue(116,$data->renderAttribute("severity"))',
            'type'=>'raw',
        )
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));
 ?>
