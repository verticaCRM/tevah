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
 * Create/update a personal inbox
 * @param X2Model $model The email inbox
 */

 ?>
<div class='form'>
<?php
$retDict = Credentials::getCredentialOptions (
    $model, 'credentialId', 'email', Yii::app()->user->id, array(), true);
$credentials = $retDict['credentials'];
$htmlOptions = $retDict['htmlOptions'];
$credentialsNotAdded = false;
if (!count ($credentials)) {
    $credentialsNotAdded = true;
    $credentials[-1] = CHtml::encode (Yii::t('emailInboxes', 'Select one'));
}


$form = $this->beginWidget ('X2ActiveForm', array (
    'id' => 'my-inbox-form',
    'htmlOptions' => array (
        'class' => 'form2',
    )
));
    echo $form->errorSummary($model);
    echo $form->label ($model, 'name');
    echo $form->textField ($model, 'name');
    echo $form->label ($model, 'credentialId');
    echo CHtml::activeDropDownList ($model, 'credentialId', $credentials, $htmlOptions);
    if ($credentialsNotAdded) {
        echo "
            <div> 
            ".CHtml::encode (
                Yii::t('emailInboxes', 'You have not added your email credentials.')).
                "&nbsp;<a href='".Yii::app()->createUrl ('/profile/manageCredentials')."'>".
                    CHtml::encode (Yii::t('emailInboxes', 'Add email credentials')).
                "</a>
            </div>";
    }
    echo '<div class="bs-row">';
        echo $form->checkBox ($model, 'settings[logOutboundByDefault]', array (
            'class' => 'left-input',
        ));
        echo $form->label ($model, 'settings[logOutboundByDefault]', array (
            'class' => 'right-label',
        ));
        echo X2Html::hint2 (EmailInboxes::getAutoLogEmailsDescription ());
    echo '</div>';
    echo '<br/>';
    echo '<br/>';
    echo '<div class="row buttons">'.
        CHtml::submitButton(
            $model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),
            array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)).
        '</div>';
$this->endWidget ();
?>
</div>
