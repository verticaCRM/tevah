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

/**
 * Description of X2ControllerPermissionsBehavior
 *
 * @package application.components.permissions
 */
class X2ControllerPermissionsBehavior extends ControllerPermissionsBehavior {

    /**
     * Extension of a base Yii function, this method is run before every action
     * in a controller. If true is returned, it procedes as normal, otherwise
     * it will redirect to the login page or generate a 403 error.
     * @param string $action The name of the action being executed.
     * @return boolean True if the user can procede with the requested action
     */
    public function beforeAction($action = null) {
        if (is_int(Yii::app()->locked) &&
                !Yii::app()->user->checkAccess('GeneralAdminSettingsTask')) {

            $this->owner->appLockout();
        }
        $auth = Yii::app()->authManager;
        $params = array();
        if (empty($action))
            $action = $this->owner->getAction()->getId();
        elseif (is_string($action)) {
            $action = $this->owner->createAction($action);
        }

        $actionId = $action->getId();

        // These actions all have a model provided with them but its assignment
        // should not be checked for an exception. They either have permission
        // for this action or they do not.
        $exceptions = array(
            'updateStageDetails',
            'deleteList',
            'updateList',
            'userCalendarPermissions',
            'exportList',
            'updateLocation'
        );
        if (($this->owner->hasProperty('modelClass') || property_exists($this->owner, 'modelClass')) && class_exists($this->owner->modelClass)) {
            $staticModel = X2Model::model($this->owner->modelClass);
        }

        if (isset($_GET['id']) && !in_array($actionId, $exceptions) && !Yii::app()->user->isGuest &&
            isset($staticModel)) {

            // Check assignment fields in the current model
            $retrieved = true;
            $model = $staticModel->findByPk($_GET['id']);
            if ($model instanceof X2Model) {
                $params['X2Model'] = $model;
            }
        }

        // Generate the proper name for the auth item
        $actionAccess = ucfirst($this->owner->getId()) . ucfirst($actionId);
        $authItem = $auth->getAuthItem($actionAccess);
//print_r($authItem);die;
        // Return true if the user is explicitly allowed to do it, or if there is no permission 
        // item, or if they are an admin
        if (!($authItem instanceof CAuthItem) || 
            Yii::app()->user->checkAccess($actionAccess, $params) || Yii::app()->params->isAdmin) {

            return true;
        } elseif (Yii::app()->user->isGuest) {
            Yii::app()->user->returnUrl = Yii::app()->request->url;
            $this->owner->redirect($this->owner->createUrl('/site/login'));
        } else
            $this->owner->denied();
    }

    /**
     * Determines if we have permission to view/edit/delete something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or 
     *  {@link X2Model}
     * @param string $action
     * @return boolean
     */
    public function checkPermissions(&$model, $action = null) {

        $view = false;
        $edit = false;
        $module = $model instanceof X2Model ? 
            Yii::app()->getModule($model->module) : Yii::app()->controller->module;

        if (isset($module)) {
            $moduleAdmin = Yii::app()->user->checkAccess(ucfirst($module->name) . 'Admin');
        } else {
            $moduleAdmin = false;
        }

        if ($model instanceof X2Model && $model->asa('permissions') != null && 
            $module instanceof CModule) {

            // Check assignment and visibility using X2PermissionsBehavior
            $view = (Yii::app()->params->isAdmin || $moduleAdmin) || 
                $model->isVisibleTo(Yii::app()->getSuName(), false);
            if ($view) { // Only check edit permissions if they're allowed to view
                $edit = (Yii::app()->params->isAdmin || $moduleAdmin) || 
                    Yii::app()->authManager->checkAccess(
                        ucfirst($module->name) . 'Update',
                        Yii::app()->getSuID(),
                        array('X2Model' => $model)
                    );
            }
        } else {
            // No special permissions checks are available
            $view = true;
            $edit = true;
        }

        //$edit = $view && $edit; // edit permission required view permission

        if (!isset($action)) // hash of all permissions if none is specified
            return array('view' => $view, 'edit' => $edit, 'delete' => $edit);
        elseif ($action == 'view')
            return $view;
        elseif ($action == 'edit')
            return $edit;
        elseif ($action == 'delete')
            return $edit;
        else
            return false;
    }

    /**
     * Format the left sidebar menu of links to remove items which a user is not
     * allowed to perform due to role settings.
     * @param array $array An array of menu items to be formatted
     * @param array $params An array of special parameters to be used for a role's biz rule
     * @return array The formatted list of menu items
     */
    function formatMenu($array, $params = array()) {
        $auth = Yii::app()->authManager;
        foreach ($array as &$item) {
            if (isset($item['url']) && is_array($item['url'])) {
                $url = $item['url'][0];
                if (preg_match('/\//', $url)) {
                    $pieces = explode('/', $url);
                    $action = "";
                    foreach ($pieces as $piece) {
                        $action.=ucfirst($piece);
                    }
                } else {
                    $action = ucfirst($this->owner->getId() . ucfirst($item['url'][0]));
                }
                // For special actions within the Admin controller that use the "checkAdminOn" 
                // biz rule method: add a module parameter for proper checking
                if($this->owner->getModule() instanceof CModule)
                    $params['module'] = $this->owner->getModule()->getId();
                $authItem = $auth->getAuthItem($action);
                if (!isset($item['visible']) || $item['visible'] == true) {
                    $item['visible'] = Yii::app()->user->checkAccess($action, $params) || is_null($authItem) || Yii::app()->params->isAdmin;
                }
            } else {
                if (isset($item['linkOptions']['submit'])) {
                    $action = ucfirst($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]));
                    $authItem = $auth->getAuthItem($action);
                    $item['visible'] = Yii::app()->user->checkAccess($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]), $params) || is_null($authItem);
                }
            }
        }
        return $array;
    }

}

?>
