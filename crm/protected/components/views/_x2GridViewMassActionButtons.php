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

/*
Parameters:
    massActions - array of strings - list of available mass actions to select from
    gridId - the id property of the X2GridView instance
    modelName - the modelName property of the X2GridView instance
    selectedAction - string - if set, used to select option from mass actions dropdown
    gridObj - object - the x2gridview instance
*/

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2GridView/X2GridViewMassActionsManager.js', CClientScript::POS_END);

/* x2prostart */

if (in_array ('updateField', $massActions)) {
    // script needed by Yii timepicker widget
    Yii::app()->clientScript->registerScriptFile (
        Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js');

    // needed by c star widget
    Yii::app()->clientScript->registerCoreScript('rating');

}
/* x2proend */

$massActionLabels = array (
    'completeAction' => Yii::t ('app', 'Complete selected {actions}', array(
        '{actions}' => strtolower(Modules::displayName(true, 'Actions'))
    )),
    'uncompleteAction' => Yii::t ('app', 'Uncomplete selected {actions}', array(
        '{actions}' => strtolower(Modules::displayName(true, 'Actions'))
    )),
    'newList' => Yii::t ('app', 'New list from selection'),
    'addToList' => Yii::t ('app', 'Add selected to list'),
    'removeFromList' => Yii::t ('app', 'Remove selected from list'),
    /* x2prostart */
    'tag' => Yii::t ('app', 'Tag selected'),
    'updateField' => Yii::t ('app', 'Update fields of selected'),
    'delete' => Yii::t ('app', 'Delete selected'),
    'mergeRecords'=>Yii::t('app','Merge records'),
    /* x2proend */
);

AuxLib::registerTranslationsScript ('massActions', array (
    'deleteprogressBarDialogTitle' => 'Mass Deletion in Progress',
    'updateFieldprogressBarDialogTitle' => 'Mass Update in Progress',
    'progressBarDialogTitle' => 'Mass Action in Progress',
    'deleted' => 'deleted',
    'tagged' => 'tagged',
    'added' => 'added',
    'updated' => 'updated',
    'removed' => 'removed',
    'doubleConfirmDialogTitle' => 'Confirm Deletion',
    'addedItems' => 'Added items to list',
    'addToList' => 'Add selected to list',
    'removeFromList' => 'Remove selected from list',
    'newList' => 'Create new list from selected',
    'add' => 'Add to list',
    'remove' => 'Remove from list',
    'noticeFlashList' => 'Mass action exectuted with',
    'errorFlashList' => 'Mass action exectuted with',
    'noticeItemName' => 'warnings',
    'errorItemName' => 'errors',
    'successItemName' => 'Close',
    'blankListNameError' => 'Cannot be left blank',
    'passwordError' => 'Password cannot be left blank',
    'close' => 'Close',
    'cancel' => 'Cancel',
    'create' => 'Create',
    'pause' => 'Pause',
    'stop' => 'Stop',
    'resume' => 'Resume',
    'complete' => 'Complete',
    'tag' => 'Tag',
    'update' => 'Update',
    'tagSelected' => 'Tag selected',
    'deleteSelected' => 'Delete selected',
    'delete' => 'Delete',
    'updateField' => 'Update fields of selected',
    'emptyTagError' => 'At least one tag must be included',
));

Yii::app()->clientScript->registerCss ('massActionsCss', "

.x2-gridview-mass-action-outer {
    position: relative;
}

@media (max-width: 820px) and (min-width: 658px) {
    .grid-view.fullscreen .x2-gridview-top-pager {
        display: none;
    }
}


/*
Check all records in data provider feature
*/
.grid-view .select-all-records-on-all-pages-strip-container {
    margin-right: -1px;
}
.grid-view .x2-gridview-fixed-top-bar-outer .select-all-records-on-all-pages-strip-container {
    margin-right: 6px;
    margin-left: 3px;
}

.grid-view .select-all-records-on-all-pages-strip-container {
    text-align: center;
    border-right: 1px solid rgb(207, 207, 207);
    border-bottom: 1px solid rgb(199, 199, 199);
    position: relative;
    z-index: 1;
}

.grid-view .select-all-records-on-all-pages-strip-container .select-all-notice,
.grid-view .select-all-records-on-all-pages-strip-container .all-selected-notice {
    padding: 4px;
}

.grid-view .select-all-records-on-all-pages-strip-container .select-all-notice {
    background: rgb(255, 255, 185);
}

.grid-view .select-all-records-on-all-pages-strip-container .all-selected-notice {
    background: rgb(203, 255, 201);
}

body.no-widgets .grid-view .x2-gridview-fixed-top-bar-outer .select-all-records-on-all-pages-strip-container {
    margin-right: 0;
}

.x2-mobile-layout .select-all-records-on-all-pages-strip-container {
    margin-left: 0;
    margin-right: -1px;
}

.grid-view .container-clone {
    visibility: hidden;
}

.x2-mobile-layout .x2grid-body-container .container-clone,
.x2grid-body-container.x2-gridview-body-without-fixed-header .container-clone {
    display: none !important;
}

/*
Flashes container
*/

.super-mass-action-feedback-box {
    margin: 5px 0;
    border: 1px solid rgb(176, 176, 176);
    background: rgb(250, 250, 250);
    box-shadow: inset 1px 1px rgb(219, 219, 219);
    padding: 4px;
    height: 76px;
    overflow-y: scroll;
}

.super-mass-action-feedback-box .success-flash {
    color: green;
}
.super-mass-action-feedback-box .error-flash {
    color: red;
}



#x2-gridview-flashes-container.fixed-flashes-container {
    position: fixed;
    opacity: 0.9;
    bottom: 5px;
}

#x2-gridview-flashes-container {
    margin-top: 5px;
    margin-right: 5px;
}

