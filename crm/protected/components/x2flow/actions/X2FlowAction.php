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

/**
 *
 * @package application.components.x2flow
 */
abstract class X2FlowAction extends X2FlowItem {

    public $trigger = null;

    /**
     * Runs the automation action with provided params.
     * @return boolean the result of the execution
     */
    abstract public function execute(&$params);

    /**
     * Checks if all the config variables and runtime params are ship-shape
     * Ignores param requirements if $params isn't provided
     * Returns an array with two elements. The first element indicates whether an error occured,
     * the second contains a log message.
     */
    public function validate(&$params=array(), $flowId) {
        $paramRules = $this->paramRules();
        if(!isset($paramRules['options'],$this->config['options']))
            return array (false, Yii::t('admin', "Flow item validation error"));

        if(isset($paramRules['modelRequired'])) {
            if(!isset($params['model']))    // model not provided when required
                return array (false, Yii::t('admin', "Flow item validation error"));
            if($paramRules['modelRequired'] != 1 && $paramRules['modelRequired'] !== get_class($params['model']))    // model is not the correct type
                return array (false, Yii::t('admin', "Flow item validation error"));
        }
        return $this->validateOptions($paramRules);
    }

    /**
     * @return mixed either a string containing the notification type for this flow's trigger, or null
     */
    public function getNotifType() {
        if($this->trigger !== null && !empty($this->trigger->notifType))
            return $this->trigger->notifType;
        return null;
    }
    /**
     * @return mixed either a string containing the notification type for this flow's trigger, or null
     */
    public function getEventType() {
        if($this->trigger !== null && !empty($this->trigger->eventType))
            return $this->trigger->eventType;
        return null;
    }

    /**
     * Sets model fields using the provided attributes and values.
     *
     * @param CActiveRecord $model the model to set fields on
     * @param array $attributes an associative array of attributes
     * @param array $params the params array passed to X2Flow::trigger()
     * @return boolean whether or not the attributes were valid and set successfully
     *
     */
    public function setModelAttributes(&$model,&$attributeList,&$params) {
        $data = array ();
        foreach($attributeList as &$attr) {
            if(!isset($attr['name'],$attr['value']))
                continue;

            if(null !== $field = $model->getField($attr['name'])) {
                // first do variable/expression evaluation, // then process with X2Fields::parseValue()
                $type = $field->type;
                $value = $attr['value'];
                if(is_string($value)){
                    if(strpos($value, '=') === 0){
                        $evald = Formatter::parseFormula($value, $params);
                        if(!$evald[0])
                            return false;
                        $value = $evald[1];
                    } elseif($params !== null){

                        if(is_string($value) && isset($params['model'])){
                            $value = Formatter::replaceVariables($value, $params['model'], $type);
                        }
                    }
                }

                $data[$attr['name']] = $value;
            }
        }
        if (!isset ($model->scenario)) 
            $model->setScenario ('X2Flow');
        $model->setX2Fields ($data);

        if ($model instanceof Actions && isset($data['complete'])) {
            switch($data['complete']) {
                case 'Yes':
                    $model->complete();
                    break;
                case 'No':
                    $model->uncomplete();
                    break;
            }
        }

        return true;
    }

    /**
     * Gets all action types.
     *
     * Optionally limits actions to a list with a property matching a value.
     * @param string $queryProperty The property of each action to test
     * @param mixed $queryValue The value to match actions against
     */
    public static function getActionTypes($queryProperty=False,$queryValue=False) {
        $types = array();
        foreach(self::getActionInstances() as $class) {
            $include = true;
            if($queryProperty)
                $include = $class->$queryProperty == $queryValue;
            if($include)
                $types[get_class($class)] = $class->title;
        }
        ksort($types);
        return $types;
    }

    public static function getActionInstances() {
        return self::getInstances('actions',array(__CLASS__, 'BaseX2FlowWorkflowStageAction', 'BaseX2FlowEmail'));
    }
}
