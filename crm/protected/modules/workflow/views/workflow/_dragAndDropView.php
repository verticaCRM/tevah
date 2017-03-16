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

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/WorkflowDragAndDropSortable.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/WorkflowManagerBase.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    $this->module->assetsUrl.'/js/DragAndDropViewManager.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/QtipManager.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->request->baseUrl.'/js/X2GridView/X2GridViewQtipManager.js', CClientScript::POS_END);

$listItemColors = Workflow::getPipelineListItemColors ($colors, true);
$listItemColorCss = '';
for ($i = 1; $i <= count ($listItemColors); ++$i) {
    $listItemColorCss .= 
    "#workflow-stage-$i .stage-member-container {
        background-color: ".$listItemColors[$i - 1][0].";
    }
    #workflow-stage-$i .stage-member-container:hover {
        background-color: ".$listItemColors[$i - 1][1].";
    }";
}
Yii::app()->clientScript->registerCss('stageMemberColorCss',$listItemColorCss);


$stages = $model->stages;

Yii::app()->clientScript->registerScript('dragAndDropScript',"
x2.dragAndDropViewManager = new x2.DragAndDropViewManager ({
    workflowId: ".$model->id.",
    currency: '".Yii::app()->params->currency."',
    stageCount: ".count ($stages).",
    connectWithClass: '.stage-members-container',
    memberListContainerSelectors: ".CJSON::encode ($memberListContainerSelectors).",
    memberContainerSelector: '.stage-member-container',
    memberContainerSelector: '.stage-member-container',
    moveFromStageAToStageBUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/moveFromStageAToStageB')."',
    completeStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/completeStage')."',
    revertStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/revertStage')."',
    startStageUrl: '".Yii::app(
        )->createUrl('/workflow/workflow/ajaxAddADeal')."',
    ajaxGetModelAutocompleteUrl: '".
        Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete')."',
    stagePermissions: ".CJSON::encode ($stagePermissions).",
    stagesWhichRequireComments: ".CJSON::encode ($stagesWhichRequireComments)." ,
    stageNames: ".CJSON::encode ($stageNames).",
    translations: ".CJSON::encode (array (
        'Stage {n}' => addslashes (Yii::t('workflow', 'Stage {n}')),
        'Save' => addslashes (Yii::t('app', 'Save')),
        'Loading...' => addslashes (Yii::t('app', 'Loading...')),
        'deal' => addslashes (Yii::t('app', 'deal')),
        'deals' => addslashes (Yii::t('app', 'deals')),
        'Submit' => addslashes (Yii::t('app', 'Submit')),
        'Comments Required' => addslashes (Yii::t('app', 'Comments Required')),
        'Add a Deal' => addslashes (Yii::t('app', 'Add a Deal')),
        'Edit' => addslashes (Yii::t('app', 'Edit')),
        'Cancel' => addslashes (Yii::t('app', 'Cancel')),
        'Close' => addslashes (Yii::t('app', 'Close')),
        'No results found.' => addslashes (Yii::t('app', 'No results found.')),
        'addADealError' => addslashes (Yii::t('app', 'Deal could not be added: ')),
        'permissionsError' => addslashes (
                Yii::t('workflow', 'You do not have permission to perform that stage change.'))
    )).",
    getStageDetailsUrl: '".CHtml::normalizeUrl(array('/workflow/workflow/getStageDetails'))."',
    stageListItemColors: ".CJSON::encode (
        array_map (function ($a) { return $a[0]; }, $listItemColors))."
});

", CClientScript::POS_READY);


?>

<!-- dialog to contain Workflow Stage Details-->
<div id="workflowStageDetails"></div>

<!-- used to set up the add a deal form -->
<div id="add-a-deal-form-dialog" style="display: none;" class='form'>
    <form>
    <div class='dialog-description'>
        <?php echo Yii::t(
            'workflow', 'Start the {workflowName} {process} for the following record:', array (
                '{workflowName}' => $model->name,
                '{process}' => Modules::displayName(false),
            )); ?> 
    </div>
    <div id='record-name-container'>
        <?php
        echo CHtml::label(Yii::t('app', 'Record Name'),'recordName'); 
        X2Model::renderModelAutocomplete ('Contacts');
        ?>
        <input type="hidden" id='new-deal-id' name="newDealId">
    </div>
    <?php
    echo CHtml::label(Yii::t('app', 'Record Type'),'modelType');
    echo CHtml::dropDownList('modelType',$modelType,array(
        'Contacts'=>Yii::t('workflow','{contacts}', array(
            '{contacts}'=>Modules::displayName(true, "Contacts")
        )),
        'Opportunity'=>Yii::t('workflow','{opportunities}', array(
            '{opportunities}'=>Modules::displayName(true, "Opportunities")
        )),
        'Accounts'=>Yii::t('workflow','{accounts}', array(
            '{accounts}'=>Modules::displayName(true, "Accounts")
        )),
    ),array(
        'id'=>'new-deal-type'
    ));
    ?>
    </form>
