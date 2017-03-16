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
 * View file for customizing and creating fields.
 *
 * Intended to be rendered partially, via AJAX, in {@link AdminController::actionCreateUpdateField()}
 */

?><div class="page-title rounded-top"><h2><?php echo $new ? Yii::t('admin', "Add a Custom Field") : Yii::t('admin', 'Customize Fields'); ?></h2></div>
<?php echo '<h3 id="createUpdateField-message" style="color:'.($error ? 'red' : 'green').'">'.$message.'</h3>'; ?>

<div class="form" id="createUpdateField-container">
    <div style="width:600px">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'criteria-form',
            'enableAjaxValidation' => false,
            'action' => $this->createUrl('createUpdateField',$new?array():array('id'=>$model->id)),
                ));
        ?>
        <em><?php echo Yii::t('app', 'Fields with <span class="required">*</span> are required.'); ?></em><hr>
        <?php if($new){ ?>
            <div class="row">
                <?php echo $form->labelEx($model, 'modelName'); ?>
                <?php echo $form->dropDownList($model, 'modelName', X2Model::getModelNames()); ?>
                <?php echo $form->error($model, 'modelName'); ?>
            </div>

            <div class="row">
                <br><div><?php echo Yii::t('admin', 'No spaces are allowed.'); ?></div><br>
                <?php echo $form->labelEx($model, 'fieldName'); ?>
                <?php echo $form->textField($model, 'fieldName',array('id'=>'fieldName-input')); ?>
                <?php echo $form->error($model, 'fieldName'); ?>
            </div>
        <?php }else{ ?>
            <div class="row">
                <?php echo $form->labelEx($model, 'modelName'); ?>
                <?php
                echo $form->dropDownList($model, 'modelName', 
                        X2Model::getModelNames(), array(
                    'empty' => Yii::t('admin', 'Select a model'),
                    'id' => 'modelName-existing'
                ));
                ?>
                <?php echo $form->error($model, 'modelName'); ?>
            </div>

            <div class="row">
                <?php echo $form->labelEx($model, 'fieldName'); ?>
                <?php
                $modelSet = !empty($model->modelName);
                $fieldList = array();
                $fieldOptions = array();
                $customOrMod = false;
                if($modelSet) {
                    $fields = Fields::model()->findAll(array(
                        'order' => 'attributeLabel', 
                        'condition' => 'modelName = :mn',
                        'params' => array(
                            ':mn' => $model->modelName
                        )
                    ));
                    foreach($fields as $existingField) {
                        $name = $existingField->fieldName;
                        $fieldList[$name] = $existingField->attributeLabel;
                        if($existingField->custom == 1) {
                            $fieldOptions[$name] = array(
                                'class' => 'field-option field-custom'
                            );
                            $customOrMod = true;
                        } else if($existingField->modified == 1) {
                            $fieldOptions[$name] = array(
                                'class' => 'field-option field-modified'
                            );
                            $customOrMod = true;
                        } else {
                            $fieldOptions[$name] = array(
                                'class' => 'field-option'
                            );
                        }
                    }
                }
                echo $form->dropDownList($model, 'fieldName', $fieldList, array(
                    'empty' => $modelSet ? Yii::t('admin', 'Select field to customize') : Yii::t('admin', 'Select a model first'),
                    'id' => 'fieldName-existing',
                    'options' => $fieldOptions
                ));
                if($modelSet && $customOrMod)
                    echo '&nbsp;'.Yii::t('app','Highlight color indicates: {custom}, {modified}',array(
                        '{custom}' => '<span class="field-option field-custom">'.Yii::t('app','custom field').'</span>',
                        '{modified}' => '<span class="field-option field-modified">'.Yii::t('app','modified default field').'</span>',
                    ));
                ?>
            </div>
            <br>
        <?php } ?>
        <div class="row">
            <div>
            <?php echo $form->labelEx($model, 'attributeLabel'); ?>
            <?php echo $form->textField($model, 'attributeLabel', array('id' => 'attributeLabel')); ?>
            <?php echo $form->error($model, 'attributeLabel'); ?>
            <br><div><?php echo Yii::t('admin', 'Attribute Label is what you want the field to be displayed as.'); ?><br>
            <?php echo Yii::t('admin', 'So for the field firstName, the label should probably be First Name'); ?></div><br>

        </div>


        <div class="row">
            <?php echo $form->labelEx($model, 'type'); ?>
            <?php
            if(!$new && !$model->custom)
                echo '<span style="color:red">'.Yii::t('admin', 'Changing the type of a default field is strongly discouraged.')
                        .' '.Yii::t('admin','It may result in data loss or irregular application behavior.').'</span><br>';
            
            echo $form->dropDownList($model, 'type', Fields::getFieldTypes('title'), array(
                'id' => 'fieldType',
                'class' => ($new ? 'new' : 'existing')
            ));
            ?>
            <?php echo $form->error($model, 'type'); ?>
        </div>
            <div class="row">
                <?php
                // Render a dropdown menu and any other fields
                // for the "linkType" field
                $genericLabel = CHtml::label(Yii::t('app','Type'),
                    CHtml::resolveName($model,$assignTypeName));
                switch($model->type) {
                    case "dropdown":
                        $dropdowns = Dropdowns::model()->findAll();
                        $arr = array();
                        foreach($dropdowns as $dropdown){
                            $arr[$dropdown->id] = $dropdown->name;
                        }

                        echo CHtml::activeDropDownList($model, 'linkType', $arr, array(
                            'id' => 'dropdown-type',
                            'class' => ($new ? 'new' : 'existing')
                        ));
                        break;
                    case "assignment":
                        $assignTypeName = 'linkType';
                        echo $genericLabel; 
                        echo CHtml::activeDropDownList($model,'linkType',array(
                            NULL => Yii::t('app','Single'),
                            'multiple' => Yii::t('app','Multiple')
                        ),array(
                            'id' => 'assignment-multiplicity',
                            'class' => ($new ? 'new' : 'existing')
                        ));
                        break;
                    case "link":
                        $query = Yii::app()->db->createCommand()
                                ->select('modelName')
                                ->from('x2_fields')
                                ->group('modelName')
                                ->queryAll();
                        $arr = array();
                        foreach($query as $array){
                            if($array['modelName'] != 'Calendar')
                                $arr[$array['modelName']] = $array['modelName'];
                        }
                        echo CHtml::activeDropDownList($model, 'linkType', $arr);
                        break;
                    case "custom":
                        ?><div class="row"><div class="cell"><?php
                        echo $genericLabel;
                        echo CHtml::activeDropDownList($model,'linkType',array(
                            'formula' => 'Formula',
                            'display' => 'HTML'
                        ));
                        ?></div><div class="cell"><?php
                        if($model->modelName) {
                            echo CHtml::label(Yii::t('admin','Attributes'),'insertAttrToken');
                            $attrTokens = array();
                            foreach( X2Model::model($model->modelName)->attributeLabels() as $name => $label) {
                                $attrTokens['{'.$name.'}'] = $label;
                            }
                            echo CHtml::dropDownList(
                                'insertAttrToken',
                                $model->isNewRecord ? '' : $model->fieldName,
                                $attrTokens
                            );
                        }?></div></div><?php
                        echo '<br />';
                        echo CHtml::activeLabel($model,'data');
                        echo CHtml::activeTextArea($model,'data',array(
                            'id' => 'custom-field-template'
                        ));
                        echo '<br />'.Yii::t('admin','The template defines how the field will be displayed in X2Engine. The type defines how to interpret the template. If the type is Formula, it will be interpreted as an X2Flow formula.');
                        echo '<br /><br />';
                        break;
                    /* x2prostart */
                    case 'timerSum':
                        echo CHtml::activeLabel($model,'linkType',array('label'=>Yii::t('actions','Type')));
                        echo CHtml::activeDropDownList($model,'linkType',Dropdowns::getItems(120),array('empty'=>Yii::t('app','all types')));
                        break;
                    /* x2proend */
                }
                

                if($model->type != 'timerSum') {
                    $dummyFieldName = 'customized_field';
                    foreach($model->getErrors('defaultValue') as $index => $message){
                        $dummyModel->addError('customized_field', $message);
                    }
                    echo CHtml::label($model->getAttributeLabel('defaultValue'), CHtml::resolveName($dummyModel, $dummyFieldName));
                    $model->fieldName = 'customized_field';
                    echo X2Model::renderModelInput($dummyModel, $model,array('id'=>'defaultValue-input-'.$model->type));
                    echo CHtml::error($dummyModel, 'customized_field');
                }
                echo "<script id=\"input-clientscript-".time()."\">\n";
                Yii::app()->clientScript->echoScripts();
                echo "\n</script>";
            ?>
            </div>
        <br>

        <?php if($model->type != 'timerSum') { ?>
            <div class="row">
                <?php echo $form->checkBox($model, 'required', array('id' => 'required')); ?>
                <?php echo $form->labelEx($model, 'required', array('style' => 'display:inline;')); ?>
                <?php echo $form->error($model, 'required'); ?>
            </div>
    
            <div class="row">
                <?php echo $form->checkBox($model, 'uniqueConstraint', array('id' => 'uniqueConstraint')); ?>
                <?php echo $form->labelEx($model, 'uniqueConstraint', array('style' => 'display:inline;')); ?>
                <?php echo $form->error($model, 'uniqueConstraint'); ?>
            </div>
    
            <div class="row">
                <?php echo $form->checkBox($model, 'searchable', array('id' => 'searchable-custom', 'onclick' => '$("#relevance_box_custom").toggle();')); ?>
                <?php echo $form->labelEx($model, 'searchable', array('style' => 'display:inline;')); ?>
                <?php echo $form->error($model, 'searchable'); ?>
            </div>
    
            <div class="row" id ="relevance_box_custom" style="display:none">
                <?php echo $form->labelEx($model, 'relevance'); ?>
                <?php echo $form->dropDownList($model, 'relevance', Fields::searchRelevance(), array("id" => "relevance-custom")); ?>
                <?php echo $form->error($model, 'relevance'); ?>
            </div>
        <?php } ?>

        <br>
        <div class="row">
            <?php echo $form->labelEx($model,'keyType'); ?>
            <?php if($model->keyType == 'PRI' || $model->keyType =='FIX') {?>
                <br /><span class="error"><?php echo Yii::t('admin','The index on this field cannot be modified.'); ?></span>
            <?php } else {
            echo $form->dropDownList($model,'keyType', array('MUL'=>Yii::t('admin','Index'),'UNI'=>Yii::t('admin','Unique')),array('empty'=>Yii::t('admin','None')));
            echo $form->error($model, 'keyType');

            }
            echo '<br />';
            echo Yii::t('admin', 'This adds an index to the field, which can improve sorting performance. Please note, however, that you cannot add a unique index to a field in a model that has duplicate entries.');
             ?>
        </div>
        <br />

        <br>
        <div class="row buttons">
            <?php 
            echo CHtml::submitButton(Yii::t('app', 'Save'),array(
                'class' => 'x2-button '.($new ? 'new' : 'existing'),
                'id' => 'createUpdateField-savebutton'
            ));
            ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div>
