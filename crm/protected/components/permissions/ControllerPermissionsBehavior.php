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
 * Description of ControllerPermissionsBehavior
 *
 * @package application.components.permissions
 */
abstract class ControllerPermissionsBehavior extends CBehavior {

    /**
     * Extension of a base Yii function, this method is run before every action
     * in a controller. If true is returned, it procedes as normal, otherwise
     * it can redirect to the login page or generate a 403 error.
     * @param string $action The name of the action being executed.
     * @return boolean True if the user can procede with the requested action
     */
    abstract function beforeAction($action = null);

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param string $action "view" "edit" or "delete" -- what we're trying to do
     * @return boolean Whether or not the user is allowed for that action
     */
    abstract function checkPermissions(&$model, $action = null);

    /**
     * Format the left sidebar menu of links to remove items which a user is not
     * allowed to perform due to role settings.
     * @param array $array An array of menu items to be formatted
     * @param array $params An array of special parameters to be used for a role's biz rule
     * @return array The formatted list of menu items
     */
    abstract function formatMenu($array, $params = array());
}

?>
