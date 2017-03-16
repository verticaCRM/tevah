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



Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');


Yii::app()->clientScript->registerPackages (array (
    'X2History' => array (
        'baseUrl' => Yii::app()->request->baseUrl,                       
        'js' => array (
            'js/X2History.js', 
        ),
        'depends' => array ('history', 'auxlib'),
    ),
), true);

?>
<div id='inbox-body-container' class='inbox-body-container'>
<?php

if ($notConfigured) {
?>
    <div id='my-email-inbox-set-up-instructions-container'>
        <h2><?php 
            echo Yii::t('emailInboxes', 'Your personal email inbox has not yet been configured.'); 
        ?></h2>
        <a href='<?php echo $this->createUrl ('configureMyInbox'); ?>'><?php 
            echo Yii::t('emailInboxes', '-Click here to configure your personal email inbox-'); 
        ?></a>
    </div>
<?php
} else {
    $this->widget('EmailInboxesGridView', array(
        'id' => 'email-list',
        'enableQtips' => true,
        'emailCount' => $mailbox->getMessageCount (),
        'qtipManager' => array (
            'EmailInboxesQtipManager',
            'loadingText'=> addslashes(Yii::t('app','loading...')),
            'qtipSelector' => ".contact-name"
        ),
        'columns' => array (
            array (
                'name' => 'flagged',
                'type' => 'raw',
                'value' => '$data->renderToggleImportant ()',
                'htmlOptions' => array (
                    'class' => 'flagged-cell'
                ),
            ),
            array (
                'name' => 'from',
                'type' => 'raw',
                'value' => '$data->renderFromField ()',
                'htmlOptions' => array (
                    'class' => 'from-cell'
                ),
            ),
            array (
                'name' => 'subject',
                'type' => 'text',
                'htmlOptions' => array (
                    'class' => 'subject-cell'
                ),
            ),
            array (
                'name' => 'date',
                'type' => 'raw',
                'value' => '$data->renderDate ()',
                'htmlOptions' => array (
                    'class' => 'date-cell'
                ),
            ),
        ),
        'rowCssClassExpression' => '$data->seen ? "seen-message-row" : "unseen-message-row"',
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
            '/css/gridview',
        'hideHeader' => true,
        'mailbox' => $mailbox,
        'messageView' => $uid !== null,
        'enableGridResizing' => false,
        'dataProvider' => $dataProvider,
        'template' => "{mailboxControls} {mailboxTabs} {items} {pager}",
        'fullscreen' => true,
        'loadingMailbox' => $loadMessagesOnPageLoad,
        /*'sortableAttributes' => array(
            'msgno' => Yii::t('emailInboxes', 'date'),
            'from',
            'subject',
        ),*/
    ));

?>
<div id='message-container' <?php echo ($uid === null) ? "style='display: none;'" : ''; ?>>
<?php
if ($uid !== null) {
    $this->actionViewMessage ($uid);
}
?>
</div>
<div id='email-quota'>
<?php
    $quota = $mailbox->quotaString;
    echo Yii::t('emailInboxes',
        ($quota ? "{quota}" : "Unable to retrieve quota information."), array(
        '{quota}' => $quota,
    ));
}
?>
</div>
<?php
Yii::app()->clientScript->registerScriptFile ($this->module->assetsUrl.'/js/emailInboxes.js');
Yii::app()->clientScript->registerScript ('emailInboxJS', '

(function () {
    x2.emailInbox = new x2.EmailInbox ({
        notConfigured: '.($notConfigured ? 'true' : 'false').',
        noneSelectedText: "'.Yii::t('emailInboxes',
                'No messages are selected!').'",
        deleteConfirmTxt: "'.Yii::t('emailInboxes',
                'Are you sure you want to delete the selected messages?').'",
        pollTimeout: '.$pollTimeout.',
        emailFolder: "'.($mailbox ? $mailbox->getCurrentFolder() : null).'",
        loadMessagesOnPageLoad: '.($loadMessagesOnPageLoad ? 'true' : 'false').'
    });
}) ();

', CClientScript::POS_READY);

?>
</div>
<div id='reply-form' style='display: none;'>
<?php
    if (isset($mailbox)) {
        $this->widget('EmailInboxesEmailForm', array(
            'mailbox' => $mailbox,
            'attributes' => array(
                'credId' => $mailbox->credentialId,
            ),
            'hideFromField' => true,
            'disableTemplates' => true,
            'startHidden' => true,
        ));
    }
?>
</div>
