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

$profile = Yii::app()->params->profile;
$miscLayoutSettings = $profile->miscLayoutSettings;

$showLabels;
if (isset ($miscLayoutSettings['x2flowShowLabels'])) { 
    $showLabels = $miscLayoutSettings['x2flowShowLabels'];
} else {
    $showLabels = true;
}

$newRecord = $model->isNewRecord;

$modelsWithInsertableAttrs = array (
    'Accounts', 'Actions', 'Contacts', 'Docs', 'Groups', 'Campaign', 'Media', 'Opportunity',
    'Product', 'Quote', 'Services', 'BugReports');


$insertableAttributes = array();
foreach ($modelsWithInsertableAttrs as $modelName) {
    $insertableAttributes[$modelName] = array ();
    foreach(X2Model::model($modelName)->attributeLabels() as $fieldName => $label) {
        $insertableAttributes[$modelName][$label] = '{'.$fieldName.'}';
    }
}

$translations = array (
    'templateChangeConfirm' =>
        Yii::t('app', 'Note: you have entered text into the email that will be lost.'.
            ' Are you sure you want to continue?'),
    'targetedContentTriggerChange' =>
        Yii::t('app', 'Note: you have entered text into the default content editor that will '.
            'be lost. Are you sure you want to continue?'),
    'targetedPageTriggerChange' =>
        Yii::t('app', 'Note: you have entered text into the default content editor that will '.
            'be lost. Are you sure you want to continue?'),
);

Yii::app()->clientScript->registerPackages (array (
    'X2Fields' => array (
        'baseUrl' => Yii::app()->request->baseUrl,                       
        'js' => array (
            'js/X2Fields.js', 
        ),
        'depends' => array ('auxlib'),
    ),
    'X2FlowFields' => array (
        'baseUrl' => Yii::app()->request->baseUrl,                       
        'js' => array (
            'js/X2FlowFields.js', 
        ),
        'depends' => array ('X2Fields', 'auxlib'),
    ),
), true);


$passVarsToClientScript = '
    x2.flow = {};
    x2.flow.translations = {};
    x2.flow.requiresCron = '.CJSON::encode ($requiresCron).';
    x2.flow.showLabels = '.($showLabels ? 'true' : 'false').';
    x2.flow.insertableAttributes = '.
        CJSON::encode ($insertableAttributes).';
    x2.flowData = '.CJSON::encode($model->flow).';
    x2.fieldUtils = new x2.FlowFields ({
        operatorList: '.CJSON::encode(X2FlowTrigger::getFieldComparisonOptions()).',
        visibilityOptions: '.CJSON::encode(array(
            array(1, Yii::t('app', 'Public')),
            array(0, Yii::t('app', 'Private')),
            array(2, Yii::t('app', 'User\'s Groups'))
        )).',
        allTags: '.CJSON::encode(Tags::getAllTags()).',
        templateSelector: "#condition-templates"
    });
';

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key => $val) {
    $passVarsToClientScript .= "x2.flow.translations['".
        $key. "'] = '" . addslashes ($val) . "';\n";
}

Yii::app()->clientScript->registerScript(
    'passVarsToX2FlowScript', $passVarsToClientScript,
    CClientScript::POS_END);

$assets = Yii::app()->getAssetManager()->publish(
    Yii::getPathOfAlias('application.extensions.CJuiDateTimePicker').DIRECTORY_SEPARATOR.'assets'
);

$cs = Yii::app()->getClientScript();
$cs->registerCssFile($assets.'/jquery-ui-timepicker-addon.css');
$cs->registerScriptFile($assets.'/jquery-ui-timepicker-addon.js', CClientScript::POS_END);
$cs->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2flowEditor.js', CClientScript::POS_END);
$cs->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/x2flow.css');

// used for rich editing in item config forms
$cs->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/ckeditor.js');
$cs->registerScriptFile(Yii::app()->getBaseUrl().'/js/ckeditor/adapters/jquery.js');
$cs->registerScriptFile(Yii::app()->request->baseUrl . '/js/emailEditor.js');

