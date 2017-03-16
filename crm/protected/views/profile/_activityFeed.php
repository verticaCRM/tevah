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

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/activityFeed.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/EnlargeableImage.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/jquery-expander/jquery.expander.js', CClientScript::POS_END);

// used for rich editing in new post text field
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/emailEditor.js');


Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/lib/moment-with-locales.min.js');


$groups = Groups::getUserGroups(Yii::app()->user->getId());
$tempUserList = array();
foreach($groups as $groupId){
    $userLinks = GroupToUser::model()->findAllByAttributes(array('groupId'=>$groupId));
    foreach($userLinks as $link){
        $user = User::model()->findByPk($link->userId);
        if(isset($user)){
            $tempUserList[] = $user->username;
        }
    }
}

$userList = array_keys(User::getNames());
$tempUserList = array_diff($userList,$tempUserList);
$usersGroups = implode(",",$tempUserList);

Yii::app()->clientScript->registerScript('setUpActivityFeedManager', "

x2.activityFeed = new x2.ActivityFeed ({
    translations: ".CJSON::encode (array (
        'Unselect All' => Yii::t('app','Unselect All'),
        'Select All' => Yii::t('app','Select All'),
        'Uncheck All' => Yii::t('app','Uncheck All'),
        'Check All' => Yii::t('app','Check All'),
        'Enter text here...' => Yii::t('app','Enter text here...'),
        'Broadcast Event' => Yii::t('app','Broadcast Event'),
        'Make Important' => Yii::t('app','Make Important'),
        'Broadcast' => Yii::t('app','Broadcast'),
        'broadcast error message 1' => Yii::t('app','Select at least one user to broadcast to'),
        'broadcast error message 2' => Yii::t('app','Select at least one broadcast method'),
        'Okay' => Yii::t('app','Okay'),
        'Nevermind' => Yii::t('app','Cancel'),
        'Create' => Yii::t('app','Create'),
        'Cancel' => Yii::t('app','Cancel'),
        'Read more' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'More'),
        'Read less' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'Less'),
    )).",
    usersGroups: '".$usersGroups."',
    minimizeFeed: ".(Yii::app()->params->profile->minimizeFeed==1?'true':'false').",
    commentFlag: false,
    lastEventId: ".(!empty($lastEventId)?$lastEventId:0).",
    lastTimestamp: ".(!empty($lastTimestamp)?$lastTimestamp:0).",
    profileId: ".$profileId.",
    myProfileId: ".Yii::app()->params->profile->id.",
    deletePostUrl: '".$this->createUrl('/profile/deletePost')."'
});

", CClientScript::POS_END);
?>

<div id='activity-feed-container' class='x2-layout-island'>
<div id='page-title-container'>
    <div class="page-title icon rounded-top activity-feed">
        <h2><?php echo Yii::t('app','Activity Feed'); ?></h2>
        <span title='<?php echo Yii::t('app', 'Feed Settings'); ?>'>
        <?php
        echo X2Html::settingsButton (Yii::t('app', 'Feed Settings'), 
            array ('id' => 'activity-feed-settings-button'));
        ?>
        </span>
        <a href='#' id='feed-filters-button' 
         class='filter-button right'>
            <span class='fa fa-filter'></span>
        </a>
        <div id="menu-links" class="title-bar" style='display: none;'>
            <?php
            echo CHtml::link(
                Yii::t('app','Toggle Comments'),'#',
                array('id'=>'toggle-all-comments','class'=>'x2-button x2-minimal-button right'));
            echo CHtml::link(
                Yii::t('app','Restore Posts'),'#',
                array('id'=>'restore-posts','style'=>'display:none;',
                    'class'=>'x2-button x2-minimal-button right'));
            echo CHtml::link(
                Yii::t('app','Minimize Posts'),
                '#',array('id'=>'min-posts','class'=>'x2-button x2-minimal-button right'));
            ?>
        </div>
    </div>
</div>

<?php
$this->renderPartial ('_feedFilters');
?>

