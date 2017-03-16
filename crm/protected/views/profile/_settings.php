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

Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/profileSettings.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCss("profileSettings", "

    
#theme-attributes select, #theme-attributes button,
#prefs-create-theme-hint {
    display: inline-block;
}

/*
prevents FF checkbox border cutoff
*/
#profile-settings input[type='checkbox'] {
    margin-left: 2px !important;
}

.preferences-section {
    border-bottom: 1px solid #C2C2C2 !important;
}

.tag{
    -moz-border-radius:4px;
    -o-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    border-style:solid;
    border-width:1px;
    border-color:gray;
    margin:2px 2px;
    display:block;
    float:left;
    padding:2px;
    background-color:#f0f0f0;
}
.tag a {
    text-decoration:none;
    color:black;
}

#settings-form .prefs-hint {
    height: 28px;
    color:#06c;
    margin-right: 4px;
}

/* override spectrum color picker css */
.sp-replacer {
    padding: 0px !important;
}
.sp-dd {
    height: 13px !important;
}
.sp-preview
{
    width:20px !important;
    height: 17px !important;
    margin-right: 5px !important;
}

/* modify standard form style, remove rounded borders */
#settings-form .form {
    margin: 0 0 0 0;
    border-radius: 0;
    -webkit-border-radius: 0;
}

#settings-form .color-picker-input {
    margin-right: 6px;
}

#settings-form #theme-attributes-body .row {
    margin-top: 5px;
    margin-bottom: 5px;
    overflow: hidden;
}

/* temporary change to allow small buttons, this should exist across the app */
#settings-form .x2-small-button  {
    padding: 0 4px 0 4px !important;
    margin: 2px 4px 0 0;
    /*float:left;*/
}

#settings-form .x2-button-group .x2-small-button  {
    padding: 0 4px 0 4px !important;
    float:left;
    margin-right: 0px;
}


/* prevents side-by-side borders between touching forms */
#profile-settings {
    border-top: 0;
}

/* prevents side-by-side borders between touching forms */
#profile-settings,
#theme-attributes,
#prefs-tags {
    border-bottom: 0;
}

#theme-attributes,
.upload-box {
    border-top: 1px solid #C2C2C2 !important;
}

/* sub-menu maximize/minimize arrows */
#theme-attributes .minimize-arrows,
#prefs-tags .minimize-arrows {
    margin-top: 15px;
    width: 20px;
    height: 20px;
    text-align: center;
}

/* spacing in the create a theme sub menu */
.theme-name-input-container {
    margin-top: 9px;
    margin-bottom: 0px;
}

/* validation in the create a theme sub menu */
#create-theme-box input.error
{
    background: #FEE;
    border-color: #C00 !important;
}

/* spacing in the create a theme sub menu */
#create-theme-box input {
    margin-top: 0px;
}

/* spacing in the create a theme sub menu */
#new-theme-name {
    width: 170px;
    margin-left: 4px;
    margin-bottom: 4px;
}

select#themeName,
select#backgroundImg,
select#loginSounds,
select#themeName,
select#notificationSounds {
    /*margin-right: 4px;*/
}

#save-changes {
    margin-bottom: 5px;
}

#prefs-save-theme-button,
#prefs-create-theme-button,
#upload-theme-button,
#export-theme-button,
#upload-background-img-button,
#upload-login-sound-button,
#upload-notification-sound-button {
    margin-top: 2px;
}

.no-theme-editor {
    display: none;
}

.no-theme-editor + #prefs-tags {
    border-top: 1px solid #C2C2C2;
}


");

$preferences = $model->theme;
$miscLayoutSettings = $model->miscLayoutSettings;

$passVariablesToClientScript = "
    x2.profileSettings = {};
    x2.profileSettings.checkerImagePath = '".
        Yii::app()->theme->getBaseUrl()."/images/checkers.gif';
    x2.profileSettings.createThemeHint = '".
        Yii::t('profile', 'Save your current theme settings as a predefined theme.')."';
    x2.profileSettings.saveThemeHint = '".
        Yii::t('profile', 'Update the settings of the currently selected predefined theme.')."';
    x2.profileSettings.normalizedUnhideTagUrl = '".
        CHtml::normalizeUrl(array("/profile/unhideTag"))."';
    x2.profileSettings.translations = {
        themeImportDialogTitle: '".Yii::t('profile', 'Import a Theme')."',
        close: '".Yii::t('app', 'close')."',
    };
    x2.profileSettings.uploadedByAttrs = {};";

// pass array of predefined theme uploadedBy attributes to client
foreach($myThemes->data as $theme){
    $passVariablesToClientScript .= "x2.profileSettings.uploadedByAttrs['".
            $theme->id."'] = '".$theme->uploadedBy."';";
}

