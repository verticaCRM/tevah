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

<div class='message-subject'>
<?php
echo CHtml::encode ($message->subject);
?>
</div>
<hr />
<div class='bs-row'>
    <div class='col-xs-6'>
        <div class='from-field' data-from='<?php echo CHtml::encode ($message->from); ?>'>
        <?php
        echo $message->renderFromField ();
        ?>
        </div>
        <div class='to-field' data-to='<?php echo CHtml::encode ($message->to); ?>'>
        <span><?php echo CHtml::encode (Yii::t('emailInboxes', 'to')).'&nbsp;'; ?></span>
        <span>
        <?php
        echo $message->renderToField ();
        ?>
        </span>
        </div>
    </div>
    <div class='col-xs-6'>
        <div class='date-field'>
        <?php
        echo $message->renderDate ();
        ?>
        </div>
        <button class='x2-button message-reply-more-button fa fa-caret-down' 
         title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'More')); ?>'></button>
        <button class='x2-button message-reply-button fa fa-reply fa-lg' 
         title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'Reply')); ?>'></button>
        <ul class='x2-dropdown-list fa-ul reply-more-menu' style='display: none;'>
            <!--<li class='message-reply-all-button'>
                <span class='fa-li fa fa-reply-all'></span><?php 
                    echo CHtml::encode (Yii::t('app', 'Reply all')) ?> </li>-->
            <li class='message-forward-button'>
                <span class='fa-li fa fa-long-arrow-right'></span><?php 
                    echo CHtml::encode (Yii::t('app', 'Forward')) ?> </li>
        </ul>
    </div>
</div>
<iframe class='message-body'></iframe>
<div class='message-body-temp'>
<?php
echo CHtml::encode ($message->body);
?>
</div>
<div class='message-attachments'>
<?php
echo $message->renderAttachmentLinks ();
?>
</div>
