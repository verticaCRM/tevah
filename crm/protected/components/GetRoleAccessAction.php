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

/* @edition:pro */

class GetRoleAccessAction extends RoleAccessActionBase {

    public function run() {
        if (isset($_POST['Roles']) || isset($_GET['name'])) {
            if (isset($_POST['Roles']))
                $name = $_POST['Roles']['name'];
            else
                $name = $_GET['name'];
            if (is_null($name)) {
                echo "";
                exit;
            }
            $auth = Yii::app()->authManager;
            $modules = Modules::model()->findAll();
            $titles = array();
            foreach ($modules as $module) {
                if ($module->pseudoModule || $module->name === 'x2Activity')
                    continue;
                $titles[$module->name] = $module->title;
            }
            $authItems = $this->getTaskAuthItemNames ();
            $accessGroups = $this->getAccessGroupsByModuleName ($authItems);

            $adminFlag = $auth->hasItemChild ($name, 'administrator');

            $this->controller->renderPartial (
                'application.views.admin.editRoleAccessPermissionsTable',
                array (
                    'accessGroups' => $accessGroups,
                    'titles' => $titles,
                    'auth' => $auth,
                    'name' => $name,
                    'adminFlag' => $adminFlag,
                ), false, true);
        }
    }

}

?>
