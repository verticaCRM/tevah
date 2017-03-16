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

class MassTag extends MassAction {

    public $hasButton = true; 

    protected $_label;

    /**
     * Renders the mass action dialog, if applicable
     * @param string $gridId id of grid view
     */
    public function renderDialog ($gridId, $modelName) {
        echo "
            <div class='mass-action-dialog' id='".$this->getDialogId ($gridId)."' 
             style='display: none;'>
                <div class='form'>
                    <div class='x2-tag-list'>
                        <span class='tag-container-placeholder'>".
                            Yii::t('app', 'Drag tags here from the tag cloud widget or click'.
                                ' to create a custom tag.')."
                        </span>
                    </div>
                </div>
            </div>";
    }

    /**
     * Renders the mass action button, if applicable
     */
    public function renderButton () {
        if (!$this->hasButton) return;
        
        echo "
            <a href='#' title='".CHtml::encode ($this->getLabel ())."'
             class='fa fa-tag fa-lg mass-action-button x2-button mass-action-button-".
                get_class ($this)."'>
            </a>";
    }


    /**
     * @return string label to display in the dropdown list
     */
    public function getLabel () {
        if (!isset ($this->_label)) {
            $this->_label = Yii::t('app', 'Tag selected');
        }
        return $this->_label;
    }

    public function getPackages () {
        return array_merge (parent::getPackages (), array (
            'X2MassTag' => array(
                'baseUrl' => Yii::app()->request->baseUrl,
                'js' => array(
                    'js/X2Tags/TagContainer.js',
                    'js/X2Tags/TagCreationContainer.js',
                    'js/X2Tags/MassActionTagsContainer.js',
                    'js/X2GridView/MassTag.js',
                ),
                'depends' => array ('X2MassAction'),
            ),
        ));
    }

    public function execute (array $gvSelection) {
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
            if ($model === null || !Yii::app()->controller->checkPermissions ($model, 'edit')) 
                continue;
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
        return $updatedRecordsNum;
    }

}