#x2-gridview-flashes-container > div {
    margin-top: 5px;
    margin-left: 4px;
}

#x2-gridview-flashes-container .flash-list-header {
    margin-bottom: 4px;
}

#x2-gridview-flashes-container .x2-gridview-flashes-list {
    clear: both;
    margin-bottom: 5px;
}

#x2-gridview-flashes-container .flash-list-left-arrow,
#x2-gridview-flashes-container .flash-list-down-arrow {
    margin-left: 6px;
    margin-top: 3px;
}

/* x2prostart */
/*
update fields dialog
*/

.x2-gridview-update-field-dialog select {
    margin-right: 4px;
    margin-top: 2px;
    margin-bottom: 2px;
}

.update-fields-inputs-container {
    margin-top: 4px;
}

.update-fields-inputs-container .update-fields-field-input-container {
    vertical-align: middle;
    margin-top: 0px;
}

.update-fields-inputs-container .updating-field-input-anim {
    margin-left: 40px;
}
/* x2proend */

/*
buttons 
*/

.mass-action-more-button-container .x2-down-arrow {
    margin-left: 30px;
    margin-top: 11px;
}

.mass-action-more-button-container .more-button-arrow {
    height: 5px;
}

.mass-action-more-button-container .more-button-label {
    display: inline !important;
    float: left;
    margin-right:5px;
}

.mass-action-more-button-container {
    margin: 0 5px 0 0;
    display: inline-block;
}

.mass-action-more-button-container button {
    display: inline;
    height: 26px;
}

/* x2prostart */
a.mass-action-button {
    height: 24px;
    width: 24px;
    line-height: 24px;
}

a.mass-action-button {
    margin-left: -4px !important;
}

a.mass-action-button:first-child {
    margin-left: 0px !important;
}

.mass-action-button-MassMoveToFolder span {
    background: url('".
        Yii::app()->getTheme()->getBaseUrl().'/images/icons/move.png'.
        "') 3px center no-repeat;
    height: 24px;
    width: 24px;
}
/* x2proend */


/*
more drop down list
*/

.x2-gridview-mass-action-buttons .more-drop-down-list.stuck {
    position: absolute !important;
    /*top: 74px !important;*/
}

.x2-gridview-mass-action-buttons .more-drop-down-list {
    position: absolute;
    top: 67px;
    z-index: 99;
    list-style-type: none;
    background: #fff;
    border: 1px solid #999;
    -moz-box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    -webkit-box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    padding: 5px 0px 5px 0px;
    clip: rect(0px,1000px,1000px,-10px);
}

.x2-gridview-mass-action-buttons .more-drop-down-list li {
    line-height: 17px;
    padding: 0 10px 0 10px;
    cursor: default;
    color: black;
}
.x2-gridview-mass-action-buttons .more-drop-down-list li:hover {
    background: #eee;
}

/*
general mass actions styling
*/

#mass-action-dialog-loading-anim {
    margin-right: 30px;
}

.x2-gridview-mass-action-buttons .dialog-help-text {
    margin-bottom: 5px;
}

.x2-gridview-mass-action-buttons {
    margin: 0 5px 0 0;
    display: inline-block;
}
");

