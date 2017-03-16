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



$this->insertMenu(true);

?>
<div class='flush-grid-view'>
<?php

$this->widget('X2GridView', array(
	'id' => 'saved-reports-grid',
	'buttons'=>array('clearFilters','columnSelector','autoResize'),
	'template'=>
        '<div class="page-title">{title}{buttons}{filterHint}{massActionButtons}{summary}</div>
        {items}{pager}',
    'title' => Yii::t('charts','Saved {Reports}', array('{Reports}' => Modules::displayName())),
	'dataProvider' => $dataProvider,
	'modelName' => 'Reports',
	'defaultGvSettings'=>array(
		'gvCheckbox' => 30,
		'name' => 125,
		'type' => 100,
		'createdBy' => 83,
		'createDate' => 91,
		'gvControls' => 73,
	),
    'filter' => $model,
    'viewName' => $this->getAction ()->getId (),
    'massActions' => array(
        'MassDelete', /*'MassUpdateFields',*/
    ),
	'specialColumns' => array(
        'name'=>array(
            'name'=>'name',
            'header'=>Yii::t('reports','Name'),
            'value'=>'$data->link',
            'type'=>'raw',
        ),
        'type'=>array(
            'name'=>'type',
            'header'=>Yii::t('reports','Type'),
            'value'=>'$data->getPrettyType ()',
            'type'=>'raw',
        ),
	),
	'enableControls'=>true,
    'gvControlsTemplate' => '{view} {delete}',
));

?>
</div>
