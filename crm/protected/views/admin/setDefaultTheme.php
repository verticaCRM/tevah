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

Yii::app()->clientScript->registerCss('setDefaultThemeCSS',"
#default-theme-submit {
    margin-top: 23px;
}

#theme-inputs {
    margin-bottom: 15px;
}

#theme-dropdown {
    float: left;
}

#import-theme-button {
    margin-top: 1px;
}

.x2-checkbox-row {
    margin: 5px 0;
}

[for='enforceDefaultTheme'] {
    padding-left: 4px !important;
}

");

Yii::app()->clientScript->registerScript('setDefaultThemeJS',"

(function setupThemeImport () {
    $('#import-theme-button').click (function () {
        if ($('#theme-import-form').closest ('.ui-dialog').length) {
            $('#theme-import-form').dialog ('open');
        }
        $('#theme-import-form').dialog ({
            title: '".CHtml::encode (Yii::t('admin', 'Import a Theme'))."',
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: '".CHtml::encode (Yii::t('admin', 'Close'))."',
                    click: function () { $(this).dialog ('close'); }
                }
            ]
        });
    });
}) ();

");

?>

<div class="page-title"><h2><?php echo Yii::t('admin','Set a Default Theme'); ?></h2></div>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'set-default-theme-form',
	'enableAjaxValidation'=>false,
)); 
?>
<div class='form'>
<?php
echo X2Html::getFlashes ();
echo Yii::t('admin','Set a default theme which will automatically be set for all new users.');
echo '&nbsp;'.Yii::t('admin', 'To get started, go to {preferences} and create at least one theme.',array('{preferences}'=>CHtml::link(Yii::t('profile','Preferences'),array('/profile/settings'))));
?>
<div id='theme-inputs'>
    <br>
    <label for='theme'><?php echo Yii::t('admin', 'Theme: '); ?></label>
    <?php
    echo CHtml::dropDownList (
        'theme', $defaultTheme ? $defaultTheme : '', $themeOptions, array (
            'class' => 'x2-select',
            'id' => 'theme-dropdown',
            'style' => 'margin-right:10px;'
        ));
    ?>
    <button type='button' class='x2-button x2-small-button' id='import-theme-button'>
        <?php echo Yii::t('profile', 'Import Theme'); ?>
    </button>
    <?php
    ?>
</div>
<div class='x2-checkbox-row'>
<?php
echo CHtml::checkBox ('setDefaultTheme', (bool) $defaultTheme, array (
    'class' => 'left' 
));
echo CHtml::label (CHtml::encode (Yii::t('admin', 'Set selected as default theme')),
    'setDefaultTheme', array ('class' => ''));
?>
</div>
<div class='x2-checkbox-row'>
<?php
echo CHtml::checkBox ('enforceDefaultTheme', $enforceDefaultTheme, array (
    'class' => 'left' 
));
echo CHtml::label (CHtml::encode (Yii::t('admin', 'Enforce use of default theme')),
    'enforceDefaultTheme', array ('class' => 'left'));
echo X2Html::hint (Yii::t('admin', 'If this option is set, users will not be able to customize or change their themes. All new users and all current users will be given this theme.'), false, null, true);
?>
</div>

<?php 
echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button', 'id'=> 'default-theme-submit')); 
?>
</div>
<?php
$this->endWidget(); 

$this->renderPartial ('application.views.profile._themeImportForm');
