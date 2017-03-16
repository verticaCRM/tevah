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
<div id="emailProgressControl" class="x2-layout form-view">
    <div class="formSection">
        <div class="formSectionHeader">
            <span class="sectionTitle"><?php echo Yii::t('marketing', 'Email Delivery Progress'); ?></span>
        </div>
        <div class="tableWrapper">
            <div class="emailProgressControl-section"><div id="emailProgressControl-bar"><div id="emailProgressControl-text"></div></div></div>
            <div class="emailProgressControl-section">
                <div id="emailProgressControl-toolbar">
                    <button class="startPause x2-button">
                    
                        <?php echo X2Html::fa('fa-pause'); ?>
                        <?php echo X2Html::fa('fa-play'); ?>
                        <span class='button-text'>
                            <?php echo Yii::t('marketing', 'Pause') ?>
                        </span>
                    </button>
                    <button class="refresh x2-button" title="<?php echo CHtml::encode(Yii::t('marketing','Click to refresh displays of campaign data on this page.')); ?>">
                        <?php echo X2Html::fa('fa-refresh'); ?>
                        <?php echo Yii::t('marketing', 'Refresh'); ?>
                    </button>
                    <span id="emailProgressControl-throbber" style="display: none;" class='load8 x2-loader loader' ></span>
                    <div id="emailProgressControl-textStatus"></div>
                </div>
            </div>
            <div class="emailProgressControl-section">
                <div id="emailProgressControl-errorContainer">
                    <hr />
                    <strong><?php echo Yii::t('marketing','Errors that occurred when attempting to send email:') ?></strong>
                    <div id="emailProgressControl-errors"></div>
                </div>
            </div>
        </div>
    </div>
</div>


