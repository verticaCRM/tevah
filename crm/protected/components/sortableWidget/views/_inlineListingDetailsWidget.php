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
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineListingDetailsWidget.css'
);

// init qtip for contact names
Yii::app()->clientScript->registerScript('contact-qtip', '
function refreshQtip() {
    $("#buyersPortfolio-grid .contact-name").each(function (i) {
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

    if($("#ListingDetails_Contacts_autocomplete").length == 1 &&
        $("ListingDetails_Contacts_autocomplete").data ("uiAutocomplete")) {
        $("#ListingDetails_Contacts_autocomplete").data( "uiAutocomplete" )._renderItem =
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

<div id="listingDetails-form"
    <?php /* x2prostart */ ?>
     style="<?php echo ($displayMode === 'grid' ?  '' : 'display: none;'); ?>"
    <?php /* x2proend */ ?>
     class="<?php echo ($this->getWidgetProperty ('mode') === 'simple' ?
         'simple-mode' : 'full-mode'); ?>">

    <?php

    $columns = array(
        array(
            'name'=>'c_name_dba_c',
            'type'=>'raw',
            'header' => Yii::t("contacts", 'Buyer'),
            'value' => 'CHtml::link($data->c_name_dba_c,Yii::app()->createUrl("listings",array($data->id=>"")),array("target"=>"_blank"))',
        ),
        array(
            'name' => 'assignedTo',
            'header' => Yii::t("contacts", 'Assigned To'),
            'value' => '$data->renderAttribute("assignedTo")',
            'type' => 'raw',
        ),
        array(
            'name' => 'c_seller',
            'header' => Yii::t("contacts", 'Seller'),
            'value' => '$data->renderAttribute("c_seller")',
            'type' => 'raw',
        ),

        array(
            'name' => 'createDate',
            'header' => Yii::t('contacts', 'Added Date'),
            'value' => '$data->renderAttribute("createDate")',
            'filterType' => 'dateTime',
            'type' => 'raw'
        ),


    );

    $this->widget('X2GridViewGeneric', array(
        'id' => "listingDetails-grid",
        'enableGridResizing' => true,
        'showHeader' => CPropertyValue::ensureBoolean (
                $this->getWidgetProperty('showHeader')),
        'defaultGvSettings' => array (
            'c_name_dba_c' => '22%',
            'assignedTo' => '15%',
            'createDate' => '10%',
            'c_seller' => '15%',

        ),
        'filter' => $this->getFilterModel (),
        'htmlOptions' => array (
            'class' =>
                ($relationshipsDataProvider->itemCount < $relationshipsDataProvider->totalItemCount ?
                    'grid-view has-pager' : 'grid-view'),
        ),
        'dataColumnClass' => 'X2DataColumnGeneric',
        'gvSettingsName' => 'inlineListingDetailsGrid',
        //'buttons'=>array('clearFilters','autoResize'),
        'template' => '<div class="title-bar">{summary}</div>{items}',
        'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
        'dataProvider' => $relationshipsDataProvider,
        'columns' => $columns,
        'enablePagination' => true,
    ));
    ?>

</div>

