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

$menuOptions = array(
    'index', 'upload',
);
$this->insertMenu($menuOptions);

// init qtip for media filenames
Yii::app()->clientScript->registerScript('media-qtip', '
function refreshQtip() {
	$(".media-name").each(function (i) {
		var mediaId = $(this).attr("href").match(/\\d+$/);

		if(mediaId !== null && mediaId.length) {
			$(this).qtip({
				content: {
					text: "'.addslashes(Yii::t('app','loading...')).'",
					ajax: {
						url: yii.scriptUrl+"/media/qtip",
						data: { id: mediaId[0] },
						method: "get"
					}
				},
				style: {
				}
			});
		}
	});
}

$(function() {
	refreshQtip();
});
');

$this->widget('X2GridView', array(
	'id' => 'media-grid',
	'title'=>Yii::t('media','Media & File Library'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize','showHidden'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon media x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        /* x2prostart */'{massActionButtons}'./* x2proend */
        '{summary}{topPager}{items}{pager}',
	'dataProvider' => $model->search(),
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'fixedHeader'=>true,
	'filter'=>$model,
    'gvSettingsName' => 'media-index',
	'defaultGvSettings'=>array(
		'fileName' => 285,
		'name' => 114,
		'associationType' => 85,
		'createDate' => 94,
		'uploadedBy' => 114,
		'filesize' => 75,
	),
	'modelName'=>'Media',
	'specialColumns' => array(
		'fileName' => array(
			'name' => 'fileName',
			'header' => Yii::t('media','File Name'),
			'type' => 'raw',
			'value' => '$data["drive"]?CHtml::link($data["name"],array("view","id"=>$data->id), array("class" => "media-name")):CHtml::link(CHtml::encode($data["fileName"]), array("view","id"=>$data->id), array("class" => "media-name"))',
		),
		'uploadedBy' => array(
			'name' => 'uploadedBy',
			'header' => Yii::t('media','Uploaded By'),
			'type' => 'raw',
			'value' => 'User::getUserLinks($data["uploadedBy"])'
		),
		'associationType' => array(
			'name' => 'associationType',
			'header' => Yii::t('media','Association'),
			'type' => 'raw',
			'value' => 'CHtml::encode($data["associationType"])'
		),
		'createDate' => array(
			'name' => 'createDate',
			'header' => Yii::t('media','Create Date'),
			'type' => 'raw',
			'value' => 'Formatter::formatLongDate($data->createDate)'
		),
		'filesize' => array(
			'name' => 'filesize',
			'header' => Yii::t('media','File Size'),
			'type' => 'raw',
			'value' => '$data->fmtSize'
		),
		'dimensions' => array(
			'name' => 'dimensions',
			'header' => Yii::t('media','Dimensions'),
			'type' => 'raw',
			'value' => '$data->fmtDimensions'
		),
	),
	'fullscreen'=>true,
));

?>
