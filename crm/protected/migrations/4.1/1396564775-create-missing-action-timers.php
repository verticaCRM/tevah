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
 * @file 1396564775-create-missing-action-timers.php
 *
 * Creates action timer records for all actions of a "timed" type.
 *
 * The idea behind this is to "fix" data of previous versions caused by a bug in
 * how action timer records are created for action records of the "timed"
 * varieties (time logged, call logged). The bug was that no timer record would
 * be created for the action (when one would need to be, for future reports that
 * operate on timer records and require this data). It would be created if the
 * user updated the action, but not upon initial creation.
 */

$createMissingActionTimers = function(){
            // No more than this number of records will be inserted at a time:
            $batchSize = 100;

            // Snapshot of X2Model::$associationModels as of April 3, 2014
            $associationModels = array(
                'media' => 'Media',
                'actions' => 'Actions',
                'calendar' => 'X2Calendar',
                'contacts' => 'Contacts',
                'accounts' => 'Accounts',
                'product' => 'Product',
                'products' => 'Product',
                'Campaign' => 'Campaign',
                'marketing' => 'Campaign',
                'quote' => 'Quote',
                'quotes' => 'Quote',
                'opportunities' => 'Opportunity',
                'social' => 'Social',
                'services' => 'Services',
                '' => ''
            );
            
            // Wrapper for accessing the above array in a safe manner
            $getAssociationModelName = function($n) use($associationModels) {
                return array_key_exists($n,$associationModels)?$associationModels[$n]:false;
            };
            
            // The CDbCommand for finding all actions in need of action records:
            $getterCommand = Yii::app()->db->createCommand()
                    ->from('x2_actions a')
                    ->leftJoin('x2_action_timers t', 't.actionId=a.id')
                    ->leftJoin('x2_users u', 'a.assignedTo=u.username')
                    ->where("
                        a.type IN ('time','call')
                        AND t.id IS NULL
                        AND u.id IS NOT NULL
                        AND a.completeDate-a.dueDate > 0");

            // Get a count of the number of records that need to be inserted:
            $getCount = clone $getterCommand;
            $actionCount = (integer) $getCount->select('COUNT(*)')->queryScalar();

            // Get all the necessary data:
            $getActionData = clone $getterCommand;
            $getActionData->select('
                        a.id as actionId,
                        a.dueDate as timestamp,
                        a.completeDate as endtime,
                        u.id as userId,
                        a.associationType as associationType,
                        a.associationId as associationId');
            $actionData = $getActionData->query();
            
            // Columns in the above query:
            $actionTimerColumns = array(
                'actionId',
                'timestamp',
                'endtime',
                'userId',
                'associationType',
                'associationId'
            );

            // Insert records
            $actionTimerRecords = array();
            $actionTimerParams = array();
            $rowCount = 0;

            // Fetch the rows one at a time to avoid exceeding PHP memory limit
            // (i.e. on systems with hundreds of thousands of action records)
            while($row = $actionData->read()) {
                // Parameters in the current record:
                $thisRow = array();
                foreach($actionTimerColumns as $col) {
                    // Parameter name:
                    $param = ":$col$rowCount";
                    $thisRow[] = $param;
                    // If it's the association type column, set it properly to
                    // a model name.
                    // 
                    // Otherwise, just set it.
                    $actionTimerParams[$param] = $col == 'associationType'
                        ? $getAssociationModelName($row[$col])
                        : $row[$col];
                }
                // Parameterized record:
                $actionTimerRecords[] = '('.implode(',',$thisRow).')';
                // Increment row count so that parameter names stay unique and
                // the batch limit can be respected:
                $rowCount++;
                
                // Insert all records or the current batch, whichever number
                // is reached first:
                if($rowCount == $actionCount || $rowCount == $batchSize){
                    $insert = 'INSERT INTO `x2_action_timers`
                        (`'.implode('`,`', $actionTimerColumns).'`)
                        VALUES
                        '.implode(',', $actionTimerRecords);
                    Yii::app()->db->createCommand($insert)
                            ->execute($actionTimerParams);
                    
                    // Reset parameters for another batch:
                    $actionTimerRecords = array();
                    $actionTimerParams = array();
                    $rowCount = 0;
                }
            }

        };

$createMissingActionTimers();

?>
