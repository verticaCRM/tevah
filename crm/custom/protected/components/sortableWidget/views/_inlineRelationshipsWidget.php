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
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineRelationshipsWidget.css'
);

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
    $("#relationships-grid .contact-name").each(function (i) {
        var contactId = $(this).attr("href").match(/\\d+$/);

        if(contactId !== null && contactId.length) {
            $(this).qtip({
                content: {
                    text: "'.addslashes(Yii::t('app','loading...')).'",
                    ajax: {
                        url: yii.baseUrl+"/index.php/contacts/qtip",
                        data: { id: contactId[0] },
                        method: "get"
                    }
                },
                style: {
                }
            });
        }
    });

    if($("#Relationships_Contacts_autocomplete").length == 1 &&
        $("Relationships_Contacts_autocomplete").data ("uiAutocomplete")) {
        $("#Relationships_Contacts_autocomplete").data( "uiAutocomplete" )._renderItem = 
            function( ul, item ) {

            var label = "<a style=\"line-height: 1;\">" + item.label;
            label += "<span style=\"font-size: 0.7em; font-weight: bold;\">";
            if(item.city || item.state || item.country) {
                label += "<br>";

                if(item.city) {
                    label += item.city;
                }

                if(item.state) {
                    if(item.city) {
                        label += ", ";
                    }
                    label += item.state;
                }

                if(item.country) {
                    if(item.city || item.state) {
                        label += ", ";
                    }
                    label += item.country;
                }
            }
            if(item.assignedTo){
                label += "<br>" + item.assignedTo;
            }
            label += "</span>";
            label += "</a>";

            return $( "<li>" )
                .data( "item.autocomplete", item )
                .append( label )
                .appendTo( ul );
        };
    }
}

$(function() {
    refreshQtip();
});
');

$relationshipsDataProvider = $this->getDataProvider ();

?>

<div id="relationships-form" 
<?php /* x2prostart */ ?>
 style="<?php echo ($displayMode === 'grid' ?  '' : 'display: none;'); ?>"
<?php /* x2proend */ ?>
 class="<?php echo ($this->getWidgetProperty ('mode') === 'simple' ? 
    'simple-mode' : 'full-mode'); ?>">

<?php

$columns = array(
    array(
        'name' => 'name',
        'header' => Yii::t("contacts", 'Name'),
        'value' => '$data->renderAttribute ("name")',
        'type' => 'raw',
    ),
    array(
        'name' => 'relatedModelName',
        'header' => Yii::t("contacts", 'Type'),
        'value' => '$data->renderAttribute ("relatedModelName")',
        'filter' => array ('' => CHtml::encode (Yii::t('app', '-Select one-'))) + 
            $linkableModelsOptions, 
        'type' => 'raw',
    ),
    array(
        'name' => 'assignedTo',
        'header' => Yii::t("contacts", 'Assigned To'),
        'value' => '$data->renderAttribute("assignedTo")',
        'type' => 'raw',
    ),
    array(
        'name' => 'description',
        'header' => Yii::t("contacts", 'Description'),
        'value' => '$data->renderAttribute("description")',
        'type' => 'raw',
    ),
    array(
        'name' => 'createDate',
        'header' => Yii::t('contacts', 'Create Date'),
        'value' => '$data->renderAttribute("createDate")',
        'filterType' => 'dateTime',
        'type' => 'raw'
    ),
);

$columns[] = array(
    // trailing dot is a kludge to prevent CDataColumn from rendering filter cell
    'name' => 'deletion.', 
    'header' => Yii::t("contacts", 'Delete'),
    'htmlOptions' => array (
        'class' =>'delete-button-cell'
    ),
    'value' => 
        "CHtml::ajaxLink(
            '<span class=\'fa fa-times x2-delete-icon\'></span>',
            '".Yii::app()->controller->createUrl('/site/deleteRelationship').
                "?firstId='.\$data->relatedModel->id.
                '&firstType='.get_class(\$data->relatedModel).
                '&secondId=".$model->id."&secondType=".get_class($model).
                "&redirect=/".Yii::app()->controller->getId()."/".$model->id."',
            array (
                'success' => 'function () {
                    $.fn.yiiGridView.update(\'relationships-grid\');
                }',
            ),
            array(
                'class'=>'x2-hint',
                'title'=>'Deleting this relationship will not delete the linked record.',
                'confirm'=>'Are you sure you want to delete this relationship?'))",
    'type' => 'raw',
);


