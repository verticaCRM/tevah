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

class MassRemoveFromList extends MassAction {

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."' 
             style='display: none;'>
                <span>".
                    Yii::t('app', 'Remove all selected records from this list?')."
                </span>
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Remove selected from list');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassRemoveFromList' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassRemoveFromList.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if (Yii::app()->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $listId = $_POST['listId'];

        foreach($gvSelection as $contactId) {
            if(!ctype_digit((string) $contactId)) {
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);
        $success = true;

        // check permissions
        if($list !== null && Yii::app()->controller->checkPermissions($list, 'edit')) {
            if ($list->removeIds($gvSelection)) {
                self::$successFlashes[] = Yii::t(
                    'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' removed from list "{list}"', array (
                            '{updatedRecordsNum}' => $updatedRecordsNum,
                            '{list}' => $list->name,
                        )
                );
            } else {
                $success = false;
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be removed from this list');
            }
        } else {
            $success = false;
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }

        return $success ? $updatedRecordsNum : 0;
    }

}
