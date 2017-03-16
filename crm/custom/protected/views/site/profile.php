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
	Yii::app()->getBaseUrl().'/js/profile.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile(Yii::app()->getTheme()->getBaseUrl().'/css/profile.css');
Yii::app()->clientScript->registerScriptFile(
	Yii::app()->getBaseUrl().'/js/jquery-expander/jquery.expander.js', CClientScript::POS_END);

// used for rich editing in new post text field
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/emailEditor.js');


Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');


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


$passVarsToClientScript = "
	x2.profile = {};
	x2.profile.DEBUG = false;//".(YII_DEBUG ? 'true' : 'false').";
	x2.profile.usersGroups = '".$usersGroups."';
	x2.profile.minimizeFeed = ".(Yii::app()->params->profile->minimizeFeed==1?'true':'false').";
	x2.profile.commentFlag = false;
	x2.profile.lastEventId = ".(!empty($lastEventId)?$lastEventId:0).";
	x2.profile.lastTimestamp = ".(!empty($lastTimestamp)?$lastTimestamp:0).";
	x2.profile.deletePostUrl = '".$this->createUrl('/profile/deletePost')."';
	x2.profile.translations = {};
";

$translations = array (
	'Uncheck Filters' => Yii::t('app','Uncheck Filters'),
	'Check Filters' => Yii::t('app','Check Filters'),
	'Enter text here...' => Yii::t('app','Enter text here...'),
	'Broadcast Event' => Yii::t('app','Broadcast Event'),
	'Make Important' => Yii::t('app','Make Important'),
	'Broadcast' => Yii::t('app','Broadcast'),
	'broadcast error message 1' => Yii::t('app','Select at least one user to broadcast to'),
	'broadcast error message 2' => Yii::t('app','Select at least one broadcast method'),
	'Okay' => Yii::t('app','Okay'),
	'Nevermind' => Yii::t('app','Nevermind'),
	'Create' => Yii::t('app','Create'),
	'Cancel' => Yii::t('app','Cancel'),
	'Read more' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'More'),
	'Read less' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'Less'),
);

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key=>$val) {
	$passVarsToClientScript .= "x2.profile.translations['".
		$key. "'] = '" . addslashes ($val) . "';\n";
}

Yii::app()->clientScript->registerScript(
	'passVarsToClientScript', $passVarsToClientScript,
	CClientScript::POS_HEAD);


?>

<div id='page-title-container'>
	<div class="page-title icon activity-feed">
		<h2><?php echo Yii::t('app','Activity Feed'); ?></h2>
		<div id="menu-links" class="title-bar">
			<?php
			echo CHtml::link(
                Yii::t('app','Toggle Comments'),'#',
                array('id'=>'toggle-all-comments','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','My Groups'),'#',
                array('id'=>'my-groups-filter','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','Just Me'),'#',
                array('id'=>'just-me-filter','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','Restore Posts'),'#',
                array('id'=>'restore-posts','style'=>'display:none;','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','Minimize Posts'),
                '#',array('id'=>'min-posts','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','Show Chart'),'#',
                array('id'=>'show-chart','class'=>'x2-button right'));
			echo CHtml::link(
                Yii::t('app','Hide Chart'),'#',
                array('id'=>'hide-chart','class'=>'x2-button right', 'style'=>'display:none;'));
			?>
		</div>
	</div>
</div>

