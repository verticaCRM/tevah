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

/*
Ensure that guest profile exists and has correct id.
Give profiles to users that don't have them.
*/


$guestProfileFix = function () {
    $guestProfileUsername = '__x2_guest_profile__';

    //print_r ('deleting guest profile'."\n");
    Yii::app()->db->createCommand ("
        delete from x2_profile where username=:guestProfileUsername
    ")->execute (array (
        ':guestProfileUsername' => $guestProfileUsername
    ));
    //print_r ('inserting new guest profile'."\n");
    Yii::app()->db->createCommand ("
        INSERT INTO x2_profile (id, fullName, username, emailAddress, status)
		    VALUES (-1, '', :guestProfileUsername, '', '0')
    ")->execute (array (
        ':guestProfileUsername' => $guestProfileUsername
    ));
    $users = Yii::app()->db->createCommand ("
        select *
        from x2_users
    ")->queryAll ();

    // look for users without a profile record and create one
    foreach ($users as $row) {
        $id = $row['id'];
        $profileCount = intval (Yii::app()->db->createCommand ("
            select count(*)
            from x2_profile
            where id=:id
        ")->queryScalar (array (
            ':id' => $id
        )));
        if ($profileCount === 0) {
            //print_r ('creating missing profile record'."\n");
            Yii::app()->db->createCommand ("
                INSERT INTO x2_profile (
                    `fullName`, `username`, `allowPost`, `emailAddress`, `status`, `id`)
                    VALUES (:fullName, :username, :allowPost, :emailAddress, :status, :id)
            ")->execute (array (
                ':fullName' => $row['firstName'].' '.$row['lastName'],
                ':username' => $row['username'],
                ':allowPost' => 1,
                ':emailAddress' => $row['emailAddress'],
                ':status' => 1,
                ':id' => $row['id']
            ));
        }
    }
};

$guestProfileFix ();
