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
view data:
    object $model the Quote model
    array $products (if $readOnly is false) active products create in the products module. This is 
        used to populate the drop down menu for new line items.
    bool $readOnly indicates whether or not the line item and
        adjustment fields are editable.
    string $namespacePrefix used to prefix unique identifiers (html element ids, javascript object 
        names).
    object $module (optional) the module whose assets url should be used to retrieve resources
*/

$module = isset ($module) ? $module : $this->module;
$actionsTab = isset ($actionsTab) ? $actionsTab : false;

$currency = Yii::app()->params->currency;
if (isset ($model)) {
    if (!empty ($model->currency)) {
        $currency = $model->currency;
    }
}


/*
Send a dictionary containing translations for the types of each input field.
Used for html title attributes.
*/
$titleTranslations = array( // keys correspond to CSS classes of each input field
    'product-name'=>Yii::t('quotes', '{product} Name',array(
        '{product}'=>Modules::displayName(false, "Products")
    )),
    'adjustment-name'=>Yii::t('quotes', 'Adjustment Name'),
    'price'=>Yii::t('quotes', 'Price'),
    'quantity'=>Yii::t('quotes', 'Quantity'),
    'adjustment'=>Yii::t('quotes', 'Adjustment'),
    'description'=>Yii::t('quotes', 'Comments')
);

if (!$readOnly) {
    Yii::app()->clientScript->registerCssFiles('lineItemsCss',
        array (
            $module->assetsUrl . '/css/lineItemsMain.css',
            $module->assetsUrl . '/css/lineItemsWrite.css',
        ), false);
} else {
    Yii::app()->clientScript->registerCssFiles('lineItemsCss',
        array (
            $module->assetsUrl . '/css/lineItemsMain.css',
            $module->assetsUrl . '/css/lineItemsRead.css',
        ), false);
}

Yii::app()->clientScript->registerScriptFile ($module->assetsUrl.'/js/LineItems.js', CClientScript::POS_HEAD);

$lineItemsVarInsertionScript = '';

/*
Send information about existing products. This information is used by the client to construct the
product selection drop-down menu.
*/
if (!$readOnly) {
    foreach ($products as $prod) {
        $lineItemsVarInsertionScript .= "productNames.push (" . CJSON::encode($prod->name) . ");\n";
        $lineItemsVarInsertionScript .= "productPrices[" . CJSON::encode($prod->name) . "] = '".
            $prod->price . "';\n";
        $lineItemsVarInsertionScript .= "productDescriptions[" . CJSON::encode($prod->name) . "] = ".
            CJSON::encode($prod->description).";\n";
    }
}

/*
Send an array containing product line information. This array is used by the client to build
the rows of the line items table.
*/
foreach ($model->productLines as $item) {
    $lineItemsVarInsertionScript .= "productLines.push (".
        CJSON::encode (array ( // keys correspond to CSS classes of each input field
        'product-name'=>array ($item->formatAttribute('name'),$item->hasErrors('name')),
        'price'=>array ($item->formatAttribute('price'),$item->hasErrors('price')),
        'quantity'=>array ($item->formatAttribute('quantity'),$item->hasErrors('quantity')),
        'adjustment'=>array ($item->formatAttribute('adjustment'),$item->hasErrors('adjustment')),
        'description'=>array ($item->formatAttribute('description'),$item->hasErrors('description')),
        'adjustment-type'=>array ($item->formatAttribute('adjustmentType'),false))).
    ");";
}

/*
Send an array containing adjustment line information. This array is used by the client to build
the rows of the line items table.
*/
foreach ($model->adjustmentLines as $item) {
    $lineItemsVarInsertionScript .= "adjustmentLines.push (".
        CJSON::encode (array ( // keys correspond to CSS classes of each input field
        'adjustment-name'=>array ($item->formatAttribute('name'),$item->hasErrors('name')),
        'adjustment'=>array ($item->formatAttribute('adjustment'),$item->hasErrors('adjustment')),
        'description'=>array ($item->formatAttribute('description'),$item->hasErrors('description')),
        'adjustment-type'=>array ($item->formatAttribute('adjustmentType'),false))).
    ");";
}

?>
<script>

