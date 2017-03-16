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

$this->pageTitle = $newRecord->renderAttribute('name');
$authParams['X2Model'] = $newRecord;
?>
<h1><span style="color:#f00;font-weight:bold;margin-left: 5px;"><?php echo Yii::t('app', 'This record may be a duplicate!'); ?></span></h1>
<div class="page-title rounded-top"><h2> <?php echo $newRecord->renderAttribute('name'); ?></h2>
    <?php
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams) && $ref != 'create') {
        echo CHtml::link(Yii::t('app', 'Edit'), $this->createUrl($moduleName . '/update', array('id' => $newRecord->id)), array('class' => 'x2-button', 'style' => 'vertical-align:baseline;'));
    }
    ?>
</div>
<?php $this->renderPartial('application.components.views._detailView', array('model' => $newRecord, 'modelName' => $modelName)); ?>
<div class="buttons">
    <?php
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('app', "Keep"), $this->createUrl('/site/resolveDuplicates'), array(
        'type' => 'POST',
        'data' => array(
            'data' => json_encode($newRecord->attributes),
            'ref' => $ref,
            'action' => 'keepThis',
            'modifier' => null,
            'modelName' => $modelName,
        ),
        'success' => 'function(data){
		window.location="' . $this->createUrl($moduleName . '/view') . '?id="+data;
	}'
            ), array(
        'class' => 'x2-button highlight x2-hint',
        'title' => 'This record is not a duplicate.',
    ));
    echo "</span>";
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('app', "Mark as Duplicate"), $this->createUrl('/site/resolveDuplicates'), array(
        'type' => 'POST',
        'data' => array(
            'data' => json_encode($newRecord->attributes),
            'ref' => $ref,
            'action' => 'ignoreNew',
            'modifier' => null,
            'modelName' => $modelName,
        ),
        'success' => 'window.location="' . $this->createUrl($moduleName . '/index') . '"'
            ), array(
        'class' => 'x2-button highlight x2-hint',
        'title' => 'This record is a duplicate and should be hidden.',
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Delete"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'deleteNew',
                'modifier' => null,
                'modelName' => $modelName,
            ),
            'success' => 'window.location="' . $this->createUrl($moduleName . '/index') . '"'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is a duplicate and should be deleted.',
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Keep + Hide Others"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'keepThis',
                'modifier' => 'hideAll',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                window.location="' . $this->createUrl($moduleName . '/view') . '?id="+data;
            }'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is not a duplicate and all possible matches should be hidden.',
            'confirm' => Yii::t('app', 'Are you sure you want to hide all other records?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Keep + Delete Others"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'data' => json_encode($newRecord->attributes),
                'ref' => $ref,
                'action' => 'keepThis',
                'modifier' => 'deleteAll',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
            window.location="' . $this->createUrl($moduleName . '/view') . '?id="+data;
        }'
                ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 'This record is not a duplicate and all possible matches should be deleted.',
            'confirm' => Yii::t('app', 'Are you sure you want to delete all other records?')
        ));
        echo "</span>";
    }
    /* x2prostart */
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(
            Yii::t('app', "Merge Records"), $this->createUrl('/site/resolveDuplicates'), array(
                'type' => 'POST',
                'data' => array(
                    'data' => CJSON::encode ($newRecord->attributes),
                    'ref' => $ref,
                    'action' => 'mergeRecords',
                    'modelName' => $modelName,
                ),
                'success' => 'function(data){
                    window.location="' . $this->createUrl('/site/mergeRecords') .
                    '?modelName="+"' . urlencode ($modelName) . '"+"&"+data;
                }'
            ), array(
            'class' => 'x2-button highlight x2-hint',
            'title' => 
                CHtml::encode (Yii::t('app', 
                    'This record is a duplicate and all possible matches should be merged together.'
                )),
        ));
        echo "</span>";
    }
    /* x2proend */
    ?>
</div>
<div style="clear:both;"></div>
<br>
<?php
if ($count > count($duplicates)) {
    echo "<div style='margin-bottom:10px;margin-left:15px;'>";
    echo "<h2 style='color:red;display:inline;'>" .
    Yii::t('app', '{dupes} records shown out of {count} records found.', array(
        '{dupes}' => count($duplicates),
        '{count}' => $count,
    ))
    . "</h2>";
    echo CHtml::link(Yii::t('app', 'Show All'), "?showAll=true", array('class' => 'x2-button', 'confirm' => Yii::t('app', 'WARNING: loading too many records on this page may tie up the server significantly. Are you sure you want to continue?')));
    echo "</div>";
}
foreach ($duplicates as $duplicate) {
    echo '<div id="' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '">';
    echo '<div class="page-title rounded-top"><h2><span class="no-bold">', Yii::t('app', 'Possible Match:'), '</span> ';
    echo $duplicate->name, '</h2></div>';

    $this->renderPartial('application.components.views._detailView', array('model' => $duplicate, 'modelName' => $moduleName));
    echo "<div style='margin-bottom:10px;'>";
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Hide This"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'ref' => $ref,
                'action' => null,
                'data' => json_encode($duplicate->attributes),
                'modifier' => 'hideThis',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                $("#' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('app', 'Are you sure you want to hide this record?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Delete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('app', "Delete This"), $this->createUrl('/site/resolveDuplicates'), array(
            'type' => 'POST',
            'data' => array(
                'ref' => $ref,
                'action' => null,
                'data' => json_encode($duplicate->attributes),
                'modifier' => 'deleteThis',
                'modelName' => $modelName,
            ),
            'success' => 'function(data){
                $("#' . str_replace(' ', '-', $duplicate->name) . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('app', 'Are you sure you want to delete this record?'),
        ));
        echo "</span></div>";
    }
    echo "</div><br><br>";
}
