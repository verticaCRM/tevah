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


// drag and drop CSS always gets loaded, this prevents layout thrashing when the UI changes 
Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/dragAndDrop.css');

Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/view.css');

Yii::app()->clientScript->registerResponsiveCssFile(
    $this->module->assetsUrl.'/css/responsiveDetailView.css');

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/workflowFunnel.css');


Yii::app()->clientScript->registerPackages (array (
    'X2History' => array (
        'baseUrl' => Yii::app()->request->baseUrl,                       
        'js' => array (
            'js/X2History.js', 
        ),
        'depends' => array ('history', 'auxlib'),
    ),
), true);

Yii::app()->clientScript->registerScript('getWorkflowStage',"

x2.WorkflowViewManager = (function () {

function WorkflowViewManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        perStageWorkflowView: true,
        workflowId: $model->id
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

/**
 * @param Number modelId (optional)
 */
WorkflowViewManager._getQueryString = function (modelId, ajax) {
    var ajax = typeof ajax === 'undefined' ? true : ajax; 
    var modelId = typeof modelId === 'undefined' ? x2.workflowViewManager.workflowId : modelId; 

    return (
        (ajax ? 'workflowAjax=true&' : '') + 'id=' + modelId +
        '&start=".Formatter::formatDate($dateRange['start'])."' +
        '&end=".Formatter::formatDate($dateRange['end'])."' +
        '&range=".$dateRange['range']."' +
        '&expectedCloseDateStart=".Formatter::formatDate($expectedCloseDateDateRange['start'])."' +
        '&expectedCloseDateEnd=".Formatter::formatDate($expectedCloseDateDateRange['end'])."' +
        '&expectedCloseDateRange=".$expectedCloseDateDateRange['range']."' +
        '&users=".$users."' +
        '&modelType=".urlencode (CJSON::encode ($modelType))."');
};

/**
 * Requests stage member grids via AJAX 
 */
WorkflowViewManager.getStageMembers = function (stage) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageMembers')) . "',
		type: 'GET',
		data: 
            WorkflowViewManager._getQueryString () + '&stage=' + stage,
		success: function(response) {
			if(response === '') return;
            $('#workflow-gridview').html(response);
            $('#workflow-gridview').removeClass ('x2-layout-island');
            $('#workflow-gridview').removeClass ('x2-layout-island-merge-bottom');
            $('#content .x2-layout-island-merge-top-bottom').
                removeClass ('x2-layout-island-merge-top-bottom').
                addClass ('x2-layout-island-merge-top');
            $.ajax({
                url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageValue')) . "',
                data: 
                    WorkflowViewManager._getQueryString () + '&stage=' + stage,
                success: function(response) {
                    $('#data-summary-box').html(response);
                }
            });
		}
	});
};

/**
 * Depresses button in interface selection button group corresponding to currently selected
 * interface
 * @param bool perStageWorkflowView
 */
WorkflowViewManager.prototype._updateChangeUIButtons = function (perStageWorkflowView) {
    $('#interface-selection').children ().removeClass ('disabled-link');
    if (perStageWorkflowView) {
        $('#per-stage-view-button').addClass ('disabled-link');
        $('#workflow-filters').hide ();
        $('#add-a-deal-button').hide ();
    } else {
        $('#drag-and-drop-view-button').addClass ('disabled-link');
        $('#workflow-filters').show ();
        $('#add-a-deal-button').show ();
    }
};

/**
 * @param bool perStageWorkflowView 
 * @param Number workflowId (optional)
 */
WorkflowViewManager.prototype._changeUI = function (perStageWorkflowView, workflowId) {
    var that = this;
    $.ajax ({
        url: '".CHtml::normalizeUrl (array ('/workflow/workflow/changeUI'))."',                 
		type: 'GET',
		data: 
            WorkflowViewManager._getQueryString (workflowId) + 
            '&perStageWorkflowView=' + perStageWorkflowView,
        success: function (data) {
            if (data !== '') {
                that._pushState (workflowId, perStageWorkflowView);
                $('.page-title').siblings ().remove ();
                $('.page-title').after ($('<div>'));
                $('.page-title').next ().replaceWith (data);
                that._updateChangeUIButtons (perStageWorkflowView);
                that.perStageWorkflowView = perStageWorkflowView;
                x2.forms.initializeMultiselectDropdowns ();
            }
        }
    });
};

