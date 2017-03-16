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

/*
Rendered by dropdown editor action
*/

Yii::app()->clientScript->registerScript('_colorDropdownFormJS',"
    // initialize value color pickers
    x2.colorPicker._initializeX2ColorPicker ();
", CClientScript::POS_END);

$i = 0;
foreach($options as $value => $label){
++$i;
?>
    <li>
        <label for='dropdown-value-<?php 
            echo $i; ?>'><?php echo Yii::t('actions', 'Color'); 
        ?></label>
        <input id='dropdown-value-<?php echo $i; ?>' class='x2-color-picker x2-color-picker-hash' 
         type="text" size="20" name="Dropdowns[values][]" value='<?php echo $value; ?>' />
        <label for='dropdown-label-<?php 
            echo $i; ?>'><?php echo Yii::t('actions', 'Label'); 
        ?></label>
        <input id='dropdown-label-<?php echo $i; ?>'type="text" size="30"  
         name="Dropdowns[labels][]" value='<?php echo $label; ?>' />
            <div class="">
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.moveOptionUp(this);">
                    [<?php echo Yii::t('admin', 'Up'); ?>]</a>
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.moveOptionDown(this);">
                    [<?php echo Yii::t('admin', 'Down'); ?>]</a>
                <a href="javascript:void(0)" 
                 onclick="x2.dropdownManager.deleteOption(this);">
                    [<?php echo Yii::t('admin', 'Del'); ?>]</a>
            </div>
            <br />
    </li>
<?php
}
echo CHtml::activeCheckbox (
    Yii::app()->settings, 'enableColorDropdownLegend',
    array (
        'class' => 'left-checkbox',
    )
);
echo CHtml::activeLabel (
    Yii::app()->settings, 'enableColorDropdownLegend');
?>
<li id='color-dropdown-option-template' style='display: none;'>
    <label for='dropdown-value-<?php 
        echo $i; ?>'><?php echo Yii::t('actions', 'Color'); 
    ?></label>
    <input disabled='disabled' id='dropdown-value-<?php echo $i; ?>' 
     class='x2-color-picker-hash' type="text" size="20" name="Dropdowns[values][]"
    />
    <label for='dropdown-label-<?php 
        echo $i; ?>'><?php echo Yii::t('actions', 'Label'); 
    ?></label>
    <input disabled='disabled' id='dropdown-label-<?php echo $i; ?>'type="text" size="30"  
     name="Dropdowns[labels][]" />
        <div class="">
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.moveOptionUp(this);">
                [<?php echo Yii::t('admin', 'Up'); ?>]</a>
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.moveOptionDown(this);">
                [<?php echo Yii::t('admin', 'Down'); ?>]</a>
            <a href="javascript:void(0)" 
             onclick="x2.dropdownManager.deleteOption(this);">
                [<?php echo Yii::t('admin', 'Del'); ?>]</a>
        </div>
        <br />
</li>
