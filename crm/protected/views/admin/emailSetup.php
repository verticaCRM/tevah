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

Yii::app()->clientScript->registerScript('toggleAuthInfo', "
    $('#Admin_emailUseAuth').change(function() {
        if(($(this).val() == 'admin' && $('#auth-info').is(':hidden'))
            || ($(this).val() != 'admin' && $('#auth-info').is(':visible'))) {

            $('#auth-info').animate({
                opacity: 'toggle',
                height: 'toggle'
            }, 400);
        }
    });

    $('#Admin_emailType').change(function() {
        if(($(this).val() == 'smtp' && $('#server-info').is(':hidden'))
            || ($(this).val() != 'smtp' && $('#server-info').is(':visible'))) {

            $('#server-info').animate({
                opacity: 'toggle',
                height: 'toggle'
            }, 400);
        }
    });
    $('#Admin_emailUseSignature').change(function() {
        if(($(this).val() == 'admin' && $('#signature-box').is(':hidden'))
            || ($(this).val() != 'admin' && $('#signature-box').is(':visible'))) {

            $('#signature-box').animate({
                opacity: 'toggle',
                height: 'toggle'
            }, 400);
        }
    });

    $('#email-setup input, #email-setup select, #email-setup textarea').change(function(){
        $('#save-button').addClass('highlight'); //css('background','yellow');
    });

    $('#imap-poll-timeout').change(function(){
        $('#imap-poll-slider').slider('value',$(this).val());
    });

", CClientScript::POS_READY);
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Email Server Configuration'); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <p><?php echo Yii::t('admin','For more information, see {config} on the X2Engine wiki.',array('{config}'=>CHtml::link(Yii::t('admin','Email Configuration'),'http://wiki.x2engine.com/wiki/E-Mail_Configuration'))); ?></p>
        <hr />
        <?php
        $form = $this->beginWidget('X2ActiveForm', array(
            'id' => 'email-setup',
            'enableAjaxValidation' => false,
                ));
        echo $form->errorSummary($model);
        ?>

        <?php /* x2prostart */ ?>
        <h4><?php echo Yii::t('admin', 'Email Client Settings'); ?></h4>
        <p><?php echo Yii::t('admin',
            'Configure timeout settings for IMAP functionality in the email client'
        ); ?></p>
        <div class="row">
            <?php
            echo $form->labelEx(
                $model, 'imapPollTimeout', array('class' => 'left'));
            echo X2Html::hint (
                Yii::t('admin', 'Set the duration between requests for new '.
                'emails, in minutes'), false, null, true);
            echo '<br/>';
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => $model->imapPollTimeout,
                'options' => array(
                    'min' => 5,
                    'max' => 30,
                    'step' => 1,
                    'change' => "js:function(event,ui) {
                        $('#imap-poll-timeout').val(ui.value);
                        $('#save-button').addClass('highlight');
                    }",
                    'slide' => "js:function(event,ui) {
                        $('#imap-poll-timeout').val(ui.value);
                    }",
                ),
                'htmlOptions' => array(
                    'id' => 'imap-poll-slider',
                    'style' => 'margin:10px 0;',
                    'class'=>'x2-wide-slider'
                ),
            ));
            echo $form->textField ($model, 'imapPollTimeout', array('id' => 'imap-poll-timeout'));
            ?>
        </div>
        <?php /* x2proend */ ?>

        <h4><?php echo Yii::t('admin', 'Default Email Delivery Method'); ?></h4>
        <p><?php
        echo Yii::t('admin', 'Define how the system sends email by default, when the email account is unspecified.').'<br /><br />';
        echo Yii::t('admin', 'Note that this will not supersede other email settings. Usage of these particular settings is a legacy feature. Unless this web server also serves as the primary mail server, it is recommended to instead use "{ma}" to set up email accounts for system usage instead.', array('{ma}' => CHtml::link(Yii::t('app', 'Manage Apps'), array('/profile/manageCredentials'))));
        ?></p>
        <div class="row">
            <?php
            // Determine the available mail sending methods:
            $can = array(
                'sendmail' => @is_executable('/usr/sbin/sendmail'),
                'qmail' => @is_executable('/var/qmail/bin/sendmail')
            );
            $mailMethods = array();
            if((bool) @ini_get('sendmail_path'))
                if(@is_executable(@ini_get('sendmail_path')))
                    $mailMethods['mail'] = Yii::t('admin', 'PHP Mail');
            if($can['sendmail'])
                $mailMethods['sendmail'] = Yii::t('admin', 'Sendmail');
            if($can['qmail'])
                $mailMethods['qmail'] = Yii::t('admin', 'Qmail');
            $mailMethods['smtp'] = Yii::t('admin', 'SMTP');
            // if currently set mail type isn't an option, default to the first available mail type
            if (!in_array ($model->emailType, array_keys ($mailMethods))) {
                $methodKeys = array_keys ($mailMethods);
                $model->emailType = array_shift ($methodKeys);
            }
            ?>
            <div class="cell" style="width:310px;">
                <?php echo $form->labelEx($model, 'emailType'); ?>
                <?php
                echo $form->dropDownList($model, 'emailType', $mailMethods, array('options' => array($model->emailType => array('selected' => true))));
                //echo $form->error($actionModel,'priority');
                ?>
            </div>
            <div class="cell">
                <?php //echo CHtml::button(Yii::t('app','Send test email'),array('class'=>'x2-button','style'=>'margin-top:16px;'))."\n";  ?>
            </div>
        </div>
        <div id="server-info"<?php if($model->emailType != 'smtp') echo ' style="display:none;"'; ?>>
            <div class="row">
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailHost'); ?>
                    <?php echo $form->textField($model, 'emailHost', array('size' => 30)); ?>
                </div>
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailPort'); ?>
                    <?php echo $form->textField($model, 'emailPort', array('style' => 'width:40px;', 'maxlength' => 5)); ?>
                </div>
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailSecurity'); ?>
                    <?php
                    echo $form->dropDownList($model, 'emailSecurity', array(
                        '' => Yii::t('admin', 'None'),
                        'tls' => Yii::t('admin', 'TLS'),
                        'ssl' => Yii::t('admin', 'SSL'),
                    ));
                    ?>
                </div>
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailUseAuth'); ?>
                    <?php
                    echo $form->dropDownList($model, 'emailUseAuth', array(
                        'none' => Yii::t('admin', 'None'),
                        // 'user'=>Yii::t('admin','User account'),
                        'admin' => Yii::t('admin', 'Global account'),
                    ));
                    ?>
                </div>
            </div>
            <div class="row" id="auth-info"<?php if($model->emailUseAuth != 'admin') echo ' style="display:none;"'; ?>>
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailUser'); ?>
                    <?php echo $form->textField($model, 'emailUser'); ?>
                </div>
                <div class="cell">
                    <?php echo $form->labelEx($model, 'emailPass'); ?>
                    <?php echo $form->passwordField($model, 'emailPass'); ?>
                </div>
            </div>
        </div>
        <br /><hr />
        <h4><?php echo Yii::t('admin', 'Bulk Email Settings'); ?></h4>
        <p><?php echo Yii::t('admin', 'Configure how X2Engine sends email when mailing en-masse.'); ?></p>
        <div class="row">
            <div class="cell">
                <?php echo $form->labelEx($model, 'emailBulkAccount'); ?>
                <?php
                echo Credentials::selectorField($model, 'emailBulkAccount', 'email', Credentials::$sysUseId['bulkEmail'], array('class' => 'email-selector', 'id' => 'email-selector-bulk'));
                ?>
            </div>
        </div>
        <div class="row email-selector-bulk-legacy">
            <div class="cell">
                <?php echo $form->labelEx($model, 'emailFromName'); ?>
                <?php echo $form->textField($model, 'emailFromName', array('size' => 30)); ?>
            </div>
            <div class="cell">
                <?php echo $form->labelEx($model, 'emailFromAddr'); ?>
                <?php echo $form->textField($model, 'emailFromAddr', array('size' => 40)); ?>
            </div>
        </div>
        <div class="row">
            <div class="cell">
                <?php echo $form->labelEx($model, 'emailBatchSize'); ?>
                <?php echo $form->textField($model, 'emailBatchSize', array('size' => 10)); ?>
            </div>
            <div class="cell">
                <?php echo $form->labelEx($model, 'emailInterval'); ?>
                <?php echo $form->textField($model, 'emailInterval', array('size' => 10)); ?>
            </div>
        </div>
        <br>
        <div class="row">
            <?php echo $form->labelEx($model, 'emailUseSignature'); ?>
            <?php
            echo $form->dropDownList($model, 'emailUseSignature', array(
                '' => Yii::t('admin', 'None'),
                'user' => Yii::t('admin', 'User\'s Choice'),
                // 'group'=>Yii::t('admin','Group signature'),
                'admin' => Yii::t('admin', 'Default Signature'),
            ));
            ?>
        </div>
        <div class="row" id="signature-box"<?php if($model->emailUseSignature != 'admin') echo ' style="display:none;"'; ?>>
            <?php echo $form->labelEx($model, 'emailSignature'); ?>
            <?php echo $form->textArea($model, 'emailSignature', array('style' => 'width:490px;height:80px;')); ?>
            <br>
            <?php echo Yii::t('admin', 'You can use the following variables in this template: {first}, {last}, {phone} and {email}.'); ?>
        </div>

    <br /><hr />
    <h4><?php echo Yii::t('admin','Notification Email Settings'); ?></h4>
    <p><?php echo Yii::t('admin','Configure how notification emails are sent.');?></p>
    <div class="row">
        <div class="cell">
            <?php
            echo $form->labelEx($model,'emailNotificationAccount');
            echo Credentials::selectorField($model,'emailNotificationAccount','email',Credentials::$sysUseId['systemNotificationEmail']);
            ?>
        </div>
    </div>

    <br /><hr />
    <h4><?php echo Yii::t('admin', 'Service Case Email Settings'); ?></h4>
    <p><?php echo Yii::t('admin', 'Configure how X2Engine sends email when responding to new service case requests.'); ?></p>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'serviceCaseEmailAccount'); ?>
            <?php
            echo Credentials::selectorField($model, 'serviceCaseEmailAccount', 'email', Credentials::$sysUseId['serviceCaseEmail'], array('class' => 'email-selector', 'id' => 'email-selector-servicecase'));
            ?>
        </div>
    </div>
    <div class="row email-selector-servicecase-legacy">
        <div class="cell">
            <?php echo $form->labelEx($model, 'serviceCaseFromEmailName'); ?>
            <?php echo $form->textField($model, 'serviceCaseFromEmailName', array('size' => 30)); ?>
        </div>
        <div class="cell">
            <?php echo $form->labelEx($model, 'serviceCaseFromEmailAddress'); ?>
            <?php echo $form->textField($model, 'serviceCaseFromEmailAddress', array('size' => 40)); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'serviceCaseEmailSubject'); ?>
            <?php echo $form->textField($model, 'serviceCaseEmailSubject', array('size' => 30)); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'serviceCaseEmailMessage'); ?>
            <?php echo $form->textArea($model, 'serviceCaseEmailMessage', array('class'=>'x2-xxwide-input', 'style' => 'height:80px;')); ?>
            <br>
            <?php echo Yii::t('admin', 'You can use the following variables in this template: {first}, {last}, {phone}, {email}, {description}, and {case}.'); ?>
        </div>
    </div>
    <?php
    if(file_exists(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'views','admin','webLeadResponseEmailSettings.php')))){
        $this->renderPartial('webLeadResponseEmailSettings',compact('form','model'));
    }

    ?>
        <br/>
        <hr/>
        <?php  
        /* x2prostart */     
        ?> 
        <h4><?php echo Yii::t('admin', 'X2Flow Email Settings'); ?></h4>
        <br/>
        <div class="row">
            <div class="cell">
                <?php
                echo $form->checkbox($model, 'x2FlowRespectsDoNotEmail', array ('class' => 'left')); 
                echo $form->labelEx(
                    $model, 'x2FlowRespectsDoNotEmail', array ('class' => 'left')); 
                echo X2Html::hint (
                    Yii::t('admin', 'If checked, emails will not be sent from X2Flow to contacts '.
                        'that have their "Do Not Email" fields checked.'), false, null, true);
                ?>
            </div>
        </div>
        <br/>
        <hr/>
        <?php
        /* x2proend */ 
        ?>
        <h4><?php echo Yii::t('admin', '"Do Not Email" Link Configuration'); ?></h4>
        <br/>
        <div class="row">
            <div class="cell">
                <?php
                echo $form->labelEx(
                    $model, 'doNotEmailLinkText', array ('class' => 'left')); 
                echo '<br/>';
                echo $form->textField ($model, 'doNotEmailLinkText'); 
                ?>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="cell">
                <?php
                echo $form->labelEx(
                    $model, 'doNotEmailPage', array ('class' => 'left')); 
                echo X2Html::hint (
                    Yii::t('admin', 'This is the page that will be displayed to contacts after '.
                        'they have clicked the "Do Not Email" link contained in an email.'), 
                        false, null, true);
                echo $form->richTextarea ($model, 'doNotEmailPage'); 
                ?>
            </div>
        </div>
        <br/><hr/>
        <br/>

        <?php //echo $form->labelEx($admin,'chatPollTime');   ?>
        <?php // echo $form->textField($admin,'chatPollTime',array('id'=>'chatPollTime'));  ?>

        <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
        <?php $this->endWidget(); ?></div>
</div>
