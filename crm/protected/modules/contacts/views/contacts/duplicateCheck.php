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

$this->pageTitle = $newRecord->renderAttribute('name');
$authParams['X2Model'] = $newRecord;

$menuOptions = array(
    'all', 'lists', 'create', 'view',
);
$this->insertMenu($menuOptions, null, $authParams);

?>
<h1><span style="color:#f00;font-weight:bold;margin-left: 5px;"><?php echo Yii::t('app', 'This record may be a duplicate!'); ?></span></h1>
<div class="page-title rounded-top"><h2><span class="no-bold"><?php echo Yii::t('app', 'You Entered:'); ?></span> <?php echo $newRecord->renderAttribute('name'); ?></h2>
    <?php
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams) && $ref != 'create')
        echo CHtml::link(Yii::t('app', 'Edit'), $this->createUrl('update', array('id' => $newRecord->id)), array('class' => 'x2-button'));
    ?>
</div>
<?php $this->renderPartial('application.components.views._detailView', array('model' => $newRecord, 'modelName' => 'contacts')); ?>
<div class="buttons">
    <?php
    echo "<span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('contacts', "Keep This Record"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
        'type' => 'POST',
        'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => null),
        'success' => 'function(data){
		window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
	}'
            ), array(
        'class' => 'x2-button highlight'
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams)) {
        echo "<span style='float:left'>";
        if ($count < 100) {
            echo CHtml::ajaxButton(Yii::t('contacts', "Keep + Hide Others"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
                'type' => 'POST',
                'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => 'hideAll'),
                'success' => 'function(data){
                window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
            }'
                    ), array(
                'class' => 'x2-button highlight',
                'confirm' => Yii::t('contacts', 'Are you sure you want to hide all other records?')
            ));
        } else {
            echo CHtml::link(Yii::t('contacts', 'Keep + Hide Others'), '#', array(
                'class' => 'x2-button x2-hint',
                'style' => 'margin-top:5px;color:black;',
                'title' => Yii::t('contacts', 'This operation is disabled because the data set is too large.'),
                'onclick' => 'return false;'
            ));
        }
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess('ContactsDelete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Keep + Delete Others"), $this->createUrl('/contacts/contacts/ignoreDuplicates'), array(
            'type' => 'POST',
            'data' => array('data' => json_encode($newRecord->attributes), 'ref' => $ref, 'action' => 'deleteAll'),
            'success' => 'function(data){
            window.location="' . $this->createUrl('/contacts/contacts/view') . '?id="+data;
        }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to delete all other records?')
        ));
        echo "</span>";
    }
    ?>
</div>
<div style="clear:both;"></div>
<br>
<?php
if ($count > count($duplicates)) {
    echo "<div style='margin-bottom:10px;margin-left:15px;'>";
    echo "<h2 style='color:red;display:inline;'>" .
    Yii::t('contacts', '{dupes} records shown out of {count} records found.', array(
        '{dupes}' => count($duplicates),
        '{count}' => $count,
    ))
    . "</h2>";
    echo CHtml::link(Yii::t('app', 'Show All'), "?showAll=true", array('class' => 'x2-button', 'confirm' => Yii::t('contacts', 'WARNING: loading too many records on this page may tie up the server significantly. Are you sure you want to continue?')));
    echo "</div>";
}
foreach ($duplicates as $duplicate) {
    echo '<div id="' . $duplicate->firstName . '-' . $duplicate->lastName . '-' . $duplicate->id . '">';
    echo '<div class="page-title rounded-top"><h2><span class="no-bold">', Yii::t('app', 'Possible Match:'), '</span> ';
    echo $duplicate->name, '</h2></div>';

    $this->renderPartial('application.components.views._detailView', array('model' => $duplicate, 'modelName' => 'contacts'));
    echo "<div style='margin-bottom:10px;'><span style='float:left'>";
    echo CHtml::ajaxButton(Yii::t('contacts', "Keep This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
        'type' => 'POST',
        'data' => array('ref' => $ref, 'action' => null, 'id' => $duplicate->id, 'newId' => $newRecord->id),
        'success' => 'function(data){
            window.location="' . $this->createUrl('/contacts/contacts/view') . '?id=' . $duplicate->id . '";
        }'
            ), array(
        'class' => 'x2-button highlight'
    ));
    echo "</span>";
    if (Yii::app()->user->checkAccess('ContactsUpdate', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Hide This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
            'type' => 'POST',
            'data' => array('ref' => $ref, 'action' => 'hideThis', 'id' => $duplicate->id, 'newId' => $newRecord->id),
            'success' => 'function(data){
                $("#' . $duplicate->firstName . "-" . $duplicate->lastName . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to hide this record?')
        ));
        echo "</span>";
    }
    if (Yii::app()->user->checkAccess('ContactsDelete', $authParams)) {
        echo "<span style='float:left'>";
        echo CHtml::ajaxButton(Yii::t('contacts', "Delete This Record"), $this->createUrl('/contacts/contacts/discardNew'), array(
            'type' => 'POST',
            'data' => array('ref' => $ref, 'action' => 'deleteThis', 'id' => $duplicate->id, 'newId' => $newRecord->id),
            'success' => 'function(data){
                $("#' . $duplicate->firstName . "-" . $duplicate->lastName . '-' . $duplicate->id . '").hide();
            }'
                ), array(
            'class' => 'x2-button highlight',
            'confirm' => Yii::t('contacts', 'Are you sure you want to delete this record?'),
        ));
        echo "</span></div>";
    }
    echo "</div><br><br>";
}
