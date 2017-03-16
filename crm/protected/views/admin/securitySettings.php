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

/* @edition:pla */

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/securitySettings.css');

?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'Firewall Settings'); ?></h2></div>
<div id='security-settings-form' class="form">
<div class='admin-form-container'>
    <?php
    X2Html::getFlashes();
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
    ));

    // IP Whitelist/Blacklist settings
    $hint = Yii::t('admin', 'You may enter entire address blocks here, either using '.
        'a * to signify the entire subnet, such as "192.168.1.*", or using CIDR notation '.
        ' to specify a subnet using a prefix like "192.168.1.0/24". Note that '.
        'entries using a * will be converted to CIDR notation.');
    echo '<h3>'.Yii::t('admin', 'IP Access Control').'</h3>';
    echo Yii::t ('admin', 'Configure the method of IP address access control used by X2. '.
                          'A whitelist will restrict logins to only those addresses that '.
                          'are present in the whitelist, while a blacklist will block '.
                          'the listed addresses from connecting.').'<br /><br />';
    echo $form->labelEx ($model, 'accessControlMethod');
    echo $form->dropDownList ($model, 'accessControlMethod', array(
        'whitelist' => Yii::t('admin', 'Whitelist'),
        'blacklist' => Yii::t('admin', 'Blacklist'),
    ), array(
        'id' => 'aclMethodDropdown',
    )).'<br /><br />';
    echo '<div class="row" id="x2-whitelist">';
    echo $form->labelEx ($model, 'ipWhitelist', array(
        'style' => 'margin-right:5px;display: inline-block;'
    ));
    echo X2Html::hint($hint, false).'<br />';
    echo Yii::t ('admin', 'To grant login permission to a select IP address, enter '.
                          'the IP address here, one address per line. All other '.
                          'login attempts will be forbidden.');
    echo $form->textArea ($model, 'ipWhitelist');
    echo '</div>';

    echo '<div class="row" id="x2-blacklist">';
    echo $form->labelEx ($model, 'ipBlacklist', array(
        'style' => 'margin-right:5px;display: inline-block;'
    ));
    echo X2Html::hint($hint, false).'<br />';
    echo Yii::t ('admin', 
        'To ban an IP address from logging in to X2, enter '.
      'the IP address here, one address per line.');

    echo $form->textArea ($model, 'ipBlacklist');
    echo '</div>';

    echo '<h3>'.Yii::t('admin', 'Failed Login Penalties').'</h3>';
    echo '<div class="row">';
    echo Yii::t ('admin', 'Configure the timeout in between failed login attempts, and the '.
                          'number of failed login attempts before the IP address is banned.'
    ).'<br /><br />';

    // Login timeout controls
    echo $form->labelEx ($model, 'loginTimeout');
    echo Yii::t('admin', 'Number of minutes user logins will be locked after too many failed '.
                         'login attempts');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->loginTimeout,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 5,
            'max' => 1440,
            'step' => 5,
            'change' => "js:function(event,ui) {
                            $('#loginTimeout').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#loginTimeout').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'loginTimeoutSlider'
        ),
    ));
    echo $form->textField ($model, 'loginTimeout', array(
        'id' => 'loginTimeout'
    ));
    echo $form->error ($model, 'loginTimeout').'<br /><br />';

    // Failed logins before Captcha controls
    echo $form->labelEx ($model, 'failedLoginsBeforeCaptcha');
    echo Yii::t('admin', 'Configure the maximum number of failed logins before '.
                         'the user is presented with a CAPTCHA');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->failedLoginsBeforeCaptcha,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 1,
            'max' => 100,
            'step' => 1,
            'change' => "js:function(event,ui) {
                            $('#failedLoginsBeforeCaptcha').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#failedLoginsBeforeCaptcha').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'failedLoginsBeforeCaptchaSlider'
        ),
    ));
    echo $form->textField ($model, 'failedLoginsBeforeCaptcha', array(
        'id' => 'failedLoginsBeforeCaptcha'
    ));
    echo $form->error ($model, 'failedLoginsBeforeCaptcha').'<br /><br />';

    // Maximum failed logins controls
    echo $form->labelEx ($model, 'maxFailedLogins');
    echo Yii::t('admin', 'Configure the maximum number of failed logins before the '.
                         'user\'s IP address is locked out. Note that this must be '.
                         'higher than the number of failed logins');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->maxFailedLogins,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 1,
            'max' => 100,
            'step' => 1,
            'change' => "js:function(event,ui) {
                            $('#maxFailedLogins').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#maxFailedLogins').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'maxFailedLoginsSlider'
        ),
    ));
    echo $form->textField ($model, 'maxFailedLogins', array(
        'id' => 'maxFailedLogins'
    ));
    echo $form->error ($model, 'maxFailedLogins').'<br /><br />';


    // Maximum logins to keep in history
    echo $form->labelEx ($model, 'maxLoginHistory');
    echo Yii::t('admin', 'Configure the maximum number of successful logins to '.
                         'store in the login history');
    $this->widget ('zii.widgets.jui.CJuiSlider', array(
        'value' => $model->maxLoginHistory,
        // additional javascript options for the slider plugin
        'options' => array(
            'min' => 10,
            'max' => 10000,
            'step' => 5,
            'change' => "js:function(event,ui) {
                            $('#maxLoginHistory').val(ui.value);
                            $('#save-button').addClass('highlight');
                        }",
            'slide' => "js:function(event,ui) {
                            $('#maxLoginHistory').val(ui.value);
                        }",
        ),
        'htmlOptions' => array(
            'style' => 'width:340px;margin:10px 0;',
            'id' => 'maxLoginHistorySlider'
        ),
    ));
    echo $form->textField ($model, 'maxLoginHistory', array(
        'id' => 'maxLoginHistory'
    ));
    echo $form->error ($model, 'maxLoginHistory').'<br /><br />';
    echo '</div>';

    echo '<br /><div class="row">';
    echo CHtml::submitButton(
        Yii::t('app', 'Save'), array(
            'class' => 'x2-button', 'id' => 'save-button'
        )) . "\n";
    echo '</div><br />';

    $this->endWidget();

    // Choose which UI section to hide on page load
    $hideControl = ($model->accessControlMethod === 'blacklist' ? 'whitelist' : 'blacklist');

    Yii::app()->clientScript->registerScript ('firewallSettingsJs', '
        // Hide unnecessary UI controls
        $("#x2-'.$hideControl.'").hide();

        $("#aclMethodDropdown").change (function() {
            if ($(this).val() === "blacklist") {
                $("#x2-whitelist").slideUp();
                $("#x2-blacklist").slideDown();
            } else {
                $("#x2-blacklist").slideUp();
                $("#x2-whitelist").slideDown();
            }
        });

        // Set up sliders to sync with text fields
        $("#loginTimeout").change (function () {
            $("#loginTimeoutSlider").slider("value", $(this).val());
        });
        $("#failedLoginsBeforeCaptcha").change (function () {
            $("#failedLoginsBeforeCaptchaSlider").slider("value", $(this).val());
        });
        $("#maxFailedLogins").change (function () {
            $("#maxFailedLoginsSlider").slider("value", $(this).val());
        });
        $("#maxLoginHistory").change (function () {
            $("#maxLoginHistorySlider").slider("value", $(this).val());
        });
    ', CClientScript::POS_READY);
?>
</div>
<?php
    // Display a grid of failed login attempts
    $this->widget('X2GridViewGeneric', array(
        'id' => 'failed-logins-grid',
	    'title'=>Yii::t('admin', 'Failed Login Attempts'),
        'dataProvider' => $failedLoginsDataProvider,
	    'baseScriptUrl'=>  
            Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	    'buttons'=>array('clearFilters','columnSelector','autoResize'),
	    'template'=> '<div class="page-title">{title}'
		    .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize'),
        'defaultGvSettings' => array (
            'IP' => 100,
            'attempts' => 120,
            'lastAttempt' => 200,
            'aclControls' => '50',
        ),
        'gvSettingsName' => 'failed-logins-grid',
    	'columns'=>array(
    		array (
                'name' => 'IP',
                'header' => Yii::t('admin','IP Address'),
            ),
    		array (
                'name' => 'attempts',
                'header' => Yii::t('admin','Last Failed Attempts'),
            ),
            array(
                'name' => 'lastAttempt',
                'header' => Yii::t('admin','Last Failed Login Attempt'),
                'type' => 'raw',
                'value' => 'Formatter::formatCompleteDate($data->lastAttempt)',
            ),
            array(
                'name' => 'aclControls',
                'header' => '',
                'type' => 'raw',
                'value' => 'Admin::renderACLControl ("blacklist", $data["IP"])',
            ),
	    ),
    ));

    echo '<br /><br />';

    // Display a grid of user login history
    $this->widget('X2GridViewGeneric', array(
        'id' => 'login-history-grid',
	    'title'=>Yii::t('admin', 'User Login History'),
        'dataProvider' => $loginHistoryDataProvider,
	    'baseScriptUrl'=>  
            Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	    'template'=> '<div class="page-title">{title}'
		    .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize', 'exportLogins'),
        'defaultGvSettings' => array (
            'username' => 100,
            'emailAddress' => 100,
            'IP' => 100,
            'timestamp' => 180,
            'aclControls' => 150,
        ),
        'gvSettingsName' => 'login-history-grid',
    	'columns'=>array(
    		array (
                'name' => 'username',
                'header' => Yii::t('admin','User'),
            ),
    		array (
                'name' => 'emailAddress',
                'header' => Yii::t('admin','Email'),
            ),
    		array (
                'name' => 'timestamp',
                'header' => Yii::t('admin','Login Time'),
                'type' => 'raw',
                'value' => 'Formatter::formatCompleteDate($data["timestamp"])',
            ),
    		array (
                'name' => 'IP',
                'header' => Yii::t('admin','IP Address'),
            ),
            array(
                'name' => 'aclControls',
                'header' => '',
                'type' => 'raw',
                'value' => 
                    '"<div class=\"x2-button-group\">".
                        Admin::renderACLControl ("blacklist", $data["IP"]).
                        Admin::renderACLControl ("whitelist", $data["IP"]).
                        Admin::renderACLControl ("disable", $data["username"]).
                    "</div>"',
            ),
	    ),
    ));
    ?>
</div>
