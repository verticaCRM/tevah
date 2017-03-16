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

/* @edition:pro */

/*
_triggerLogsGridView partial view
Displays a gridview which contains records from the x2_trigger_logs table.

View Parameters:
triggerLogsDataProvider - A data provider for the trigger log records which will be
    displayed in the gridview.
parentView - the view from which this partial was rendered. Used to set page-specific behavior.
    If this value is set to "flowEditor", the parameter flowId should also be set.
flowId - if set, the delete all logs button will only delete logs that belong to this
    flow, otherwise it will delete logs belonging to all flows

Precondition:
-if parentView is set to "flowEditor", flowId is set
*/

if (YII_DEBUG && $parentView === "flowEditor" && !isset ($flowId)) {
    AuxLib::debugLog ("Error: _triggerLogsGridView: Precondition failed: ".
        "\"if parentView is set to 'flowEditor', flowId is set\"");
}

if (!isset ($triggerLogsDataProvider)) return;


Yii::app()->clientScript->registerScript('triggerLogsGridViewScript','

    function getLogMessage (retStatus, message) {
        if (retStatus) {
            if (message === "") {
                return $("<span>", { // flow action succeeded
                    html: "&nbsp(<b style=\"color: green;\">'.
                        CHtml::encode (Yii::t("app", "Success")).'</b>)"
                });
            } else {
                return $("<span>", {
                    html: "&nbsp(<b style=\"color: green;\">'.
                        CHtml::encode (Yii::t("app", "Success")).
                        '</b>: " + message + ")"
                });
            }
        } else {
            if (message === "") {
                return $("<span>", { // failed with no error message
                    html: "&nbsp(<b style=\"color: red;\">'.
                        CHtml::encode (Yii::t("app", "Failure")).'</b>)"
                });
            } else {
                return $("<span>", {
                    html: "&nbsp(<b style=\"color: red;\">'.
                        CHtml::encode (Yii::t("app", "Failure")).'</b>: " + message + ")"
                });
            }
        }
    }

    // append pretty log output to prettyLog, called recursively for conditional switches
    function traceBranch (prettyLog, triggerLog) {
        x2.DEBUG && console.log ("traceBranch: triggerLog = ");
        x2.DEBUG && console.log (triggerLog);

        var logLine, flowItemStatus, flowAction;
        for (var i = 0; i < triggerLog.length; ++i) {
            logLine = $("<div>");
            x2.DEBUG && console.log (triggerLog[i][0]);
            flowAction = triggerLog[i][0];
            if (flowAction === "X2FlowSwitch") {
                var branch =
                    triggerLog[i][1] ? "'.
                        CHtml::encode (Yii::t("app", "Yes")).'" : "'.
                        CHtml::encode (Yii::t("app", "No")).'";
                $(logLine).append ($("<span>", {
                    text: x2.flow.translations[triggerLog[i][0]] + ": " + branch
                }));
                $(prettyLog).append ($(logLine));
                traceBranch (prettyLog, triggerLog[i][2]);
                break;
            }
            $(logLine).append ($("<span>", {
                text: x2.flow.translations[triggerLog[i][0]]
            }));
            flowItemStatus = triggerLog[i][1];
            $(logLine).append (getLogMessage (flowItemStatus[0], flowItemStatus[1]));
            $(prettyLog).append ($(logLine));
        }

    }

    // show particulal trigger log with pretty formatting
    function openTriggerLogDialog (triggerLog, triggeredAt, flowId) {
        if ($("#trigger-log-dialog").is (":visible")) return;
        $("#trigger-log-dialog").show ();
        x2.DEBUG && console.log ("bye");
        x2.DEBUG && console.log (triggerLog);
        var triggerInfo = triggerLog[0];
        var prettyLog = $("<div>", {
            id: "trigger-log-fmt"
        });
        $(prettyLog).append ($("<span>", {
            text: triggeredAt
        }));

        var triggerRetStatus = triggerLog[1][0];

        $(prettyLog).append (
            $("<div>").append (
                $("<span>", {
                    text: "'.
                        CHtml::encode (Yii::t('studio', 'Trigger: ')).'" + 
                        triggerInfo["triggerName"]
                }),
                getLogMessage (true,
                    (triggerInfo["modelLink"] ? triggerInfo["modelLink"] : ""))
            )
        );

        x2.DEBUG && console.log ("triggerRetStatus = " + typeof triggerRetStatus);

        var branchLogs = triggerLog.slice (1);
        var branchRetStatus;
        for (var i in branchLogs) {
            branchRetStatus = branchLogs[i][0];
            if (branchRetStatus === true) {
                traceBranch (prettyLog, branchLogs[i][1]);
            } else {
                $(prettyLog).append (
                    $("<div>").append (
                        $("<span>", {
                            text: "'.CHtml::encode (Yii::t('studio', 'Execute Branch: ')).'"
                        }),
                        getLogMessage (branchRetStatus, branchLogs[i][1])
                    )
                );
            }
        }

        $("#trigger-log-dialog").append ($(prettyLog));
        $("#trigger-log-dialog").dialog ({
            autoOpen: true,
            title: flowId + " " + "'.addslashes (Yii::t('studio','Trigger Log')).'",
            width: 600,
            height: 500,
            show: {
                effect: "fade",
                delay: 100
            },
            resizable: true,
            open: function () {
            },
            close: function (event, ui) {
                $("#trigger-log-dialog").children ().remove ();
                $("#trigger-log-dialog").hide ();
            }
        });
    }

    function deleteAllLogsForAllFlows () {
        if (!window.confirm (
            x2.flow.translations["Are you sure you want to permanently delete all trigger logs?"]))
            return;
        x2.DEBUG && console.log ("hello");
        $.ajax ({
            url: yii.scriptUrl + "/studio/deleteAllTriggerLogsForAllFlows",
            success: function (data) {
                if (data === "success") {
                    $.fn.yiiGridView.update ("trigger-log-grid");
                }
            }
        });
    }

    function deleteAllLogs (flowId) {
        if (!window.confirm (
            x2.flow.translations["Are you sure you want to delete all trigger logs?"]))
            return;
        x2.DEBUG && console.log ("hello");
        $.ajax ({
            url: yii.scriptUrl + "/studio/deleteAllTriggerLogs",
            data: {
                flowId: flowId
            },
            success: function (data) {
                if (data === "success") {
                    $.fn.yiiGridView.update ("trigger-log-grid");
                }
            }
        });
    }

', CClientScript::POS_HEAD);

$translations = array (
    'Are you sure you want to delete all trigger logs?' => Yii::t('studio',
        'Are you sure you want to delete all trigger logs?'),
    'Are you sure you want to permanently delete all trigger logs?' => Yii::t('studio',
        'Are you sure you want to permanently delete all trigger logs?'),
    'X2FlowSwitch' => Yii::t('studio', 'Conditional Switch')
);

foreach(X2FlowAction::getActionTypes() as $type => $title) {
    $translations[$type] = $title;
}

$passVarsToClientScript = '
    if (!x2.flow) x2.flow = {};
    if (!x2.flow.translations) x2.flow.translations = {};
';

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key=>$val) {
    $passVarsToClientScript .= "x2.flow.translations['".
        $key. "'] = '" . addslashes ($val) . "';\n";
}

