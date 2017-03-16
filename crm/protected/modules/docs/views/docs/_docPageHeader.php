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

$pieces = explode(", ",$model->editPermissions);
$user = Yii::app()->user->getName();

$authParams = array('X2Model' => $model);
$menuOptions = array(
    'index', 'create', 'createEmail', 'createQuote',
);
$action = $this->action->id;
if (!$model->isNewRecord) {
    $existingRecordMenuOptions = array(
        'view', 'permissions', 'exportToHtml',
    );
    if ($model->checkEditPermission() && $action != 'update')
        $existingRecordMenuOptions[] = 'edit';
    if (Yii::app()->user->checkAccess('DocsDelete', array('createdBy' => $model->createdBy)))
        $existingRecordMenuOptions[] = 'delete';
    $menuOptions = array_merge($menuOptions, $existingRecordMenuOptions);;
}
$this->insertMenu($menuOptions, $model, $authParams);

?>
<div class="page-title icon docs"><h2><span class="no-bold"><?php echo CHtml::encode($title); ?></span> <?php echo CHtml::encode($model->name); ?></h2>
<?php
if(!$model->isNewRecord){
    if($model->checkEditPermission() && $action != 'update'){
        echo X2Html::editRecordButton($model);
        // echo CHtml::link('<span></span>', array('/docs/docs/update', 'id' => $model->id), array('class' => 'x2-button x2-hint icon edit right', 'title' => Yii::t('docs', 'Edit')));
    }
    echo CHtml::link('<span></span>', array('/docs/docs/create', 'duplicate' => $model->id), array('class' => 'x2-button icon copy right', 'title' => Yii::t('docs', 'Make a copy')));
    echo "<br>\n";
}
?>
</div>
