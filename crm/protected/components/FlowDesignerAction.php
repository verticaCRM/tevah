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

/**
 * External action for creating and editing X2Flow automation workflows
 *
 * @package application.components
 */
class FlowDesignerAction extends CAction {
    public function run($pageSize=null) {

        $viewParams = array ();

        if(isset($_GET['id'])){
            $flow = $this->loadModel($_GET['id']);
            User::addRecentItem('f', $flow->id, Yii::app()->user->getId()); 
        } else {
            $flow = new X2Flow;
        }

        // $flowData = false;

        if(isset($_POST['X2Flow'])) {
            $flow->attributes = $_POST['X2Flow'];
            $flowData = CJSON::decode($flow->flow);
            $flow->name = $_POST['X2Flow']['name'];
            $flowData['flowName'] = $flow->name;
            $flow->flow = CJSON::encode ($flowData);
            $flow->active = $_POST['X2Flow']['active'];

			if($flow->save())
				$this->getController()->redirect(array('/studio/flowDesigner','id'=>$flow->id));
            else 
                AuxLib::debugLogR ($flow->getErrors ());
		}

        if (isset ($flow->name)) {
            $triggerLogsDataProvider = new CActiveDataProvider('TriggerLog', array(
                        'criteria' => array(
                            'condition' =>
                                'flowId='.$flow->id,
                            'order' => 'triggeredAt DESC'
                        ),
                        'pagination'=>array(
                            'pageSize' => !empty($pageSize) ?
                                $pageSize :
                                Profile::getResultsPerPage()
                        ),
                    ));
            $viewParams['triggerLogsDataProvider'] = $triggerLogsDataProvider;
        }

        if (isset ($_GET['ajax']) && $_GET['ajax'] = 'trigger-log-grid') {
            $this->controller->renderPartial (
                '_triggerLogsGridView', array (
                    'triggerLogsDataProvider' => $triggerLogsDataProvider,
                    'flowId' => $flow->id,
                    'parentView' => 'flowEditor'
                )
            );
            Yii::app()->end ();
        }

        // order action types
        $actionTypes = X2FlowAction::getActionTypes();
        asort ($actionTypes);

        $viewParams['model'] = $flow;
        $viewParams['actionTypes'] = $actionTypes;
        $viewParams['triggerTypes'] = X2FlowTrigger::getTriggerTypes();
        $viewParams['requiresCron'] = array_keys(
            array_merge(X2FlowAction::getActionTypes('requiresCron',true),
                X2FlowTrigger::getTriggerTypes('requiresCron',true)));

        $this->getController()->render('flowEditor', $viewParams);
    }

    /**
     * Saves all the items in a given branch. Recurses when a switch is encountered.
     * @param array &$items the items in the current branch
     * @return integer ID of the first item in this branch
     */
    protected function saveFlowBranch(&$items,$flowId) {
        for($i=count($items)-1;$i>=0;$i--) {        // loop backwards through the flow
            $flowItem = new X2FlowItem;
            $flowItem->flowId = $flowId;
            $flowItem->type = $item[$i]['type'];

            if($item[$i]['type'] == 'switch') {
                $flowItem->config = CJSON::encode($items[$i]['conditions']);    // save the conditions for this switch
                $flowItem->nextIfTrue = $this->saveFlowBranch($items[$i]['trueBranch'],$flowId);
                $flowItem->nextIfFalse = $this->saveFlowBranch($items[$i]['falseBranch'],$flowId);
            } else {
                $flowItem->config = CJSON::encode($items[$i]['fields']);    // save the conditions for this switch
                $flowItem->nextIfTrue = $followingId;
            }
            if($flowItem->save())
                return $flowItem->id;    // return ID for the next call up the stack to set as $item->nextIfTrue
            else
                return false;    // invalid, abort
        }
        return null;    // end of flow branch
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     *
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     */
    public function loadModel($id) {
        if(null === $model = CActiveRecord::model('X2Flow')->findByPk((int)$id))
            throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
        return $model;
    }
}
?>
