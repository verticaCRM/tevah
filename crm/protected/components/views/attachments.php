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

Yii::app()->clientScript->registerCss('attachmentsCss',"

#attachment-form input,
#attachment-form select {
    margin-right: 11px;
}

");

?>
<div id="attachment-form-top"></div>
<div id="attachment-form"<?php if($startHidden) echo ' style="display:none;"'; ?>>
    <div class="form x2-layout-island">
        <?php
        if (!$mobile) {
        ?>
        <b><?php echo Yii::t('app', 'Attach a File'); ?></b><br />
        <?php
        }
        echo CHtml::form(
            array('/site/upload'), 'post', 
            array(
                'enctype' => 'multipart/form-data', 'id' => 'attachment-form-form'
            )
        );
        echo "<div class='row'>";
        echo CHtml::hiddenField('associationType', $this->associationType);
        echo CHtml::hiddenField('associationId', $this->associationId);
        echo CHtml::hiddenField('attachmentText', '');
        if (isset ($profileId))
            echo CHtml::hiddenField('profileId', $profileId);
        $visibilityHtmlAttrs = array ();
        if ($mobile)
            $visibilityHtmlAttrs['data-mini'] = 'true';
        echo CHtml::dropDownList(
            'private', 'public', 
            array(
                '0' => Yii::t('actions', 'Public'), 
                '1' => Yii::t('actions', 'Private')
            ),
            $visibilityHtmlAttrs
        );
        $fileFieldHtmlAttrs = array (
            'id' => 'upload', 
            'onchange' => "x2.attachments.checkName(event)"
        );
        if ($mobile) {
            $fileFieldHtmlAttrs['data-inline'] = 'true';
            $fileFieldHtmlAttrs['data-mini'] = 'true';
        }
        echo CHtml::fileField(
            'upload', '', $fileFieldHtmlAttrs
        );
        if ($mobile) 
            echo '<div style="display:none;">';
        echo CHtml::submitButton(
            Yii::t('app','Submit'), 
            array(
                'id' => 'submitAttach', 'disabled' => 'disabled', 'class' => 'x2-button',
                'style' => 'display:inline'
            )
        );
        if ($mobile) 
            echo "</div>";
        echo "</div>";
        if(Yii::app()->settings->googleIntegration){
            $auth = new GoogleAuthenticator();
            if($auth->getAccessToken()){
                echo "<div class='row'>";
                echo CHtml::label(Yii::t('app','Save to Google Drive?'), 'drive');
                echo CHtml::checkBox('drive');
                echo "</div>";
            }
        }
        echo CHtml::endForm();
        ?>
    </div>
</div>