/**
 * Push browser state to preserve back button funtionality across ajax loaded pages
 */
WorkflowViewManager.prototype._pushState =  function (workflowId, perStageWorkflowView) {
    var newUrl = window.location.href.replace (/workflow\/\d+/, 'workflow/' + workflowId);
    newUrl = newUrl.replace (/id=\d+/, 'id=' + workflowId);
    perStageWorkflowViewGETParamVal = perStageWorkflowView ? 'true' : 'false';
    newUrl = newUrl.replace (
        /perStageWorkflowView=[^&]+/, 'perStageWorkflowView=' + perStageWorkflowViewGETParamVal);

    x2.history.pushState (
        { workflowId: workflowId, 
          perStageWorkflowView: perStageWorkflowView }, '', newUrl);
};

WorkflowViewManager.prototype._setUpWorkflowSelection = function () {
    var that = this;

    $('#title-bar-workflow-select').unbind ('click._setUpWorkflowSelection')
        .bind ('click._setUpWorkflowSelection', function () {

            var workflowId = $(this).val ();
            if (workflowId !== that.workflowId) {
                that._changeUI (that.perStageWorkflowView, workflowId);
            }
            that.workflowId = workflowId;
        });
        
    x2.history.bind (function () {
        var state = window.History.getState ();

        workflowId = state.data.workflowId;
        perStageWorkflowView = state.data.perStageWorkflowView;

        that._changeUI (perStageWorkflowView, workflowId);
        $('#title-bar-workflow-select').val (workflowId);
        if (typeof workflowId !== 'undefined') {
            that.workflowId = workflowId;
        } 
        return false;
    });

};

/**
 * Unselect pipeline/funnel menu item and selects funnel/pipeline menu item, respectively.
 * @param object menuItem the currently selected menu item <li> element
 * @param bool pipeline
 */
WorkflowViewManager.prototype._swapViewMenuItems = function (menuItem, pipeline) {
    var pipeline = typeof pipeline === 'undefined' ? false : pipeline; 

    $(menuItem).children ().first ().replaceWith ($('<span>', {
        html: $(menuItem).children ().first ().html (),
        id: $(menuItem).children ().first ().attr ('id')
    }));
    if (pipeline) {
        $(menuItem).next ().children ().first ().replaceWith ($('<a>', {
            html: $(menuItem).next ().children ().first ().html (),
            id: $(menuItem).next ().children ().first ().attr ('id'),
            href: '#',
        }));
    } else {
        $(menuItem).prev ().children ().first ().replaceWith ($('<a>', {
            html: $(menuItem).prev ().children ().first ().html (),
            id: $(menuItem).prev ().children ().first ().attr ('id'),
            href: '#'
        }));
    }
};



/**
 * Set up behavior of interface selection buttons 
 */
WorkflowViewManager.prototype._setUpUIChangeBehavior = function () {
    var that = this;
    $('#interface-selection a').click (function (evt) {
        evt.preventDefault ();
        var id = $(this).attr ('id');                                                

        if (id === 'per-stage-view-button') {
            if (!that.perStageWorkflowView) {
                that._swapViewMenuItems ($('#funnel-view-menu-item').closest ('li'), true);
                that._changeUI (true, that.workflowId);
            }
        } else { // id === 'drag-and-drop-view-button
            if (that.perStageWorkflowView) {
                that._swapViewMenuItems ($('#pipeline-view-menu-item').closest ('li'), false);
                that._changeUI (false, that.workflowId);
            }
        }

        return false;
    });
    $('#funnel-view-menu-item').closest ('li').click (function (evt) {
        if (!that.perStageWorkflowView) {
            that._swapViewMenuItems (this, true);
            that._changeUI (true, that.workflowId);
        }
        return false;
    });
    $('#pipeline-view-menu-item').closest ('li').click (function (evt) {
        if (that.perStageWorkflowView) {
            that._swapViewMenuItems (this, false);
            that._changeUI (false, that.workflowId);
        }
        return false;
    });
};

