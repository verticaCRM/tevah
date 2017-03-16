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

class ActionsQuickCreateRelationshipBehavior extends QuickCreateRelationshipBehavior {

    protected $inlineFormPathAlias = 'application.modules.actions.views.actions._form'; 

    /**
     * Renders an inline record create/update form
     * @param object $model 
     * @param bool $hasErrors
     */
    public function renderInlineForm ($model, $hasErrors, array $viewParams = array ()) {
        $formModel = new ActionsQuickCreateFormModel;
        $formModel->attributes = $_POST;
        $formModel->validate ();
        $actionType = $formModel->actionType;
        if ($actionType) {
            $secondModelName = $formModel->secondModelName;
            $secondModelId = $formModel->secondModelId;
            $associationType = X2Model::getAssociationType ($secondModelName);
            $model->associationType = X2Model::getAssociationType ($secondModelName);

            $tabClass = Publisher::$actionTypeToTab[$actionType];
            $tab = new $tabClass;
            $tab->namespace = get_class ($this);
            $tab->startVisible = true;

            $this->owner->widget('Publisher', array(
                'associationType' => $associationType,
                'associationId' => $model->id,
                'assignedTo' => Yii::app()->user->getName(),
                'calendar' => false,
                'renderTabs' => false,
                'tabs' => array ($tab),
                'namespace' => $tab->namespace,
            ));
        }

        switch ($actionType) {
            case 'action':
            case 'call':
            case 'note':
            case 'event':
            case 'time':
            case 'products':
                $this->owner->renderPartial (
                    'application.components.views.publisher.tabFormContainer', 
                    array (
                        'tab' => $tab,
                        'model' => $formModel->getAction (),
                        'associationType' => $model->associationType,
                    ), false, true);
                break;
            default:
                parent::renderInlineForm ($model, $hasErrors, array_merge (array (
                    'namespace' => get_class ($this),
                ), $viewParams));
                //Yii::app()->controller->badRequest (Yii::t('app', 'Invalid action type'));
        }
    }

    public function quickCreate ($model) {
        if (isset ($_POST['SelectedTab'])) {
            $this->owner->actionPublisherCreate ();
        } else {
            return parent::quickCreate ($model);
        }
    }

}

class ActionsQuickCreateFormModel extends X2FormModel {
    public $secondModelName;
    public $secondModelId;
    public $actionType;
    protected $throwsExceptions = true;

    private $_model;
    public function getModel () {
        if (!isset ($this->_model)) {
            $modelName = $this->secondModelName;
            $this->_model = $modelName::model ()->findByPk ($this->secondModelId);
        }
        return $this->_model;
    }

    private $_action;
    public function getAction () {
        if (!isset ($this->_action)) {
            $action = new Actions;
            $action->setAttributes (array (
                'associationType' => X2Model::getAssociationType ($this->secondModelName),
                'associationId' => $this->secondModelId,
                'assignedTo' => Yii::app()->user->getName (),
            ), true);
            $this->_action = $action;
        }
        return $this->_action;
    }

    public function rules () {
        return array (
            array (
                'secondModelName, secondModelId', 'required'
            ),
            array (
                'actionType', 'safe',
            ),
        );
    }
}

?>
