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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/packager.css');

?>

<div class="page-title"><h2><?php 
    echo CHtml::encode (Yii::t('admin', 'X2Packager')); 
?></h2></div>

<div id='packager-form' class="form">
    <?php 
    echo Yii::t('admin', 
       'X2Packager allows you to export or import a complete set '.
       'of customizations to the system, including a custom theme, '.
       'modules, fields and dropdowns, {processes}, document and email '.
       'templates, and optionally {contact} data', array(
           '{contact}' => strtolower(Modules::displayName (false, 'Contacts')),
           '{processes}' => strtolower(Modules::displayName (true, 'Workflow')),
       ));
    echo '<h3>'.Yii::t('admin', 'Applied Packages').'</h3>';
    if (!empty($appliedPackages)) {
        echo '<ul>';
        foreach ($appliedPackages as $package) {
            echo '<li>'. $package['name'];
            echo CHtml::link (Yii::t('admin', 'Revert'),
                array('revertPackage', 'name' => $package['name']),
                array('class' => 'x2-button revert-btn'));
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo CHtml::encode (Yii::t('admin', 'There are currently no packages applied'));
    }


    echo '<h3>'.CHtml::encode (Yii::t('admin', 'Import X2Package')).'</h3>';
    echo CHtml::form('previewPackageImport','post',array('enctype'=>'multipart/form-data','id'=>'file-form'));
    echo CHtml::fileField('data', '', array('id'=>'import-data'));
    echo CHtml::submitButton(Yii::t('admin','Import'), array(
        'class' => 'x2-button',
        'id' => 'import-button'
    ));
    echo CHtml::endForm(); ?>
    <div id="file-form-status" style="color:red"></div>
        <?php echo X2Html::getFlashes(); ?>
    <br /><br />
    <hr>
    <h3><?php echo Yii::t('admin', 'Export X2Package'); ?></h3>
        <?php echo Yii::t('admin', 'Select the components of the system you would like to package'); ?>
    <br /><br />

<?php
    echo '<h4>'.Yii::t('admin', 'Package Name').'</h4>';
    echo '<div class="row">';
    echo CHtml::textField ('packageName', '', array(
        'placeholder' => Yii::t('admin', 'Please enter a package name'),
    ));
    echo '</div>';
    echo '<div class="row">';
    echo CHtml::textArea ('packageDescription', '', array(
        'placeholder' => Yii::t('admin', 'Please enter a description for your package'),
    ));
    echo '</div>';

    echo '<div class="row">';
    $this->renderPackageComponentSelection (Yii::t('admin', 'Modules'), 'module',
        function($elem) { return $elem->title; }, $modules, Yii::t('admin', 'modules'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Custom Fields'), 'field',
        function($elem) { return $elem->modelName.'.'.$elem->fieldName; }, $fields, Yii::t('admin', 'fields'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Form Layouts'), 'formLayout',
        function($elem) { return X2Model::getModelTitle($elem->model).' '.$elem->version; },
        $forms, Yii::t('admin', 'form layout'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Flows'), 'flow',
        function($elem) { return $elem->name; }, $flows, Yii::t('admin', 'flows'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Media'), 'media',
        function($elem) {
            return $elem->fileName . ($elem->name ? ' ('.$elem->name.')' : ''); },
        $media, null, array (
            'class' => 'media-items-box',
        ));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Themes'), 'themes',
        function($elem) { return $elem->fileName; }, $themes);

    $this->renderPackageComponentSelection (Yii::t('admin', '{processes}', array(
        '{processes}' => Modules::displayName(true, 'Workflow')
    )), 'process', function($elem) { return $elem->name; }, $processes, Yii::t('admin', 'processes'));

    $this->renderPackageComponentSelection (Yii::t('admin', 'Templates'), 'template',
        function($elem) { return $elem->name.' ('.ucfirst($elem->type).')'; }, $templates,
        Yii::t('admin', 'templates'));

    echo '<h4>'.CHtml::encode (Yii::t('admin', 'Data')).'</h4>';
    echo '<div class="row">';
    echo '<div class="cell">'.CHtml::checkbox ('includeContacts').'</div>';
    echo '<div class="cell" style="padding-top:4px">'.CHtml::label (Yii::t('admin',
        'Include {contact} data?', array(
            '{contact}' => strtolower(Modules::displayName (false, 'Contacts')),
        )
    ), 'includeContacts').'</div>';
    echo '</div><br />';

    echo '<br /><div id="status-box"></div><br />';
    echo CHtml::button(Yii::t('admin', 'Export'), array(
        'class' => 'x2-button',
        'id' => 'export-button'
    ));
    echo '<div id="export-loading"></div>';
    echo CHtml::button(Yii::t('admin', 'Download'), array(
        'class' => 'x2-button',
        'id' => 'download-link',
    ));

    Yii::app()->clientScript->registerScript ('package-export','
    (function () {
        var collectExportables = function(type) {
            var exportables = [];
            $(".exportable-" + type + ":checked").each(function() {
                var elemId = $(this).attr("id").split("-");
                if (elemId.length > 1)
                    exportables.push(elemId[1]);
            });
            return exportables;
        };

        var getExportComponents = function() {
            var exportComponents = {
                "selectedModules": collectExportables("module"),
                "selectedFields": collectExportables("field"),
                "selectedFormLayout": collectExportables("formLayout"),
                "selectedX2Flow": collectExportables("flow"),
                "selectedWorkflow": collectExportables("process"),
                "selectedDocs": collectExportables("template"),
                "selectedMedia": collectExportables("media"),
                "selectedThemes": collectExportables("themes"),
                "includeContacts": $("#includeContacts").attr("checked")=="checked"?"true":"false",
                "packageName": $("#packageName").val(),
                "packageDescription": $("#packageDescription").val()
            }
            if (exportComponents["selectedModules"].length == 0 &&
                    exportComponents["selectedFields"].length == 0 &&
                    exportComponents["selectedFormLayout"].length == 0 &&
                    exportComponents["selectedX2Flow"].length == 0 &&
                    exportComponents["selectedWorkflow"].length == 0 &&
                    exportComponents["selectedDocs"].length == 0 &&
                    exportComponents["selectedMedia"].length == 0 &&
                    exportComponents["selectedThemes"].length == 0) {
                alert("'.CHtml::encode (Yii::t('admin', 'Nothing selected to package!')).'");
                return false;
            }
            return exportComponents;
        };

        var selectAll = function(type) {
            $(".exportable-" + type).each (function() {
                $(this).attr("checked", "checked");
            });
        };
        var deselectAll = function(type) {
            $(".exportable-" + type).each (function() {
                $(this).removeAttr("checked");
            });
        };

        $("#export-button").click(function() {
            var exportComponents = getExportComponents();
            if (!exportComponents)
                return;

            $("#status-box").html ("'.CHtml::encode (Yii::t('admin', 'Beginning export')).'");
            $("#status-box").css ("color", "green");
            $("#export-button").hide();
            auxlib.containerLoading($("#export-loading"));

            $.ajax({
                url: "'.$this->createUrl ('exportPackage').'",
                type: "post",
                data: exportComponents,
                success: function(data) {
                    data = JSON.parse(data);
                    if (data[0] == "success") {
                        $("#status-box").append ("<br />'.Yii::t('admin', 'Export complete').'");
                        $("#download-link").slideDown();
                    } else {
                        $("#status-box").html ("'.Yii::t('admin', 'Export failed: ').'");
                        $("#status-box").append (data["message"]);
                        $("#status-box").css ("color", "red");
                        $("#export-button").show();
                    }
                    $("#export-loading").children().remove();
                }
            });
        });

        $(".selectall").click(function(e) {
            var namespace = $(this).attr ("id").split("-")[1];
            if ($(this).attr("checked") === "checked")
                selectAll (namespace);
            else
                deselectAll (namespace);
        });
        $("#download-link").click(function(e) {
            e.preventDefault();  //stop the browser from following
            window.location.href = "downloadData?file=X2Package-" +
                $("#packageName").val() + ".zip";
        });

        $("#import-button").click(function(e) {
            e.preventDefault();
            var filename = $("#import-data").val();
            var filenameComponents = filename.split(".");
            if (filename.length === 0 || filenameComponents.length < 2) {
                $("#file-form-status").html ("'. 
                    CHtml::encode (Yii::t('admin', 'No file specified')).'");
            } else if (filenameComponents[1] !== "zip") {
                $("#file-form-status").html ("'. 
                    CHtml::encode (Yii::t('admin', 'Not a zip archive')).'");
            } else {
                $("#file-form-status").html ("");
                $("#file-form").submit();
            }
        });

        $(function() {
            $("#download-link").hide();
        });
    }) ();
    ', CClientScript::POS_END);
?>
</div>