<div class="form" id="post-form" style="clear:both">
    <?php $feed=new Events; ?>
    <?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'feed-form',
    'enableAjaxValidation'=>false,
    'method'=>'post',
    )); ?>
    <div class="float-row" style='overflow:visible;'>
        <?php
        echo $form->textArea($feed,'text',array('style'=>'width:99%;height:25px;color:#aaa;display:block;clear:both;'));
        echo "<div id='post-buttons' style='display:none;'>";
        echo $form->dropDownList($feed,'associationId',$users, 
            array (
                'style' => ($isMyProfile ? '' : 'display:none;'),
                'class' => 'x2-select'
            )
        );
        $feed->visibility=1;
        echo $form->dropDownList($feed,'visibility',
            array(1=>Yii::t('actions','Public'), 0=>Yii::t('actions','Private')),
            array ('class' => 'x2-select')
        );
        function translateOptions($item){
            return Yii::t('app',$item);
        }
        echo $form->dropDownList($feed,'subtype',
            array_map(
                'translateOptions',json_decode(Dropdowns::model()->findByPk(113)->options,true)),
            array ('class' => 'x2-select'));
        ?>
        <div id='second-row-buttons-container'>
            <?php
            echo CHtml::submitButton(
                Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
            if ($isMyProfile) {
                echo CHtml::button(
                    Yii::t('app','Attach A File/Photo'),
                    array(
                        'class'=>'x2-button','onclick'=>"$('#attachments').slideToggle();",
                        'id'=>"toggle-attachment-menu-button"));
            }
            ?>
        </div>
        </div>
        <?php
        ?>
    </div>
    <?php $this->endWidget(); ?>
</div>
<?php
if ($isMyProfile) {
?>
<div id="attachments" style="display:none;">
<?php 
$this->widget(
    'Attachments',
    array(
        'associationType'=>'feed',
        'associationId'=>Yii::app()->user->getId(),
        'profileId'=>$profileId,
    )
); 
?>
</div>
<?php
}
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$stickyDataProvider,
    'itemView'=>'_viewEvent',
    'viewData' => array (
        'profileId' => $profileId
    ),
    'id'=>'sticky-feed',
    'htmlOptions' => array (
        'style' => $stickyDataProvider->itemCount === 0 ? 'display: none;' : '',
    ),
    'pager' => array(
        'class' => 'ext.infiniteScroll.IasPager',
        'rowSelector'=>'.view.top-level',
        'listViewId' => 'sticky-feed',
        'header' => '',
        'options'=>array(
            'onRenderComplete'=>'js:function(){
                x2.activityFeed.makePostsExpandable ();
                if(x2.activityFeed.minimizeFeed){
                    x2.activityFeed.minimizePosts();
                }
                if(x2.activityFeed.commentFlag){
                    $(".comment-link").click();
                }
            }'
        ),
    ),
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
    'template'=>'{pager} {items}'
));
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_viewEvent',
    'viewData' => array (
        'profileId' => $profileId
    ),
    'id'=>'activity-feed',
    'pager' => array(
        'class' => 'ext.infiniteScroll.IasPager',
        'rowSelector'=>'.view.top-level',
        'listViewId' => 'activity-feed',
        'header' => '',
        'options'=>array(
            'onRenderComplete'=>'js:function(){
                x2.activityFeed.makePostsExpandable ();
                if(x2.activityFeed.minimizeFeed){
                    x2.activityFeed.minimizePosts();
                }
                if(x2.activityFeed.commentFlag){
                    $(".comment-link").click();
                }
                $.each($(".comment-count"),function(){
                    if($(this).attr("val")>0){
                        $(this).parent().click();
                    }
                });
            }'
        ),
    ),
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
    'template'=>'{pager} {items}',
));

?>
<div id="make-important-dialog" style="display: none;">
    <div class='dialog-explanation'>
        <?php echo Yii::t('app','Leave colors blank for defaults.');?>
    </div>
    <div>
        <?php
            echo CHtml::label(Yii::t('app','What color should the event be?'),'broadcastColor');
        ?>
        <div class='row'>
            <?php echo CHtml::textField('broadcastColor',''); ?>
        </div>
    </div>
    <div>
        <?php echo CHtml::label(Yii::t('app','What color should the font be?'),'fontColor'); ?>
            <div class='row'>
        <?php echo CHtml::textField('fontColor',''); ?>
        </div>
    </div>
    <div>
        <?php echo CHtml::label(Yii::t('app','What color should the links be?'),'linkColor'); ?>
        <div class='row'>
            <?php echo CHtml::textField('linkColor',''); ?>
        </div>
    </div>
</div>
<div id="broadcast-dialog" style='display: none;'>
    <div class='dialog-explanation'>
        <?php echo Yii::t('app', 'Select a group of users to send this event to via email or notification.'); ?>
    </div>
    <select id='broadcast-dialog-user-select' class='multiselect' multiple='multiple' size='6'>
        <?php foreach ($userModels as $user) { ?>
        <option value="<?php echo $user->id; ?>"> <?php echo $user->firstName . ' ' . $user->lastName; ?> </option>
        <?php } ?>
    </select>
    <div>
        <?php echo CHtml::label(Yii::t('app','Do you want to email selected users?'),'email-users'); ?>
        <?php echo CHtml::checkBox('email-users'); ?>
    </div>
    <div id='notify-users-checkbox-container'>
        <?php echo CHtml::label(Yii::t('app','Do you want to notify selected users?'),'notify-users'); ?>
        <?php echo CHtml::checkBox('notify-users'); ?>
    </div>
</div>
</div>
