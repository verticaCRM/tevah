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
 * Description of ActionTimerControl
 *
 * @author Raymond Colebaugh <raymond@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimerControl extends X2Widget{
    private $_timer;
    public $model;

    public $associationType;

    private $_hideForm = false;
    
    public function init() {
        $seconds = time() - $this->timer->timestamp;
        $totalSeconds = ActionTimer::getTimeSpent($this->model->id,get_class($this->model));
        Yii::app()->clientScript->registerScript('actionTimerVars', '
                if (typeof x2.actionTimer == "undefined")
                    x2.actionTimer = {};
                x2.actionTimer.actionUrl = '. json_encode(Yii::app()->controller->createUrl('/actions/actions/timerControl')) .';
                var seconds = '. ($this->getTimer()->isNewRecord ? 0 : $seconds) .';
                var totalSeconds = '.$totalSeconds.';
                x2.actionTimer.elapsed = {hours: 0, minutes: 0, seconds: seconds};
                x2.actionTimer.totalElapsed = {hours:0,minutes:0,seconds:totalSeconds+seconds};
                x2.actionTimer.initialElapsed = {hours:0,minutes:0,seconds:totalSeconds+seconds};
                x2.actionTimer.normalizeTime();
                x2.actionTimer.oldTitle = document.title;
                x2.actionTimer.displayInTitle = '.json_encode((integer) !Yii::app()->params->profile->disableTimeInTitle).';
                x2.actionTimer.text = '.json_encode(array_merge(array(
                    'Hours' => Yii::t('app','Hours'),
                    'Minutes' => Yii::t('app','Minutes'),
                    'Pause' => Yii::t('app','Pause'),
                    'Start' => Yii::t('app','Start'),
                    'Stop' => Yii::t('app','Stop'),
                ),Dropdowns::getItems(120))).';
                // True if started, false if not:
                x2.actionTimer.getElement("#actionTimerStartButton").data("status", '. (!$this->timer->isNewRecord ? "true" : "false") .');
                x2.actionTimer.getElement("#actionTimerControl-total").text(x2.actionTimer.formatTotal());
                x2.actionTimer.publisherAction = '.json_encode(Yii::app()->controller->createUrl('/actions/actions/publisherCreate')).';
                // Finally, now that everything is declared, start (if timer already started)
                if(x2.actionTimer.getElement("#actionTimerStartButton").data("status") == true) {
                    x2.actionTimer.start();
                }
                ',CClientScript::POS_READY);
        if($totalSeconds + $seconds == 0) {
            $this->_hideForm = true;
            //Yii::app()->clientScript->registerCss('actionTimer-hidden','#actionTimerLog-form {display: none;}');
        }
        Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/actionTimer.js');
        //Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/actionTimer.css');
        parent::init();
    }
    
    public function getTimer() {
        if (!isset($this->_timer)) {
            $this->_timer = ActionTimer::setup(false, array(
                        'associationId' => $this->model->id,
                        'associationType' => get_class($this->model),
                        'userId' => Yii::app()->getSuId()
            ));
        }
        return $this->_timer;
    }
    
    public function setTimer(ActionTimer $value) {
        $this->_timer = $value;
    }
    
    public function run() {
        $this->render('actionTimerControl', array(
            'model' => $this->model,
            'timer'=>$this->timer,
            'started'=>!$this->timer->isNewRecord,
            'associationType' => $this->model->module,
            'hideForm' => $this->_hideForm,
        ));
    }
}
