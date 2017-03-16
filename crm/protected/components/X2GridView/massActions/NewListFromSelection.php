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

class NewListFromSelection extends MassAction {

    protected $_label;

    private $listId;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' 
             id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span>".
                    Yii::t('app', 'What should the list be named?')."
                </span>
                <br/>
                <input class='left new-list-name'></input>
            </div>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'New list from selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2NewListFromSelection' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/NewListFromSelection.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
        if (Yii::app()->controller->modelClass !== 'Contacts' ||
            !isset ($_POST['listName']) || $_POST['listName'] === '') {
            
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        if (!Yii::app()->params->isAdmin && 
            !Yii::app()->user->checkAccess ('ContactsCreateListFromSelection')) {

            return -1;
        }

        $listName = $_POST['listName'];
        foreach($gvSelection as &$contactId){
            if(!ctype_digit((string) $contactId))
                throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
        }

        $list = new X2List;
        $list->name = $_POST['listName'];
        $list->modelName = 'Contacts';
        $list->type = 'static';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;
        $list->createDate = time();
        $list->lastUpdated = time();

        $itemModel = X2Model::model('Contacts');
        $success = true;
        if($list->save()){ // if the list is valid save it so we can get the ID
            $count = 0;
            foreach($gvSelection as &$itemId){

                if($itemModel->exists('id="'.$itemId.'"')){ // check if contact exists
                    $item = new X2ListItem;
                    $item->contactId = $itemId;
                    $item->listId = $list->id;
                    if($item->save()) // add all the things!
                        $count++;
                }
            }
            $list->count = $count;
            $this->listId = $list->id;
            if($list->save()) {
                self::$successFlashes[] = Yii::t(
                    'app', '{count} record'.($count === 1 ? '' : 's').
                        ' added to new list "{list}"', array (
                            '{count}' => $count,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'List created but records could not be added to it');
            }
        } else {
            $success = false;
            self::$errorFlashes[] = Yii::t(
                'app', 'List could not be created');
        }
        return $success ? $count : -1;

    }

    /**
     * Add list id to response data so that subsequent client requests can be for add to list 
     * mass action
     */
    protected function generateSuperMassActionResponse ($successes, $selectedRecords, $uid) {
        $response = parent::generateSuperMassActionResponse ($successes, $selectedRecords, $uid);
        $response['listId'] = $this->listId;
        return $response;
    }

}