Yii::app()->clientScript->registerResponsiveCss ('massActionsCssResponsive', "

@media (max-width: 657px) {
    .x2-gridview-mass-action-buttons {
        position: absolute;
        width: 137px;
        top: -41px;
        right: -179px;
        margin: 0px;
    }
    .show-top-buttons .x2-gridview-mass-action-buttons {
        right: -183px; 
    }
}

@media (min-width: 658px) {
    .x2-gridview-mass-action-buttons .more-drop-down-list.fixed-header {
        /*position: fixed;*/
    }
    .x2-gridview.fullscreen .x2-gridview-mass-action-buttons .more-drop-down-list.fixed-header {
        position: absolute;
    }
}

");

// destroy mass action dialogs, save checks so that can be preserved through grid update
$beforeUpdateJSString = "
    x2.DEBUG && console.log ('beforeUpdateJSString');
    /* x2prostart */ 
    if ($.inArray ('tag', x2.".$namespacePrefix."MassActionsManager._massActions) !== -1) 
        x2.".$namespacePrefix."MassActionsManager.tagContainer.destructor ();
    /* x2proend */
    
    $('.mass-action-dialog').each (function () {
        //x2.massActions.DEBUG && console.log ('destroying dialog loop');
        if ($(this).closest ('.ui-dialog').length) {
            //x2.massActions.DEBUG && console.log ('destroying dialog');
            $(this).dialog ('destroy');
            $(this).hide ();
        }
    });

    // save to preserve checks
    x2.".$namespacePrefix."MassActionsManager.saveSelectedRecords ();

    // show loading overlay to prevent grid view user interaction
    $('#".$gridId." .x2-gridview-updating-anim').show ();
";

$gridObj->addToBeforeAjaxUpdate ($beforeUpdateJSString);

// reapply event handlers and checks
$afterUpdateJSString = "
    x2.DEBUG && console.log ('afterUpdateJSSTring');
    if (typeof x2.".$namespacePrefix."MassActionsManager !== 'undefined') 
        x2.".$namespacePrefix."MassActionsManager.reinit (); 
    $('#".$gridId." .x2-gridview-updating-anim').hide ();
";

$gridObj->addToAfterAjaxUpdate ($afterUpdateJSString);

foreach ($massActionObjs as $obj) {
    $obj->registerPackages ();
}

Yii::app()->clientScript->registerScript($namespacePrefix.'massActionsInitScript',"
    if (typeof x2.".$namespacePrefix."MassActionsManager === 'undefined') {
        console.log ('new X2GridViewMassActionsManager');
        x2.".$namespacePrefix."MassActionsManager = new x2.GridViewMassActionsManager ({
            massActions: ".CJSON::encode ($massActions).",
            gridId: '".$gridId."',
            namespacePrefix: '".$namespacePrefix."',
            gridSelector: '#".$gridId."',
            fixedHeader: ".($fixedHeader ? 'true' : 'false').",
            massActionUrl: '".Yii::app()->request->getScriptUrl () . '/' . 
                lcfirst ($gridObj->moduleName) .  '/x2GridViewMassAction'."',
            /* x2prostart */ 
            updateFieldInputUrl: '".Yii::app()->request->getScriptUrl () . '/' . 
                lcfirst ($gridObj->moduleName) .  '/getX2ModelInput'."',
            /* x2proend */ 
            modelName: '".$modelName."',
            translations: ".CJSON::encode (array (
                'deleteprogressBarDialogTitle' => Yii::t('app', 'Mass Deletion in Progress'),
                'updateFieldprogressBarDialogTitle' => Yii::t('app', 'Mass Update in Progress'),
                'progressBarDialogTitle' => Yii::t('app', 'Mass Action in Progress'),
                'deleted' => Yii::t('app', 'deleted'),
                'tagged' => Yii::t('app', 'tagged'),
                'added' => Yii::t('app', 'added'),
                'updated' => Yii::t('app', 'updated'),
                'removed' => Yii::t('app', 'removed'),
                'doubleConfirmDialogTitle' => Yii::t('app', 'Confirm Deletion'),
                'addedItems' => Yii::t('app', 'Added items to list'),
                'addToList' => Yii::t('app', 'Add selected to list'),
                'removeFromList' => Yii::t('app', 'Remove selected from list'),
                'newList' => Yii::t('app', 'Create new list from selected'),
                'moveToFolder' => Yii::t('app', 'Move selected messages'),
                'moveOneToFolder' => Yii::t('app', 'Move message'),
                'move' => Yii::t('app', 'Move'),
                'add' => Yii::t('app', 'Add to list'),
                'remove' => Yii::t('app', 'Remove from list'),
                'noticeFlashList' => Yii::t('app', 'Mass action exectuted with'),
                'errorFlashList' => Yii::t('app', 'Mass action exectuted with'),
                'noticeItemName' => Yii::t('app', 'warnings'),
                'errorItemName' => Yii::t('app', 'errors'),
                'successItemName' => Yii::t('app', 'Close'),
                'blankListNameError' => Yii::t('app', 'Cannot be left blank'),
                'passwordError' => Yii::t('app', 'Password cannot be left blank'),
                'close' => Yii::t('app', 'Close'),
                'cancel' => Yii::t('app', 'Cancel'),
                'create' => Yii::t('app', 'Create'),
                'pause' => Yii::t('app', 'Pause'),
                'stop' => Yii::t('app', 'Stop'),
                'resume' => Yii::t('app', 'Resume'),
                'complete' => Yii::t('app', 'Complete'),
                'tag' => Yii::t('app', 'Tag'),
                'update' => Yii::t('app', 'Update'),
                'tagSelected' => Yii::t('app', 'Tag selected'),
                'deleteSelected' => Yii::t('app', 'Delete selected'),
                'delete' => Yii::t('app', 'Delete'),
                'updateField' => Yii::t('app', 'Update fields of selected'),
                'emptyTagError' => Yii::t('app', 'At least one tag must be included'),
            )).",
            expandWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Expand_Widget.png'."',
            collapseWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Collapse_Widget.png'."',
            closeWidgetSrc: '".Yii::app()->getTheme()->getBaseUrl().
                '/images/icons/Close_Widget.png'."',
            progressBarDialogSelector: '#$namespacePrefix-progress-dialog',
            enableSelectAllOnAllPages: ".
                ($gridObj->enableSelectAllOnAllPages ? 'true' : 'false').",
            totalItemCount: {$gridObj->dataProvider->totalItemCount},
            idChecksum: '$idChecksum',
        });
    } else {
        // grid was refreshed, total item count may have changed
        x2.{$namespacePrefix}MassActionsManager.totalItemCount = 
            {$gridObj->dataProvider->totalItemCount};
        x2.{$namespacePrefix}MassActionsManager.idChecksum = 
            '$idChecksum';
    }