Yii::app()->clientScript->registerScript(
    'passVarsToTriggerLogsScript', $passVarsToClientScript,
    CClientScript::POS_END);

$cGridViewParams = array (
    'id' => 'trigger-log-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.
        Yii::app()->theme->name.'/css/gridview',
    'dataProvider' => $triggerLogsDataProvider,
    'buttons' => array ('refresh', 'autoResize'),
    'gvSettingsName' => 'triggerLogsGrid'.$parentView,
    'defaultGvSettings' => array (
        'triggeredAt' => 200,
        'triggerLog' => 200,
        'delete' => 30, 
    ),
    'columns' => array (
        array(
            'name' => 'triggeredAt',
            'header' => Yii::t('studio', 'Triggered At'),
            'value' => 'Formatter::formatLongDateTime ($data["triggeredAt"])'
        ),
        array(
            'name' => 'triggerLog',
            'header' => Yii::t('studio', 'Log Output'),
            'value' =>
                'CHtml::link (Yii::t("studio", "View Log Output"), "javascript:void(0)",
                    array (
                        "onclick" => "
                            openTriggerLogDialog (".$data["triggerLog"].",
                                \'".Formatter::formatLongDateTime ($data["triggeredAt"])."\',
                                \'".(X2Flow::model()->findByPk ($data["flowId"])->name)."\');
                        "
                    )
                )',
            'type' => 'raw'
        ),
        array(
            'class' => 'X2ButtonColumn',
            'template' => '{delete}',
            'name' => 'delete',
            'buttons' => array (
                'delete' => array (
                    'url' => 'Yii::app()->request->scriptUrl .
                        \'/studio/deleteTriggerLog?id=\' . $data["id"]'
                )
            ),
        ),
    ),
    'enablePagination' => true,
    'enableSorting' => false
);

