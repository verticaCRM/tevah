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


class X2GridViewMassActionAction extends CAction {

    // used to hold success, warning, and error messages
    private static $successFlashes = array ();
    private static $noticeFlashes = array ();
    private static $errorFlashes = array ();


    /**
     * Echoes flashes in the flash arrays
     */
    private static function echoFlashes () {
        echo CJSON::encode (array (
            'notice' => self::$noticeFlashes,
            'success' => self::$successFlashes,
            'error' => self::$errorFlashes
        ));
    }

    private function completeSelected ($gvSelection) {
        $updatedRecordsNum = Actions::changeCompleteState ('complete', $gvSelection);
        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} action'.($updatedRecordsNum === 1 ? '' : 's').
                    ' completed', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        }
    }

    private function uncompleteSelected ($gvSelection) {
        $updatedRecordsNum = Actions::changeCompleteState ('uncomplete', $gvSelection);
        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} action'.($updatedRecordsNum === 1 ? '' : 's').
                    ' uncompleted', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        }
    }

    /**
     * Delete selected records
     */
    private function deleteSelected ($gvSelection) {
        if (!isset ($_POST['modelType'])) {

            throw new CHttpException (400, Yii::t('app', 'Bad request.'));
            return;
        }
        $_GET['ajax'] = true; // prevent controller delete action from redirecting

        $updatedRecordsNum = sizeof ($gvSelection);
        $unauthorized = 0;
        $failed = 0;
        foreach ($gvSelection as $recordId) {

            // controller action permissions only work for the module's main model
            if (X2Model::getModelName (Yii::app()->controller->module->name) === 
                $_POST['modelType']) {

                if(!ctype_digit((string) $recordId))
                    throw new CHttpException(400, Yii::t('app', 'Invalid selection.'));
                try{
                    if($this->controller->beforeAction('delete'))
                        $this->controller->actionDelete ($recordId);
                }catch(CHttpException $e){
                    if($e->statusCode==403)
                        $unauthorized++;
                    else
                        throw $e;
                }
            } else if (Yii::app()->params->isAdmin) {
                // at the time of implementing this, the only model types that this applies to
                // are AnonContact and Fingerprint, both of which can only be deleted by admin users

                if (class_exists ($_POST['modelType'])) {
                    $model = X2Model::model ($_POST['modelType'])->findByPk ($recordId);
                    if (!$model || !$model->delete ()) {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            } else {
                $unauthorized++;
            }
        }
        $updatedRecordsNum = $updatedRecordsNum - $unauthorized - $failed;
        self::$successFlashes[] = Yii::t(
            'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
            ' deleted', array('{updatedRecordsNum}' => $updatedRecordsNum)
        );
        if($unauthorized > 0){
            self::$errorFlashes[] = Yii::t(
                'app', 'You were not authorized to delete {unauthorized} record'.
                ($unauthorized === 1 ? '' : 's'), array('{unauthorized}' => $unauthorized)
            );
        } 

    }

    /**
     * Tag selected records
     */
    private function tagSelected ($gvSelection) {
        if (!isset ($_POST['tags']) || !is_array ($_POST['tags']) ||
            !isset ($_POST['modelType'])) {

            throw new CHttpException (400, Yii::t('app', 'Bad request.'));
            return;
        }
        $modelType = X2Model::model ($_POST['modelType']);
        if ($modelType === null) {
            throw new CHttpException (400, Yii::t('app', 'Invalid model type.'));
            return;
        }

        $updatedRecordsNum = 0;
        $tagsAdded = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !$this->controller->checkPermissions ($model, 'edit')) continue;
            $recordUpdated = false;
            foreach ($_POST['tags'] as $tag) {
                if (!$model->addTags ($tag)) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be tagged with {tag}. This record '.
                            'may already have this tag.', array (
                            '{recordId}' => $recordId, '{tag}' => $tag
                        )
                    );
                } else {
                    $tagsAdded++;
                    $recordUpdated = true;
                }
            }
            if ($recordUpdated) $updatedRecordsNum++;
        }

        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{tagsAdded} tag'.($tagsAdded === 1 ? '' : 's').
                    ' added to {updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's'),
                    array (
                        '{updatedRecordsNum}' => $updatedRecordsNum,
                        '{tagsAdded}' => $tagsAdded
                    )
            );
        }

    }

    /**
     * Update fields of selected records
     */
    private function updateFieldsOfSelected ($gvSelection, $fields) {
        $modelType = X2Model::Model ($this->controller->modelClass);
        $updatedRecordsNum = 0;
        foreach ($gvSelection as $recordId) {
            $model = $modelType->findByPk ($recordId);
            if ($model === null || !$this->controller->checkPermissions ($model, 'edit')) {
                self::$noticeFlashes[] = Yii::t(
                    'app', 'Record {recordId} could not be updated.', array (
                        '{recordId}' => $recordId
                    )
                ).($model === null ? 
                    Yii::t('app','The record could not be found.') : 
                    Yii::t('app','You do not have sufficient permissions.'));
                continue;
            }

            if (isset($fields['associationType']) && isset($fields['associationName']) && 
                $fields['associationType'] !== 'none') {

                // If we are setting an association, lookup the association id
                $attributes = array('name' => $fields['associationName']);
                $associatedModel = X2Model::Model($fields['associationType'])
                    ->findByAttributes($attributes);
                $fields['associationId'] = $associatedModel->id;
            }

            $model->setX2Fields($fields);

            if (!$model->save()) {
                $errors = $model->getAllErrorMessages();
                foreach ($errors as $err) {
                    self::$noticeFlashes[] = Yii::t(
                        'app', 'Record {recordId} could not be updated: '.$err,
                        array ('{recordId}' => $recordId)
                    );
                }
                continue;
            }
            $updatedRecordsNum++;
        }
        if ($updatedRecordsNum > 0) {
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                    ' updated', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        }

    }

    /**
     * Add selected records to list with given id
     */
    public function removeFromList($gvSelection, $listId){
        foreach($gvSelection as $contactId) {
            if(!ctype_digit((string) $contactId)) {
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if($list !== null && $this->controller->checkPermissions($list, 'edit')) {
            if ($list->removeIds($_POST['gvSelection'])) {
                self::$successFlashes[] = Yii::t(
                    'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' removed from list "{list}"', array (
                            '{updatedRecordsNum}' => $updatedRecordsNum,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be removed from this list');
            }
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }
    }

    /**
     * Add selected records to list with given id
     */
    public function addToList($gvSelection, $listId){
        foreach($gvSelection as &$contactId) {
            if(!ctype_digit((string) $contactId)) {
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
            }
        }

        $list = CActiveRecord::model('X2List')->findByPk($listId);
        $updatedRecordsNum = sizeof ($gvSelection);

        // check permissions
        if ($list !== null && $this->controller->checkPermissions ($list, 'edit')) {
            if ($list->addIds($gvSelection)) {
                self::$successFlashes[] = Yii::t(
                    'app', '{updatedRecordsNum} record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' added to list "{list}"', array (
                            '{updatedRecordsNum}' => $updatedRecordsNum,
                            '{list}' => $list->name,
                        )
                );
            } else {
                self::$errorFlashes[] = Yii::t(
                    'app', 'The selected record'.($updatedRecordsNum === 1 ? '' : 's').
                        ' could not be added to this list');
            }
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 'You do not have permission to modify this list');
        }
    }

    /**
     * Create new list with given name and add selected contacts to it
     */
    public function createList ($gvSelection, $listName) {
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
                    'app', 'List could not be created');
            }
        } else {
            self::$errorFlashes[] = Yii::t(
                'app', 'List could not be created');
        }
    }

    /**
     * Execute specified mass action on specified records
     */
    public function run(){
        if (!isset ($_POST['massAction']) || !isset ($_POST['gvSelection']) ||
            !is_array ($_POST['gvSelection'])) {

            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }

        $massAction = $_POST['massAction'];
        $gvSelection = $_POST['gvSelection'];
        switch ($massAction) {
            case 'completeAction':
                $this->completeSelected ($gvSelection);
                break;
            case 'uncompleteAction':
                $this->uncompleteSelected ($gvSelection);
                break;
            case 'delete':
                $this->deleteSelected ($gvSelection);
                break;
            case 'tag':
                $this->tagSelected ($gvSelection);
                break;
            case 'updateFields':
                if (!isset ($_POST['fields'])) {
                    throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                }
                $this->updateFieldsOfSelected (
                    $gvSelection, $_POST['fields']);
                break;
            case 'addToList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
                    throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                }
                $this->addToList ($gvSelection, $_POST['listId']);
                break;
            case 'removeFromList':
                if ($this->controller->modelClass !== 'Contacts' || !isset ($_POST['listId'])) {
                    throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                }
                $this->removeFromList ($gvSelection, $_POST['listId']);
                break;
            case 'createList':
                if ($this->controller->modelClass !== 'Contacts' ||
                    !isset ($_POST['listName']) || $_POST['listName'] === '') {
                    
                    throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                }
                $this->createList ($gvSelection, $_POST['listName']);
                break;
            default:
                throw new CHttpException (400, Yii::t('app', 'Bad Request'));
                return;
        }
        self::echoFlashes ();
    }

}

?>
