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



Yii::app()->clientScript->registerCssFiles ('SummationReportCss', array (
    Yii::app()->controller->module->getAssetsUrl ().'/css/gridReportsGridView.css',
    Yii::app()->controller->module->getAssetsUrl ().'/css/summationReport.css',
    Yii::app()->theme->baseUrl.'/css/gridview/styles.css'), false);

?>
<div id='content-container-inner'>
<div class='form form2'>
<?php
$attributeOptions = X2Model::model ($formModel->primaryModelType)
    ->getFieldsForDropdown (true, false);
$aggregateFieldOptions = $formModel->getAggregateFieldOptions ();
$groupsOrderFieldOptions = $formModel->addAggregatesToFieldOptions (
    $aggregateFieldOptions, false, true);
$groupsFilterFieldOptions = $formModel->addAggregatesToFieldOptions (
    $formModel->getAggregateFieldOptions (true), true);
$form = $this->beginWidget ('SummationReportForm', array (
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
    echo $form->label ($formModel, 'groupsAllFilters');
    echo $form->filterConditionList (
        $formModel, 'groupsAllFilters', array (), $groupsFilterFieldOptions);
    ?> 
    <br/>
    <?php
    echo $form->label ($formModel, 'groupsAnyFilters');
    echo $form->filterConditionList (
        $formModel, 'groupsAnyFilters', array (), $groupsFilterFieldOptions);
    ?>
    <br/>
    <?php
    echo $form->label ($formModel, 'groups');
    echo $form->sortByAttrPillBox ($formModel, 'groups', $attributeOptions, array (
        'id' => 'group-by-pill-box',
    ));
    ?>
    <br/>
    <?php
    echo $form->label ($formModel, 'aggregates');
    echo $form->aggregatesPillBox (
        $formModel, 'aggregates', $aggregateFieldOptions,
        array (
            'id' => 'aggregates-pill-box',
        ));
    ?>
    <br/>
    <?php
    echo $form->label ($formModel, 'groupsOrderBy');
    echo $form->sortByAttrPillBox (
        $formModel, 'groupsOrderBy', $groupsOrderFieldOptions,
        array (
            'id' => 'group-order-by-pill-box',
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
    <div id='drill-down-section' class='x2-collapsible-outer collapsed'>
        <div class='x2-collapse-handle'>
            <h3><?php echo CHtml::encode (Yii::t('reports', 'Drill Down')); ?></h3>
            <span class='x2-collapse-button fa fa-caret-left right'></span>
            <span class='x2-expand-button fa fa-caret-down right' style='display: none;'></span>
        </div>
        <div class='x2-collapsible' style='display: none;'>
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
            echo $form->label ($formModel, 'drillDownColumns');
            echo $form->attributePillBox ($formModel, 'drillDownColumns', $attributeOptions);
            ?>
            <br/>
            <?php
            echo $form->label ($formModel, 'orderBy');
            echo $form->sortByAttrPillBox ($formModel, 'orderBy', $attributeOptions, array (
                'id' => 'order-by-pill-box',
            ));
            ?>
        </div>
    </div>
    <br/>
    <br/>
    <?php
    echo $form->generateReportButton ();
$this->endWidget ();
?>
</div>
</div>


