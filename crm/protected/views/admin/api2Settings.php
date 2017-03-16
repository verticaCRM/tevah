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

/**
 * Settings for the 2nd-gen REST API
 */
?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'REST API Settings'); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <div class="row">
            <p><?php echo Yii::t('admin', 'These settings configure the behavior '
                    . 'of the X2Engine REST API at: {url}. For more information '
                    . 'about this API and how to use it, see {docUrl}', array(
                '{url}' => '<strong>'.CHtml::encode($this->createAbsoluteUrl('/api2')).'</strong>',
                 '{docUrl}' => CHtml::link(Yii::t('admin','The X2Engine REST API Reference'),'http://wiki.x2engine.com/wiki/REST_API_Reference')

                )); ?></p>
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'settings-form',
            ));
            $model->api2->renderInputs();
            ?>
            <?php
            echo CHtml::submitButton(Yii::t('app', 'Save'), array('class' => 'x2-button', 'id' => 'save-button')) . "\n";
            $this->endWidget();
            ?>
        </div>
    </div>
</div>
