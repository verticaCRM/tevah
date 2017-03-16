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

/* @edition:pro */

$locked = Yii::app()->locked;
if(is_int($locked) && $locked == 0) {
    $locked = filemtime(Yii::app()->lockFile);
}
$isLocked = is_int($locked);

?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Lock or Unlock X2Engine'); ?></h2></div>
<div class="admin-form-container">
    <div class="form">
        <div class="row">
            <p><?php echo Yii::t('admin', 'This feature is for shutting down X2Engine during periods of maintenance or whenever it might be favorable to prevent access and data entry. When the application is locked, it cannot be used except by the administrator, and all services (such as the web lead form and API) will be unavailable.'); ?></p>
            <?php if($isLocked): ?>
                <p><strong><?php echo Yii::t('admin', 'X2Engine is currently locked. Time it was locked: {time}', array('{time}' => Formatter::formatDateTime($locked))); ?></strong></p>
            <?php else: ?>
                <p><strong><?php echo Yii::t('admin', 'X2Engine is not currently locked.'); ?></strong></p>
            <?php
            endif;
            echo CHtml::link($isLocked ? Yii::t('admin', 'Unlock X2Engine') : Yii::t('admin', 'Lock X2Engine'), array('/admin/lockApp', 'toggle' => (string) (int) !$isLocked), array('class' => 'x2-button'));
            if($isLocked):
                ?>
                <br /><br /><p><?php echo Yii::t('admin', 'You can manually unlock the application by deleting the file {file} in {dir}', array('{file}' => '<em>"X2Engine.lock"</em>','{dir}'=>'protected/runtime'));
                ?></p>
<?php endif; ?>

        </div><!-- .row -->
    </div><!-- .form -->
</div><!-- .span-16 -->