Yii::app()->clientScript->registerScript(
        'passVariablesToClientScript', $passVariablesToClientScript, CClientScript::POS_BEGIN);

// If the user was redirected from /site/upload and the "useId" parameter is 
// available, set the background to that so they get instant feedback
if(isset($_GET['bgId'])) {
    $media = Media::model()->findByPk($_GET['bgId']);
    if($media instanceof Media) {
        Yii::app()->clientScript->registerScript(
            'setBackgroundToUploaded',
            '$("select#backgroundImg").val('
                .json_encode(
                    'media/'.Yii::app()->user->name.'/'.$media->fileName).').trigger("change");'
            ,CClientScript::POS_READY);
    }
}

?>

<?php
$form = $this->beginWidget('X2ActiveForm', array(
    'id' => 'settings-form',
    'enableAjaxValidation' => false,
        ));
?>
<?php echo $form->errorSummary($model); ?>

<div id="profile-settings" class="form">
    <?php
    echo X2Html::getFlashes ();
    ?>
    <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disablePhoneLinks', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disablePhoneLinks', array('style' => 'display:inline;'));
            ?>
            <span class='x2-hint' title='<?php 
             echo Yii::t('app', 'Prevent phone number fields from being formatted as links.'); ?>'>[?]</span>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableAutomaticRecordTagging', 
                    array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            echo '&nbsp;'.$form->labelEx(
                    $model, 'disableAutomaticRecordTagging', array('style' => 'display:inline;'));
            ?>
            <span class='x2-hint' title='<?php 
             echo Yii::t('app', 'Prevent tags from being automatically generated when hashtags are detected in record fields.'); ?>'>[?]</span>
        </div>
    </div>
    <?php if(Yii::app()->contEd('pro')) { ?>
    <div class="row"> 
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableTimeInTitle', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disableTimeInTitle', array('style' => 'display:inline;'));
            ?>
        </div>
    </div>
    <?php } ?>
     <div class="row" style="margin-bottom:10px;">
        <div class="cell">
            <?php
            echo $form->checkBox(
                    $model, 'disableNotifPopup', array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
            <?php
            echo $form->labelEx(
                    $model, 'disableNotifPopup', array('style' => 'display:inline;'));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'startPage'); ?>
            <?php
            echo $form->dropDownList(
                $model, 'startPage', $menuItems,
                array('onchange' => 'js:x2.profileSettings.highlightSave();', 'style' => 'min-width:140px;'));
            ?>
        </div>
        <div class="cell">
            <?php echo $form->labelEx($model, 'resultsPerPage'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'resultsPerPage', Profile::getPossibleResultsPerPage(),
                    array('onchange' => 'js:x2.profileSettings.highlightSave();', 'style' => 'width:100px'));
            ?>
        </div>

    </div>
    <div class="row">
        <div class="cell">
            <?php echo $form->labelEx($model, 'language'); ?>
            <?php
            echo $form->dropDownList(
                    $model, 'language', $languages, array('onchange' => 'js:x2.profileSettings.highlightSave();'));
            ?>
        </div>
        <div class="cell">
            <?php
            if(!isset($model->timeZone))
                $model->timeZone = "Europe/London";
            ?>
            <?php echo $form->labelEx($model, 'timeZone'); ?>
            <?php
            echo $form->dropDownList(
                $model, 'timeZone', $times,
                array(
                    'onchange' => 'js:x2.profileSettings.highlightSave();'
                ));
            ?>
        </div>
    </div>