", CClientScript::POS_END);

?>

<span class='x2-gridview-mass-action-outer'>

<div id='<?php echo $gridId; ?>-mass-action-buttons' class='x2-gridview-mass-action-buttons'>
    <?php
    // TODO: check if buttons are present before rendering button set
    $massActionButtons = true;
    if ($massActionButtons) {
    ?>
    <div class='mass-action-button-set x2-button-group'>
        <?php
        foreach ($massActionObjs as $obj) {
            $obj->renderButton ();
        }
        ?>
    </div>

    <?php
    }
    if (count ($massActionObjs) > 1) {
    ?>
        <div class='mass-action-more-button-container'>
            <button class='mass-action-more-button x2-button'  
             title='<?php echo Yii::t('app', 'More mass actions'); ?>'>
                <span class='more-button-label'>
                    <?php echo Yii::t('app', 'More'); ?>
                </span>
                <img class='more-button-arrow' 
                 src='<?php echo Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'; ?>' />
            </button>
        </div>
        <ul style='display: none;'
         class="more-drop-down-list<?php echo ($fixedHeader ? ' fixed-header' : ''); ?>"> 
        <?php
        foreach ($massActionObjs as $obj) {
            $obj->renderListItem ();
        }
    }
    ?>
    </ul>
    <?php
    foreach ($massActionObjs as $obj) {
        $obj->renderDialog ($gridId, $modelName);
    }
    ?>
</div>

</span>
<?php
if (isset ($modelName)) {
?>
<div class='mass-action-dialog double-confirmation-dialog' style='display: none;'>
    <span><?php 
        echo Yii::t(
            'app', 'You are about to delete {count} {recordType}.', 
            array (
                '{recordType}' => X2Model::getRecordName ($modelName, true),
                '{count}' => '<b>'.$gridObj->dataProvider->totalItemCount.'</b>',
            ));
        echo Yii::t('app', 'This action cannot be undone.'); 
        ?>
        </br>
        </br>
        <?php
        echo Yii::t('app', 'Please enter your password to confirm that you want to '.
            'delete the selected records.'); 
    ?></span>
    </br>
    </br>
    <?php
    echo CHtml::label (Yii::t('app', 'Password:'), 'LoginForm[password]');
    ?>
    <input name="password" type='password'>
</div>
<?php
}
?>
<div id='<?php echo $namespacePrefix; ?>-progress-dialog' class='progress-dialog mass-action-dialog'
 style='display: none;'>
<?php
    $this->widget ('X2ProgressBar', array (
        'uid' => $gridObj->namespacePrefix,
        'max' => $gridObj->dataProvider->totalItemCount,
        'label' => '',
    ));
?>
<div class='super-mass-action-feedback-box'>
</div>
</div>
