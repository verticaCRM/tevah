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

Yii::import('application.components.util.*');

/**
 * Repair action/contact data corrupted by user deletion prior to version 4.1
 */
class RepairUserDataCommand extends CConsoleCommand {

    public function actionRepair ($username) {
        /**/print ("Repairing user data for ".$username."\n");

        $adminUser = User::model()->findByPk(1);
        if (!$adminUser) {
            throw new CException (Yii::t('app', 'admin user could not be found'));
            return 1;
        }

        $params = array (
            ':username' => $username,
            ':adminUsername' => $adminUser->username
        );

        /**/print ("Reassigning associated actions\n");

        // reassign associated actions
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET completedBy=:adminUsername
            WHERE assignedTo=:username AND completedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute (array (
            ':username' => $username
        ));

        /**/print ("Reassigning associated contacts\n");

        // reassign related contacts to anyone
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute ($params);
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute (array (
            ':username' => $username
        ));

        return 0;
    }

}

?>

