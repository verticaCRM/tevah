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

<div id="password-reset-form-outer">
    <div class="container" id="login-page">
        <div id="login-box">
            <div id='login-title-container'>
                <h1 id='app-title'>
                    <?php echo $title; ?>
                </h1>
                <p><?php echo $message; ?></p>
            </div>
            <?php if($scenario != 'message') { ?>
            <?php echo CHtml::beginForm(); ?>
            <div class="form" id="login-form">
                <div class="row">
                    <?php if($scenario=='new') {
                        echo CHtml::activeTextField($request, 'email').'<br />';
                        echo CHtml::errorSummary($request);
                    } else if($scenario == 'apply') {
                        echo CHtml::activeLabel($resetForm, 'password');
                        echo CHtml::activePasswordField($resetForm, 'password').'<br />';
                        echo CHtml::activeLabel($resetForm, 'confirm');
                        echo CHtml::activePasswordField($resetForm, 'confirm').'<br />';
                        echo CHtml::errorSummary($resetForm);
                    }
                    echo CHtml::submitButton(Yii::t('app','Submit'),
                            array(
                                'class'=>'x2-button x2-blue',
                                'style'=>'color:white; margin: 0 auto;'));
                    ?>
                </div>
            </div><!-- #login-form -->
            <?php echo CHtml::endForm(); ?>
            <?php } else {
                echo '<hr />'.CHtml::link(Yii::t('app','Sign In'),
                                        array('/site/login'),
                                        array(
                                            'class'=>'x2-button x2-blue',
                                            'style'=>'color:white;'
                                        )
                                        );
            } ?>
        </div><!-- #login-box -->
    </div><!-- #login-page -->
</div><!-- #password-reset-form-outer -->
<div id="racing-stripe">
</div>