$this->widget('X2GridViewGeneric', array(
    'id' => "relationships-grid",
    'enableGridResizing' => false,
    'showHeader' => CPropertyValue::ensureBoolean (
        $this->getWidgetProperty('showHeader')),
    'defaultGvSettings' => array (
        'name' => '22%',
        'relatedModelName' => '18%',
        'assignedTo' => '13%',
        'label' => '13%',
        'createDate' => '10%',
        'description' => '15%',
        'deletion.' => 70,
    ),
    'filter' => $this->getFilterModel (),
    'htmlOptions' => array (
        'class' => 
            ($relationshipsDataProvider->itemCount < $relationshipsDataProvider->totalItemCount ?
            'grid-view has-pager' : 'grid-view'),
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'inlineRelationshipsGrid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.
        '/css/gridview',
    'template' => '<div class="title-bar">{summary}</div>{items}{pager}',
    'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
    'dataProvider' => $relationshipsDataProvider,
    'columns' => $columns,
));
?>
</div>

<!--/* x2prostart */-->

<div id='inline-relationships-graph-container'>
<?php
if ($displayMode === 'graph') {
    Yii::app()->controller->renderPartial ('application.views.relationships.graphInline', array (
        'model' => $this->model,
        'inline' => true,
        'height' => $height,
    ));
}
?>
</div>

<!--/* x2proend */-->

<?php
if($hasUpdatePermissions) {

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');
?>

<div class='clearfix'></div>
<form id='new-relationship-form' class="form" style='display: none;'>
    <input type="hidden" id='ModelId' name="ModelId" value="<?php echo $model->id; ?>">
    <input type="hidden" id='ModelName' name="ModelName" value="<?php echo $modelName; ?>">


    <div class='row'>
        <?php 
        echo CHtml::label(Yii::t('apps','Link Type:'), 'RelationshipModelName');
        echo CHtml::dropDownList (
            'RelationshipModelName', 'Contacts', $linkableModelsOptions, 
            array (
                'id' => 'relationship-type',
                'class' => 'x2-select, field',
            ));
        echo CHtml::link(
            '', '#',
            array(
                'onclick'=>'return false;',
                'id'=>'quick-create-record',
                'class' => 'fa fa-plus fa-lg pseudo-link',
                'style' => 'visibility: hidden; height:16px;',
            ));
        ?>
    </div>

    <div class='row'>
        <?php
        echo CHtml::label( Yii::t('apps','Name:'), 'RelationshipName');
        echo "<div id='inline-relationships-autocomplete-container'>";
        X2Model::renderModelAutocomplete ('Contacts');
        echo CHtml::hiddenField ('RelationshipModelId');
        echo("</div>");
        echo CHtml::textField ('myName',$model->name, array('disabled'=>'disabled'));
        ?>
        <!-- <input type="hidden" id='RelationshipModelId' name="RelationshipModelId"> -->
    </div>

    <div class='row'>
        <?php
        echo X2Html::label (Yii::t('app', 'Label:'), 'RelationshipLabelButton');
        echo X2Html::textField ('secondLabel');


        echo X2Html::textField ('firstLabel', '' ,array(
            'title' => Yii::t('apps','Create a different label for ').$model->name));
        echo X2Html::hiddenField ('mutual','true');
        echo X2Html::link (
            '', '', 
            array(
                'id'=>'RelationshipLabelButton',
                'class' => 'pseudo-link fa fa-long-arrow-right',
                'title' => Yii::t('apps','Create a different label for ').$model->name
            ));
        ?>
    </div>
    
    <?php 
        echo CHtml::button (
            Yii::t('app', 'Create Relationship'), 
            array('id' => 'add-relationship-button', 'class'=>'x2-button'));
    ?>

</form>

<?php 
} 
?>

