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

/**
 * Task legend:
 *  Admin  - AdminAccess
 *  Delete - (Private)?FullAccess
 *  Update - (Private)?UpdateAccess
 *  Create - (Private)?BasicAccess
 *  Read   - (Private)?ReadOnlyAccess
 */

class EditRoleAccessAction extends RoleAccessActionBase {

    public function run() {
        $this->clearAuthCache ();

        $model = new Roles;
        $authItems = $this->getTaskAuthItemNames ();
        $accessGroups = $this->getAccessGroupsByModuleName ($authItems);
        $dataProvider = new CActiveDataProvider('Roles');
        $roles = Roles::model()->findAll ();

        if (isset($_POST['Roles'])) {
            $name = $_POST['Roles']['name'];
            unset ($_POST['Roles']);
            $adminFlag = isset ($_POST['adminFlag']) ? true : false;
            unset ($_POST['adminFlag']);
            $this->editRole ($name, $_POST, $adminFlag, $accessGroups);
            $this->controller->redirect('editRoleAccess');
        }

        $this->controller->render('manageRoleAccess', array(
            'dataProvider' => $dataProvider,
            'model' => $model,
            'roles' => $roles,
            'accessGroups' => $accessGroups,
        ));
    }

    public function clearAuthCache () {
        if (!is_null(Yii::app()->db->getSchema()->getTable('x2_auth_cache'))) {
            if (Yii::app()->hasComponent('authCache')) {
                $authCache = Yii::app()->authCache;
                if (isset($authCache))
                    $authCache->clear();
            }
        }
    }

    /**
     * @param string $parent auth item name
     * @return array names of auth item children of parent
     */
    private function getAuthItemChildrenNames ($parent) {
        $auth = Yii::app()->authManager;
        $tempAuthChilren = $auth->getItemChildren($parent);
        $authChildren = array();
        foreach ($tempAuthChilren as $tempItem) {
            $authChildren[] = $tempItem->name;
        }
        return $authChildren;
    }

    /* x2tempstart */   
    // should eventually be moved to a centralized place, like an AuthItem validator.
    // this duplicates logic in editRoleAccessPermissionsTable.php
    /**
     * Ensures that if admin access is set for a given module, all other forms of access are granted
     * @param mixed $accessSettings
     * @param mixed $accessGroups
     */
    private function normalizeAccessSettings (&$accessSettings, $accessGroups) {
        $missingSettings = array ();
        foreach ($accessSettings as $item => $value) {
            $pieces = explode('-', $item);
            if (count($pieces) === 2) { // ignore privacy settings
                $moduleName = ucfirst($pieces[0]);
                $settingName = $pieces[1];
                if ($settingName === 'admin') {
                    $accessGroup = $accessGroups[$pieces[0]];
                    foreach (self::$permissionNames as $name => $keyword) {
                        $accessName = $keyword . 'Access';
                        if (in_array($accessName, $accessGroup)) {
                            $missingSettings[$pieces[0] . '-' . $name] = true;
                        }
                    }
                }
            }
        }
        $accessSettings = array_merge ($accessSettings, $missingSettings);
    }
    /* x2tempend */ 

    /**
     * Removes all old auth item children and adds new ones corresponding to specified access
     * settings
     * @param string $name name of the role to edit
     * @param array $accessSettings settings of the form 
     *  <module name>-<create|read|update|delete|admin>[-<private|all>]?
     * @param bool $adminFlag true if the role should be flagged as admin, false otherwise
     */
    private function editRole ($name, $accessSettings, $adminFlag, $accessGroups) {
        $role = Roles::model()->findByAttributes(array('name' => $name));

        if (isset($role) || $name === 'DefaultRole') {
            $this->normalizeAccessSettings ($accessSettings, $accessGroups);

            $auth = Yii::app()->authManager;
            $authRole = $auth->getAuthItem($name);
            if (!isset($authRole)) {
                $authRole = $auth->createRole($role->name);
                $auth->assign($name, $role->id);
            }

            if ($adminFlag) {
                if (!$auth->hasItemChild($name, 'administrator'))
                    $auth->addItemChild($name, 'administrator');
                return;
            }else {
                $auth->removeItemChild($name, 'administrator');
            }
            $authItems = $this->getTaskAuthItemNames ();
            $authChildren = $this->getAuthItemChildrenNames ($name);

            $modules = Modules::model()->findAll();
            foreach ($modules as $module) {
                $childItems = ArrayUtil::arraySearchPreg(
                    "^" . ucfirst($module->name) . "(.*?)Access", $authChildren);
                foreach($childItems as $childItem){
                    $auth->removeItemChild($name, $authChildren[$childItem]);
                }
            }
            // Iterate through crud access settings, adding corresponding auth item children
            foreach ($accessSettings as $item => $value) {
                $pieces = explode('-', $item);
                if (count($pieces) === 2) { // ignore privacy settings
                    $moduleName = ucfirst($pieces[0]);
                    if (!isset (self::$permissionNames[$pieces[1]]))
                        throw new CException ('invalid access level: '.$pieces[1]);
                    $access = self::$permissionNames[$pieces[1]];

                    // if present, use privacy setting to construct auth item child 
                    // relationships
                    if(isset($accessSettings[$pieces[0].'-'.$pieces[1].'-privacy'])){
                        $privacy = $accessSettings[$pieces[0].'-'.$pieces[1].'-privacy'];
                        if($privacy === 'private'){
                            $authRole->addChild($moduleName."Private".$access."Access");
                        }else{
                            $authRole->addChild($moduleName.$access."Access");
                        }
                    }else{
                        $authRole->addChild($moduleName.$access."Access");
                    }
                }
            }
            if (!is_null(Yii::app()->db->getSchema()->getTable('x2_auth_cache'))) {
                if (Yii::app()->hasComponent('authCache')) {
                    $authCache = Yii::app()->authCache;
                    if (isset($authCache)) {
                        $authCache->clear();
                    }
                }
            }
        }
    }
}

?>
