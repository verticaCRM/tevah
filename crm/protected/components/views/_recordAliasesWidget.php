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

$aliasTypeOptions = RecordAliases::model ()->getAliasTypeOptions ();

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/views/recordAliasesWidget.css');
Yii::app()->clientScript->registerScriptFile(
    'http://www.skypeassets.com/i/scom/js/skype-uri.js');
Yii::app()->clientScript->registerScript('recordAliasesWidgetJS',"

$(function () {
    new x2.RecordAliasesWidget ({
        element: '.record-aliases-dropdown-container',
        aliasOptions: ".CJSON::encode ($aliasTypeOptions).",
        aliasTypeIcons: ".CJSON::encode ($aliasModel->getAllIcons ()).",
        baseUrl: yii.scriptUrl + 
            '/".Yii::app()->controller->module->name."/".Yii::app()->controller->module->name."',
        translations: ".CJSON::encode (array (
            'dialogTitle' => Yii::t('app', 'Create Alias'),
            'cancel' => Yii::t('app', 'Cancel'),
            'create' => Yii::t('app', 'Create'),
            'confirmDeletion' => Yii::t('app', 'Are you sure you want to delete this alias?'),
            'confirmDeletionTitle' => Yii::t('app', 'Delete alias?'),
            'OK' => Yii::t('app', 'OK'),
            'skypeQtipLoadingText' => Yii::t('app', 'Loading...'),
        )).",
        recordId: ".$this->model->id."
    });
});

", CClientScript::POS_END);

if (!$this->formOnly) {
?>
<span class='<?php echo CHtml::encode (strtolower (get_class ($this->model))); ?> icon'></span>
<div class='record-aliases-dropdown-container'>
    <h2 class='record-name'>
    <?php
        echo $this->model->name;
    ?>
    </h2>
    <button title="<?php echo CHtml::encode (Yii::t('app', 'View aliases')) ?>" 
     class='view-aliases-button fa fa-caret-square-o-down'></button>
    <ul class='alias-dropdown x2-dropdown-list' style='display: none;'>
        <span>
        <?php
        foreach ($this->getAliases () as $alias) {
        ?>
        <li data-alias-type='<?php echo CHtml::encode ($aliasTypeOptions[$alias->aliasType]); ?>'
         data-id='<?php echo $alias->id ?>'><?php 
            echo $alias->getIcon (true); 
            ?>
            <span class='record-alias'>
            <?php
                echo $alias->renderAlias ();
            ?>
            </span>
            <span class='delete-alias-button fa fa-times' 
             title="<?php echo CHtml::encode (Yii::t('app', 'Delete alias')); ?>"></span>
            <?php
        ?></li>     
        <?php
        }
        ?>
        <li class='alias-template' style='display: none;'>
            <span class='record-alias'>
            </span>
            <span class='delete-alias-button fa fa-times' 
             title="<?php echo CHtml::encode (Yii::t('app', 'Delete alias')); ?>"></span>
            <?php
        ?></li>     
        </span>
        <li class='new-alias-button x2-button'><?php 
            echo CHtml::encode (Yii::t('app', 'Add new alias')) 
        ?></li>     
    </ul>
    <?php
}
    $form = $this->beginWidget('CActiveForm', array (
        'htmlOptions' => array (
            'class' => 'add-alias-dialog form2',
            'style' => 'display: none;',
        ),
    ));
    echo $form->errorSummary ($aliasModel);
    ?>
        <?php
        echo $form->hiddenField ($aliasModel, 'recordId');
        echo $form->label ($aliasModel, 'alias', array (
            'class' => 'left-label',
            'label' => CHtml::encode (Yii::t('app', 'Alias:')),
        ));
        echo $form->TextField ($aliasModel, 'alias');
        $i = 0;
        if ($aliasModel->aliasType) {
            $checkedType = $aliasModel->aliasType;
        } else {
            $checkedType = 'email';
        }
        foreach ($aliasModel->getAliasTypeOptions () as $val => $label) {
            $aliasModel->aliasType = $val;
            if ($i % 3 === 0) {
                echo '<div class="bs-row">';
            }
            echo '<div class="alias-type-cell">';
                echo '<span class="'.($val === $checkedType ? ' selected' : '').'" 
                    title='.CHtml::encode ($aliasTypeOptions[$val]).'>';
                if ($val === 'other') {
                    echo $form->label ($aliasModel, CHtml::encode (Yii::t('app', 'other')), 
                        array ('class' => 'left-label'));
                } else {
                    echo $aliasModel->getIcon (false, true); 
                }
                echo $form->radioButton (
                    $aliasModel, 'aliasType', array (
                        'id' => 'alias-type-'.$val,
                        'value' => $val,
                        'data-default' => $val,
                        'checked' => $val === $checkedType ? 'checked' : '',
                        'uncheckValue' => null,
                    ));
                echo '</span>';
            echo '</div>';
            if ($i % 3 === 2) {
                echo '</div>';
            }
            $i++;
        }
        if ($i % 3 !== 0) {
            echo '</div>';
        }
    $this->endWidget ();
if (!$this->formOnly) {
    ?>
</div>
<?php
}
?>
