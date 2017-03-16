<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
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


if (AuxLib::isIE8 ()) {
    Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jqplot/excanvas.js');
}

Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/X2Geometry.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/BaseFunnel.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/Funnel.js', CClientScript::POS_END);

Yii::app()->clientScript->registerScript('_funnelJS',"

x2.funnel = new x2.Funnel ({
    workflowStatus: ".CJSON::encode ($workflowStatus).",
    translations: ".CJSON::encode (array (
        'Total Records' => Yii::t('workflow', 'Total Records'),
        'Total Amount' => Yii::t('workflow', 'Total Amount'),
    )).",
    stageCount: ".$stageCount.",
    recordsPerStage: ".CJSON::encode ($recordsPerStage).",
    stageValues: ".CJSON::encode ($stageValues).",
    totalValue: '".addslashes ($totalValue)."',
    containerSelector: '#funnel-container',
    stageNameLinks: ".CJSON::encode ($stageNameLinks).",
    colors: ".CJSON::encode ($colors).",
});

", CClientScript::POS_END);

?>
<div id='funnel-container'></div>
<?php
