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

class MassEmailDelete extends EmailMassAction {

    public $hasButton = true; 

    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Delete selected');
        }
        return $this->_label;
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             data-singular-title='".CHtml::encode (Yii::t('app', 'Delete message'))."'
             class='fa fa-trash fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassEmailDelete' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2GridView/MassEmailDelete.js',
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
        if ($mailbox instanceof EmailInboxes)
            $mailbox->deleteMessages ($uids);
        else
            throw new CHttpException(
                400, Yii::t('emailInboxes', 'Unable to load selected mailbox'));
        $updatedRecordsNum = count ($uids);
        self::$successFlashes[] = Yii::t(
            'app', '{updatedRecordsNum} email'.($updatedRecordsNum === 1 ? '' : 's').
                ' deleted', array ('{updatedRecordsNum}' => $updatedRecordsNum)
        );
    }

}

?>
