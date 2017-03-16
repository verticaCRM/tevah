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

/* x2prostart */
Yii::app()->clientScript->registerCss('publicInfoCss',"
#domain-alias-explanation {
    background-color: rgb(223, 223, 223);
    padding: 11px;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -o-border-radius: 4px;
    border: 1px solid #C0C0C0;
    margin:10px;
}
#cname-record-example td {
    border-bottom: 1px solid rgb(194,194,194);
    padding: 4px;
}
");
/* x2proend */

?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Public Info Settings'); ?></h2></div>
<div class="admin-form-container">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'settings-form',
        'enableAjaxValidation' => false,
            ));
    ?>
    <div class="form">
        <?php echo $form->labelEx($model, 'externalBaseUrl'); ?><br />
        <p><?php 
        echo Yii::t('admin', 'This will be the web root URL to use for generating URLs to '.
            'public-facing resources, i.e. email tracking images, the web tracker, targeted '.
            'content etc.');
        ?></p>
        <?php
/* x2prostart */
        ?>
        <p><?php 
        echo Yii::t('admin','If you want to track contacts on a website with a domain that is '.
            'different from the domain on which X2 is hosted, you\'ll have to set this to your '.
            'website domain alias (see below for information on setting up a domain alias).');
        ?>
        </p>
<?php
/* x2proend */
?>
        <p><?php 
        echo Yii::t('admin', 'You ');
        /* x2prostart */  
        echo Yii::t('admin', ' also ');
        /* x2proend */ 
        echo Yii::t('admin', 'should use this if the CRM is behind a firewall and you access X2Engine using a different URL than one would use to access it from the internet (i.e. a host name / IP address on a private subnet or VPN).'); 
        ?></p>
        <?php echo $form->textField($model, 'externalBaseUrl',array('style' => 'width: 90%')); ?>
        <?php echo CHtml::error($model, 'externalBaseUrl');?>

        <br /><br />
        
        <?php echo $form->labelEx($model,'externalBaseUri'); ?><br />
        <p><?php echo Yii::t('admin','If the relative path from the web root differs between how you are accessing it now and how it will be accessed through public-facing URLs, enter it here. For example, if the CRM is accessed within {samplePrivateUrl}, and public assets will be accessed within {samplePublicUrl}, set this value to {samplePublicUri}.',array(
            '{samplePrivateUrl}' => 'http://internaldomain.net/x2',
            '{samplePublicUrl}' => 'http://publicsite.com/crm',
            '{samplePublicUri}' => '/crm'
        )) ?></p>
        <?php echo $form->textField($model,'externalBaseUri'); ?>
        <?php echo CHtml::error($model, 'externalBaseUri');?>
<?php
/* x2prostart */
?>
        <div id="domain-alias-explanation">
            <p><?php
        echo Yii::t('admin','To set up a website domain alias for tracking, you\'ll need to create a'.
            ' CNAME DNS resource record through your domain name registrar. Your CNAME record\'s name should '. 
            'refer to a subdomain of your website and should point to the domain of your CRM.'); ?>
            </p>
            <p>
        <?php
        echo Yii::t('admin','For example, '.
            'if your website is on the domain {websiteDomain}, your domain alias could be {domainAlias}.'.
            ' If your CRM is hosted at {crmDomain}, {domainAlias} would be an alias for '.
            '{crmDomain}. Your CNAME record would then look as follows:', array (
                '{websiteDomain}' => 'www.example.com',  
                '{crmDomain}' => 'www.examplecrm.com',  
                '{domainAlias}' => 'www2.example.com',  
            )); ?>
            </p>
            <p>
              <table id="cname-record-example">
                  <tr>
                      <td>Name</td>
                      <td>Type</td>
                      <td>Value</td>
                  </tr>
                  <tr>
                      <td>www2.example.com</td>
                      <td>CNAME</td>
                      <td>www.examplecrm.com</td>
                  </tr>
              </table>
            </p>
        </div>
<?php
/* x2proend */
?>
    </div><!-- .form -->

    <?php echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button'))."\n"; ?>
    <?php $this->endWidget(); ?>
</div><!-- .span-16 -->