if ($parentView === "flowEditor") {
    $cGridViewParams['template'] = '
        <div class="page-title">
            <h2>'.CHtml::encode (Yii::t('studio', 'Flow Trigger Logs')).'</h2>
            {buttons}{summary}
        </div>{items}{pager}';
    $cGridViewParams['summaryText'] =
         CHtml::button (Yii::t('app', 'Delete All Logs'), array (
            'class' => 'gridview-button',
            'id' => 'delete-all-logs-button',
            'title' => Yii::t('app', 'Delete All Logs'),
            'href' => "javascript:void(0)",
            'onclick' =>
                (isset($flowId) ?
                'deleteAllLogs (\''.$flowId.'\');' :
                'deleteAllLogsForAllFlows ();')
         ))
        .Yii::t('app', 'Displaying {start}-{end} result(s).')
        .'<div class="form no-border" style="margin: 0; padding: 2px 3px;'.
          'display: inline-block; vertical-align: middle; overflow: hidden;"> '
            .CHtml::dropDownList(
                'resultsPerPage', Profile::getResultsPerPage(),
                Profile::getPossibleResultsPerPage(), array(
                    'ajax' => array(
                        'url' => $this->createUrl('/profile/setResultsPerPage'),
                        'complete' => "function(response) {
                            $.fn.yiiGridView.update('trigger-log-grid', {data: {'id_page': 1}})
                        }",
                        'data' => "js: {results: $(this).val()}",
                    ),
                    'style' => 'margin: 0;',
                )
            )
        .' </div>';
} else if ($parentView === "triggerLogs") {
    $cGridViewParams['defaultGvSettings'] = array_merge (
        array ('flowName' => 250), $cGridViewParams['defaultGvSettings']);
    array_splice ($cGridViewParams['columns'], 0, 0, array (
            array (
                'name' => 'flowName',
                'header' => Yii::t('studio', 'Flow Name'),
                'value' => 'CHtml::link (X2Flow::model()->findByPk ($data["flowId"])->name,
                    array ("/studio/flowDesigner", "id" => $data["flowId"]))',
                'type' => 'raw'
            )
        )
    );
    $cGridViewParams['template'] =
        '<div class="page-title icon x2flow"><h2>'.Yii::t('app','Trigger Logs').'</h2>'
            .'{buttons}'
            .'<div class="title-bar right">'
            .CHtml::link(Yii::t('app','Delete All Logs'),'#',array(
                'class'=>'x2-button',
                'onclick' => (!isset($flowId) ? 'deleteAllLogsForAllFlows ();' : '')
            ))
            .'{summary}</div></div>{items}{pager}';
    $cGridViewParams['summaryText'] =
		 Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>')
			. '<div class="form no-border" style="display:inline;"> '
			. CHtml::dropDownList(
                'resultsPerPage', Profile::getResultsPerPage(),
                Profile::getPossibleResultsPerPage(), array(
					'ajax' => array(
						'url' => $this->createUrl('/profile/setResultsPerPage'),

                        'complete' => "function(response) {
                            $.fn.yiiGridView.update('trigger-log-grid', {data: {'id_page': 1}}) }",
						'data' => 'js: {results: $(this).val()}',
					)
				))
			. ' </div>';
} else {
    if (YII_DEBUG) AuxLib::debugLog ("Error: _triggerLogsGridView: invalid parentView " . $parentView);
}

if ($parentView === 'flowEditor') {
?>
<div class="form" id="x2flow-trace-box" style="display: none;">
    <div id="x2flow-trace-menu">
        <?php
            $this->widget('X2GridViewGeneric', $cGridViewParams);
        ?>

    </div>
</div>
<?php
} else if ($parentView === 'triggerLogs') {
?>
<div class="flush-grid-view">
<?php
    $this->widget('X2GridViewGeneric', $cGridViewParams);
?>
</div>
<?php
}
?>

<div id="trigger-log-dialog" style="display: none;"></div>
