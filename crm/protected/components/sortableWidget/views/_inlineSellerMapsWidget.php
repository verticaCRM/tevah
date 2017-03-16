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
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineSellerMapsWidget.css'
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

    if($("#BuyersPortfolio_Contacts_autocomplete").length == 1 &&
        $("BuyersPortfolio_Contacts_autocomplete").data ("uiAutocomplete")) {
        $("#BuyersPortfolio_Contacts_autocomplete").data( "uiAutocomplete" )._renderItem =
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

<div id="sellerMaps-form"
    <?php /* x2prostart */ ?>
     style="<?php echo ($displayMode === 'grid' ?  '' : 'display: none;'); ?>"
    <?php /* x2proend */ ?>
     class="<?php echo ($this->getWidgetProperty ('mode') === 'simple' ?
         'simple-mode' : 'full-mode'); ?>">

<?php

$columns = array(
    array(
        'name'=>'nameId',
        'type'=>'raw',
        'header' => Yii::t("contacts", 'Buyer'),
        'value' => 'CHtml::link($data->relatedModel->Contacts->name,Yii::app()->createUrl("accounts",array($data->relatedModel->Contacts->id=>"")),array("target"=>"_blank"))',
    ),
    array(
        'name' => 'assignedTo',
        'header' => Yii::t("contacts", 'Assigned To'),
        'value' => '$data->renderAttribute("assignedTo")',
        'type' => 'raw',
    ),
    /* array(
         'name' => 'c_seller',
         'header' => Yii::t("contacts", 'Seller'),
         'value' => '$data->relatedModel->Clisings->renderAttribute("c_seller")',
         'type' => 'raw',
     ),*/
    array(
        'name' => 'c_listing_date_approved_c',
        'header' => Yii::t("contacts", 'Date approved'),
        'value' => '$data->relatedModel->Clisings->renderAttribute("c_listing_date_approved_c")',
        'type' => 'raw',
    ),
    array(
        'name' => 'leadstatus',
        'header' => Yii::t("contacts", 'Buyer Status'),
        'value' => '$data->relatedModel->Contacts->renderAttribute("leadstatus")',
        'type' => 'raw',
    ),
    array(
        'name' => 'createDate',
        'header' => Yii::t('contacts', 'Added Date'),
        'value' => '$data->renderAttribute("createDate")',
        'filterType' => 'dateTime',
        'type' => 'raw'
    ),
    array(
        'name' => 'c_created_by_user',
        'header' => Yii::t('contacts', 'Added By'),
        'value' =>  function($data){
                if ($data->relatedModel->c_created_by_user != '')
                {
                    $CreatedBy = $data->relatedModel->renderAttribute("c_created_by_user");
                }
                else
                {
                    $CreatedBy = $data->relatedModel->renderAttribute("c_create_by_buyer");
                }

                return $CreatedBy;

            },

        'type' => 'raw'
    ),
    array(
        'name' => 'c_added_from',
        'header' => Yii::t('contacts', 'Added From'),
        'value' => '$data->renderAttribute("c_added_from")',
        'type' => 'raw'
    ),

    array(
        'name' => 'c_released_by',
        'header' => Yii::t('contacts', 'Released By'),
        //'value' => '$data->renderAttribute("c_released_by")',
        'value' =>  function($data){
                if ($data->relatedModel->c_released_by != '')
                {
                    $c_released_by = $data->relatedModel->renderAttribute("c_released_by");
                }
                else
                {
                    $c_released_by = '';
                }

                return $c_released_by;

            },
        'type' => 'raw'
    ),

    array(
        'name' => 'c_date_released',
        'header' => Yii::t('contacts', 'Released Date'),
        'value' => '$data->renderAttribute("c_date_released")',
        //  'value' => ' Formatter::formatCompleteDate($data->c_date_released) ',
        'filterType' => 'dateTime',
        'type' => 'raw'
    ),

    /*array(
        'name' => 'phone',
        'header' => Yii::t('contacts', 'Phone'),
        'value' => '$data->relatedModel->Contacts->renderAttribute("phone")',
        'type' => 'raw'
    ),
    array(
        'name' => 'email',
        'header' => Yii::t('contacts', 'Email'),
        'value' => '$data->relatedModel->Contacts->renderAttribute("c_email")',
        'type' => 'raw'
    ),*/
    array(
        'name' => 'c_release_status',
        'header' => Yii::t('contacts', 'Status'),
        'value' => '$data->renderAttribute("c_release_status")',
        'type' => 'raw'
    ),
    /*array(
        'name' => 'c_is_hidden',
        'header' => Yii::t('contacts', 'Is Hidden'),
        'value' =>  function($data){
                $c_is_hidden = $data->getHiddenStatus();
                if ($c_is_hidden == 0)
                {
                    $hiddenStatus = 'Visible';
                }
                else
                {
                    $hiddenStatus = 'Hidden';
                }

                return $hiddenStatus;

            },
        'type' => 'raw'
    ),
    */
    /*array(
        'name' => 'city',
        'header' => Yii::t("contacts", 'City'),
        'value' => '$data->relatedModel->Contacts->renderAttribute ("city")',
        'type' => 'raw',
    ),
    array(
        'name' => 'country',
        'header' => Yii::t("contacts", 'County'),
        'value' => '$data->relatedModel->Contacts->renderAttribute("country")',
        'type' => 'raw',
    ),
    array(
        'name' => 'state',
        'header' => Yii::t("contacts", 'State'),
        'value' => '$data->relatedModel->Contacts->renderAttribute("state")',
        'type' => 'raw',
    ),
    */


);

$this->widget('X2GridViewGeneric', array(
    'id' => "sellerMaps-grid",
    'enableGridResizing' => true,
    'showHeader' => CPropertyValue::ensureBoolean (
            $this->getWidgetProperty('showHeader')),
    'defaultGvSettings' => array (
        'nameId' => '22%',
        'assignedTo' => '13%',
        // 'c_seller' => '15%',
        'c_listing_date_approved_c' => '10%',
        'leadstatus' => '10%',
        'createDate' => '10%',
        'c_created_by_user' => '10%',
        'c_added_from' => '10%',
        'c_date_released' => '10%',
        'c_release_status' => '10%',
        'c_released_by' => '10%',
        //   'c_is_hidden' => '10%',
        //  'city' => '10%',
        ////  'country' => '10%',
        //  'state' => '10%',
        //  'phone' => '10%',
        //  'email' => '10%',

    ),
    'filter' => $this->getFilterModel (),
    'htmlOptions' => array (
        'class' =>
            ($relationshipsDataProvider->itemCount < $relationshipsDataProvider->totalItemCount ?
                'grid-view has-pager' : 'grid-view'),
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'inlineSellerMapsGrid',
    'buttons'=>array('clearFilters','autoResize'),
    'template' => '<div class="title-bar">{summary}</div>{buttons}{items}{pager}',
    'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
    'dataProvider' => $relationshipsDataProvider,
    'columns' => $columns,
    'enablePagination' => true,
));
?>

</div>

