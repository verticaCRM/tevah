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
<div class="page-title"><h2><?php echo Yii::t('admin','Rename a Module'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','You can rename a module by selecting a module and typing the new name below.'); ?>
<br><br>
<?php
    echo CHtml::label(Yii::t('admin', 'Module:'), '');
    $moduleOptions = array_merge(array('' => Yii::t('admin', 'Please select a module')), $modules);
    X2Html::getFlashes();
    echo CHtml::form('renameModules','post',array('enctype'=>'multipart/form-data'));
    echo CHtml::dropDownList('module', '', $moduleOptions)."<br><br>";
    echo CHtml::label(Yii::t('admin', 'New Name:'), 'name');
    echo CHtml::textField('name');
    echo "<div id='itemNameField'>";
    echo CHtml::label(Yii::t('admin', 'Item Name'), 'itemName');
    echo CHtml::textField('itemName');
    echo "</div>";
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button'));
    echo CHtml::endForm();
?> </div>

<?php Yii::app()->clientScript->registerScript ('renameModulesJs', "
    $('#module').change(function() {
        var selectedModule = $('#module').find(':selected').val();
        var modules = ". CJSON::encode($modules) ."
        var itemNames = ". CJSON::encode($itemNames) ."

        $('#name').val(modules[selectedModule]);
        if (typeof itemNames[selectedModule] !== 'undefined') {
            $('#itemNameField').show();
            $('#itemName').val(itemNames[selectedModule]);
        } else {
            $('#itemNameField').hide();
            $('#itenName').val('');
        }
    });

    $(function() {
        // Hide the item name field on page load if module's items cannot be renamed
        var selectedModule = $('#module').find(':selected').val();
        var itemNames = ". CJSON::encode($itemNames) ."

        if (typeof itemNames[selectedModule] === 'undefined') {
            $('#itemNameField').hide();
            $('#itenName').val('');
        }
    });
", CClientScript::POS_READY);
