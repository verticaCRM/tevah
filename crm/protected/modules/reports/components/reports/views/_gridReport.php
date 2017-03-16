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



$this->widget('GridReportGridView', array_merge (
    $gridViewParams, 
    array(
        'id' => 'generated-report',
        'gvSettingsName' => 'generated-report',
        /*'defaultGvSettings' => array (
            'name' => 180,
            'myModelName' => 180,
            'assignedTo' => 180,
            'createDate' => 180,
            'deletion' => 60
        ),*/
        'gridViewJSClass' => 'gridReportGridSettings',
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
            '/css/gridview',
        //'template' => '<div class="title-bar">{summary}</div>{items}{pager}',
        'template' => 
            '<div class="page-title title-bar">{reportButtons}{summary}</div>{items}{pager}',
	    //'columns' => $columns,
    )
));
$this->renderPartial (
    'application.modules.reports.components.reports.views._totalsRow', array (
        'totalsRowGridViewParams' => $totalsRowGridViewParams, 
    ));
