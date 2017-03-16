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
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/inlineBuyersPortfolioWidget.css'
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

<div id="buyersPortfolio-form"
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
        'header' => Yii::t("contacts", 'Listing'),
        'value' => 'CHtml::link($data->relatedModel->Clisings->c_name_dba_c,Yii::app()->createUrl("clistings",array($data->relatedModel->Clisings->id=>"")),array("target"=>"_blank"))',
    ),
    array(
        'name' => 'c_listing_id',
        'header' => Yii::t("contacts", 'Listing ID'),
        'value' => 'CHtml::link($data->relatedModel->Clisings->id,Yii::app()->createUrl("clistings",array($data->relatedModel->Clisings->id=>"")),array("target"=>"_blank"))',
        'type' => 'raw',
    ),
    array(
        'name' => 'assignedTo',
        'header' => Yii::t("contacts", 'Assigned To'),
        'value' => '$data->renderAttribute("assignedTo")',
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
        'name' => 'c_date_released',
        'header' => Yii::t('contacts', 'Released Date'),
        'value' => '$data->renderAttribute("c_date_released")',
        //  'value' => ' Formatter::formatCompleteDate($data->c_date_released) ',
        'filterType' => 'dateTime',
        'type' => 'raw'
    ),

    array(
        'name' => 'c_sales_stage',
        'header' => Yii::t('contacts', 'Stage'),
        'value' => '$data->relatedModel->Clisings->renderAttribute("c_sales_stage")',
        'type' => 'raw'
    ),
    array(
        'name' => 'c_release_status',
        'header' => Yii::t('contacts', 'Status'),
        'value' => '$data->renderAttribute("c_release_status")',
        'type' => 'raw'
    ),
    array(
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

    array(
        'name' => 'c_listing_city_c',
        'header' => Yii::t("contacts", 'City'),
        'value' => '$data->relatedModel->Clisings->renderAttribute ("c_listing_city_c")',
        'type' => 'raw',
    ),
    array(
        'name' => 'c_listing_town_c',
        'header' => Yii::t("contacts", 'County'),
        'value' => '$data->relatedModel->Clisings->renderAttribute("c_listing_town_c")',
        'type' => 'raw',
    ),
    array(
        'name' => 'c_listing_region_c',
        'header' => Yii::t("contacts", 'State'),
        'value' => '$data->relatedModel->Clisings->renderAttribute("c_listing_region_c")',
        'type' => 'raw',
    ),
    array(
        'name' => 'c_financial_net_cashflow_c',
        'header' => Yii::t("contacts", 'Owner\'s Cash Flow'),
        'value' =>  function($data){
                 /*
                 ({c_financial_net_cashflow_c}+{c_financial_officersalary_c}+{c_financial_ownerhealthins_c}+{c_financial_businessloans_c}+{c_financial_addback_interest_c}+{c_financial_ownercc_c}+{c_financial_ownercell_c}+{c_financial_ownerlease_c}+{c_financial_fuelvehicle_c}+{c_financial_other_income_c});
                */

                $ClisingsDetails = $data->relatedModel->Clisings;

                $c_ownerscashflow = $ClisingsDetails->c_financial_net_cashflow_c + $ClisingsDetails->c_financial_officersalary_c  + $ClisingsDetails->c_financial_ownerhealthins_c + $ClisingsDetails->c_financial_businessloans_c + $ClisingsDetails->c_financial_addback_interest_c + $ClisingsDetails->c_financial_ownercc_c + $ClisingsDetails->c_financial_ownercell_c + $ClisingsDetails->c_financial_ownerlease_c + $ClisingsDetails->c_financial_fuelvehicle_c + $ClisingsDetails->c_financial_other_income_c;

                return $c_ownerscashflow;

            },
        'type' => 'raw',
    ),
    /* array(
         'name' => 'c_financial_net_cashflow_c',
         'header' => Yii::t("contacts", 'Net Cash Flow'),
         'value' => '$data->relatedModel->Clisings->c_financial_net_cashflow_c',
         'type' => 'raw',
     ),*/
    array(
        'name' => 'c_listing_askingprice_c',
        'header' => Yii::t("contacts", 'Asking Price'),
        'value' => '$data->relatedModel->Clisings->renderAttribute ("c_listing_askingprice_c")',
        'type' => 'raw',
    ),

);

