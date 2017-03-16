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



Yii::app()->clientScript->registerCssFile(Yii::app()->controller->module->getAssetsUrl ().
    '/css/gridReportsGridView.css');
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/gridview/styles.css');

Yii::app()->clientScript->registerCss('gridReportCSS',"
#content {
    background: none !important;
    border: none !important;
}
#report-container {
    margin-top: 5px;
}
");

// render grid report settings (start closed if report is being generated, open otherwise)

?>
<div id='content-container-inner'>
<div class='form'>
<?php
$form = $this->beginWidget ('GridReportForm', array (
    'reportContainerId' => 'report-container',
    'formModel' => $formModel,
));
    echo $form->errorSummary ($formModel);
    echo $form->label ($formModel, 'primaryModelType');
    echo $form->primaryModelTypeDropDown ($formModel);
    ?>
    <div class='bs-row'>
        <span class='left'>
        <?php
        echo $form->label ($formModel, 'rowField');
        echo $form->fieldDropdown ($formModel, 'rowField', $fieldOptions);
        ?>
        </span>
    </div>
    <div class='bs-row'>
        <span class='left'>
        <?php
        echo $form->label ($formModel, 'columnField');
        echo $form->fieldDropdown ($formModel, 'columnField', $fieldOptions);
        ?>
        </span>
    </div>
    <?php
    echo $form->label ($formModel, 'cellDataType');
    echo $form->dropDownList(
        $formModel, 'cellDataType', 
        array(
            'count' => Yii::t('reports', 'Count'),
            'sum' => Yii::t('reports', 'Sum'),
            'avg' => Yii::t('reports','Average')
        ), array (
            'id' => 'cell-data-type',
        ));
    ?>
    <div id='cell-data-field-container' <?php 
     echo (empty ($formModel->cellDataType) || $formModel->cellDataType === 'count' ? 
        'style="display: none"' : ''); ?>>
        <label><?php echo Yii::t('reports','Cell Data Field');?></label>
        <?php
        echo $form->dropDownList(
            $formModel, 'cellDataField', $cellDataFieldOptions);
        ?>
    </div>
    <br/>
    <br/>
    <?php
    echo $form->label ($formModel, 'allFilters');
    echo $form->filterConditionList ($formModel, 'allFilters');
    ?> 
    <br/>
    <?php
    echo $form->label ($formModel, 'anyFilters');
    echo $form->filterConditionList ($formModel, 'anyFilters');
    ?>
    <br/>
    <?php
    echo $form->generateReportButton ();
$this->endWidget ();
?>
</div>
</div>
<!-- <div id='report-container' class='x2-layout-island' style='display: none;'> -->
<!-- </div> -->
