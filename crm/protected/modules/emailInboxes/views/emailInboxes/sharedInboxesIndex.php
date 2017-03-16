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
    'inbox', 'sharedInboxesIndex', 'createSharedInbox',
);
$this->insertMenu ($menuOptions);

$this->widget('X2GridView', array(
	'id'=>'shared-email-inboxes-grid',
    'enableQtips' => false,
	'title'=>Yii::t('emailInboxes', 'Shared Inboxes'),
	'buttons'=>array('advancedSearch','clearFilters','columnSelector','autoResize'),
	'template'=> 
        '<div id="x2-gridview-top-bar-outer" class="x2-gridview-fixed-top-bar-outer">'.
        '<div id="x2-gridview-top-bar-inner" class="x2-gridview-fixed-top-bar-inner">'.
        '<div id="x2-gridview-page-title" '.
         'class="page-title icon emailInboxes x2-gridview-fixed-title">'.
        '{title}{buttons}{filterHint}{massActionButtons}{summary}{topPager}'.
        '{items}{pager}',
    'fixedHeader'=>true,
	'dataProvider'=>$emailInboxesDataProvider,
	'filter'=>$model,
	'pager'=>array('class'=>'CLinkPager','maxButtonCount'=>10),
	'modelName'=>'EmailInboxes',
	'viewName'=>'sharedInboxesIndex',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'name' => 125,
		'credentialId' => 165,
        'assignedTo' => 125,
		'gvControls' => 73,
	),
    'specialColumns' => array (
        'gvControls' => array (
            'template' => '{update}{delete}',
            'buttons' => array (
                'update' => array (
                    'url' => 
                        'Yii::app()->controller->createUrl (
                            "updateSharedInbox", array ("id" => $data->id));',
                ),
                'delete' => array (
                    'url' => 
                        'Yii::app()->controller->createUrl (
                            "deleteSharedInbox", array ("id" => $data->id));',
                ),
            ),
            'id' => 'C_gvControls',
            'class' => 'X2ButtonColumn',
            'header' => Yii::t('app','Tools'),
        ),
        'name' => array (
            'name' => 'name',
            'header' => Yii::t('app', 'Name'),
            'value' => 'CHtml::link (
                $data->name,
                Yii::app()->controller->createUrl ("updateSharedInbox", array (  
                    "id" => $data->id
                )))',
            'type' => 'raw',
        ),
        'credentialId' => array (
            'name' => 'credentialId',
            'header' => Yii::t('app', 'Email Credentials') ,
			'value'=>'$data->credentialsName',
			'type'=>'raw',
        )
    ),
    'massActions'=>array(
        'MassDelete', 'MassUpdateFields',
    ),
	'enableControls'=>true,
	'enableTags'=>false,
	'fullscreen'=>true,
));
?>