$columns[] = array(
    'name'  =>'deletion.',
    'header' => Yii::t("contacts", 'Actions'),
    'htmlOptions' => array (
        'class' =>'action-button-cell',
    ),

    'value' => function($data){
            $c_release_status = $data->getReleaseStatus();

            $c_is_hidden = (int)$data->getHiddenStatus();

            if ($c_is_hidden != 1)
            {
                $updateButton = '<span class=\'fa fa-power-off x2-hint\' title=\'Hide this portfolio record.\' onclick="javascript:listingActions(this, \'hide\', '.$data->relatedModel->id.', \''.Yii::app()->controller->createUrl('/site/showHidePortfolioItem').'\', event); return false;" style="cursor: pointer;"></span>';

            }
            else
            {
                $updateButton = '<span class=\'fa fa-repeat x2-hint\' title=\'UnHide this portfolio record.\' onclick="javascript:listingActions(this, \'show\', '.$data->relatedModel->id.', \''.Yii::app()->controller->createUrl('/site/showHidePortfolioItem').'\',  event); return false;" style="cursor: pointer;" ></span>';
            }
            if ($c_release_status != 'Released')
            {
                $releaseButton = '<span class=\'fa fa-download x2-hint\' title=\'Release this portfolio record.\' onclick="javascript:listingActions(this, \'Released\', '.$data->relatedModel->id.', \''.Yii::app()->controller->createUrl('/site/updatePortfolioItemStatus').'\',  event); return false;" style="cursor: pointer;" ></span>';
            }
            else
            {
                $releaseButton = '';
            }
            return $updateButton.' &nbsp; &nbsp; '.$releaseButton;
        },
    'type'  => 'raw',
);
$this->widget('X2GridViewGeneric', array(
    'id' => "buyersPortfolio-grid",
    'enableGridResizing' => true,
    'showHeader' => CPropertyValue::ensureBoolean (
            $this->getWidgetProperty('showHeader')),
    'defaultGvSettings' => array (
        'name' => '22%',
        'nameID' => '18%',
        'assignedTo' => '13%',
        'createDate' => '10%',
        'c_date_released' => '10%',
        'c_release_status' => '10%',
        'c_is_hidden' => '10%',
        'c_listing_city_c' => '10%',
        'c_listing_town_c' => '10%',
        'c_listing_region_c' => '10%',
        'c_financial_net_cashflow_c' => '10%',
        'c_listing_askingprice_c' => '10%',
        'deletion.' => 70,
    ),
    'filter' => $this->getFilterModel (),
    'htmlOptions' => array (
        'class' =>
            ($relationshipsDataProvider->itemCount < $relationshipsDataProvider->totalItemCount ?
                'grid-view has-pager' : 'grid-view'),
    ),
    'dataColumnClass' => 'X2DataColumnGeneric',
    'gvSettingsName' => 'inlineBuyersPortfolioGrid',
    'buttons'=>array('clearFilters','autoResize'),
    'template' => '<div class="title-bar">{summary}</div>{buttons}{items}{pager}',
    'afterAjaxUpdate' => 'js: function(id, data) { refreshQtip(); }',
    'dataProvider' => $relationshipsDataProvider,
    'columns' => $columns,
    'enablePagination' => true,
));
?>

</div>

<!--/* x2prostart */-->

