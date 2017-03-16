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
 * Create/update a shared inbox
 * @param X2Model $model The new email inbox
 */

 ?>
<div class='form'>
<?php
$form = $this->beginWidget ('CActiveForm', array (
    'id' => 'shared-email-inbox-form',
));
    echo $form->errorSummary($model);
    echo $form->label ($model, 'name');
    echo $form->textField ($model, 'name');
    echo $form->label ($model, 'credentialId');
    echo $model->renderInput ('credentialId');
    echo $form->label ($model, 'assignedTo');
    echo $model->renderInput ('assignedTo');
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
