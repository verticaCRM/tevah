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

//$this->pageTitle = Yii::app()->settings->appName . ' - Login';
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

?>

<div class='background-stripe'>
</div>

<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', 
            array(
                'id' => 'login-form',
                'enableClientValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                ),    
            )
        );
    ?>
    <div class='x2-icon'>
        <img src='<?php echo Yii::app()->baseUrl . '/uploads/logos/Golden-Tree-Business-Brokers%20logo.png'?>' />
        <!--<div>X2</div>-->
        <!--<div>touch</div>-->
    </div>
    <div data-role="fieldcontain">
        <!--?php echo $form->label($model, 'username', array()); ?-->
        <?php 
        if ($hasProfile) $model->username = $profile->username;
        echo $form->textField($model, 'username', 
            array(
                'placeholder'=>Yii::t('app','username')
            )
        ); ?>
    </div>
    <div data-role="fieldcontain">
        <!--?php echo $form->label($model, 'password', array()); ?-->
        <?php echo $form->passwordField($model, 'password', 
            array(
                'placeholder'=>Yii::t('app','password')
            )
        ); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>

    <?php if($model->useCaptcha && CCaptcha::checkRequirements()) { ?>
        <div data-role="field contain">
            <?php
            $this->widget('CCaptcha',array(
                'clickableImage'=>true,
                'showRefreshButton'=>false,
                'imageOptions'=>array(
                    'style'=>'display:block;cursor:pointer;',
                    'title'=>Yii::t('app','Click to get a new image')
                )
            ));
            echo '<p class="hint">'.Yii::t('app','Please enter the letters in the image above.').'</p>';
            echo $form->textField($model,'verifyCode', array('style'=>'height:50px;'));
            echo $form->error($model, 'verifyCode'); 
            ?>
        </div>
    <?php } ?>
    <?php echo CHtml::submitButton(Yii::t('app', 'Login')); ?>
    <table class='login-row'>
        <tbody>
            <tr>
                <td>
                    <div data-role="fieldcontain" class='remember-me-checkbox-container'>
                        <?php 
                        if ($model->rememberMe) {
                            echo $form->hiddenField($model,'rememberMe',array('value'=>1));
                        ?>
                        <a href='<?php echo Yii::app()->createUrl ('/mobile/site/forgetMe'); ?>'
                         class='x2-link x2-minimal-link'>
                            <?php echo Yii::t('app', 'Change User'); ?>
                        </a>
                        <?php
                        } else {
                            echo $form->checkBox(
                                $model,'rememberMe',array('value'=>'1','uncheckedValue'=>'0')); 
                            echo $form->label($model,'rememberMe',array('style'=>'font-size:10px;')); 
                            echo $form->error($model,'rememberMe'); 
                        }
                        ?>
                    </div>
                </td>
                <td>
                    <div data-corners="true" data-shadow="true" 
                    data-iconshadow="true" data-wrapperels="span" 
                    data-theme="a" data-disabled="false" 
                    class="ui-btn ui-btn-corner-all ui-btn-up-a full-site" 
                    aria-disabled="false">
                        <?php echo CHtml::link (
                            Yii::t('mobile', 'Go to Full Site'),
                            Yii::app()->getBaseUrl().'/index.php/site/index?mobile=false',
                            array(
                                'rel'=>'external', 
                                'onClick'=>'setMobileBrowserFalse()',
                                'class'=>'ui-btn-inner'
                                )
                            ); ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>


    <?php $this->endWidget(); ?>
</div>
<script>
// prevent ajax form post to ensure that application config settings get set after login
$.mobile['ajaxEnabled'] = false; 
</script>
