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

$migrationScript2plaphp = function () {

    Yii::app()->db->createCommand ("INSERT INTO `x2_fields` (`attributeLabel`,`custom`,`data`,`defaultValue`,`fieldName`,`isVirtual`,`keyType`,`linkType`,`modelName`,`modified`,`readOnly`,`relevance`,`required`,`safe`,`searchable`,`type`,`uniqueConstraint`) VALUES ('Subtype',0,NULL,NULL,'eventSubtype',0,NULL,'121','Actions',0,0,'',0,1,0,'dropdown',0),('Status',0,NULL,NULL,'eventStatus',0,NULL,'122','Actions',0,0,'',0,1,0,'dropdown',0)")->execute ();
    Yii::app()->db->createCommand ("UPDATE `x2_fields` SET `linkType`='123',`type`='dropdown' WHERE `modelName`='Actions' AND `fieldName`='color'")->execute ();
    Yii::app()->db->createCommand ("UPDATE `x2_fields` SET `required`=1 WHERE `modelName`='Actions' AND `fieldName`='actionDescription'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item` WHERE `name`='ChartsFullAccess'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item` WHERE `name`='ReportsFullAccess'")->execute ();
    Yii::app()->db->createCommand ("INSERT INTO `x2_auth_item` (`bizrule`,`data`,`description`,`name`,`type`) VALUES (NULL,'N;','','ChartsReadOnlyAccess',1),(NULL,'N;','','ReportsReadOnlyAccess',1),(NULL,'N;','','CalendarIcal',0)")->execute ();
    Yii::app()->db->createCommand ('INSERT INTO `x2_dropdowns` (`id`,`multi`,`name`,`options`,`parent`,`parentVal`) VALUES (122,0,\'Event Statuses\',\'{"Confirmed":"Confirmed","Cancelled":"Cancelled"}\',NULL,NULL),(123,0,\'Event Colors\',\'{"#008000":"Green","#3366CC":"Blue","#FF0000":"Red","#FFA500":"Orange","#000000":"Black"}\',NULL,NULL),(121,0,\'Event Subtypes\',\'{"Meeting":"Meeting","Appointment":"Appointment","Call":"Call"}\',NULL,NULL)')->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsPipeline'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsDelete'")->execute ();
    /* x2proend */ 
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsDealReport'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsWorkflow'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsWorkflow'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='DefaultRole' AND `child`='ChartsFullAccess'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsActivityReport'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsSales'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsSaveTempImage'")->execute ();
    /* x2proend */ 
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsSavedReports'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsDeleteNote'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsPrintReport'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='DefaultRole' AND `child`='DocsUpdatePrivate'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsLeadVolume'")->execute ();
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsMinimumRequirements'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsGridReport'")->execute ();
    /* x2proend */ 
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsDeleteNote'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ChartsFullAccess' AND `child`='ChartsMarketing'")->execute ();
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsSaveReport'")->execute ();
    /* x2proend */ 
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsLeadPerformance'")->execute ();
    /* x2proend */ 
    /* x2prostart */ 
    Yii::app()->db->createCommand ("DELETE FROM `x2_auth_item_child` WHERE `parent`='ReportsFullAccess' AND `child`='ReportsMinimumRequirements'")->execute ();
    /* x2proend */ 
    Yii::app()->db->createCommand (
        "INSERT INTO `x2_auth_item_child` (`child`,`parent`) VALUES 
            ('ChartsDeleteNote','ChartsReadOnlyAccess'),
            ('ChartsLeadVolume','ChartsReadOnlyAccess'),
            ('ChartsMarketing','ChartsReadOnlyAccess'),
            ('ChartsMinimumRequirements','ChartsReadOnlyAccess'),
            ('ChartsPipeline','ChartsReadOnlyAccess'),
            /* x2plastart */
            ('CalendarIcal','GuestSiteFunctionsTask'),
            /* x2plaend */
            ('ChartsReadOnlyAccess','administrator'),
            ('ChartsReadOnlyAccess','DefaultRole'),
            ('ChartsSales','ChartsReadOnlyAccess'),
            /* x2prostart */ 
            ('ReportsActivityReport','ReportsReadOnlyAccess'),
            ('ReportsDealReport','ReportsReadOnlyAccess'),
            ('ReportsDeleteNote','ReportsReadOnlyAccess'),
            ('ReportsDelete','ReportsReadOnlyAccess'),
            ('ReportsGridReport','ReportsReadOnlyAccess'),
            ('ReportsLeadPerformance','ReportsReadOnlyAccess'),
            ('ReportsMinimumRequirements','ReportsReadOnlyAccess'),
            ('ReportsPrintReport','ReportsReadOnlyAccess'),
            ('ReportsSavedReports','ReportsReadOnlyAccess'),
            ('ReportsSaveReport','ReportsReadOnlyAccess'),
            ('ReportsSaveTempImage','ReportsReadOnlyAccess'),
            ('ReportsWorkflow','ReportsReadOnlyAccess'),
            /* x2proend */ 
            ('ChartsWorkflow','ChartsReadOnlyAccess')
        ")->execute ();

};

$migrationScript2plaphp ();
