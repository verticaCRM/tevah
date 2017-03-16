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


Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/emailInboxes.css');

$this->insertMenu (array(
    'inbox', 'configureMyInbox', 'sharedInboxesIndex', 'createSharedInbox',
));

?>
<div id='inbox-body-container' class='inbox-body-container'>
    <div id='my-email-inbox-reconfigure-instructions-container'>
        <h2><?php 
            echo Yii::t('emailInboxes', 
                "Failed to open IMAP connection. Please check your email configuration and ensure".
                " that your email credentials are valid.");
        ?></h2>
        <a href='<?php echo $this->createUrl ('configureMyInbox'); ?>'><?php 
            echo Yii::t('emailInboxes', '-Configure your personal email inbox-'); 
        ?></a>
    </div>
</div>

