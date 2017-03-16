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

/*
Used to render tab forms in without the publisher
*/

$form = $this->beginWidget(
    'X2ActiveForm',
    array(
        'id' => 'publisher-form',
        'namespace' => $tab->namespace,
    )); 
?>
<div class='form'>
<?php

$tab->renderTab (array (
    'form' => $form,
    'model' => $model,
    'associationType' => $associationType,
));
if ($associationType !== 'calendar') {
    echo $form->hiddenField($model, 'associationType'); 
    echo $form->hiddenField($model, 'associationId'); 
}
?>
<div class='row'>
    <input type='submit' value='Save' id='<?php echo $form->resolveId ('save-publisher'); ?>' 
     class='x2-button'>
    <?php echo CHtml::hiddenField('SelectedTab', $tab->tabId); ?> 
</div>
<?php $this->endWidget(); ?>
</div class='form'>
<?php
