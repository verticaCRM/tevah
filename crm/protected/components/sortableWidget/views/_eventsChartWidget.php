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


$this->widget('X2Chart', array (
    'getChartDataActionName' => 'getEventsBetween',
    'suppressChartSettings' => false,
    'actionParams' => array (),
    'metricTypes' => array (
        'any'=>Yii::t('app', 'All Events'),
        'notif'=>Yii::t('app', 'Notifications'),
        'feed'=>Yii::t('app', 'Feed Events'),
        'comment'=>Yii::t('app', 'Comments'),
        'record_create'=>Yii::t('app', 'Records Created'),
        'record_deleted'=>Yii::t('app', 'Records Deleted'),
        'weblead_create'=>Yii::t('app', 'Webleads Created'),
        'workflow_start'=>Yii::t('app', 'Process Started'),
        'workflow_complete'=>Yii::t('app', 'Process Complete'),
        'workflow_revert'=>Yii::t('app', 'Process Reverted'),
        'email_sent'=>Yii::t('app', 'Emails Sent'),
        'email_opened'=>Yii::t('app', 'Emails Opened'),
        'web_activity'=>Yii::t('app', 'Web Activity'),
        'case_escalated'=>Yii::t('app', 'Cases Escalated'),
        'calendar_event'=>Yii::t('app', 'Calendar Events'),
        'action_reminder'=>Yii::t('app', 'Action Reminders'),
        'action_complete'=>Yii::t('app', 'Actions Completed'),
        'doc_update'=>Yii::t('app', 'Doc Updates'),
        'email_from'=>Yii::t('app', 'Email Received'),
        'voip_calls'=>Yii::t('app', 'VOIP Calls'),
        'media'=>Yii::t('app', 'Media')
    ),
    'chartType' => 'eventsChart',
    'getDataOnPageLoad' => true,
    'hideByDefault' => false,
    'isAjaxRequest' => $isAjaxRequest,
    'chartSubtype' => $chartSubtype
));
