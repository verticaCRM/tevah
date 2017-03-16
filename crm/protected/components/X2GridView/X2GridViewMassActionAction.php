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
 **********************************************************************************/

Yii::import('application.components.X2GridView.massActions.*');

class X2GridViewMassActionAction extends CAction {
    
    /**
     * Mass action names and mass action class names
     */
    private $massActionClasses = array (
        'MassAddToList',
        'MassCompleteAction',
        'MassUncompleteAction',
        'MassRemoveFromList',
        'NewListFromSelection',
        /* x2prostart */ 
        'MassEmailDelete',
        'MassAssociateEmails',
        'MassMarkAsRead',
        'MassMarkAsUnread',
        'MassMoveToFolder',
        'MassDelete',
        'MassTag',
        'MassUpdateFields',
        'MergeRecords',
        /* x2proend */ 
    );

    private $_massActions;

    /**
     * @return array instances of mass action objects indexed by mass action name
     */
    public function getMassActionInstances () {
        if (!isset ($this->_massActions)) {
            $this->_massActions = array ();
            foreach ($this->massActionClasses as $class) {
                $this->_massActions[$class] = new $class;
            }
        }
        return $this->_massActions;
    }

    /**
     * validates mass action name and returns MassAction instance that corresponds with it
     * @param string $massAction
     */
    private function getInstanceFor ($massAction) {
        $instances = $this->getMassActionInstances ();
        if (!in_array ($massAction, array_keys ($instances))) {
            /**/AuxLib::debugLogR ('invalid mass action '.$massAction);
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        return $instances[$massAction];
    }

    /**
     * Execute specified mass action on specified records
     */
    public function run(){
        if (Yii::app()->user->isGuest) {
            Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/site/login'));
        }

        if (Yii::app()->request->getRequestType () === 'GET') {
            $_POST = $_GET;
        }

        if (isset ($_POST['passConfirm']) && $_POST['passConfirm']) {
            MassAction::superMassActionPasswordConfirmation ();
            return;
        }
        if (!isset ($_POST['massAction']) || 
            ((!isset ($_POST['superCheckAll']) || !$_POST['superCheckAll']) &&
             (!isset ($_POST['gvSelection']) || !is_array ($_POST['gvSelection'])))) {

            /**/AuxLib::debugLogR ('run error');
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $massAction = $_POST['massAction'];
        $massActionInstance = $this->getInstanceFor ($massAction);

        if (isset ($_POST['superCheckAll']) && $_POST['superCheckAll']) {
            $uid = $_POST['uid'];
            $idChecksum = $_POST['idChecksum'];
            $totalItemCount = intval ($_POST['totalItemCount']);
            $massActionInstance->superExecute ($uid, $totalItemCount, $idChecksum);
        } else {
            $gvSelection = $_POST['gvSelection'];
            $massActionInstance->execute ($gvSelection);
            $massActionInstance::echoFlashes ();
        }
    }

}

?>
