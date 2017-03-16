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

/* @edition:pro */

$grid = $this->widget('X2GridView', array(
    'id' => 'services-grid',
	'title'=>Yii::t('services','Service Cases'),
	'buttons'=>array('columnSelector'),
	'template'=> '<div class="page-title">{title}{buttons}{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    // 'enableSorting'=>false,
    // 'model'=>$model,
    'filter' => null,
    // 'columns'=>$columns,
    'modelName' => 'Services',
    'viewName' => 'services',
    // 'columnSelectorId'=>'contacts-column-selector',
    'defaultGvSettings' => array(
        'id' => 100,
        'status' => 150,
        'impact' => 150,
        'lastUpdated' => 200,
        'updatedBy' => 200,
    //		'name'=>234,
    //		'type'=>108,
    //		'annualRevenue'=>128,
    //		'phone'=>115,
    ),
    'specialColumns' => array(
        'id' => array(
            'name' => 'id',
            'header' => Yii::t('services', 'ID'),
            'type' => 'raw',
            'value' => 'CHtml::link($data["id"], array("view","id"=>$data["id"]))',
        ),
    ),
    'enableControls' => true,
	'fullscreen'=>true,
        ));
Yii::app()->clientScript->registerScript(__CLASS__.'#'.$grid->getId().'_gvSettings',
			"$('#".$grid->getId()." table').gvSettings({
				viewName:'".$grid->viewName."',
				columnSelectorId:'".$grid->columnSelectorId."',
				columnSelectorHtml:'".addcslashes($grid->columnSelectorHtml,"'")."',
				ajaxUpdate:".($grid->ajax?'true':'false').",
			});",CClientScript::POS_READY);
?>
