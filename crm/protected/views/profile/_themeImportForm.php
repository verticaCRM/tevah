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

Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/FileUtil.js', CClientScript::POS_END);

Yii::app()->clientScript->registerScript('importThemeJS',"

$(function () {

$('#submit-theme').click (function (e) {
    return x2.fileUtil.validateFile ($('#theme-upload'), ['json'], this);
});

});
");


echo CHtml::form(
    array (''), 
    'post', 
    array(
        'enctype' => 'multipart/form-data',
        'id' => 'theme-import-form',
        'style' => 'display: none;'
    )
);
?>
<div class='form'>
    <div id='upload-how-to'>
    <?php
    echo Yii::t('profile', 'Upload a theme that has been exported with the theme import tool.');
    ?>
    </div>
    <?php
    echo CHtml::fileField(
        'themeImport', 
        '', 
        array (
            'id' => 'theme-upload',
            'onchange' => 'x2.fileUtil.validateFile (this, ["json"], $("#submit-theme"));'
        )
    );
    ?>
        <select name='private' class='prefs-theme-privacy-setting x2-select'>
            <option value='0' selected='selected'>
                <?php echo Yii::t('app', 'Public'); ?>
            </option>
            <option value='1'>
                <?php echo Yii::t('app', 'Private'); ?>
            </option>
        </select>
    <?php
    echo CHtml::submitButton(
        Yii::t('app','Submit'), 
        array(
            'id' => 'submit-theme', 
            'class' => 'x2-button',
        )
    );
    ?>
</div>
<?php
echo CHtml::endForm();
