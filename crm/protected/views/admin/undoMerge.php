<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/* @edition:pro */

$columns = array(
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Record Link')),
        'name' => 'modelLink',
        'type' => 'raw',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Merged Record')),
        'name' => 'mergeModel',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Record Type')),
        'name' => 'modelType',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Involved Records')),
        'name' => 'recordCount',
    ),
    array(
        'header' => CHtml::encode(Yii::t('admin', 'Timestamp')),
        'name' => 'mergeDate',
        'type' => 'raw',
        'value' => 'Formatter::formatDateTime($data["mergeDate"])',
        'headerHtmlOptions' => array('style'=>'width:175px'),
    ),
    array(
        'type' => 'raw',
        'header' => CHtml::encode(Yii::t('admin', 'Undo Merge')),
        'name' => 'invalidUndo',
        'value' => 'CHtml::ajaxButton("' . Yii::t('admin', "Undo") . '","undoMerge",array('
        . '"type"=>"POST","data"=>array("mergeModelId"=>$data["mergeModelId"],"modelType"=>$data["modelType"]),'
        . '"success"=>"window.location = window.location"'
        . '),'
        . 'array("class"=>"x2-button","style"=>$data["invalidUndo"]?"color:grey":"","disabled"=>$data["invalidUndo"]?"disabled":""))'
    )
);

echo "<div class='page-title'><h2>" . Yii::t('admin', 'Undo Record Merge') . "</h2></div>";
echo "<div class='form'>";
echo "<div style='width:600px;'><br>";
echo Yii::t('admin', "This page allows for reverting record merges which users have performed in the app.")
 . "<br><br>";
echo Yii::t('admin', "Reverted merges will restore all original data to the original records, and delete the record that was created by the merge. Any new data on the merged record will be lost.")
 . "<br><br>";
echo Yii::t('admin', "Merged records which have been merged again into new records cannot be reverted until all record merges further down the chain are undone.");
echo "</div><br>";
echo "</div>";

echo "<div class='page-title'><h2>Record Merge Log</h2></div>";
$this->widget('application.components.X2GridView.X2GridViewGeneric', array(
    'id' => 'merge-grid',
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'filter' => $filtersForm,
));
