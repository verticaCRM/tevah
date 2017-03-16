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

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('profiles-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

Yii::app()->clientScript->registerCss ('profilesStyle', "
    #profiles-grid .summary {
        margin-left: 5px;
    }
");

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed'),'url'=>array('index')),
	array('label'=>Yii::t('profile','People')),
));
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model, 
)); ?>
</div>
<div class='flush-grid-view'>
<?php
/*$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'profiles-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('profile','People').'</h2><div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('index','clearFilters'=>1))
		.'{summary}</div></div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'fullName',
			'value'=>'CHtml::link($data->fullName,array("view","id"=>$data->id))',
			'headerHtmlOptions'=>array('style'=>'width:35%;'),
			'type'=>'raw',
			),
		'tagLine',
	),
));*/

$this->widget('X2GridViewLess', array(
	'id'=>'profiles-grid',
	'title'=>Yii::t('profile', 'People'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon contacts x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search (),
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	'modelName'=>'Profile',
	'viewName'=>'profiles',
	'defaultGvSettings'=>array(
		'fullName' => 125,
		'tagLine' => 165,
		'isActive' => 80,
	),
    'modelAttrColumnNames'=>array (
        'tagLine', 'username', 'officePhone', 'cellPhone', 'emailAddress', 'googleId'
    ),
	'specialColumns'=>array(
		'fullName'=>array(
			'name'=>'fullName',
			'header'=>Yii::t('profile', 'Full Name'),
			'value'=>'CHtml::link($data->fullName,array("view","id"=>$data->id))',
			'type'=>'raw',
		),
		'isActive'=>array(
			'name'=>'isActive',
			'header'=>Yii::t('profile', 'Active'),
			'value'=>'"<span title=\''.
                '".(Session::isOnline ($data->username) ? '.
                 '"'.Yii::t('profile', 'Active User').'" : "'.Yii::t('profile', 'Inactive User').'")."\''.
                ' class=\'".(Session::isOnline ($data->username) ? '.
                '"active-indicator" : "inactive-indicator")."\'></span>"',
			'type'=>'raw',
		),
	),
	'enableControls'=>false,
	'fullscreen'=>true,
));
?>
</div>