</div>

<div id='workflow-filters-container' style="display: none;" class='pipeline-view'>
<?php
$this->renderPartial ('_processStatus', array (
    'dateRange' => $dateRange,
    'expectedCloseDateDateRange' => $expectedCloseDateDateRange,
    'model' => $model,
    'modelType' => $modelType,
    'users' => $users,
    'parentView' => '_dragAndDropView'
));
?>
</div>


<div id='stage-member-list-container-top-scrollbar-outer'><div id='stage-member-list-container-top-scrollbar'><div></div></div></div>
<div id='stage-member-lists-container' class='x2-layout-island x2-layout-island-merge-top clearfix'>
    <div id='stage-member-lists-container-inner'>
<?php
$modelTypes = array_flip (X2Model::$associationModels);
$recordNames = X2Model::getAllRecordNames ();
?>
<div id='stage-member-prototype' style='display: none;'>
<?php
// render a dummy item view so that it can be cloned on the client
$this->renderpartial ('_dragAndDropItemView', array (
    'data' => array (
        'recordType' => 'contacts',
        'id' => null,
        'name' => null,
    ),
    'recordNames' => $recordNames,
    'dummyPartial' => true,
));
?>
</div>
<?php

$colorGradients = array (); 

for ($i = 0; $i < sizeof ($colors); $i++) {
    list($r,$g,$b) = X2Color::hex2rgb2 ($colors[$i][0]);
    list($r2,$g2,$b2) = X2Color::hex2rgb2 ($colors[$i][1]);
    $colorStr1 = "rgba($r, $g, $b, 0.65)";
    $colorStr2 = "rgba($r2, $g2, $b2, 0.65)";
    $colorGradients[] = 
       'background: '.$colors[$i][0].';
        background: -moz-linear-gradient(top,    '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -webkit-linear-gradient(top,    '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -o-linear-gradient(top,        '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: -ms-linear-gradient(top,        '.$colorStr1.' 0%, '.$colorStr2.' 100%);
        background: linear-gradient(to bottom, '.$colorStr1.' 0%, '.$colorStr2.' 100%);';
}


for ($i = 0; $i < count ($stages); ++$i) {
    $stage = $stages[$i];
    ?>
    <div class='stage-members'>
    <div class='stage-member-staging-area'></div>
    <?php
    $this->widget ('zii.widgets.CListView', array (
        'pager' => array (
            /*'class' => 'CLinkPager',
            'header' => '',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'maxButtonCount' => 3,
            'htmlOptions' => array (
                'class' => 'button-group-pager'
            )*/
            'class' => 'ext.infiniteScroll.IasPager',
            'rowSelector'=>'.stage-member-container',
            'listViewId' => 'workflow-stage-'.($i + 1),
            'header' => '',
            'options'=>array(
                'onRenderComplete'=>'js:function(){
                    x2.dragAndDropViewManager.refresh ();
                }',
                'history' => false
            ),
        ),
        'id' => 'workflow-stage-'.($i + 1),
        'dataProvider' => $this->getStageMemberDataProviderMixed (
            $model->id, $dateRange, $expectedCloseDateDateRange, $i + 1, $users, $modelType),
        'itemView' => '_dragAndDropItemView',
        'viewData' => array (
            'modelTypes' => $modelTypes,
            'recordNames' => $recordNames,
            'dummyPartial' => false,
        ),
        'template' => 
            '<div class="stage-list-title" style="'.$colorGradients[$i].'">'.
                '<h2>'.$stage['name'].'</h2>
                <div class="stage-title-row">
                <div class="total-projected-stage-value">'.
                Formatter::formatCurrency ($stageValues[$i][1]).
                '</div>
                <div class="total-stage-deals">
                    <span class="stage-deals-num">'.$stageValues[$i][3].'</span>
                    <span>'.
                        ($stageValues[$i][3] === 1 ? 
                            Yii::t('workflow', 'deal') :
                            Yii::t('workflow', 'deals')).'</span>
                </div>
                <img class="workflow-stage-arrow" 
                 src="'.Yii::app()->theme->getBaseUrl ()."/images/workflowStageArrow.png".'" />
            </div>
            {pager}</div>{items}',
        'itemsCssClass' => 'items stage-members-container',
        'afterAjaxUpdate' => 'function (id, data) {
            x2.dragAndDropViewManager.refresh ();
        }'
    ));
    ?>
    </div>
    <?php
}

?>
    </div>
</div>
