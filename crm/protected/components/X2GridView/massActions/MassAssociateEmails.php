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

class MassAssociateEmails extends EmailMassAction {

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Log email');
        }
        return $this->_label;
    }

    /**
     * Renders the list item for the mass action dropdown 
     */
    public function renderListItem () {
        echo "
            <li class='x2-hint mass-action-button mass-action-".get_class ($this)."' ".
            ($this->hasButton ? 'style="display: none;"' : '')." 
             title='".CHtml::encode (EmailInboxes::getLogEmailDescription ())."'
             data-singular-label='".CHtml::encode (Yii::t('app', 'Log email'))."'>
            ".CHtml::encode ($this->getLabel ())."
            </li>";
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassAssociateEmails' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassAssociateEmails.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    /**
     * @param array $gvSelection array of ids of records to perform mass action on
     */
    public function execute (array $gvSelection) {
        $uids = $gvSelection;
        $mailbox = $this->getMailbox ();

        if ($mailbox instanceof EmailInboxes) {
            list ($errors, $warnings) = $mailbox->logMessages ($uids);
        } else {
            throw new CHttpException(
                400, Yii::t('emailInboxes', 'Unable to load selected mailbox'));
        }
        $updatedRecordsNum = count ($uids) - count ($errors) - count ($warnings);
        if ($updatedRecordsNum)
            self::$successFlashes[] = Yii::t(
                'app', '{updatedRecordsNum} email'.($updatedRecordsNum === 1 ? '' : 's').
                    ' associated', array ('{updatedRecordsNum}' => $updatedRecordsNum)
            );
        self::$errorFlashes = $errors;
        self::$noticeFlashes = $warnings;
    }

}

?>
