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

#export-how-to,
#export-how-to + div {
    margin-bottom: 10px;
}


");

Yii::app()->clientScript->registerScript('importFlowJS',"

$(function () {

$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '".$this->createUrl(
        '/admin/downloadData',array('file'=>$_SESSION['flowExportFile']))."';
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
        'url' => array ('importFlow'),
    ),
    array (
        'label' => Yii::t('studio', 'Export Flow'),
    )
);

?>
<div class="page-title icon x2flow">
    <h2><?php echo Yii::t('studio', 'Export Flow'); ?></h2>
</div>
<form>
<div class='form'>
    <div id='export-how-to'>
    <?php
    echo Yii::t('studio', 'Please click the button below to begin the export. Do not close this page until the export is finished.');
    ?>
    </div>
    <div>
    <?php
    echo Yii::t('studio', 'You are currently exporting ').CHtml::link (CHtml::encode ($flow->name), $this->createUrl ('/studio/flowDesigner', array ('id' => $flow->id)));
    ?>
    </div>
    <input type='hidden' name='export' value='1'>
    <input type='hidden' name='flowId' value='<?php echo $flow->id; ?>'>
    <?php
    echo CHtml::submitButton(
        Yii::t('app','Export'), 
        array(
            'id' => 'export-flow', 
            'class' => 'x2-button',
        )
    );
    ?>
    <div style="<?php echo $download ? '' : 'display:none'; ?>" id="download-link-box">
        <?php echo Yii::t('studio','Please click the link below to download the exported flow.');?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
    </div>
</div>
</form>
