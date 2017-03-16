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

class X2WebUser extends CWebUser {

    /**
     * Roles that the user currently has
     * @var type
     */
    private $_roles;

    public function checkAccess($operation, $params = array()){
        return Yii::app()->getAuthManager()->checkAccess($operation, $this->getId(), $params);
    }

    /**
     * Runs the user_login automation trigger
     *
     * @param $fromCookie whether the login was automatic (cookie-based)
     */
    protected function afterLogin($fromCookie){
        if(!$fromCookie){
            X2Flow::trigger('UserLoginTrigger', array(
                'user' => $this->getName()
            ));
        }
    }

    /**
     * Runs the user_logout automation trigger
     *
     * @return boolean whether or not to logout
     */
    protected function beforeLogout(){
        X2Flow::trigger('UserLogoutTrigger', array(
            'user' => $this->getName()
        ));
        return parent::beforeLogout();
    }

    /**
     * Retrieves roles for the user
     */
    public function getRoles(){
        if(!isset($this->_roles)){
            $this->_roles = Roles::getUserRoles($this->getId());
        }
        return $this->_roles;
    }

}
