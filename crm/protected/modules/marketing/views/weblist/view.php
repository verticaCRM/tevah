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

$this->pageTitle = $model->name;

$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'lists', 'newsletters', 'view', 'edit', 'delete', 'weblead', 'webtracker',
);
/* x2plastart */
$plaOptions = array(
    'anoncontacts', 'fingerprints'
);
$menuOptions = array_merge($menuOptions, $plaOptions);
/* x2plaend */
$this->insertMenu($menuOptions, $model, $authParams);


?>


<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
} ?>

<div>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'contacts-grid',
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template'=> '<div class="page-title icon marketing"><h2>'.$model->name.'</h2>'
        .CHtml::link(
            Yii::t('marketing','Email Entire List'),
            Yii::app()->createUrl('/marketing/marketing/create',array('Campaign[listId]'=>$model->nameId)),
            array('class'=>'x2-button right')
        )
        .'<a class="x2-button right" href="javascript:void(0);" '.
          'onclick="$(\'html,body\').animate({scrollTop: $(\'#webform\').offset().top});">'
          .Yii::t('marketing','Create Web Form') 
        .'</a>'
        .'<div class="title-bar">{summary}</div></div>{items}{pager}',
    'dataProvider'=>$model->statusDataProvider(20),
    'columns'=>array(
        array(
            'name'=>'emailAddress',
            'header'=>Yii::t('contacts','Email'),
            'headerHtmlOptions'=>array('style'=>'width: 20%;'),
        ),    
        array(
            'name'=>'name',
            'header'=>Yii::t('contacts','Name'),
            'headerHtmlOptions'=>array('style'=>'width: 15%;'),
            'value'=>'CHtml::link($data["firstName"] . " " . '.
                '$data["lastName"],array("/contacts/contacts/view","id"=>$data["contactId"]))',
            'type'=>'raw',
        ),
        array(
            'header'=>Yii::t('marketing','Unsubscribed'),
            'class'=>'CCheckBoxColumn',
            'checked'=>'$data["unsubscribed"] != 0',
            'selectableRows'=>0,
            'htmlOptions'=>array('style'=>'text-align: center;'),
            'headerHtmlOptions'=>array('style'=>'width: 9%;')
        ),
        array(
            'name'=>'remove',
            'header'=>'',
            'value'=>'CHtml::link ("<div class=\'fa fa-times x2-delete-icon\'></div>",
                array("removeFromList",
                    "email" => $data["emailAddress"],
                    "lid" => $data["listId"]),
                array(
                    "confirm" => Yii::t("marketing", "Are you sure you want to remove this ".
                        "email address from the list?")
                ))',
            'type'=>'raw',
        ),
    ),
));
?>
</div>

<div class="span-12" style="margin-top: 23px;">
<a id="webform"></a>
<?php 
if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
    $condition = ' AND t.visibility="1" OR t.assignedTo="Anyone" OR t.assignedTo="'.
        Yii::app()->user->getName().'"';
    /* x2temp */
    $groupLinks = 
        Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where(
            'userId='.Yii::app()->user->getId())->queryColumn();
    if(!empty($groupLinks)) {
        $condition .= ' OR t.assignedTo IN ('.implode(',',$groupLinks).')';
    }

    $condition .= ' OR (t.visibility=2 AND t.assignedTo IN 
        (SELECT username FROM x2_group_to_user WHERE groupId IN
        (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
} else {
    $condition='';
}
$forms = WebForm::model()->findAll('type="weblist"'.$condition);
$this->renderPartial('application.components.views._createWebForm', 
    array(
        'forms'=>$forms, 
        'id'=>$model->id,
        'webFormType'=>'weblist'
    )
);
?>
</div>

<div class="span-12" style="margin-top: 23px;">
<?php

if (isset($_GET['history'])) {
    $history = $_GET['history'];
} else {
    $history = "all";
}

$this->widget('zii.widgets.CListView', array(
    'id'=>'campaign-history',
    'dataProvider'=>$this->getHistory($model),
    'itemView'=>'application.modules.actions.views.actions._view',
    'htmlOptions'=>array('class'=>'action list-view'),
    'template'=> 
        ($history == 'all' ? '<h3>'.Yii::t('app','History')."</h3>" : 
            CHtml::link(
                Yii::t('app','History'),
                'javascript:$.fn.yiiListView.update("campaign-history", {data: "history=all"})')).
        " | ".
        ($history=='actions' ? '<h3>'.Yii::t('app','Actions')."</h3>" : 
            CHtml::link(
                Yii::t('app','Actions'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=actions"})')).
        " | ".
        ($history=='comments' ? '<h3>'.Yii::t('app','Comments')."</h3>" : 
            CHtml::link(
                Yii::t('app','Comments'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=comments"})')).
        " | ".
        ($history=='attachments' ? '<h3>'.Yii::t('app','Attachments')."</h3>" : 
            CHtml::link(
                Yii::t('app','Attachments'),
                'javascript:$.fn.yiiListView.update("campaign-history", '.
                '{data: "history=attachments"})')).
        '</h3>{summary}{sorter}{items}{pager}',
));
?>
</div>
