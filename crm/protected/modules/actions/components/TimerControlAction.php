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
 * TimerControlAction provides an action for the ActionTimer widget to
 * initiate ajax requests to start and stop the timer.
 *
 * @author Raymond Colebaugh <raymond@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class TimerControlAction extends CAction{
    
    public function behaviors() {
        return array(
            'ResponseBehavior' => array(
                'class' => 'application.components.ResponseBehavior',
                'isConsole' => false
        ));
    }

    public function run($stop = 0,$summation=0,$reset=0) {
        $this->attachBehaviors($this->behaviors());
        if($summation) {
            $timers = Yii::app()->db->createCommand()
                    ->select('*')
                    ->from(ActionTimer::model()->tableName())
                    ->where('
                        userId=:userId
                        AND associationId=:associationId
                        AND associationType=:associationType
                        AND endtime IS NOT NULL
                        AND actionId IS NULL')
                    ->queryAll(true, array(
                        ':userId'=>Yii::app()->user->id,
                        ':associationId'=>$_POST['ActionTimer']['associationId'],
                        ':associationType' => $_POST['ActionTimer']['associationType']
            ));
            header('Content-type: application/json');
            echo CJSON::encode($timers);
            return;
        }
        if($reset) {
            Yii::app()->db->createCommand()
                    ->delete(ActionTimer::model()->tableName(), '
                        associationId=:associationId
                        AND associationType=:associationType
                        AND userId=:userId
                        AND actionId IS NULL', array(
                        ':associationId' => $_POST['ActionTimer']['associationId'],
                        ':associationType'=>$_POST['ActionTimer']['associationType'],
                        ':userId' => Yii::app()->user->id,
                    ));
            $this->respond(Yii::t('app','Time cleared'));
            return;
        }
        $this->attachBehaviors($this->behaviors());
        $timer = ActionTimer::setup(true, $_POST['ActionTimer']);
        if($stop == 1) {
            $timer->attributes = $_POST['ActionTimer'];
            $timer->stop();
            $message = "Timer stopped";
        }
        else
            $message = "Timer started";
        $this->response['attributes'] = $timer->getAttributes();
        $this->response['timeSpent'] = ActionTimer::getTimeSpent($timer->associationId,$timer->associationType);
        $this->respond($message);
    }
}