<div id='activity-feed-chart-container' style='display: none;'>

	<select id='chart-type-selector'>
		<option value='eventsChart'>
			<?php echo Yii::t('app', 'Events'); ?>
		</option>
		<option value='usersChart'>
			<?php echo Yii::t('app', 'User Events'); ?>
		</option>
	</select>
	<select id='chart-subtype-selector'>
		<option value='line'>
			<?php echo Yii::t('app', 'Line Chart'); ?>
		</option>
		<option value='pie'>
			<?php echo Yii::t('app', 'Pie Chart'); ?>
		</option>
	</select>

	<?php
		$this->widget('X2Chart', array (
			'getChartDataActionName' => 'getEventsBetween',
			'suppressChartSettings' => false,
			'actionParams' => array (),
			'metricTypes' => array (
				'any'=>Yii::t('app', 'All Events'),
				'notif'=>Yii::t('app', 'Notifications'),
				'feed'=>Yii::t('app', 'Feed Events'),
				'comment'=>Yii::t('app', 'Comments'),
				'record_create'=>Yii::t('app', 'Records Created'),
				'record_deleted'=>Yii::t('app', 'Records Deleted'),
				'weblead_create'=>Yii::t('app', 'Webleads Created'),
				'workflow_start'=>Yii::t('app', 'Workflow Started'),
				'workflow_complete'=>Yii::t('app', 'Workflow Complete'),
				'workflow_revert'=>Yii::t('app', 'Workflow Reverted'),
				'email_sent'=>Yii::t('app', 'Emails Sent'),
				'email_opened'=>Yii::t('app', 'Emails Opened'),
				'web_activity'=>Yii::t('app', 'Web Activity'),
				'case_escalated'=>Yii::t('app', 'Cases Escalated'),
				'calendar_event'=>Yii::t('app', 'Calendar Events'),
				'action_reminder'=>Yii::t('app', 'Action Reminders'),
				'action_complete'=>Yii::t('app', 'Actions Completed'),
				'doc_update'=>Yii::t('app', 'Doc Updates'),
				'email_from'=>Yii::t('app', 'Email Received'),
				'voip_calls'=>Yii::t('app', 'VOIP Calls'),
				'media'=>Yii::t('app', 'Media')
			),
			'chartType' => 'eventsChart',
			'getDataOnPageLoad' => true,
			'hideByDefault' => true
		));
	?>

	<?php
		$usersArr = array ();
		foreach ($usersDataProvider->data as $user) {
			$usersArr[$user->username] = $user->firstName.' '.$user->lastName;
		}

		$this->widget('X2Chart', array (
			'getChartDataActionName' => 'getEventsBetween',
			'suppressChartSettings' => false,
			'actionParams' => array (),
			'metricTypes' => $usersArr,
			'chartType' => 'usersChart',
			'getDataOnPageLoad' => true,
			'hideByDefault' => true
		));
	?>
</div>



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
		echo $form->dropDownList($feed,'associationId',$users);
		$feed->visibility=1;
		echo $form->dropDownList($feed,'visibility',array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')));
		function translateOptions($item){
			return Yii::t('app',$item);
		}
		echo $form->dropDownList($feed,'subtype',array_map('translateOptions',json_decode(Dropdowns::model()->findByPk(113)->options,true)));
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"$('#attachments').slideToggle();", 'id'=>"toggle-attachment-menu-button"));
		echo "</div>";
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>

<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed','associationId'=>Yii::app()->user->getId())); ?>
</div>

<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$stickyDataProvider,
	'itemView'=>'_viewEvent',
	'id'=>'sticky-feed',
	'pager' => array(
					'class' => 'ext.infiniteScroll.IasPager',
					'rowSelector'=>'.view.top-level',
					'listViewId' => 'sticky-feed',
					'header' => '',
					'options'=>array(
						'onRenderComplete'=>'js:function(){
							makePostsExpandable ();
							if(x2.profile.minimizeFeed){
								minimizePosts();
							}
							if(x2.profile.commentFlag){
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
	'id'=>'activity-feed',
	'pager' => array(
					'class' => 'ext.infiniteScroll.IasPager',
					'rowSelector'=>'.view.top-level',
					'listViewId' => 'activity-feed',
					'header' => '',
					'options'=>array(
						'onRenderComplete'=>'js:function(){
							makePostsExpandable ();
							if(x2.profile.minimizeFeed){
								minimizePosts();
							}
							if(x2.profile.commentFlag){
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
<div id="broadcast-dialog">
	<div class='dialog-explanation'>
		<?php echo Yii::t('app', 'Select a group of users to send this event to via email or notification.'); ?>
	</div>
    <select id='broadcast-dialog-user-select' class='multiselect' multiple='multiple' size='6'>
		<?php foreach ($usersDataProvider->data as $user) { ?>
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
<style>

</style>

