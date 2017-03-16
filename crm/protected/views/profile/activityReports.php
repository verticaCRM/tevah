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
$this->insertActionMenu();
// $this->actionMenu = array(
//     array('label' => Yii::t('profile', 'View Profile'), 'url' => array('view', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Edit Profile'), 'url' => array('update', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Change Settings'), 'url' => array('settings'),),
//     array('label' => Yii::t('profile', 'Change Password'), 'url' => array('changePassword', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Manage Apps'), 'url' => array('manageCredentials')),
//     array('label' => Yii::t('profile', 'Manage Email Reports')),
// );

$this->widget('X2GridViewGeneric', array(
    'id' => 'email-reports-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
    'template' => '<div class="page-title icon profile"><h2>' . CHtml::encode(Yii::t('profile', 'Manage Email Reports')) . '</h2>'
    . '{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        'name' => array(
            'name'=>'name',
            'header' => Yii::t('profile','Name'),
        ),
        'schedule' => array(
            'name'=>'schedule',
            'header'=>Yii::t('profile','Schedule'),
        ),
        'active' => array(
            'name' => 'active',
            'header' => Yii::t('profile','Active'),
            'type' => 'raw',
            'value' => 'CHtml::tag("span",array("id"=>$data->id."-status"),$data->cronEvent->recurring?Yii::t("profile","Yes"):Yii::t("profile","No"))'
        ),
        'lastSent' => array(
            'name' => 'lastSent',
            'header' => Yii::t('profile','Last Sent'),
            'type' => 'raw',
            'value' => 'isset($data->cronEvent->lastExecution)?Formatter::formatLongDateTime($data->cronEvent->lastExecution):Yii::t("profile","Never")',
        ),
        'controls' => array(
            'name' => 'controls',
            'header' => Yii::t('profile','Controls'),
            'type' => 'raw',
            'value' => 'CHtml::ajaxButton($data->cronEvent->recurring?Yii::t("profile","Stop"):Yii::t("profile","Start"), "toggleEmailReport", array("success"=>"function(html){jQuery(\"#".$data->id."-status\").html(html==0?\"Yes\":\"No\");jQuery(\"#".$data->id."-toggle-button\").val(html==0?\"Stop\":\"Start\")}","data"=>array("id"=>$data->id)), array("id"=>$data->id."-toggle-button","class"=>"x2-button","style"=>"float:left"))' .
            '.CHtml::ajaxButton(Yii::t("profile","Delete"),"deleteEmailReport",array("complete"=>"$.fn.yiiGridView.update(\'email-reports-grid\')","data"=>array("id"=>$data->id)),array("class"=>"x2-button","style"=>"float:right"))',
        ),
    ),
));
?>