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

Yii::app()->clientScript->registerCss('importFlowCss',"

#upload-how-to {
    margin-bottom: 20px;
}

");

Yii::app()->clientScript->registerScript('importFlowJS',"

$(function () {

$('#submit-flow').click (function (e) {
    return x2.fileUtil.validateFile ($('#flow-upload'), ['json'], this);
});

});
");

$this->actionMenu = array(
	array('label'=>Yii::t('studio','Manage Flows'), 'url' => array ('flowIndex')),
	array(
        'label'=>Yii::t('studio','Create Flow'),
        'url'=>array('flowDesigner'),
        'visible'=>Yii::app()->contEd('pro'),
    ),
    array (
        'label' => Yii::t('studio', 'All Trigger Logs'),
        'url' => array ('triggerLogs'),
        'visible' => Yii::app()->contEd('pro')
    ),
    array (
        'label' => Yii::t('studio', 'Import Flow'),
    ),
);

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/FileUtil.js', 
    CClientScript::POS_END);

?>
<div class="page-title icon x2flow">
    <h2><?php echo Yii::t('studio', 'Import Flow'); ?></h2>
</div>
<?php
echo CHtml::form(
    array ('/studio/importFlow'),
    'post', 
    array(
        'enctype' => 'multipart/form-data'
    )
);
?>
<div class='form'>
    <?php
    if ($model && YII_DEBUG) echo CHtml::errorSummary ($model);
    echo X2Html::getFlashes (); 
    ?>
    <div id='upload-how-to'>
    <?php
    echo Yii::t('studio', 'Upload a flow that has been exported using the X2Flow export tool.');
    ?>
    </div>
    <?php
    echo CHtml::fileField(
        'flowImport', 
        '', 
        array (
            'id' => 'flow-upload',
            'onchange' => 'x2.fileUtil.validateFile (this, ["json"], $("#submit-flow"));'
        )
    );
    echo CHtml::submitButton(
        Yii::t('app','Submit'), 
        array(
            'id' => 'submit-flow', 
            'class' => 'x2-button',
        )
    );
    ?>
</div>
<?php
echo CHtml::endForm();