<div id='inline-buyersPortfolio-graph-container this_page'>
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

    Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/BuyersPortfolio.js');
    ?>

    <div class='clearfix'></div>
    <form id='new-buyersPortfolio-form' class="form" style='display: none;'>
        <input type="hidden" id='ModelId' name="ModelId" value="<?php echo $model->id; ?>">
        <input type="hidden" id='ModelName' name="ModelName" value="<?php echo $modelName; ?>">

        <h2>All Listings</h2>
        <div class="clistings-error" style="display: none;">No listing was selected</div>
        <?php
        $ClistingsModel=new Clistings('search');

        $this->widget('X2GridView', array(
            'id'=>'BuyersPortfolio_all_listings',
            'title'=>"All Listings",
            'buttons'=>array('clearFilters','columnSelector','autoResize'),
            'template' => '<div class="title-bar">{summary}</div>{buttons}{items}{pager}',
            'fixedHeader'=>false,
            'dataProvider'=>$ClistingsModel->search(),
            'filter'=>$ClistingsModel,
            'modelName'=>'Clistings',
            'viewName'=>'clistings',
            'defaultGvSettings'=>array(
                'gvCheckbox' => 30,
                'name'=>257,
                'assignedTo'=>105,
                'c_listing_city_c' => '10%',
                'c_listing_town_c' => '10%',
                'c_listing_region_c' => '10%',
                'c_financial_net_cashflow_c' => '10%',
                'c_listing_askingprice_c' => '10%',
                'c_ownerscashflow' => '10%',
            ),
            'specialColumns'=>array(
                'name'=>array(
                    'name'=>'name',
                    // 'value'=>'CHtml::link($data->name,array("view","id"=>$data->id))',
                    'value' => 'CHtml::link($data->name,Yii::app()->createUrl("clistings",array($data->id=>"")))',
                    'type'=>'raw',
                ),
                'description'=>array(
                    'name'=>'description',
                    'header'=>Yii::t('app','Description'),
                    'value'=>'Formatter::trimText($data->description)',
                    'type'=>'raw',
                ),
                'c_financial_net_cashflow_c'=>array(
                    'name'=>'c_financial_net_cashflow_c',
                    'header'=>Yii::t('app','Net Cash Flow'),
                    'value'=>'$data->c_financial_net_cashflow_c',
                    'type'=>'raw',
                ),
                'c_listing_region_c'=>array(
                    'name'=>'c_financial_net_cashflow_c',
                    'header'=>Yii::t('app','State'),
                    'value'=>'$data->c_listing_region_c',
                    'type'=>'raw',
                ),
                'c_ownerscashflow'=>array(
                    'name'=>'c_ownerscashflow',
                    'header'=>Yii::t('app','Owner\'s Cash Flow'),
                    'value' =>  function($data){

                            /*
                             ({c_financial_net_cashflow_c}+{c_financial_officersalary_c}+{c_financial_ownerhealthins_c}+{c_financial_businessloans_c}+{c_financial_addback_interest_c}+{c_financial_ownercc_c}+{c_financial_ownercell_c}+{c_financial_ownerlease_c}+{c_financial_fuelvehicle_c}+{c_financial_other_income_c});
                            */

                            $ClisingsDetails = $data;

                            $c_ownerscashflow = $ClisingsDetails->c_financial_net_cashflow_c + $ClisingsDetails->c_financial_officersalary_c  + $ClisingsDetails->c_financial_ownerhealthins_c + $ClisingsDetails->c_financial_businessloans_c + $ClisingsDetails->c_financial_addback_interest_c + $ClisingsDetails->c_financial_ownercc_c + $ClisingsDetails->c_financial_ownercell_c + $ClisingsDetails->c_financial_ownerlease_c + $ClisingsDetails->c_financial_fuelvehicle_c + $ClisingsDetails->c_financial_other_income_c;

                            return $c_ownerscashflow;

                        },
                    'type'=>'raw',
                ),
            ),
            'enableControls'=>false,
            'fullscreen'=>false,
        ));
        ?>

        <?php
        echo CHtml::button (
            Yii::t('app', 'Add to portfolio'),
            array('id' => 'add-buyersPortfolio-button', 'class'=>'x2-button'));
        ?>

    </form>

<?php
}
?>

