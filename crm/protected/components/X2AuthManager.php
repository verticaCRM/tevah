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
 * RBAC auth manager for X2Engine
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2AuthManager extends CDbAuthManager {


    public $caching = true;

    /**
     * Stores auth data in the scope of the current request
     *
     * @var type
     */
    private $_access;

    /**
     * Internal "cache" of user names
     * @var type
     */
    private $_usernames = array();

    /**
     * Access check function.
     *
     * Checks access and attempts to speed up all future access checks using
     * caching and storage of the variable within {@link _access}.
     * 
     * Note, only if parameters are empty will permissions caching or storage
     * in {@link _access} be effective, because parameters (i.e. the assignment
     * of a record based on the value of its assignedTo field) are expected to
     * vary. For example, in record-specific permission items checked for
     * multiple records. That is why $params be empty for any shortcuts to be
     * taken.
     *
     * @param string $itemName Name of the auth item for which access is being checked
     * @param integer $userId ID of the user for which to check access
     * @param array $params Parameters to pass to business rules
     * @return boolean
     */
    public function checkAccess($itemName, $userId, $params = array()) {
        if(!isset($this->_access))
            $this->_access = array();

        if(isset($this->_access[$userId][$itemName])) {
            // Shortcut 1: return data stored in the component's property
            return $this->_access[$userId][$itemName];
        } else if($this->caching && empty($params)) {
            // Shortcut 2: load the auth cache data and return if a result was found
            if(!isset($this->_access[$userId]))
                $this->_access[$userId] = Yii::app()->authCache->loadAuthCache($userId);
            if(isset($this->_access[$userId][$itemName]))
                return $this->_access[$userId][$itemName];
        } else {
            // Merely prepare _access[$userId]
            if(!isset($this->_access[$userId]))
                $this->_access[$userId] = array();
        }

        // Get assignments via roles.
        //
        // In X2Engine's system, x2_auth_assignment doesn't refer to users, but
        // to roles. Hence, the ID of each role is sent to 
        // parent::getAuthAssignments rather than a user ID, which would be
        // meaningless in light of how x2_auth_assignment stores roles.
        $roles = Roles::getUserRoles($userId);
        $assignments = array();
        foreach($roles as $roleId) {
            $assignments = array_merge($assignments, parent::getAuthAssignments($roleId));
        }

        // Prepare the username for the session-agnostic permissions check:
        if(!isset($this->_usernames[$userId])) {
            if($userId == Yii::app()->getSuId())
                $user = Yii::app()->getSuModel();
            else
                $user = User::model()->findByPk($userId);
            if($user instanceof User)
                $this->_usernames[$userId] = $user->username;
            else
                $this->_usernames[$userId] = 'Guest';
        }
        if(!isset($params['userId']))
            $params['userId'] = $userId;

        // Get whether the user has access:
        $hasAccess = parent::checkAccessRecursive($itemName, $userId, $params, $assignments);

        if(empty($params)) {
            // Store locally.
			$this->_access[$userId][$itemName] = $hasAccess;
            // Cache
            if($this->caching)
                Yii::app()->authCache->addResult($userId,$itemName,$hasAccess);
		}

        return $hasAccess;
    }

    /**
     * Checks for admin access on a specific named module.
     *
     * Originally written as a kludge to bypass checking for overall admin access when
     * performing a generic admin action that is specific to a module. Specifically, it
     * was written for exporting models as a fix for 4.1.6, wherein otherwise a user would
     * need full admin rights and not just contact module admin rights to export contacts.
     *
     * Note, since this starts its own chain of recursive access checking, extreme caution
     * should be used when using this method inside of a business rule, because infinite 
     * loops could potentially occur.
     *
     * @param array $params An associative array that is presumed to contain a "userId"
     *  element that refers to the user ID (as if $params is as within a business rule),
     *  and also expects a model (or module) parameter.
     */
    public function checkAdminOn($params) {
        if(!isset($params['userId']))
            return false;

        // Look in the $_GET superglobal for 'model' if the 'model' parameter is not available
        $modelName = isset($params['model'])
            ? ($params['model'] instanceof X2Model ? get_class($params['model']) : $params['model'])
            : (isset($_GET['model']) ? $_GET['model'] : null);

        // Determine the module on which admin access will be checked, based on a model class:
        if(empty($params['module']) && !empty($modelName)) {
            if(($staticModel = X2Model::model($modelName)) instanceof X2Model) {
                if(($lb = $staticModel->asa('X2LinkableBehavior')) instanceof X2LinkableBehavior) {
                    $module = !empty($lb->module)?$lb->module:null;
                }
            }
        }
        if(!isset($module)) // Check if module parameter is specified and use it if so:
            $module = isset($params['module']) ? $params['module'] : null;

        if(!empty($module)) {
           // Perform a check for the existence of the item name (because, per the original 
           // design of X2Engine's permissions, for backwards compatibility: if no auth 
           // item exists, permission will be granted by default).
           $itemName = ucfirst($module).'AdminAccess';
            if(!(bool)$this->getAuthItem($itemName))
                return false;
        } else {
            // Use the generic administrator auth item if there is no module specified:
            $itemName = 'administrator';
        }
        //AuxLib::debugLogR(compact('params','itemName','userId','module','modelName'));
        return $this->checkAccess($itemName,$params['userId'],$params);
    }

    /**
     * Assignment check function for business rules
     *
     * @param array $params
     * @return boolean
     */
    public function checkAssignment($params){
        return isset($params['X2Model'])
                && $params['X2Model'] instanceof X2Model
                && $params['X2Model']->isAssignedTo($this->_usernames[$params['userId']]);
    }

    /**
     * Visibility check function for business rules
     * 
     * @param array $params
     * @return boolean
     */
    public function checkVisibility($params) {
        return isset($params['X2Model'])
                && $params['X2Model'] instanceof X2Model
                && $params['X2Model']->isVisibleTo($this->_usernames[$params['userId']]);
    }

}

?>
