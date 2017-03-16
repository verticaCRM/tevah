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

$accountModule = Modules::model()->findByAttributes(array('name'=>'accounts'));
$contactModule = Modules::model()->findByAttributes(array('name'=>'contacts'));

$menuOptions = array(
    'index', 'create', 'import', 'export',
);
if ($accountModule->visible && $contactModule->visible)
    $menuOptions[] = 'quick';
$this->insertMenu($menuOptions);


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
	'id'=>'opportunities-grid',
    'title'=>Yii::t('opportunities','{opportunities}', array(
        '{opportunities}'=>Modules::displayName(),
    )),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=>
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon opportunities x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        /* x2prostart */'{massActionButtons}'./* x2proend */
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	// 'columns'=>$columns,
	'modelName'=>'Opportunity',
	'viewName'=>'opportunities',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
		'name' => 164,
		'quoteAmount' => 95,
		'probability' => 77,
		'expectedCloseDate' => 125,
		'createDate' => 78,
		'lastActivity' => 79,
		'assignedTo' => 119,
	),
	'specialColumns'=>array(
		'name'=>array(
			'name'=>'name',
			'header'=>Yii::t('opportunities','Name'),
			'value'=>'CHtml::link($data->renderAttribute("name"),array("view","id"=>$data->id))',
			'type'=>'raw',
		),
        'id'=>array(
			'name'=>'id',
			'header'=>Yii::t('opportunities','ID'),
			'value'=>'CHtml::link($data->id,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
	),
	'enableControls'=>true,
	'fullscreen'=>true,
));

?>