WorkflowViewManager.prototype._init = function () {
    this._setUpUIChangeBehavior ();
    this._setUpWorkflowSelection ();
};

return WorkflowViewManager;

}) ();

$(function () { 
    x2.workflowViewManager = new x2.WorkflowViewManager ({
        perStageWorkflowView: ".($perStageWorkflowView ? 'true' : 'false')."
    }); 
});


",CClientScript::POS_HEAD);

Yii::app()->clientScript->registerX2Flashes();
$this->setPageTitle(Yii::t('workflow', 'View {process}', array(
    '{process}' => Modules::displayName(false),
)));

$menuOptions = array(
    'index', 'create', 'edit', 'funnel', 'pipeline', 'delete',
);
$this->insertMenu($menuOptions, $model);

// Handle disabling links for workflow views
$unsetUrlIndex = ($perStageWorkflowView ? 4 : 3);
unset($this->actionMenu[3]['url'], $this->actionMenu[4]['url']);
$this->actionMenu[$unsetUrlIndex]['url'] = '#';

?>
<div id='content-container-inner'>
<div class="responsive-page-title page-title icon workflow ">
    <h2><span class="no-bold">
        <?php echo Yii::t('workflow','{process}:', array(
            '{process}' => Modules::displayName(false),
        )); ?>
    </span> 
        <?php 
        echo CHtml::dropDownList ('workflows', $model->id, $workflows, array (
            'class' => 'x2-minimal-select x2-select',
            'id' => 'title-bar-workflow-select',
        ));
        ?>
    </h2>
    <?php 
    echo ResponsiveHtml::gripButton ();
    ?>
    <div class='responsive-menu-items'>
    <div id='interface-selection' class='x2-button-group right'>
        <a href='#' id='per-stage-view-button' 
         title='<?php echo Yii::t('workflow', 'Funnel View'); ?>'
         class='x2-button<?php echo ($perStageWorkflowView ? ' disabled-link' : ''); ?>'>
         <div></div>
         <div></div>
         <div></div>
        </a>
        <a href='#' id='drag-and-drop-view-button' 
         title='<?php echo Yii::t('workflow', 'Pipeline View'); ?>'
         class='x2-button<?php echo ($perStageWorkflowView ? '': ' disabled-link'); ?>'>
         <div></div>
         <div></div>
         <div></div>
        </a>
    </div>

    <a href='#' id='workflow-filters' class='filter-button right x2-button'
     title='<?php echo Yii::t('workflow', 'Filters'); ?>'
     <?php echo ($perStageWorkflowView ? 'style="display: none;"' : ''); ?>><span></span>
    </a>
    <a href='#' id='add-a-deal-button' class='right x2-button'
     <?php echo ($perStageWorkflowView ? 'style="display: none;"' : ''); ?>>
     <?php echo Yii::t('app', 'Add a Deal'); ?>   
    </a>

    </div>
</div>
<?php
if ($perStageWorkflowView) {
    $this->renderPartial ('_perStageView',
        array (
            'model'=>$model,
            'modelType'=>$modelType,
            'viewStage'=>$viewStage,
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'users'=>$users,
        )
    );
} else {
    $this->renderPartial ('_dragAndDropView',
        array (
            'model'=>$model,
            'modelType'=>$modelType,
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'colors'=>$colors,
            'memberListContainerSelectors'=>$memberListContainerSelectors,
            'stagePermissions'=>$stagePermissions,
            'stagesWhichRequireComments'=>$stagesWhichRequireComments,
            'stageNames'=>$stageNames,
            'stageValues' => $stageValues,
            'users'=>$users,
            'listItemColors' => $listItemColors,
        )
    );
}
?>
</div>
