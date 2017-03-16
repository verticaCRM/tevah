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

if (!isset ($data)) $data = array ();
if (!isset ($report)) $report = null;

Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->getAssetsUrl ().
    '/css/reports.css');
Yii::app()->clientScript->registerX2Flashes ();

$this->insertMenu(true);

?>
<div class="reports-page-title page-title">
<h2>
<?php 
if ($report === null) {
    echo CHtml::encode (Yii::t('reports', Reports::prettyType ($type) . ' Report')); 
} else {
    echo CHtml::encode ($report->name);
}
?>
</h2>
<?php
if ($report === null) {
?>
<a id="report-settings-save-button" class="x2-button">
   <?php echo CHtml::encode (Yii::t('reports', 'Save Report')); ?>
</a>
<?php
} else {
?>
 <span id="minimize-button" 
  title="<?php echo CHtml::encode (Yii::t('reports', 'Minimize Settings')); ?> "
  class="minimize"><i class='fa fa-lg fa-caret-down'></i>
  </span>
  <a id="report-update-button" class="x2-button"  
 title="<?php echo CHtml::encode (Yii::t('reports', 'Save changes')); ?>">
   <?php echo CHtml::encode (Yii::t('reports', 'Save')); ?>
</a>
<a id="report-copy-button" 
 title="<?php echo CHtml::encode (Yii::t('reports', 'Copy this report')); ?> "
 class="x2-button icon copy"><span></span></a>


<?php
}
?>
</div>
<?php
$this->renderPartial (
    $type.'Report', array_merge ($data, array (
        'formModel' => $formModel
    )));

if($report !== null) { 
  $this->widget('ChartDashboard', array(
          'report' => $report
      )
  );
}
?>

<div id='report-container' class='x2-layout-island' style='display: none;'>
</div>

<?php
$this->widget('InlineEmailForm',
    array(
        'startHidden'=>true,
    )
);

?>
<div id='report-settings-dialog' class='form' style='display: none;'>
    <label class='left-label' for='report-settings-name'><?php 
        echo CHtml::encode (Yii::t('reports', 'Report Name: ')); ?></label>
    <input id='report-settings-name' type='text' name='reportSettingsName' />
</div>

<?php 

if ($report !== null) {
    $this->widget('ChartCreator', array(
            'report' => $report,
            'autoOpen' => isset($_GET['chart'])
        )
    );
}
?>
