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

/**
 * Used by inline workflow widget to render the funnel 
 */


if (AuxLib::isIE8 ()) {
    Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/jqplot/excanvas.js');
}

if ($this->id !== 'Workflow')
    $assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.workflow.assets'),false,-1,YII_DEBUG?true:null);
else 
    $assetsUrl = $this->module->assetsUrl;

Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/X2Geometry.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/BaseFunnel.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $assetsUrl.'/js/InlineFunnel.js', CClientScript::POS_END);


Yii::app()->clientScript->registerScript('_funnelJS',"

x2.inlineFunnel = new x2.InlineFunnel ({
    workflowStatus: ".CJSON::encode ($workflowStatus).",
    translations: ".CJSON::encode (array (
        'Completed' => Yii::t('workflow', 'Completed'),
        'Started' => Yii::t('workflow', 'Started'),
        'Details' => Yii::t('workflow', 'Details'),
        'Revert Stage' => Yii::t('workflow', 'Revert Stage'),
        'Complete Stage' => Yii::t('workflow', 'Complete Stage'),
        'Start' => Yii::t('workflow', 'Start'),
        'noRevertPermissions' => 
            Yii::t('workflow', 'You do not have permission to revert this stage.'),
        'noCompletePermissions' => 
            Yii::t('workflow', 'You do not have permission to complete this stage.'),


    )).",
    stageCount: ".$stageCount.",
    containerSelector: '#funnel-container',
    colors: ".CJSON::encode ($colors).",
    revertButtonUrl: '".Yii::app()->theme->getBaseUrl ()."/images/icons/Uncomplete.png',
    completeButtonUrl: '".Yii::app()->theme->getBaseUrl ()."/images/icons/Complete.png',
    stageNames: ".CJSON::encode (Workflow::getStageNames ($workflowStatus)).",
    stagePermissions: ".CJSON::encode (Workflow::getStagePermissions ($workflowStatus)).",
    uncompletionPermissions: ".
        CJSON::encode (Workflow::getStageUncompletionPermissions ($workflowStatus)).",
    stagesWhichRequireComments: ".
        CJSON::encode (Workflow::getStageCommentRequirements ($workflowStatus))."
});

", CClientScript::POS_END);

?>
<div id='funnel-container'></div>
<?php