(function () {

var productNames = [];
var productLines = [];
var adjustmentLines = [];
var productPrices = {};
var productDescriptions = {};

<?php echo $lineItemsVarInsertionScript; ?>

x2.<?php echo $namespacePrefix; ?>lineItems = new x2.LineItems ({
    currency: '<?php echo $currency; ?>',
    readOnly: <?php echo $readOnly ? 'true' : 'false'; ?>,
    deleteImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/delete.png'; ?>',
    arrowBothImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_both.png'; ?>',
    arrowDownImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_down.png'; ?>',
    titleTranslations: <?php echo CJSON::encode ($titleTranslations); ?>,
    productNames: productNames,
    productPrices: productPrices,
    productDescriptions: productDescriptions,
    view: 'default',
    productLines: productLines,
    adjustmentLines: adjustmentLines,
    namespacePrefix: '<?php echo $namespacePrefix; ?>'
});

}) ();

</script>

<?php
if (YII_DEBUG && YII_UNIT_TESTING) {
    Yii::app()->clientScript->registerScriptFile($module->assetsUrl . '/js/quotesUnitTests.js',
        CClientScript::POS_END);
}
?>

<div id="<?php echo $namespacePrefix ?>-line-items-table" class='line-items-table<?php echo $actionsTab ? ' line-items-mini' : ''; echo $readOnly ? ' line-items-read' : ' line-items-write'; ?>'>

<?php
if (YII_DEBUG && YII_UNIT_TESTING) {
    echo "<div id='qunit-fixture'></div>";
}
?>

<?php
    // For the create and update page, create a drop down menu for previous product
    // selection
    if (!$readOnly && isset ($products)) {
        echo "<ul class='product-menu'>";
        foreach ($products as $prod) {
            echo "<li><a href='#'>" . CHtml::encode($prod->name) . "</a></li>";
        }
        echo "</ul>";
    }
?>

<table class='quote-table'>
    <thead>
        <tr>
            <th class='first-cell'></th>
            <th class="lineitem-name"><?php echo Yii::t('products', 'Line Item'); ?></th>
            <th class="lineitem-price"><?php echo Yii::t('products', 'Unit Price'); ?></th>
            <th class="lineitem-quantity"><?php echo Yii::t('products', 'Quantity'); ?></th>
            <th class="lineitem-adjustments"><?php echo Yii::t('products', 'Adjustments'); ?></th>
            <th class="lineitem-description"><?php echo Yii::t('products', 'Comments'); ?></th>
            <th class="lineitem-total"><?php echo Yii::t('products', 'Price'); ?></th>
        </tr>
    </thead>
    <tbody class='line-items<?php if (!$readOnly) echo ' sortable' ?>'>
     <!-- line items will be placed here by addLineItem() in javascript -->
    </tbody>
    <tr class='subtotal-row'>
        <td class='first-cell'> </td>
        <td colspan='<?php echo $actionsTab ? 2 : 4; ?>'> </td>
        <td class="text-field"><span style="font-weight:bold"> Subtotal: </span></td>
        <td class="subtotal-container input-cell">
            <input type="text" readonly='readonly' onfocus='this.blur();'
             style="font-weight:bold" id="<?php echo $namespacePrefix ?>-subtotal"  
             class='subtotal' name="Quote[subtotal]">
            </input>
        </td>
    </tr>
    <tbody class='adjustments<?php if (!$readOnly) echo ' sortable' ?>'>
     <!-- adjustments will be placed here by addAdjustment() in javascript -->
    </tbody>
    <tbody id='quote-total-section'>
    <tr>
        <td class='first-cell'> </td>
        <td colspan='<?php echo $actionsTab ? 2 : 4; ?>'> </td>
        <td class='text-field'><span style="font-weight:bold"> Total: </span></td>
        <td class="total-container input-cell">
            <input type="text" readonly='readonly' onfocus='this.blur();' style="font-weight:bold" 
             id="<?php echo $namespacePrefix; ?>-total" class='total' name="Quote[total]">
            </input>
        </td>
    </tr>
    </tbody>
</table>
<?php if(!$readOnly): ?>
<button type='button' class='x2-button add-line-item-button'>+&nbsp;<?php echo Yii::t('quotes', 'Add Line Item'); ?></button>
<button type='button' class='x2-button add-adjustment-button'>+&nbsp;<?php echo Yii::t('quotes', 'Add Adjustment'); ?></button>
<?php endif; ?>


</div>

