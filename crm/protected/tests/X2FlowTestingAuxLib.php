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

Yii::import('application.components.x2flow.*');
Yii::import('application.components.x2flow.actions.*');
Yii::import('application.components.x2flow.triggers.*');

/**
 * Utility methods for flow related unit tests.
 */
class X2FlowTestingAuxLib {

    /**
     * Clears all trigger logs
     */
    public function clearLogs () {
        Yii::app()->db->createCommand ('delete from x2_trigger_logs where 1=1')
            ->execute ();
        $count = Yii::app()->db->createCommand (
            'select count(*) from x2_trigger_logs
             where 1=1')
             ->queryScalar ();
        $this->assertTrue ($count === '0');
    }

    /**
     * Returns trace of log for specified flow 
     * @return null|array
     */
    public function getTraceByFlowId ($flowId) {
        $log = TriggerLog::model()->findByAttributes (array (
            'flowId' => $flowId,
        ));
        if ($log) {
            $decodedLog = CJSON::decode ($log->triggerLog);
            return $decodedLog[1];
        } else {
            return $log;
        }
    }

    /**
     * Decodes flow from flow fixture record. 
     * @param X2DbTestCase $context
     * @param string $rowAlias The row within the fixture to get
     * @param string $fixtureName Name of the fixture from which to get data
     * @return array decoded flow JSON string
     */
    public function getFlow ($context,$rowAlias=null,$fixtureName = 'x2flow') {
        if (!$rowAlias) {
            $aliases = array_keys ($context->{$fixtureName});
            $rowAlias = $aliases[0];
        }
        return CJSON::decode ($context->{$fixtureName}[$rowAlias]['flow']);
    }

    /**
     * Checks each entry in triggerLog looking for errors
     * @param array $trace One of the return value of executeFlow ()
     * @return bool true if an error was found in the log, false otherwise
     */
    public function checkTrace ($trace) {
        if (!$trace[0]) return false;
        $trace = $trace[1];
        while (true) {
            $complete = true;
            foreach ($trace as $action) {
                if ($action[0] === 'X2FlowSwitch') {
                    $trace = $action[2];
                    $complete = false;
                    break;
                }
                if (!$action[1][0]) return false;
            }
            if ($complete) break;
        }
        return true;
    }

    /**
     * Flattens the X2Flow trace, making it much easier to read programmatically. 
     * @param array $trace One of the return value of executeFlow ()
     * @return array flattened trace
     */
    public function flattenTrace ($trace) {
        if (!$trace[0]) return false;
        $flattenedTrace = array (array ('action' => 'start', 'error' => $trace[0]));
        $trace = $trace[1];
        while (true) {
            $complete = true;
            foreach ($trace as $action) {
                if ($action[0] === 'X2FlowSwitch') {
                    array_push ($flattenedTrace, array (
                        'action' => $action[0],
                        'branch' => $action[1],
                    ));
                    $trace = $action[2];
                    $complete = false;
                    break;
                } else {
                    array_push ($flattenedTrace, array (
                        'action' => $action[0],
                        'error' => $action[1][0],
                        'message' => $action[1][1],
                    ));
                }
            }
            if ($complete) break;
        }
        return $flattenedTrace;
    }

    /**
     * Returns array of decoded flows from fixture records
     * @param X2DbTestCase $context A test case for which to obtain data
     * @param string $fixtureName The name of the fixture to pull from
     * @return <array of arrays> decoded flow JSON strings
     */
    public function getFlows ($context,$fixtureName = 'x2flow') {
         return array_map (function ($a) { return CJSON::decode ($a['flow']); }, $context->{$fixtureName});
    }

    /**
     * Executes a specified flow, ensuring that flows won't get triggered recursively
     * @param object $flow An X2Flow model
     */
    public function executeFlow ($flow, $params) {
        $X2Flow = new ReflectionClass ('X2Flow');
        $_triggerDepth = $X2Flow->getProperty ('_triggerDepth');
        $_triggerDepth->setAccessible (TRUE);
        $_triggerDepth->setValue (1);
        $fn = TestingAuxLib::setPublic ('X2Flow', 'executeFlow');
        $returnVal = $fn (array (&$flow, &$params));
        $_triggerDepth->setValue (0);
        return $returnVal;
    }

    public function assertGetInstances ($context, $subClass,$ignoreClassFiles) {
        $items = call_user_func("X2Flow{$subClass}::get{$subClass}Instances");
        $allFiles = scandir(
            $actionsPath = 
                Yii::getPathOfAlias('application.components.x2flow.'.strtolower($subClass).'s'));

        $classFiles = array();
        foreach($allFiles as $file) {
            $classPath = $actionsPath.DIRECTORY_SEPARATOR.$file;
            if(is_file($classPath) && !is_dir($classPath)) {
                $classFiles[] = substr($file,0,-4);
            }
        }
        $classesLoaded = array();
        foreach($items as $itemObject) {
            $classesLoaded[] = get_class($itemObject);
        }
        $classesNotLoaded = array_diff($classFiles,$classesLoaded);
        $classesShouldBeLoaded = array_diff($classesNotLoaded,$ignoreClassFiles);
        $context->assertEquals(
            array(),$classesShouldBeLoaded,'Some classes were not instantiated.');
    }

}

?>
