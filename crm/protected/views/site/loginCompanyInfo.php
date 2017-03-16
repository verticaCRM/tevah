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

/* x2plastart */
if (X2_PARTNER_DISPLAY_BRANDING) {
?>
<div id="login-x2engine-partner-content">
    <?php
        $brandingFile = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'login.php';
        $brandingFileTemplate = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'login_example.php';
        if(file_exists($brandingFile)){
            require_once $brandingFile;
        }else{
            require_once $brandingFileTemplate;
        }
    ?>
</div>
<?php
}
/* x2plaend */
?>
<div id="login-x2engine"<?php /* x2plastart */echo (X2_PARTNER_DISPLAY_BRANDING ? 'class="with-partner-branding"' : '');/* x2plaend */ ?> >
    <div class="cell company-logo-cell">
        <?php echo CHtml::image(Yii::app()->loginLogoUrl, 'X2Engine', array('id' => 'login-logo', 'width' => 60, 'height' => 60)); ?>
    </div>
    <div id='x2-info'>
        <div id="login-version">
            <span>VERSION <?php echo Yii::app()->params->version; ?>, <a href="http://www.x2engine.com">X2Engine, Inc.</a></span>
            <span><?php echo strtoupper(Yii::app()->getEditionLabel(true)); ?>
            </span>
        </div>
        
        <div>
        <?php 
        if(Yii::app()->settings->edition == 'opensource'){
            echo '&nbsp;&bull;&nbsp;'.CHtml::link("LICENSE", Yii::app()->baseUrl.'/LICENSE.txt');
        } ?>
        </div>
    </div>
</div>
