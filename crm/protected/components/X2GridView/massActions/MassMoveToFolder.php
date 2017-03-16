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

class MassMoveToFolder extends EmailMassAction {

    public $hasButton = true;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        $mailbox = Yii::app()->controller->selectedMailbox;
        echo "
            <div class='mass-action-dialog' 
             id='".$this->getDialogId ($gridId)."' style='display: none;'>
                <span>".
                    Yii::t('app', 'Move messages to')."
                </span>
                <span style='display: none;'>".
                    Yii::t('app', 'Move message to')."
                </span>
                <br/>".
                CHtml::dropDownList ('targetFolder', '',
                    array_combine($mailbox->folders, $mailbox->folders),
                    array('class' => 'email-folder-dropdown'))."
            </div>";
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             class='fa fa-folder fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }


    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Move to');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassMoveToFolder' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassMoveToFolder.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    /**
     * @param array $gvSelection array of ids of records to perform mass action on
     */
    public function execute (array $gvSelection) {
        if (Yii::app()->controller->modelClass !== 'EmailInboxes' ||
            !isset ($_POST['targetFolder']) || $_POST['targetFolder'] === '') {
            
            throw new CHttpException (400, Yii::t('app', 'Bad Request'));
        }
        $targetFolder = $_POST['targetFolder'];

        $uids = $gvSelection;
        $mailbox = $this->getMailbox ();

        $folders = $mailbox->folders;
        if (!in_array($targetFolder, $folders))
            throw new CHttpException(400, Yii::t('emailInboxes', "Invalid folder specified"));
        if ($mailbox instanceof EmailInboxes) {
            $success = $mailbox->moveMessages ($uids, $targetFolder);
        } else {
            throw new CHttpException(
                400, Yii::t('emailInboxes', 'Unable to load selected mailbox'));
        }

        if ($success) {
            $updatedRecordsNum = count ($uids);
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} email'.($updatedRecordsNum === 1 ? '' : 's').
                    ' moved to {folderName}',
                    array (
                        '{updatedRecordsNum}' => $updatedRecordsNum,
                        '{folderName}' => $targetFolder,
                    )
            );
        } else {
            self::$successFlashes[] = Yii::t(
                'app', 'Selected email'.($updatedRecordsNum === 1 ? '' : 's').
                    ' could not be moved to {folderName}',
                    array (
                        '{folderName}' => $targetFolder,
                    )
            );
        }
    }

}

?>
