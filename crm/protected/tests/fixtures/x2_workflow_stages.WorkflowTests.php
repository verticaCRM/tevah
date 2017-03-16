<?php

return array(
  'stage1' => 
  array (
    'id' => '5',
    'workflowId' => '2',
    'stageNumber' => '1',
    'name' => 'Received Resume',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '0',
    'requireComment' => '0',
  ),
  'stage2' => 
  array (
    'id' => '6',
    'workflowId' => '2',
    'stageNumber' => '2',
    'name' => 'Phone Interview',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-1',
    'requireComment' => '0',
  ),
  'stage3' => 
  array (
    'id' => '7',
    'workflowId' => '2',
    'stageNumber' => '3',
    'name' => 'Phone Interview with Mgr',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-2',
    'requireComment' => '0',
  ),
  'stage4' => 
  array (
    'id' => '8',
    'workflowId' => '2',
    'stageNumber' => '4',
    'name' => 'In Person Interview',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-3',
    'requireComment' => '1',
  ),
  'stage5' => 
  array (
    'id' => '9',
    'workflowId' => '2',
    'stageNumber' => '5',
    'name' => 'Send Acceptance Email',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-4',
    'requireComment' => '0',
  ),
  'stage6' => 
  array (
    'id' => '10',
    'workflowId' => '2',
    'stageNumber' => '6',
    'name' => 'Send New Hire Paperwork',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-5',
    'requireComment' => '0',
  ),
  'stage7' => 
  array (
    'id' => '11',
    'workflowId' => '2',
    'stageNumber' => '7',
    'name' => 'Schedule Training',
    'description' => NULL,
    'conversionRate' => NULL,
    'value' => NULL,
    'requirePrevious' => '-6',
    'requireComment' => '0',
  ),
);

?>
