<?php
/***********************************************************************************
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

/* @edition:pro */

abstract class RoleAccessActionBase extends CAction {

    /**
     * @var array $permissionNames mapping between permission name and corresponding auth item
     *  key word
     */
    public static $permissionNames = array (
        'view' => 'ReadOnly',
        'create' => 'Basic',
        'update' => 'Update',
        'delete' => 'Full',
        'admin' => 'Admin',
    );

    /**
     * @param array $authItems names of tasks in the auth item table
     * @return array groups of access level names indexed by module name
     */
    protected function getAccessGroupsByModuleName (array $authItems) {
        $accessGroups = array();
        $modules = Modules::model()->titleSorted()->findAll();
        foreach ($modules as $module) {
            if ($module->name === 'x2Activity' || $module->pseudoModule)
                continue;

            $accessGroups[$module->name][] = "-";
            $items = ArrayUtil::arraySearchPreg(
                "^" . ucfirst($module->name) . "(.*?)Access", $authItems);
            foreach ($items as $item) {
                $tempName = $authItems[$item];
                $authItems[$item] = preg_replace(
                    '/' . ucfirst($module->name) . '/', '', $authItems[$item]);
                $accessGroups[$module->name][$tempName] = $authItems[$item];
            }
        }
        return $accessGroups;
    }

    /**
     * @return array names of tasks 
     */
    protected function getTaskAuthItemNames () {
        $auth = Yii::app()->authManager;
        $authItems = array ();
        $tempAuthItems = $auth->getAuthItems(1);
        foreach ($tempAuthItems as $item) {
            $authItems[] = $item->name;
        }
        return $authItems;
    }

}

?>
