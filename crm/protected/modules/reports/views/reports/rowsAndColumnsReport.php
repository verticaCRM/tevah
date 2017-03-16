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



Yii::app()->clientScript->registerCssFiles ('RowsAndColumnsReportCss', array (
    Yii::app()->controller->module->getAssetsUrl ().'/css/gridReportsGridView.css',
    Yii::app()->controller->module->getAssetsUrl ().'/css/rowsAndColumnsReport.css',
    Yii::app()->theme->baseUrl.'/css/gridview/styles.css'), false);

// render grid report settings (start closed if report is being generated, open otherwise)

?>
<div id='content-container-inner'>
<div class='form form2'>
<?php
$attributeOptions = X2Model::model ($formModel->primaryModelType)
    ->getFieldsForDropdown (true, false);
$form = $this->beginWidget ('X2ReportForm', array (
    'reportContainerId' => 'report-container',
    'formModel' => $formModel,
));
    echo $form->errorSummary ($formModel);
    echo $form->label ($formModel, 'primaryModelType');
    echo $form->primaryModelTypeDropDown ($formModel);
    ?>
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
    echo $form->label ($formModel, 'columns');
    echo $form->attributePillBox ($formModel, 'columns', $attributeOptions);
    ?>
    <br/>
    <?php
    echo $form->label ($formModel, 'orderBy');
    echo $form->sortByAttrPillBox ($formModel, 'orderBy', $attributeOptions, array (
        'id' => 'order-by-pill-box',
    ));
    ?>
    <br/>
    <?php
    echo $form->checkBox ($formModel, 'includeTotalsRow');
    echo $form->label ($formModel, 'includeTotalsRow', array (
        'class' => 'right-label',
    ));
    ?>
    <br/>
    <?php
    echo $form->generateReportButton ();
$this->endWidget ();
?>
</div>
</div>