$cs->registerScriptFile(Yii::app()->theme->getBaseUrl().'/css/listview/jquery.yiigridview.js');


Yii::app()->clientScript->registerScript('flowEditorScript', '
$(function flowEditorMain () {

    $("#show-trace-button").on ("click", function () {
        if ($("#x2flow-trace-box").is (":visible")) {
            $("#x2flow-trace-box").slideUp ();
        } else {
            $("#x2flow-trace-box").slideDown ();
            $("html,body").animate({
                scrollTop: ($("#x2flow-trace-box").offset().top - 100)
            }, 300);
        }
    });

    // shows/hides flow item labels
    $("#x2flow-show-labels-checkbox").on ("change", function () {
        if (this.checked) { // show labels
            $(".x2flow-main").find (".x2flow-icon-label").show ();
            $(".x2flow-node.x2flow-action").each (function () {
                if ($(this).children ().first ().hasClass ("x2flow-icon-label")) {
                    $(this).children ().first ().attr ("style", "");
                }
            });
            $(".x2flow-node").each (function () {
                if ($(this).children ().first ().hasClass ("x2flow-icon-label")) {
                    $(this).removeClass ("no-label"); // used to position arrows
                }
            });
            $("#trigger").find (".x2flow-icon-label").attr ("style", "");
            $("#trigger").removeClass ("no-label");
            auxlib.saveMiscLayoutSetting ("x2flowShowLabels", 1);
            x2.flow.showLabels = true;
        } else { // hide labels
            $(".x2flow-main").find (".x2flow-icon-label").hide ();
            $(".x2flow-node.x2flow-action").each (function () {
                if ($(this).children ().first ().hasClass ("x2flow-icon-label")) {
                    $(this).children ().first ().attr ("style", "display: none;");
                }
            });
            $(".x2flow-node").each (function () {
                if ($(this).children ().first ().hasClass ("x2flow-icon-label")) {
                    $(this).addClass ("no-label"); // used to position arrows
                }
            });
            $("#trigger").find (".x2flow-icon-label").attr ("style", "display: none;");
            $("#trigger").addClass ("no-label");
            auxlib.saveMiscLayoutSetting ("x2flowShowLabels", 0);
            x2.flow.showLabels = false;
        }
    });

})

', CClientScript::POS_END);


$this->actionMenu = array(
    array('label' => Yii::t('studio', 'Manage Flows'), 'url' => array('flowIndex')),
);
if($model->isNewRecord) {
    $this->actionMenu[] = array('label' => Yii::t('studio', 'Create Flow'));
} else {
    $this->actionMenu[] = array('label' => Yii::t('studio', 'Create Flow'), 'url' => array('flowDesigner'));
    // $this->actionMenu[] = array('label' => Yii::t('module', 'Edit'), 'url' => array('flowDesigner', 'id' => $model->id));
    $this->actionMenu[] = array('label' => Yii::t('module', 'Update'));
    $this->actionMenu[] = array('label' => Yii::t('module', 'Delete'), 'url' => '#', 'linkOptions' => array('submit' => array('deleteFlow', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')));
}

$this->actionMenu[] = array (
    'label' => Yii::t('studio', 'All Trigger Logs'),
    'url' => array ('triggerLogs')
);

/* x2plastart */
if (!$model->isNewRecord) {
    $this->actionMenu[] = array (
        'label' => Yii::t('studio', 'Export Flow'), 
        'url' => array ('exportFlow', 'flowId' => $model->id),
    );
}
$this->actionMenu[] = array (
    'label' => Yii::t('studio', 'Import Flow'), 
    'url' => array ('importFlow'),
);
/* x2plaend */

$actionMenuHtml = '
<div id="item-box">
<div class="x2flow-node X2FlowSwitch" style="">
    <span>'.Yii::t('studio', 'Condition').'</span>
    <div class="icon">
        <div class="x2flow-yes-label">'.Yii::t('app', 'Yes').'</div>
        <div class="x2flow-no-label">'.Yii::t('app', 'No').'</div>
    </div>
    <div class="x2flow-branch-wrapper">
        <div class="x2flow-branch">
            <div class="bracket"></div>
            <div class="x2flow-node x2flow-empty"></div>
        </div>
        <div class="x2flow-branch">
            <div class="bracket"></div>
            <div class="x2flow-node x2flow-empty"></div>
        </div>
    </div>
</div>';

foreach($actionTypes as $type => $title) {
    $actionMenuHtml .=
        '<div class="x2flow-node x2flow-action '.$type.
          ($showLabels ? "" : " no-label").'"
          title="'.addslashes(Yii::t('studio', $title)).'"'.
          ((($type === 'X2FlowPushWebContent' && 
              $model->triggerType !== 'TargetedContentRequestTrigger') ||
            ($type === 'X2FlowPushWebPage' && 
              $model->triggerType !== 'TargetedPageRequestTrigger')) ? 
              'style="display: none;"' : '').'>
            <div class="x2flow-icon-label" '.($showLabels ? "": "style='display: none;'").'>'.
                Yii::t('studio', $title).
            '</div>
            <span>'.Yii::t('studio', $title).'</span>
         </div>';
}
$actionMenuHtml .= '</div>';

$this->leftPortlets[] = array(
    'options' => array('title' => Yii::t('studio', 'Flow Actions'), 'id' => 'flow-actions'),
    'content' => $actionMenuHtml
);

// if(!empty($flowData))
    // Yii::app()->clientScript->registerScript('vcrListCookie', 'var flowData = '.CJSON::encode($flowData), CClientScript::POS_READY);





?>
<!--
<div class="page-title"></div><span class="no-bold"></span>


        'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
-->
<div class="page-title icon x2flow">
    <h2><?php echo $model->isNewRecord? Yii::t('studio', 'Create Flow') : Yii::t('admin', 'Update Flow'); ?></h2>
    <a class="x2-button highlight right" id="save-button" href="javascript:void(0);"><?php echo Yii::t('app', 'Save'); ?></a>
    <?php
    if (isset ($triggerLogsDataProvider)) {
    ?>
    <a class="x2-button right" id="show-trace-button" href="javascript:void(0);">
        <?php echo Yii::t('studio', 'Show Trigger Logs'); ?>
    </a>
    <?php
    }
    ?>
</div>
<div class="form x2flow-start">
    <?php 
    $form = $this->beginWidget(
        'CActiveForm', array('id' => 'submitForm', 'enableAjaxValidation' => false)); 
    echo $form->errorSummary($model); 
    echo X2Html::getFlashes (); 
    ?>
    <div class="row">
        <div class="form x2flow-trash"></div>
        <!--<div class="cell">
            <div class="x2flow-node x2flow-trigger x2flow-empty" id="trigger" title="<?php echo addslashes(Yii::t('studio', 'Select a trigger')); ?>"></div>
        </div>-->
        <div class="cell">
            <?php 
                asort ($triggerTypes);
                $allTriggers = array_merge(
                    array('x2flow-empty' => Yii::t('studio', 'Select a trigger')), 
                    $triggerTypes);
                echo $form->label($model, 'triggerType'); 
                echo CHtml::dropdownList('trigger-selector', '', $allTriggers,
                    array('id' => 'trigger-selector'
                )); 
            ?>
        </div>
        <div class="cell">
            <?php echo $form->label($model, 'name'); ?>
            <?php echo $form->textField($model, 'name'); ?>
            <?php echo $form->hiddenField($model, 'flow', array('id' => 'flowDataField')); ?>
        </div>
        <div class="cell">
            <?php echo $form->label($model, 'active'); ?>
            <?php echo $form->dropdownList($model, 'active', array(1 => Yii::t('app', 'Yes'), 0 => Yii::t('app', 'No'))); ?>
        </div>
        <div class="cell right" id='x2flow-show-labels-checkbox-container'>
            <input id='x2flow-show-labels-checkbox' type='checkbox' class='right'
             <?php echo ($showLabels ? "checked='checked'" : ''); ?>>
            <label for='x2flow-show-labels-checkbox' class='right'>
                <?php echo CHtml::encode (Yii::t('studio', 'Toggle Node Labels')); ?>
            </label>
        </div>
    </div>
    <div id='targeted-content-embed-code-container'
     <?php echo ($model->triggerType !== 'TargetedContentRequestTrigger') ? 
        'style="display: none;"' : ''; ?> class='row'>
        
        <h4><?php echo Yii::t('app', 'Embed Code:'); ?></h4>
        <span class='x2-hint'
            title='<?php echo Yii::t('app', 
                'The web content returned by '.
                'this flow will replace the embed code when a visitor comes to your page. Use '.
                'the Push Web Content flow action to create targeted web content.');  
            ?>'>&nbsp;[?]</span>
        <p><?php 
            echo Yii::t('app', 'Copy and paste this code into your website.'); 
        ?></p>
        <textarea <?php 
         echo (!isset ($model->id) ? // flow not yet saved
            'disabled="true" placeholder="'.
                Yii::t('app', 'Saving the flow will generate an embed code').'"' : ''); ?>><?php 
            if (isset ($model->id)) { // flow has been saved
                echo "<script type='text/javascript' src='".
		            Yii::app()->createExternalUrl('/api/targetedContent').'?flowId='.$model->id.
                    "'></script>";
            } 
        ?></textarea>
    </div>
    <?php $this->endWidget(); ?>
</div>
<div class="form x2flow-main" id="x2flow-main">
    <div class="x2flow-node x2flow-trigger x2flow-empty
     <?php echo ($showLabels ? "" : " no-label"); ?>"
     id="trigger" title="<?php echo addslashes(Yii::t('studio', 'Select a trigger')); ?>">
        <div class="x2flow-icon-label" <?php echo ($showLabels ? "": "style='display: none;'") ?>></div>
    </div>
    <div class="x2flow-branch">
        <div class="bracket hidden"></div>
        <div class="x2flow-node x2flow-empty"></div>
    </div>
</div>
<?php
/*
<b>Free For All</b><br>
Assigns all web leads to "Anyone" and users can re-assign to themselves.<br><br>
<b>Even Distribution</b><br>
Assigns web leads to whomever has the lowest number of uncompleted actions, evening out the number of uncompleted actions between users.<br><br>
<b>Round Robin</b><br>
Assigns leads to each user going through the list one by one. <br><br>
<b>Custom Round Robin</b><br>
Same as above but allows you to set custom rules.  i.e. if a contact comes in with a specific value, it will be distributed to a group of users you specify.
This option will not work unless you create custom rules.<br><br>
<b>Single User</b><br>
The Single User option will assign all leads to the specified user.
<br><br><br>
<b>Online Only</b><br>
This option will filter your routing rule so that leads only go to a subset of the users who are logged in.
i.e. if you set custom rules to go to 4 different users, but 2 are logged in, only those 2 will get the leads
 */
?>
<div class="form" id="x2flow-config-box">
    <div id="x2flow-main-config"></div><hr>
    <div id="x2flow-conditions" class="x2-sortlist"><ol></ol></div>
    <div id="x2flow-attributes" class="x2-sortlist">
        <label class='x2flow-api-attributes-section-header' style='display: none;'><?php 
            echo Yii::t('studio', 'Attributes:'); ?></label>
        <ol></ol></div>
    <div id="x2flow-headers" class="x2-sortlist">
        <label class='x2flow-api-attributes-section-header' style='display: none;'><?php 
            echo Yii::t('studio', 'Headers:'); ?></label>
        <ol></ol></div>
    <div>
        <?php 
        echo CHtml::dropdownList(
            'type', '', X2FlowTrigger::getGenericConditions(),
            array(
                'id' => 'x2flow-condition-type',
                'style' => 'display:none;'
            )); 
        echo CHtml::button(
            Yii::t('studio', 'Add Condition'),
            array(
                'id' => 'x2flow-add-condition',
                'class' => 'x2-button',
                'style' => 'display:none;'
            )); 
        ?>
    </div>
    <?php 
    echo CHtml::button(
        Yii::t('studio', 'Add Attribute'),
        array(
            'id' => 'x2flow-add-attribute',
            'class' => 'x2-button',
            'style' => 'display:none;'
        )); 
    echo CHtml::button(
        Yii::t('studio', 'Add Header'),
        array(
            'id' => 'x2flow-add-header',
            'class' => 'x2-button',
            'style' => 'display:none;'
        )); 
    ?>
</div>

<?php

if (isset ($triggerLogsDataProvider) && isset ($model->id)) {
    $this->renderPartial (
        '_triggerLogsGridView', array (
            'triggerLogsDataProvider' => $triggerLogsDataProvider,
            'flowId' => $model->id,
            'parentView' => 'flowEditor'
        )
    );
}
?>

<!-- HTML templates -->
<div id="item-delete"></div>
<div id="condition-templates" style="display:none;">
    <ol>
        <li>
            <div class="handle"></div>
            <fieldset></fieldset>
            <a href="javascript:void(0)" class="del"></a>
        </li>
    </ol>
    <div class="cell x2fields-attribute">
        <!--<label><?php echo Yii::t('studio', 'Attribute'); ?></label>-->
        <select name="attribute"></select>
    </div>
    <div class="cell x2fields-operator">
        <!--<label><?php echo Yii::t('studio', 'Comparison'); ?></label>-->
        <select name="operator"></select>
    </div>
    <div class="cell x2fields-value">
        <!--<label><?php echo Yii::t('studio', 'Value'); ?></label>-->
        <input type="text" />
    </div>
    <fieldset class="API_params">
        <div class="cell x2fields-attribute">
            <label><?php echo Yii::t('studio', 'Name'); ?></label>
            <input type="text" name="attribute" />
        </div>
        <div class="cell x2fields-value">
            <label><?php echo Yii::t('studio', 'Value'); ?></label>
            <input type="text" name="value" />
        </div>
    </fieldset>
    <fieldset class="APIHeaders">
        <div class="cell x2fields-attribute">
            <label><?php echo Yii::t('studio', 'Name'); ?></label>
            <input type="text" name="attribute" />
        </div>
        <div class="cell x2fields-value">
            <label><?php echo Yii::t('studio', 'Value'); ?></label>
            <input type="text" name="value" />
        </div>
    </fieldset>
</div>
<?php  
// workflow status condition is handled as a special case

$workflows = Workflow::getList(false);	// no "none" options
$workflowIds = array_keys($workflows);
$stages = count($workflowIds) ? 
    Workflow::getStagesByNumber($workflowIds[0]) : array('---');
?>
<div id="workflow-condition-template" style="display:none;">
    <ol>
        <li>
            <div class="handle"></div>
            <fieldset>
                <div class="cell x2fields-workflow-id">
                    <div class="cell inline-label"><?php 
                        echo Yii::t('studio', 'Process: '); ?></div>
                    <?php  
                    echo CHtml::dropDownList ('workflowId', '', $workflows, array ('id' => false));
                    ?> 
                </div>
                <div class="cell x2fields-workflow-stage-number">
                    <div class="cell inline-label"><?php echo Yii::t('studio', 'Stage: '); ?></div>
                    <?php  
                    echo CHtml::dropDownList ('stageNumber', '', $stages, array ('id' => false));
                    ?> 
                </div>
                <div class="cell x2fields-workflow-stage-state">
                    <div class="cell inline-label"><?php echo Yii::t('studio', 'State: '); ?></div>
                    <?php  
                    echo CHtml::dropDownList ('stageState', '', array (
                        'completed' => Yii::t('x2flow', 'Completed'),
                        'started' => Yii::t('x2flow', 'Started'),
                        'notStarted' => Yii::t('x2flow', 'Not Started'),
                        'notCompleted' => Yii::t('x2flow', 'Not Completed'),
                    ), array ('id' => false));
                    ?> 
                </div>
            </fieldset>
            <a href="javascript:void(0)" class="del"></a>
        </li>
    </ol>
</div>
<!-- end templates -->
