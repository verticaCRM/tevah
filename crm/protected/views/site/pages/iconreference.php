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
 ?>

<?php
$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('help','Icon Reference');


$cssString = "
    #icon-reference-title {
        width: 1002px;
        margin-right: 0;
        padding: 0 0 0 0;
    }

    div.icon-reference {
        width: 1000px;
        margin-left: 50px;
        margin-right: 0;
        padding: 0 0 0 0;
    }

    div.icon-reference .section-title {
        margin-left: 20px;
        margin-top: 15px;
    }

    div.icon-reference .cell {
        margin-left: 20px;
        margin-bottom: 10px;
    }
 
    div.icon-reference .row {
        width: 460px;
        height: 60px;
        line-height: 60px;
        vertical-align: middle;
    }
 
    div.icon-reference img {
        vertical-align: middle;
        display: inline-block;
        /*margin-bottom: 20px;*/
    }

    div.icon-reference .icon-container {
        float: left;
        height: 60px;
        font-size: 30px;
        color: #004baf; // darkBlue in colors.scss
    }
 
    div.icon-reference .icon-description {
        margin-left: 60px;
        height: 60px;
    }

    div.icon-reference .icon-description p {
        vertical-align: middle;
        margin: 0 0 0 0;
        display: inline-block;
        font-size: 12px;
	    font-family: Arial, Helvetica, sans-serif;
        line-height: 14px;
    }

    .img-box .stacked-icon {
        top: 32px;
    }

    .icon-reference .section-title {
        background: none;
    }


";

Yii::app()->clientScript->registerCss ('icon-reference-css', $cssString);


?>

<div id="icon-reference-title" class="page-title">
    <h2> <?php echo Yii::t('help', 'Icon Reference'); ?> </h2>
</div>


<div id="icon-reference-section-1" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'Modules'); ?>
    </h2>
    <div class="column1 cell">
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-building'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('accounts', 'Accounts'), array ('/accounts/accounts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('activity') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Activity Feed'), array ('/profile/view', 'id' => Yii::app()->user->getId())); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-play-circle') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Actions'), array ('/actions/actions/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-calendar-o') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('calendar', 'Calendar'), array ('/calendar/calendar/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bar-chart') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo /* x2prostart */CHtml::link (/* x2proend */Yii::t('app', 'Charts')/* x2prostart */, array ('/reports/chartDashboard'))/* x2proend */; ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('contact') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('contacts', 'Contacts'), array ('/contacts/contacts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-file-o') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('docs', 'Docs'), array ('/docs/docs/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-users'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Groups'), array ('/groups/groups/index')); ?> </p>
            </div>
        </div>
    </div>
    <div class="cell">
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-music'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Media'), array ('/media/media/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bullhorn') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Marketing'), array ('/marketing/marketing/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bullseye'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('opportunities', 'Opportunities'), array ('/opportunities/opportunities/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('package'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('products', 'Products'), array ('/products/products/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('quotes'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('quotes', 'Quotes'), array ('/quotes/quotes/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('service'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('help', 'Services'), array ('/services/services/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('funnel'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('workflow', 'Process'), array ('/workflow/workflow/index')); ?> </p>
            </div>
        </div>
    </div>
</div>

<?php 
Yii::app()->clientScript->registerCssFile (Yii::app()->theme->baseUrl.'/css/activityFeed.css');

/* The event icons are assigned in activityFeed.css, so this way reuses the assignments there */

$arr1 = array (
    'action_complete'    => 'Action Completed',
    'action_reminder'    => 'Action Reminder',
    'generic_calendar_event'     => 'Calendar Event',
    'doc_update'         => 'Document Updated',
    'email_from'         => 'Email Received',
    'email_sent'         => 'Email Sent',
    'record_create'      => 'Record Created',
    'record_deleted'     => 'Record Deleted',
);


$arr2 = array (
    'notif'              => 'Notification',
    'web_activity'       => 'Web Activity',
    'weblead_create'     => 'Web Lead Created',
    'case_escalated'     => 'Case Escalated',
    'email_opened'       => 'Email Opened',
    'workflow_revert'    => 'Workflow Reverted',
    'workflow_complete'  => 'Workflow Completed',
    'workflow_start'     => 'Workflow Started',
);


function echoIcons ($array) {
    foreach($array as $key => $value) {
        echo "<div class='row'>
            <div class='img-box $key'>
                <div class='stacked-icon'></div>
            </div>
            <div class='icon-description'>
                <p> 
                    ".Yii::t ('help', $value)."
                </p>
            </div>
        </div>";
    }
}
?>


<div id="activity-feed-container" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'Events'); ?>
    </h2>
    <div class="column1 cell">
        <?php echoIcons($arr1); ?>
    </div>
    <div class="cell">
        <?php echoIcons($arr2); ?>
    </div>
</div>



