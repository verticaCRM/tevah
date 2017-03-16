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

$this->pageTitle=Yii::app()->name . ' - Login';

$hasProfile = false;
if(isset($_COOKIE['LoginForm'])) {
    $model->setAttributes($_COOKIE['LoginForm']);
    if (is_array ($_COOKIE['LoginForm']) &&
        in_array ('username', array_keys ($_COOKIE['LoginForm']))) {

        $username = $_COOKIE['LoginForm']['username'];
        $profile = Profile::model ()->findByAttributes (array (
            'username' => $username
        ));
        if ($profile) {
            $profileId = $profile->id;
            $fullName = $profile->fullName;
            $hasProfile = true;
        } 
    }
}

$loginBoxHeight = 210;

/* x2plastart */
if (X2_PARTNER_DISPLAY_BRANDING) {
    $loginBoxHeight -= 36;
} 
/* x2plaend */

if ($hasProfile) {
    $loginBoxHeight -= 30;
}

if ($model->useCaptcha) {
    $loginBoxHeight -= 86;
}


Yii::app()->clientScript->registerCss('loginExtraCss', "

#login-box-outer {
    top: ".$loginBoxHeight."px;
}

");


Yii::app()->clientScript->registerScript('loginPageJS', "
(function () {

/*$('#login-form-inputs-container').children ('input').focus (function () {
    $(this).addClass ('login-input-focus');
    $(this).siblings ('input').removeClass ('login-input-focus');
}).blur (function () {
    $(this).removeClass ('login-input-focus');
    $(this).siblings ('input').removeClass ('login-input-focus');
});*/

document.getElementById('LoginForm_username').focus (); // for when autofocus isn't supported

}) ();
    

", CClientScript::POS_READY);


// Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/loginTheme.js', CClientScript::POS_END);
?>
<div id="login-box-outer">
<div class="container<?php echo (isset ($profileId) ? ' welcome-back-page' : ''); ?>" id="login-page">
<div id="login-box">

    <div id='login-title-container'>
<?php if(Yii::app()->settings->appName != "X2Engine"): ?>
        <h1 id='app-title'>
            <?php echo Yii::t('app', '{appName}', array (
                '{appName}' => CHtml::encode (Yii::app()->settings->appName))); ?>
        </h1>
        <h2 id='app-description'>
            <?php echo Yii::t('app', '{appDescription}', array (
                '{appDescription}' => CHtml::encode (Yii::app()->settings->appDescription))); ?>
        </h2>
    <?php endif ?>
    </div>
    <?php $form=$this->beginWidget('CActiveForm', array(
        // 'id' => 'login-form',
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'clientOptions' => array(
            'validateOnSubmit' => false,
        ),
    ));
    ?>
    <div class="form" id="login-form">
        <?php if( isset($_POST['themeName']) ) 
            echo CHtml::hiddenField('themeName', $_POST['themeName']);    
        ?>

        <div class="row">
            <div class="cell form-cell" id="login-form-inputs-container">
                <?php
                if (isset ($profileId)) {
                ?>
                <div class='avatar-cell'>
                    <span class='image-alignment-helper'></span>
                    <?php Profile::renderFullSizeAvatar ($profileId, 105); ?>
                </div>
                <?php
                }
                ?>
                <?php 
                if ($hasProfile) { 
                ?>
                <div id='full-name'><?php echo $fullName; ?></div>
                <?php
                }
                if (AuxLib::isIE () && AuxLib::getIEVer () < 10) 
                    echo $form->label(
                        $model, 'username', array (
                            'style' => ($hasProfile ? 'display: none;' : '')));
    
                if ($hasProfile) { 
                    echo $form->hiddenField($model, 'username');
                } else {
                    echo $form->textField($model, 'username',
                        array(
                            'placeholder' => Yii::t('app', 'Username')
                        ));
                }
                
                if (AuxLib::isIE () && AuxLib::getIEVer () < 10) 
                    echo $form->label($model, 'password', array('style' => 'margin-top:5px;')); 
                echo $form->passwordField(
                    $model, 'password',
                    array(
                        'placeholder' => Yii::t('app', 'Password')
                    ));
                echo $form->error($model, 'password'); 
    
                if($model->useCaptcha && CCaptcha::checkRequirements()) { 
                ?>
                <div class="row captcha-row">
                    <?php
                    echo '<div id="captcha-container">';
                    $this->widget('CCaptcha', array(
                        'clickableImage' => true,
                        'showRefreshButton' => false,
                        'imageOptions' => array(
                            'id' => 'captcha-image',
                            'style' => 'display:block;cursor:pointer;',
                            'title' => Yii::t('app', 'Click to get a new image')
                        )
                    )); echo '</div>';
                    echo '<p class="hint">'.Yii::t('app', 'Please enter the letters in the image above.').'</p>';
                    echo $form->textField($model, 'verifyCode');
                    ?>
                </div><?php } ?>
                <div class="row" id='signin-button-container'>
                    <button class='x2-button x2-blue' id='signin-button'>
                        <?php
                        echo Yii::t('app', 'Sign In');
                        ?>
                    </button>
                </div><!-- #signin-button-container -->
                <div class='row bottom-row'>
                <div class="cell remember-me-cell">
                    <?php
                    if ($model->rememberMe) {
                        echo $form->hiddenField($model, 'rememberMe', array('value' => 1));
                    ?>
                    <a href="<?php echo Yii::app()->createUrl ('/site/site/forgetMe'); ?>"
                     class="x2-link x2-minimal-link">
                        <?php echo Yii::t('app', 'Change User'); ?>
                    </a>
                    <?php
                    } else {
                        echo $form->checkBox(
                            $model, 'rememberMe', array('value' => '1', 'uncheckedValue' => '0'));
                        echo $form->label(
                            $model, 'rememberMe', array('style' => 'font-size:10px;'));
                        echo $form->error($model, 'rememberMe');
                    }?>
                </div>
                <?php

 //                   echo CHtml::link(Yii::t('app','Need help?'),array('/site/anonHelp'),
 //                       array( 'class'=>'x2-minimal-link help-link'));
                    ?>
                </div><!-- .row.bottom-row -->
                <div class="row login-links">
                    <?php
//                    echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/google_icon.png" id="google-icon" /> '.Yii::t('app', 'Sign in with Google'),
//                        (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') .
//                        ((substr($_SERVER['HTTP_HOST'], 0, 4)=='www.')?substr($_SERVER['HTTP_HOST'], 4):$_SERVER['HTTP_HOST']) .
//                        $this->createUrl('/site/googleLogin'), 
//                        array('class' => 'alt-sign-in-link google-sign-in-link'));
                    echo CHtml::link(
                        '<img src="'.Yii::app()->baseUrl.'/images/mobile.png" id="mobile-icon" /> 
                            Mobile',
                        Yii::app()->getBaseUrl() . '/index.php/mobile/site/login',
                        array('class'=>'x2touch-link alt-sign-in-link')); 
                    ?>
                </div>
                </div>
                <div style='display:none' class="row theme-selection">
                    <span class="switch" >
                        <a class="fa fa-moon-o"></a>
                    </span>
                </div>
        </div><!-- #login-form-inputs-container -->
        </div><!-- .row -->
    </div><!-- # login-form -->
<?php $this->endWidget(); 
?>
</div><!-- #login-box -->
</div><!-- #login-page -->


<?php
$this->renderPartial ('loginCompanyInfo');
?>

</div>

