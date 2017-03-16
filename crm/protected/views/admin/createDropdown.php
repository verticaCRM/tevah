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
<div class="page-title rounded-top"><h2><?php echo Yii::t('admin','Dropdown Editor'); ?></h2></div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'field-form',
	'enableAjaxValidation'=>false,
        'action'=>'dropDownEditor',
)); ?>

    <div class="row">
        <?php 
        echo $form->labelEx($model,'name'); 
        echo $form->textField($model,'name');
        echo $form->error($model,'name'); 
        ?>
    </div>
    <div id="dropdown-options">
        <label><?php echo Yii::t('admin','Dropdown Options');?></label>
        <ol>
            <li>
                <input type="text" size="30" name="Dropdowns[options][]" />

                <div class="">
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.moveOptionUp(this);">[<?php 
                        echo Yii::t('admin','Up'); ?>]</a>
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.moveOptionDown(this);">[<?php 
                        echo Yii::t('admin','Down'); ?>]</a>
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.deleteOption(this);">[<?php 
                        echo Yii::t('admin','Del'); ?>]</a>
                </div>
                <br />
            </li>
            <?php
            echo CHtml::activeLabel($model, 'multi', array (
                'class' => 'multi-checkbox-label',
            )).'&nbsp;'.CHtml::activeCheckBox($model, 'multi');
            ?>
        </ol>
    </div>
    <a href="javascript:void(0)" 
     onclick="x2.dropdownManager.addOption();" class="add-dropdown-option">[<?php 
        echo Yii::t('admin','Add Option'); ?>]</a>
    <div class="row buttons">
        <br />
		<?php 
        echo CHtml::submitButton(
            $model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),
            array('class'=>'x2-button')
        ); 
        ?>
    </div>
<?php $this->endWidget();?>
</div>
