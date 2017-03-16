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

?>
<div id='<?php echo $this->namespace; ?>conversion-warning-dialog' style='display: none;' 
 class='form'>
    <p><?php 
    echo Yii::t('app', 'Converting this {model} to {article} {targetModel} could result in data 
        from your {model} being lost. The following field incompatibilities have been detected: ',
        array (
            '{model}' => $modelTitle,
            '{targetModel}' => $targetModelTitle,
            '{article}' => preg_match ('/^[aeiouAEIOU]/', $targetModelTitle) ? 'an' : 'a',
        )); 
    ?>
    </p>
    <ul class='errorSummary'>
    <?php
    foreach ($this->model->getConversionIncompatibilityWarnings ($this->targetClass) as $message) {
        ?>
        <li><?php echo $message ?></li>
        <?php
    }
    ?>
    </ul>
    <p><?php 
    echo Yii::t('app', 'To resolve these incompatibilities, make sure that every custom '.
        '{model} field has a corresponding {targetModel} custom field of the same name and type.', 
        array (
            '{model}' => $modelTitle,
            '{targetModel}' => $targetModelTitle,
        ));
    ?>
    </p>
</div>
