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

Yii::import('application.modules.actions.models.*');
/**
 * Model class for recording time spent on records, i.e. contacts, opportunities, etc.
 *
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimer extends CActiveRecord {

    public static function model($class = __CLASS__) {
        return parent::model($class);
    }

    public function tableName(){
        return 'x2_action_timers';
    }

    public function rules() {
        return array(
            array('userId,associationId,associationType,type','safe')
        );
    }

    /**
     * Computes the time spent on an action vis a vis its timer records
     * @param type $actionId
     * @return type
     */
    public static function actionTimeSpent($actionId){
        return Yii::app()->db->createCommand()
                        ->select('SUM(`endtime`-`timestamp`)')
                        ->from(self::model()->tableName())
                        ->where('`actionId`=:actionId')
                        ->queryScalar(array(':actionId' => $actionId));
    }

    /**
     * Performs a sum over timer records
     * @param type $id
     * @param type $associationType
     * @return type
     */
    public static function getTimeSpent($id = null,$associationType = null,$userId = null) {
        $timeSpent = 0;
        $attributes = array(
            'associationId' => $id,
            'userId' => $userId === null ? Yii::app()->getSuId() : $userId
        );
        if($associationType !== null)
            $attributes['associationType'] = $associationType;
        $cases = self::model()->findAllByAttributes($attributes, 'endtime IS NOT NULL AND actionId IS NULL');
        foreach($cases as $case)
            $timeSpent += $case->endtime - $case->timestamp;
        return $timeSpent;
    }
    
    public static function humanReadableTimeSpent($id = null) {
        $duration = "0s";
        $seconds = self::getTimeSpent($id);
        if ($seconds != 0) {
            $intervals = array(
                'd' => 24 * 60 * 60,
                'h' => 60 * 60,
                'm' => 60,
                's' => 1
                );
            $values = array();
            foreach($intervals as $unit=>$interval) {
                if($quotient = intval($seconds / $interval)) {
                    $readable = $quotient . $unit;
                    array_push($values, $readable);
                    $seconds -= $quotient * $interval;
                }
            }
            $duration = implode(' ', $values);
        }
        return $duration;
    }

    /**
     * Return an initialized active record model, matching any that exist.
     *
     * It is preferable to use this instead of constructing a timer object
     * manually, to avoid violating the unique constraint.
     *
     * @param bool $save Whether to save the new timer upon initialization.
     * @param array $attributes The initial identifying attributes for the timer.
     *   If a preexisting timer record matching them is found, it will be
     *   returned in place of a new model.
     */
    public static function setup($save=false,$attributes = array()) {
        
        if(!isset($attributes['userId']))
            $attributes['userId'] = Yii::app()->getSuId();
        if(!isset($attributes['associationId'])) {
            $attributes['associationId'] = null;
        }
        if(!isset($attributes['type'])) {
            $attributes['type'] = null;
        }
        $uniqueAttributes = array_intersect_key($attributes, array_flip(array('userId','associationId')));

        $criteria = new CDbCriteria(array('condition' => 'endtime IS NULL OR endtime = ""'));
        $existing = self::model()->findByAttributes($uniqueAttributes, $criteria);

        if((bool) $existing) {
            return $existing;
        } else {
            $class = __CLASS__;
            $timer = new $class;
            $timer->attributes = $attributes;
            $timer->timestamp = time();
            if($save)
                $timer->save();
            return $timer;
        }
    }

    /**
     * Ends the "timer" and creates an action record based upon it.
     * 
     * @param array $actionAttr attributes of the action
     * @return Actions
     */
    public function stop() {
        $this->endtime = time();
        $this->update(array('endtime', 'type'));
    }
}

?>
