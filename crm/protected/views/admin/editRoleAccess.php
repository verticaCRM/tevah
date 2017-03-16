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

Yii::app()->clientScript->registerCss('editRoleAccessCss', "
#role-access-form .row > span {
    margin-right: 5px;
}

/*@media (max-width: 482px) {
    #role-access-form .row > span {
        display: block;
    }
}*/

#permissions-table {
    border-width:1px; border-color: #666666; #border-collapse: collapse; 
}
#permissions-table th{
    text-align:center; border-width: 1px; padding: 8px; border-style: solid; 
    border-color: #999999; background-color: #dedede; font-weight:bold; 
}
#permissions-table td { 
    border-width: 1px; padding: 8px; border-style: solid; border-color: #999999; 
    text-align:center; 
}
#permissions-table td * { 
    display:inline; 
}
#permissions-table tr.odd-row { 
    background: #F5F4DE; 
}
.table-hint {
    position: relative; 
    left: 3px;
    cursor: pointer;
}
.master-checkbox {
    position: relative; top:2px; left:2px; 
}

");

Yii::app()->clientScript->registerScript('editRoleAccessJS',"
$(function () {
    $('#admin-flag').click (function () { 
        var value = $('#admin-flag').attr('checked');
        if (value === 'checked') {
            $('#role-access-form').hide();
        } else {
            $('#role-access-form').show();
        }
    });
});
", CClientScript::POS_END);


?>
<div class="page-title rounded-top">
    <h2><?php echo Yii::t('admin', 'Edit Role Access'); ?></h2>
</div>
<div class="form">
    <?php
    $list = Roles::model()->findAll();
    $names = array('DefaultRole' => 'Default Role');
    foreach ($list as $role) {
        $names[$role->name] = $role->name;
    }
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'roleEdit-form',
        'enableAjaxValidation' => false,
        'action' => 'editRoleAccess',
    ));
    ?>

    <em><?php 
    echo Yii::t('app', 'Fields with <span class="required">*</span> are required.'); 
    ?></em>
    <br>

    <div class="row">
        <?php 
        echo $form->labelEx($model, 'name');
        echo $form->dropDownList($model, 'name', $names, array(
            'empty' => Yii::t('admin', 'Select a role'),
            'id' => 'editDropdown',
            'ajax' => array(
                'type' => 'POST',
                'url' => CController::createUrl('/admin/getRoleAccess'), 
                'update' => '#role-access-form', 
                'complete' => "function(){
                    x2.forms.setUpQTips ();
                    var dropdownValue = $('#editDropdown').val();
                    if(dropdownValue !== 'authenticated' && dropdownValue !== '')
                        $('#admin-flag-box').show();
                    else
                        $('#admin-flag-box').hide();

                    if(dropdownValue === '')
                        $('#roleForm').hide();
                    else
                        $('#roleForm').show();

                    if (dropdownValue === 'DefaultRole') {
                        $('#admin-flag').attr ('disabled', 'disabled');
                    } else {
                        $('#admin-flag').removeAttr ('disabled');
                    }
                }",
        )));
        echo $form->error($model, 'name'); 
        ?>
    </div>
    <div id="roleForm">
        <div id="role-access-form">
        </div>
    </div>
    <br>
    <div class="row buttons">
        <?php 
        echo CHtml::submitButton(
            Yii::t('app', 'Save'), 
            array(
                'class' => 'x2-button',
                'id' => 'edit-role-access-form-save-button',
            )
        ); 
        ?>
    </div>
    <?php $this->endWidget(); ?>
</div>
