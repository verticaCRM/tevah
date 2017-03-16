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

/**
 * Credentials form.
 *
 * @var Credentials $model The ID of the credentials being modified
 * @var bool $includeTitle Whether to print the title inside of the
 * @var User $user The system user
 */

Yii::app()->clientScript->registerCss('credentialsFormCss',"

form > .twitter-credential-input {
    width: 300px; 
}

");

echo '<div class="form">';

$action = null;
if($model->isNewRecord)
	$action = array('/profile/createUpdateCredentials','class'=>$model->modelClass);
else
	$action = array('/profile/createUpdateCredentials','id'=>$model->id);

echo CHtml::beginForm($action);
?>

<!-- Credentials metadata -->
<?php
echo $includeTitle ? $model->pageTitle.'<hr />' : '';
// Model class hidden field, so that it saves properly:
echo CHtml::activeHiddenField($model,'modelClass');

if (!$disableMetaDataForm) {
    echo CHtml::activeLabel($model, 'name');
    echo CHtml::error($model, 'name');
    echo CHtml::activeTextField($model, 'name');

    echo CHtml::activeLabel($model, 'private');
    echo CHtml::activeCheckbox($model, 'private',array('value'=>1));
    echo X2Html::hint2(Yii::t('app', 'If you disable this option, administrators and users granted privilege to do so will be able to use these credentials on your behalf.'));

    if($model->isNewRecord){
        if(Yii::app()->user->checkAccess('CredentialsAdmin')){
            $users = array($user->id => Yii::t('app', 'You'));
            $users[Credentials::SYS_ID] = 'System';
            echo CHtml::activeLabel($model, 'userId');
            echo CHtml::activeDropDownList($model, 'userId', $users, array('selected' => Credentials::SYS_ID));
        }else{
            echo CHtml::activeHiddenField($model, 'userId', array('value' => $user->id));
        }
    }
}

?>
	
<!-- Credentials details (embedded model) -->
<?php
$this->widget('EmbeddedModelForm', array(
	'model' => $model,
	'attribute' => 'auth'
));
?>
</div>

<div class="credentials-buttons">
<?php
echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button credentials-save','style'=>'display:inline-block;margin-top:0;'));
echo CHtml::link(Yii::t('app','Cancel'),array('/profile/manageCredentials'),array('class'=>'x2-button credentials-cancel'));
if (isset ($model->auth->enableVerification) && $model->auth->enableVerification) {
    echo CHtml::link(Yii::t('app', 'Verify Credentials'), "#", array('class' => 'x2-button credentials-verify', 'style' => 'margin-left: 5px;'));
    ?><div id='verify-credentials-loading'></div>
<?php
}
?>
</div><?php
echo CHtml::endForm();


$verifyCredsUrl = Yii::app()->createUrl("profile/verifyCredentials");
?>

<div id="verification-result">

</div>
<script type='text/javascript'>
    $(function() {
        if (typeof x2 == 'undefined')
            x2 = {};
        if (typeof x2.credManager == 'undefined')
            x2.credManager = {};

        /**
         * Function to toggle an input between text and password type
         * @param string passwordField Password field identifier
         */
        x2.credManager.swapPasswordVisibility = function(elem) {
            var passwordField = $(elem);
            var newObj = document.createElement('input');
            newObj.setAttribute('value', passwordField.val() );
            newObj.setAttribute('name', passwordField.attr('name'));
            newObj.setAttribute('id', passwordField.attr('id'));

            if ($('#password-visible').attr('checked') === 'checked')
                newObj.setAttribute('type', 'text');
            else
                newObj.setAttribute('type', 'password');
            passwordField.replaceWith(newObj);
        }

        $(".credentials-verify").click(function(evt) {
            evt.preventDefault();
            var email = $("#Credentials_auth_email").val();
            // Check if user name is different than email
            if ($('#Credentials_auth_user').length && $('#Credentials_auth_user').val ()) 
                email = $('#Credentials_auth_user').val();
            var password = $("#Credentials_auth_password").val();

            // server, port, and security are not specified in the form for GMail accounts
            var server;
            var port;
            var security;
            if ($('#Credentials_auth_server').length)
                server = $('#Credentials_auth_server').val();
            else
                server = 'smtp.gmail.com';
            if ($('#Credentials_auth_port').length)
                port = $('#Credentials_auth_port').val();
            else
                port = 587;
            if ($('#Credentials_auth_security').length)
                security = $('#Credentials_auth_security').val();
            else
                security = 'tls';

            var successMsg = "<?php echo Yii::t('app', 'Authentication successful.'); ?>";
            var failureMsg = "<?php echo Yii::t('app', 'Failed to authenticate! Please check you credentials.'); ?>";
            auxlib.containerLoading($('#verify-credentials-loading'));
            // Hide previous result if any
            $('#verification-result').html('');
            $('#verification-result').removeClass();

            $.ajax({
                url: "<?php echo $verifyCredsUrl; ?>",
                type: 'post',
                data: {
                    email: email,
                    password: password,
                    server: server,
                    port: port,
                    security: security
                },
                complete: function(xhr, textStatus) {
                    $('#verify-credentials-loading').children().remove();
                    // auxlib.pageLoadingStop();
                    if (xhr.responseText === '' && textStatus === 'success') {
                        $("#verification-result").addClass('flash-success');
                        $("#verification-result").removeClass('flash-error');
                        $("#verification-result").html(successMsg);
                    } else {
                        $("#verification-result").addClass('flash-error');
                        $("#verification-result").removeClass('flash-success');
                        $("#verification-result").html(failureMsg);
                    }
                }
            });
        });
    });
</script>
