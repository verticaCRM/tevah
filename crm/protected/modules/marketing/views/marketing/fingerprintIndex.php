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

/* @edition:pla */

$this->pageTitle = Yii::t('marketing','Fingerprints');
$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'weblead', 'webtracker', 'anoncontacts',
    'fingerprints', 'x2flow',
);
$this->insertMenu($menuOptions);

?>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
	echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}

$this->widget('X2GridView', array(
	'id'=>'fingerprint-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'title'=>Yii::t('marketing','Fingerprints'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon marketing x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}'.
        '{massActionButtons}'.
        '{summary}{topPager}{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$model->search(),
	// 'enableSorting'=>false,
	// 'model'=>$model,
	'filter'=>$model,
	// 'columns'=>$columns,
	'modelName'=>'Fingerprint',
	'viewName'=>'fingerprint',
	// 'columnSelectorId'=>'contacts-column-selector',
	'defaultGvSettings'=>array(
        'gvCheckbox' => 30,
        'fingerprint' => 160,
        'userAgent' => 140,
        'language' => 50,
        'plugins' => 100,
        'javaEnabled' => 60,
        'cookiesEnabled' => 60,
        'screenRes' => 80,
        'timezone' => 140,
        'anonymous' => 80,
        'createDate' => 80,
	),
	'specialColumns'=>array(
        'fingerprint' => array(
            'name'=>'fingerprint',
            'value'=>array($model, 'renderContactLink'),
        ),
        'timezone' => array(
            'name'=>'timezone',
            'header'=>Yii::t('marketing', 'Timezone'),
            'value'=>'$data->timezoneString',
            'type'=>'raw',
        )
//		'name'=>array(
//			'name'=>'name',
//			'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
//			'type'=>'raw',
//		),
//		'description'=>array(
//			'name'=>'description',
//			'header'=>Yii::t('marketing','Description'),
//			'value'=>'Formatter::trimText($data->description)',
//			'type'=>'raw',
//		),
	),
        'massActions'=>array('MassDelete'),
	'enableControls'=>false,
	'fullscreen'=>true,
));

?>
