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
?>
<div class='stage-member-container stage-member-id-<?php echo $data['id']; 
 ?> stage-member-type-<?php echo $data['recordType']; ?>'> 

<?php
$modelName = X2Model::getModelName ($data['recordType']);
$recordName = $recordNames[$modelName];
?>

<div class='stage-icon-container' 
 title='<?php echo Yii::t('workflow', '{recordName}', array ('{recordName}' => $recordName)); ?>'>
    <img src='<?php 
        echo Yii::app()->theme->getBaseUrl ().'/images/workflow_stage_'.$data['recordType'].
            '.png'; ?>' 
     class='stage-member-type-icon left' alt=''>
</div>
<div class='stage-member-name left'><?php 
    if (!$dummyPartial) {
        echo X2Model::getModelLinkMock (
            $modelName,
            $data['nameId'],
            array (
                'data-qtip-title' => $data['name']
            )
        );
    }
?></div>
<div class='stage-member-button-container'>
    <a class='stage-member-button complete-stage-button right x2-button x2-minimal-button' 
     style='display: none;' title='<?php echo Yii::t('app', 'Complete Stage'); ?>'>&gt;</a>
    <a class='stage-member-button undo-stage-button x2-button x2-minimal-button right' 
     style='display: none;' title='<?php echo Yii::t('app', 'Undo Stage'); ?>'>&lt;</a>
    <a class='stage-member-button edit-details-button right' style='display: none;'
     title='<?php echo Yii::t('app', 'View/Edit Workflow Details'); ?>'>
        <span class='x2-edit-icon'></span>
</a>
</div>
<div class='stage-member-info'>
<span class='stage-member-value'>
<?php
if (!$dummyPartial) {
echo Yii::app()->locale->numberFormatter->formatCurrency (
    Workflow::getProjectedValue ($data['recordType'], $data),Yii::app()->params->currency);
}
?>
</span>
</div>

</div>
