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

//AuxLib::debugLog(get_class($model));
//AuxLib::debugLog(var_export($model->relations(),1));
$this->widget('X2Chart', array (
	'getChartDataActionName' => 
		Yii::app()->request->getScriptUrl () . '/marketing/marketing/getCampaignChartData',
	'suppressChartSettings' => true,
	'suppressDateRangeSelector' => true,
	'actionParams' => array (
		'id'=>isset ($model->listIdModel) ? $model->listIdModel->id : null,
		'modelName'=> isset ($model->listIdModel) ? $model->listIdModel->modelName : null,
	),
	'metricTypes' => array (
		'sent'=>Yii::t('app', 'Sent'),
		'opened'=>Yii::t('app', 'Opened'),
		'unsubscribed'=>Yii::t('app', 'Unsubscribed')
	),
	'chartType' => 'campaignChart',
	'getDataOnPageLoad' => true,
	'widgetParams' => array (
		'launchDate' => $model->launchDate
	),
	'hideByDefault' => $hideByDefault
)); 

?>

