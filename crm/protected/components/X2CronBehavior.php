<?php

/* * *******************************************************************************
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
 * ****************************************************************************** */

/* @edition:pro */

Yii::import('application.modules.actions.models.*');

/**
 * Run scheduled tasks, including emailing.
 * 
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2CronBehavior extends CBehavior {

    public $isConsole = true;

    public function log($message, $level = 'trace', $category = 'application.automation.cron') {
        Yii::log($message, $level, $category);
    }

    public function runCron() {
        if (isset($_SERVER['REMOTE_ADDR']))
            $this->log('Cron request made from ' . $_SERVER['REMOTE_ADDR']);
        else
            $this->log(sprintf('Cron command executing with uid/gid %d:%d', posix_geteuid(), posix_getegid()));

        $t0 = time();    // monitor how long this is taking

        $events = CActiveRecord::model('CronEvent')->findAllBySql(
                'SELECT * from x2_cron_events ' .
                'WHERE time < ' . time() . ' ' .
                'ORDER BY priority DESC');
        $timeout = Yii::app()->settings->batchTimeout;

        $n_events = 0;
        foreach ($events as &$event) {
            if (time() - $t0 > $timeout) {    // stop after X seconds, we don't want to time out
                $this->log("Time limit of $timeout seconds reached after processing $n_events " .
                        "X2Flow events.", 'error');
                return;
            }
            $n_events++;
            $data = CJSON::decode($event->data);    // attempt to decode JSON data,
            if ($data === false) {                    // delete and skip event if it's corrupt
                $this->log("Encountered a corrupt event record that will be deleted (invalid " .
                        "JSON): id={$event->id}, data={$event->data}", 'error');
                $event->delete();
                continue;
            }

            if ($event->type === 'x2flow') {
                // var_dump($data);
                if (isset($data['flowId'], $data['flowPath'], $data['params'])) {
                    $flow = CActiveRecord::model('X2Flow')->findByPk($data['flowId']);
                    if ($flow !== null) {

                        // reload the model into the params array
                        if (isset($data['modelId'], $data['modelClass'])) {
                            $data['params']['model'] = CActiveRecord::model($data['modelClass'])->
                                    findByPk($data['modelId']);
                            if (is_null($data['params']['model'])) {
                                $event->delete();
                                continue;
                            }
                        }

                        $triggerLogId = null;
                        if (isset($data['triggerLogId'])) {
                            $triggerLogId = $data['triggerLogId'];
                            $flowRetArr = X2Flow::resumeFlowExecution(
                                            $flow, $data['params'], $data['flowPath'], $triggerLogId);
                            $flowTrace = $flowRetArr['trace'];
                            TriggerLog::appendTriggerLog($triggerLogId, array($flowTrace));
                        } else {
                            $flowTrace = X2Flow::resumeFlowExecution(
                                            $flow, $data['params'], $data['flowPath']);
                        }

                        if (!$event->recurring) {
                            $event->delete();    // it was a one-time thing, we're done
                        } else {
                            $event->update(array(
                                'lastExecution' => time(),
                                'time' => $event->time + $event->interval,
                            ));
                        }
                    } else {
                        $event->delete();    // flow has been deleted or something
                    }
                } else {
                    $event->delete();    // event is missing parameters
                }
            } else if ($event->type == 'activity_report') {
                $filters = json_decode($data['filters'], true);
                $userId = $data['userId'];
                $limit = $data['limit'];
                $range = $data['range'];
                $deleteKey = $data['deleteKey'];
                $message = Events::generateFeedEmail($filters, $userId, $range, $limit, $event->id, $deleteKey);
                $eml = new InlineEmail;
                $emailFrom = Credentials::model()->getDefaultUserAccount(Credentials::$sysUseId['systemNotificationEmail'], 'email');
                if ($emailFrom == Credentials::LEGACY_ID) {
                    $eml->from = array(
                        'name' => 'X2Engine Email Capture',
                        'address' => Yii::app()->settings->emailFromAddr,
                    );
                } else {
                    $eml->credId = $emailFrom;
                }

                $mail = $eml->mailer;
                $mail->FromName = 'X2Engine';
                $mail->Subject = 'X2Engine Activity Feed Report';
                $mail->MsgHTML($message);
                $profRecord = Profile::model()->findByPk($userId);
                if (isset($profRecord)) {
                    $mail->addAddress($profRecord->emailAddress);
                    $mail->send();
                    $event->recur();
                } else {
                    // Corrupt event
                    $event->delete();
                }
            }
        }
        $t1 = time();
        $t_events = $t1 - $t0;
        if ($n_events > 0)
            $this->log("Processed $n_events cron events.");

        $criteria = new CDbCriteria();
        // $criteria->addInCondition(array(
        $actionOverdueFlows = CActiveRecord::model('X2Flow')->
                findAllByAttributes(array('active' => true, 'triggerType' => 'ActionOverdueTrigger'));

        $n_overdue = 0;
        foreach ($actionOverdueFlows as &$flow) {
            $t1_0 = time();
            if ($t1_0 - $t0 > $timeout) {
                $this->log(
                        "Time limit of $timeout seconds reached after processing $n_events cron " .
                        "events ($t_events seconds) and $n_overdue overdue action X2Flow triggers (" .
                        ($t1_0 - $t1) . " seconds)");
                return;
            }
            $flow = CJSON::decode($flow->flow);
            if ($flow === false || !isset($flow['trigger']['type'], $flow['trigger']['options']))
                continue;
            $options = &$flow['trigger']['options'];

            if (!isset($options['duration']) || !isset($options['duration']['value']))
                continue;

            $time = X2FlowItem::calculateTimeOffset((float) $options['duration']['value'], 'secs');

            if ($time === false)
                continue;

            $n_overdue++;
            $time = time() - $time;

            $criteria = new CDbCriteria;
            $criteria->addCondition('flowTriggered=0 AND complete != "Yes" AND dueDate < ' . $time);
            // var_dump('complete != "Yes" AND dueDate < '.$time);
            $criteria->limit = 100;

            // printR(CActiveRecord::model('Actions')->count($criteria));
            $actions = CActiveRecord::model('Actions')->findAll($criteria);

            foreach ($actions as &$action) {
                if (time() - $t0 > $timeout)    // stop; we don't want to time out
                    return;
                $action->flowTriggered = 1;
                $action->update(array('flowTriggered'));
                X2Flow::trigger('ActionOverdueTrigger', array(
                    'model' => $action,
                    'duration' => time()-$action->dueDate,
                ));
            }
        }
        if ($n_overdue > 0) {
            $this->log("Processed $n_overdue overdue action X2Flow triggers in " . (time() - $t1) .
                    " seconds.");
        }
        // Finally, send unset campaign email using any remaining time:
        if (!$this->isConsole) {
            Yii::import('application.modules.marketing.components.CampaignMailingBehavior');
            $results = CampaignMailingBehavior::sendMail(null, $t0);
            if (isset($results['messages'])) {
                if (is_array($results['messages'])) {
                    $this->log("Ran marketing batch emailer. " . implode("\n", $results['messages']));
                }
            }
        }
    }

}

?>
