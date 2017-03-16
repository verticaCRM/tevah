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

class MassDelete extends MassAction {

    public $hasButton = true; 

    protected $requiresPasswordConfirmation = true;

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
                    Yii::t('app', 'Are you sure you want to delete all selected records?')."
                    <br/>".
                    Yii::t('app', 'This action cannot be undone.')."
                </span>
            </div>";
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             class='fa fa-trash fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Delete selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassDelete' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassDelete.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
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
                    //$_GET['id'] = $recordId; // only users who can delete all records can
                    // call this action, so we don't need to check the assignedTo field
                    if(Yii::app()->controller->beforeAction('delete'))
                        Yii::app()->controller->actionDelete ($recordId);
                    //unset ($_GET['id']);
                }catch(CHttpException $e){
                    if($e->statusCode === 403)
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
        return $updatedRecordsNum;
    }

}
