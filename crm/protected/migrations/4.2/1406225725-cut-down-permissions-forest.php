<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/**
 * @file 1406225725-cut-down-permissions-forest.php
 * 
 * Permissions have been refactored to break the traditional inheritance structure
 * and have more granular controls. This script serves to fix older versions.
 * 
 * Originally, the permissions system had a strict inheritance structure where
 * each level of access implicitly granted each level of access "below" it where
 * Admin > Delete > Update > Create > View
 * Now, each permission can be granted individually and so older systems need
 * the links between permissions broken up and a little extra fiddling to make
 * them still functional in the new system.
 */
$fixPermissions = function() {
    // Valid access types that we'll need to split on
    $accessTypes = array('PrivateReadOnly', 'PrivateUpdate', 'PrivateFull', 'PrivateBasic', 'ReadOnly', 'Basic', 'Update', 'Full', 'Admin');
    // Get all auth_item_child records with "SomethingAccess" as parent and child,
    // i.e. (parent, child) ("ContactsUpdateAccess", "ContactsBasicAccess")
    $result = Yii::app()->db->createCommand()
            ->select('parent, child')
            ->from('x2_auth_item_child')
            ->where('parent LIKE "%Access" AND child LIKE "%Access"')
            ->queryAll();
    foreach ($result as $item) {
        $parent = $item['parent'];
        $child = $item['child'];
        $module = "";
        $permission = "";
        // Figure out which access type it is by splitting on each possible access
        // type until we get a match.
        foreach ($accessTypes as $type) {
            $pieces = explode($type, $parent);
            if (count($pieces) > 1) {
                $module = $pieces[0];
                $permission = $type . "Access";
                break;
            }
        }
        // Delete the old parent-child linkage
        Yii::app()->db->createCommand()
                ->delete('x2_auth_item_child', 'parent=:parent AND child=:child', array(':parent' => $parent, ':child' => $child));
        // Figure out if this auth item already links to "MinimumRequirements" for the module
        $minResult = Yii::app()->db->createCommand()
                ->select('parent, child')
                ->from('x2_auth_item_child')
                ->where('parent=:parent AND child LIKE "%MinimumRequirements"', array(':parent' => $parent))
                ->queryAll();

        if (count($minResult) == 0) {
            // If it doesn't, check to make sure that the minimum requirements item exists
            $minExists = Yii::app()->db->createCommand()
                    ->select('name')
                    ->from('x2_auth_item')
                    ->where('name=:name', array(':name' => $module . 'MinimumRequirements'))
                    ->queryRow();
            if (count($minExists) >= 1) {
                // If it does, each access type needs to inherit from minimum requirements in the new system
                Yii::app()->db->createCommand()
                        ->insert('x2_auth_item_child', array(
                            'parent' => $parent,
                            'child' => $module . "MinimumRequirements"
                ));
            }
        }
        // Find all roles which inherit this access item
        $roles = Yii::app()->db->createCommand()
                ->select('b.name')
                ->from('x2_auth_item_child a')
                ->join('x2_auth_item b', 'a.parent = b.name')
                ->where('b.type = 2 AND a.child=:child', array(':child' => $parent))
                ->queryAll();
        foreach ($roles as $role) {
            /*
             * What's going on here is a little complicated. In the old system,
             * each level of permission inherited all permissions below it. We
             * need to use a switch to figure out which permission they had, then
             * we don't put breaks in our switch so it cascades down and we add
             * the permission for each access level it would have inherited 
             * in the old system.
             */
            $roleName = $role['name'];
            $cmd = Yii::app()->db->createCommand();
            $sql = "INSERT IGNORE x2_auth_item_child (parent, child) VALUES (:parent, :child)";
            $cmd->setText($sql);
            switch ($permission) {
                case "AdminAccess":
                    if ($module == "Reports" || $module == "Charts") {
                        // Reports and Charts don't inherit.
                        break;
                    }
                    $cmd->execute(array(':parent' => $roleName, ':child' => $module . 'FullAccess'));
                case "FullAccess":
                case "PrivateFullAccess":
                    $cmd->execute(array(':parent' => $roleName, ':child' => $module . 'UpdateAccess'));
                case "UpdateAccess":
                case "PrivateUpdateAccess":
                    $cmd->execute(array(':parent' => $roleName, ':child' => $module . 'BasicAccess'));
                case "BasicAccess":
                case "PrivateBasicAccess":
                    $cmd->execute(array(':parent' => $roleName, ':child' => $module . 'ReadOnlyAccess'));
                default:
                    break;
            }
        }
    }
};

$fixPermissions();
?>