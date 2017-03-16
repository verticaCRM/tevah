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


$attributeLabels = $model->attributeLabels();

// $showSocialMedia = Yii::app()->params->profile->showSocialMedia;
// $showWorkflow = Yii::app()->params->profile->showWorkflow;

$cs = Yii::app()->getClientScript();


if ($modelName == 'contacts' || $modelName == 'opportunities') {
$cs->registerScript('toggleWorkflow', "
    function showWorkflow() {
        $('tr#workflow-row').show();
        $('tr#workflow-toggle').hide();
    }
    function hideWorkflow() {
        $('tr#workflow-row').hide();
        $('tr#workflow-toggle').show();
    }
", CClientScript::POS_HEAD);
}

// $(function() {\n"
// .($showWorkflow? "showWorkflow();\n" : "hideWorkflow()\n")
// ."});",CClientScript::POS_HEAD);

$cs->registerScript('setFormName', "
window.formName = '$modelName';
", CClientScript::POS_HEAD);

$scenario = isset($scenario) ? $scenario : 'Default';
$nameLink = isset($nameLink) ? $nameLink : false;

$authParams['X2Model'] = $model;
$moduleName = X2Model::getModuleName($modelName);
$modelEdit = Yii::app()->params->isAdmin || Yii::app()->user->checkAccess(ucfirst($moduleName) . 'Update', $authParams);

// check the app cache for the data
$layoutData = Yii::app()->cache->get('form_' . $modelName . '_' . $scenario);
$fields = array();


// remove this later, once all models extend X2Models
if (method_exists($model, 'getFields')) {
    $fields = $model->getFields(true);
} else {
    foreach (X2Model::model('Fields')->findAllByAttributes(
            array('modelName' => ucfirst($modelName))) as $fieldModel) {

        $fields[$fieldModel->fieldName] = $fieldModel;
    }
}

if ($layoutData === false) {
    $layout = FormLayout::model()->findByAttributes(
            array('model' => ucfirst($modelName), 'defaultView' => 1, 'scenario' => $scenario));

    if (isset($layout)) {
        $layoutData = json_decode($layout->layout, true);
        Yii::app()->cache->set('form_' . $modelName . '_' . $scenario, $layoutData, 0);    // cache the data
    }
}

if ($layoutData !== false && isset($layoutData['sections']) && count($layoutData['sections']) > 0) {
    echo '<div class="x2-layout x2-layout-island' . ((isset($halfWidth) && $halfWidth) ? ' half-width' : '') . '">';
    $formSettings = Profile::getFormSettings($modelName);

    $fieldPermissions = array();

    if (!Yii::app()->params->isAdmin && !empty(Yii::app()->params->roles)) {
        $rolePermissions = Yii::app()->db->createCommand()
                ->select('fieldId, permission')
                ->from('x2_role_to_permission')
                ->join('x2_fields', 'x2_fields.modelName="' . $modelName .
                        '" AND x2_fields.id=fieldId AND roleId IN (' . implode(',', Yii::app()->params->roles) . ')')
                ->queryAll();

        foreach ($rolePermissions as &$permission) {
            if (!isset($fieldPermissions[$permission['fieldId']]) ||
                    $fieldPermissions[$permission['fieldId']] < (int) $permission['permission']) {
                $fieldPermissions[$permission['fieldId']] = (int) $permission['permission'];
            }
        }
    }

    if (!isset($specialFields))
        $specialFields = array();
    if (!isset($suppressFields))
        $suppressFields = array();

    $i = 0;
    foreach ($layoutData['sections'] as &$section) {
        $noItems = true; // if no items, don't display section
        // set defaults
        if (!isset($section['title']))
            $section['title'] = '';
        if (!isset($section['collapsible']))
            $section['collapsible'] = false;
        if (!isset($section['rows']))
            $section['rows'] = array();
        if (!isset($formSettings[$i])) {
            $formSettings[$i] = 1;
        }

        $collapsed = !$formSettings[$i] && $section['collapsible'];

        $htmlString = '';

        $htmlString .= '<div class="formSection';
        if ($section['collapsible'])
            $htmlString .= ' collapsible';
        if (!$collapsed)
            $htmlString .= ' showSection';
        $htmlString .= '">';

        $htmlString .= '<div class="formSectionHeader">';
        if ($section['collapsible']) {
            $htmlString .=
                    '<a href="javascript:void(0)" class="formSectionHide">
                    ' . X2Html::fa('fa-caret-down') . '
                </a>';
            $htmlString .=
                    '<a href="javascript:void(0)" class="formSectionShow">
                    ' . X2Html::fa('fa-caret-right') . '
                </a>';
        }
        if (!empty($section['title'])) {
            $htmlString .= '<span class="sectionTitle" title="' . addslashes($section['title']) . '">' .
                    Yii::t(strtolower(Yii::app()->controller->id), $section['title']) . '</span>';
        } else {
            $htmlString .= '<span class="sectionTitle"></span>';
        }
        $htmlString .= '</div>';

        if (!empty($section['rows'])) {
            $htmlString .= '<div class="tableWrapper"';
            if ($collapsed)
                $htmlString .= ' style="display:none;"';
            $htmlString .= '><table>';

            foreach ($section['rows'] as &$row) {
                $htmlString .= '<tr class="formSectionRow">';
                if (isset($row['cols'])) {
                    foreach ($row['cols'] as &$col) {

                        $width = isset($col['width']) ? ' style="width:' . $col['width'] . 'px"' : '';
                        $htmlString .= "<td$width>";
                        if (isset($col['items'])) {
                            foreach ($col['items'] as &$item) {


                                if (isset($item['name'], $item['labelType'], $item['readOnly'], 
                                    $item['height'], $item['width'])) {

                                    $fieldName = preg_replace('/^formItem_/u', '', $item['name']);
                                    if (isset($fields[$fieldName])) {
                                        $field = $fields[$fieldName];

                                        if (in_array($fieldName, $suppressFields) ||
                                                isset($fieldPermissions[$field->id]) &&
                                                $fieldPermissions[$field->id] == 0) {
                                            unset($item);
                                            $htmlString .= '</div></div>';
                                            continue;
                                        } else {
                                            $noItems = false;
                                        }

                                        $labelType = isset($item['labelType']) ? $item['labelType'] : 'top';
                                        switch ($labelType) {
                                            case 'inline': $labelClass = 'inlineLabel';
                                                break;
                                            case 'none': $labelClass = 'noLabel';
                                                break;
                                            case 'left': $labelClass = 'leftLabel';
                                                break;
                                            case 'top':
                                            default: $labelClass = 'topLabel';
                                        }
                                        $inlineEdit = $modelEdit && $scenario != 'Inline' && (!$field->readOnly && (!isset($fieldPermissions[$field->id]) || (isset($fieldPermissions[$field->id]) && $fieldPermissions[$field->id] === 2)));
                                        $htmlString .= "<div id=\"{$field->modelName}_{$field->fieldName}_field\"" .
                                                " class=\"formItem $labelClass";
                                        if ($inlineEdit) {
                                            $htmlString.= "  inline-edit";
                                        }
                                        $htmlString .= "\">";
                                        $htmlString .= CHtml::label($model->getAttributeLabel($field->fieldName), false);

                                        $class = 'formInputBox';
                                        $style = 'width:' . $item['width'] . 'px;';
                                        if ($field->type == 'text') {
                                            $class .= ' textBox';
                                            $textFieldHeight = $item['height'] . 'px';
                                            $style .= 'min-height:' . $textFieldHeight;
                                        }

                                        $htmlString .= '<div class="' . $class . '" style="' . $style . '">';
                                        if ($inlineEdit) {
                                            $htmlString .=
                                                    '<span class="model-input" 
                                                  id="' . $field->modelName . '_' .
                                                    $field->fieldName . '_field-input" 
                                                  style="display:none">' .
                                                    $model->renderInput($field->fieldName, array(
                                                        'tabindex' => isset($item['tabindex']) ?
                                                                $item['tabindex'] : null,
                                                        'disabled' => $item['readOnly'] ?
                                                                'disabled' : null,
                                                    )) . '</span>';
                                            if ($field->type === 'rating') {
                                                $val = $model->$fieldName;
                                                if (empty($model->$fieldName)) {
                                                    $val = 0;
                                                }
                                            }
                                        }
                                        $htmlString .= '<span class="model-attribute" id="' . $field->modelName . '_' . $field->fieldName . '_field-field">';
                                        if (isset($specialFields[$fieldName])) {
                                            $fieldHtml = $specialFields[$fieldName];
                                        } else {
                                            if ($field->fieldName == 'name' && $nameLink && $model->asa('X2LinkableBehavior')) {
                                                $fieldHtml = $model->link;
                                            } else {
                                                $fieldHtml = $model->renderAttribute(
                                                        $field->fieldName, true, false);
                                            }
                                        }
                                        if ($fieldHtml === '') {
                                            $htmlString .= '&nbsp;';
                                        } else {
                                            $htmlString .= $fieldHtml;
                                        }
                                        $htmlString .= '</span>';
                                    }
                                }
                                unset($item);
                                $htmlString .= '</div>';
                                if ($inlineEdit) {
                                    $htmlString .= CHtml::link (X2Html::fa('fa-edit'), '#', array(
                                        'class' => 'edit-icon active',
                                        'title' => Yii::t('app','Edit field'),
                                    ));
                                    $htmlString .= CHtml::link (X2Html::fa('fa-check-circle'), '#', array(
                                        'class' => 'confirm-icon',
                                        'title' => Yii::t('app', 'Confirm changes'),
                                    ));
                                    $htmlString .= CHtml::link (X2Html::fa('fa-times-circle'), '#', array(
                                        'class' => 'cancel-icon',
                                        'title' => Yii::t('app', 'Cancel changes'),
                                    ));
                                }
                                $htmlString .= '</div>';
                            }
                        }
                        $htmlString .= '</td>';
                    }
                }
                unset($col);
                $htmlString .= '</tr>';
            }
            $htmlString .= '</table></div>';
        }
        unset($row);
        $htmlString .= '</div>';
        if (!$noItems)
            echo $htmlString;
        $i++;
    }
    echo '</div>';
}
if ($scenario != 'Inline') {
    $jsParams = CJSON::encode (array (
        'modelId' => $model->id,
        'translations' => array (
            'unsavedChanges' => Yii::t('app', 'There are unsaved changes on this page.')
        )
    ));

    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/inlineEditor.js');
    Yii::app()->clientScript->registerScript('inlineEditJS', "
        new x2.InlineEditor($jsParams); 
    ", CClientScript::POS_READY);
}
?>