</div>
<div id="theme-attributes" class='form preferences-section<?php 
    echo /* x2plastart */($displayThemeEditor ? 
        ' no-theme-editor': /* x2plaend */''/* x2plastart */)/* x2plaend */; 
    ?>'>
    <div id="theme-attributes-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('app', 'Theme'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow" src="<?php 
                echo Yii::app()->theme->getBaseUrl()."/images/icons/Expand_Widget.png"; ?>" />
            <img class="hide prefs-collapse-arrow" src="<?php 
                echo Yii::app()->theme->getBaseUrl()."/images/icons/Collapse_Widget.png"; ?>" />
        </div>
    </div>
    <div id="theme-attributes-body" class="row prefs-body" <?php echo
        ($miscLayoutSettings['themeSectionExpanded'] == false ? 'style="display: none;"' : ''); ?>>
        <div class="row" id='theme-mgmt-buttons'>
            <input type="hidden" id="themeName" class="theme-attr x2-select" name="preferences[themeName]" />

            <div class='x2-button-group'>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-create-theme-button'>
                            <?php echo X2Html::fa("fa-copy") ?>
                            <?php echo Yii::t('profile', 'New'); ?>
                </button>
                <!-- <span id="prefs-create-theme-hint" class='prefs-hint'></span> -->
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-save-theme-button'>
                            <?php echo X2Html::fa("fa-save") ?>
                            <?php echo Yii::t('profile', 'Save'); ?>
                </button>
                <!-- <span id="prefs-save-theme-hint" class='hide prefs-hint'></span> -->
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-delete-theme-button'>
                            <?php echo X2Html::fa("fa-trash") ?>
                            <?php echo Yii::t('profile', 'Delete'); ?>
                </button>
                <?php /* x2plastart */ ?>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-import-theme-button'>
                            <?php echo X2Html::fa("fa-download") ?>
                            <?php echo Yii::t('profile', 'Import'); ?>
                </button>
                <button type='button' class='x2-button x2-small-button'
                        id='prefs-export-theme-button'>
                            <?php echo X2Html::fa("fa-upload") ?>
                            <?php echo Yii::t('profile', 'Export'); ?>
                </button>
            </div>
            <?php /* x2plaend */ ?>
            <div style="clear:both"></div>

            <?php $this->renderPartial('_themeSettings', array(
                'myThemes' => $myThemes,
                'selected' => $preferences['themeName'])
            ); ?>

        </div>

        <?php 
            ThemeGenerator::renderSettings();
        ?>
        <div class="row">
            <label for="backgroundTiling">
                <?php echo Yii::t('app', 'Background Tiling') ?>
            </label>
            <select id="backgroundTiling" name="preferences[backgroundTiling]"
             class='theme-attr x2-select'>
                        <?php
                        $tilingOptions = array(
                            'stretch', 'center', 'repeat', 'repeat-x', 'repeat-y');
                        foreach($tilingOptions as $option){
                            ?>
                    <option value="<?php echo $option; ?>"
                    <?php
                    echo $option == $preferences['backgroundTiling'] ?
                            "selected=\'selected\'" : '';
                    ?>>
                                <?php echo Yii::t('app', $option) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row">
            <label for="backgroundImg">
                <?php echo Yii::t('profile', 'Background Image'); ?>
            </label>
            <select id="backgroundImg" name="preferences[backgroundImg]"
                    class='theme-attr x2-select'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach ($myBackgrounds->data as $background) { ?>
                    <option value="<?php
                        echo $background->uploadedBy == null ?
                            $background->fileName :
                            ('media/'.$background->uploadedBy.'/'.$background->fileName); ?>"
                        <?php
                        if($background->fileName == $preferences['backgroundImg']){
                            echo "selected='selected'";
                        } ?>>
                        <?php echo $background->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-background-img-button'>
                        <?php echo Yii::t('profile', 'Upload Background Image'); ?>
            </button>
        </div>
        <div class="row">
            <label for="loginSounds">
                <?php echo Yii::t('profile', 'Login Sound'); ?>
            </label>
            <select id="loginSounds" name="preferences[loginSound]" class='x2-select'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach($myLoginSounds->data as $loginSound){ ?>
                    <option value="<?php
                echo $loginSound->id.",".
                $loginSound->fileName.",".$loginSound->uploadedBy;
                    ?>"
                            id="sound-<?php echo $loginSound->id; ?>"
                            <?php
                            if($loginSound->fileName == $model->loginSound){
                                echo "selected='selected'";
                            }
                            ?>>
                                <?php echo $loginSound->fileName; ?>
                    </option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-login-sound-button'>
                        <?php echo Yii::t('profile', 'Upload Login Sound'); ?>
            </button>
        </div>
        <div class="row">
            <label for="notificationSounds">
                <?php echo Yii::t('profile', 'Notification Sound'); ?>
            </label>
            <select id="notificationSounds" name="preferences[notificationSound]"
                    class='x2-select'>
                <option value=""> <?php echo Yii::t('app', 'None'); ?> </option>
                <?php foreach($myNotificationSounds->data as $notificationSound){ ?>
                    <option value="<?php
                        echo $notificationSound->id.",".$notificationSound->fileName.",".
                            $notificationSound->uploadedBy; ?>"
                     id="sound-<?php echo $notificationSound->id; ?>"
                     <?php
                     if($notificationSound->fileName == $model->notificationSound){
                         echo "selected='selected'";
                     }
                     ?>><?php echo $notificationSound->fileName; ?></option>
                <?php } ?>
            </select>
            <button type='button' class='x2-button x2-small-button'
                    id='upload-notification-sound-button'>
                        <?php echo Yii::t('profile', 'Upload Notification Sound'); ?>
            </button>
        </div>
    </div>

</div>

<div id="prefs-tags" class="form preferences-section">
    <div id="tags-title-bar" class="row prefs-title-bar">
        <h3 class="left"><?php echo Yii::t('profile', 'Unhide Tags'); ?></h3>
        <div class="right minimize-arrows">
            <img class="prefs-expand-arrow"
                 src="<?php 
                    echo Yii::app()->theme->getBaseUrl() ?>/images/icons/Expand_Widget.png"/>
            <img class="hide prefs-collapse-arrow"
                 src="<?php 
                    echo Yii::app()->theme->getBaseUrl() ?>/images/icons/Collapse_Widget.png"/>
        </div>
    </div>
    <div id="tags-body" class="row prefs-body" <?php echo
        ($miscLayoutSettings['unhideTagsSectionExpanded'] == false ? 
            'style="display: none;"' : ''); ?>>
        <?php
        foreach($allTags as &$tag){
            echo '<span class="tag unhide" tag-name="'.substr($tag['tag'], 1).'">'.
            CHtml::link(
                $tag['tag'], array('/search/search','term'=>'#'.ltrim($tag['tag'], '#')),
                array('class' => 'x2-link x2-tag')).
            '</span>';
        }
        ?>
    </div>
</div>

<div class="form">
    <br/>
    <div class="row buttons">
        <?php
        echo CHtml::submitButton(
            ($model->isNewRecord ? Yii::t('app', 'Create') :
                Yii::t('app', 'Save Profile Settings')), 
            array('id' => 'save-changes', 'class' => 'x2-button'));
        ?>
    </div>
</div>

<?php $this->endWidget(); ?>

<div class="form hide upload-box preferences-section" id="create-theme-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Save Theme As'); ?></h3>
        <span class='left'>
            <?php
            echo Yii::t('app', 'Saving a theme will create a theme from your'.
                    ' current theme settings');
            ?>.
        </span>
        <br/>
        <div class='theme-name-input-container'>
            <span class='left'> <?php echo Yii::t('app', 'Theme name'); ?>: </span>
            <input id="new-theme-name"> </input>
        </div>
        <select class='prefs-theme-privacy-setting x2-select'>
            <option value='0' selected='selected'>
                <?php echo Yii::t('app', 'Public'); ?>
            </option>
            <option value='1'>
                <?php echo Yii::t('app', 'Private'); ?>
            </option>
        </select>
        <br/>
        <div class="row buttons">
            <button id='create-theme-submit-button' class='x2-button submit-upload'>
                <?php echo Yii::t('app', 'Create'); ?>
            </button>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
    </div>
</div>

<div class="form hide upload-box preferences-section" id="upload-background-img-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Background Image'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data'
        )); ?>
        <?php echo CHtml::dropDownList(
            'private',
            'public',
            array(
                '0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
                'Private'
            ))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'bg'); 
        echo CHtml::fileField('upload', '', array('id' => 'background-img-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(
                Yii::t('app', 'Upload'), 
                array(
                    'id' => 'upload-background-img-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )); ?>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-login-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Login Sound'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data')
        ); 
        echo CHtml::dropDownList(
            'private', 'public', array('0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
            'Private'))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'loginSound'); 
        echo CHtml::fileField('upload', '', array('id' => 'login-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(
                Yii::t('app', 'Upload'), 
                array(
                    'id' => 'upload-login-sound-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )
            ); ?>
            <button class="x2-button cancel-upload"><?php 
                echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<div class="form hide upload-box" id="upload-notification-sound-box">
    <div class="row">
        <h3><?php echo Yii::t('profile', 'Upload a Notification Sound'); ?></h3>
        <?php echo CHtml::form(
            array('site/upload', 'id' => $model->id), 'post',
            array('enctype' => 'multipart/form-data')
        ); 
        echo CHtml::dropDownList(
            'private', 'public', 
            array(
                '0' => Yii::t('actions', 'Public'), '1' => Yii::t('actions',
                'Private'
            ))); 
        echo CHtml::hiddenField('associationId', Yii::app()->user->getId()); 
        echo CHtml::hiddenField('associationType', 'notificationSound'); 
        echo CHtml::fileField('upload', '', array('id' => 'notification-sound-file')); ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton (Yii::t('app', 'Upload'),
                array(
                    'id' => 'upload-notification-sound-submit-button', 'disabled' => 'disabled',
                    'class' => 'x2-button submit-upload'
                )); ?>
            <button class="x2-button cancel-upload"><?php echo Yii::t('app', 'Cancel'); ?></button>
        </div>
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<?php
/* x2plastart */
$this->renderPartial ('_themeImportForm');
/* x2plaend */
?>
