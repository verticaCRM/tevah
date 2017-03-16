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

(function () {

module("editable");

var extractCurrency = x2.LineItems.prototype.extractCurrency;

test ("line items test", function () {
    /*
    Click new line item button, assert new line item is created.
    */
    var lineItemCount = $('.quote-table').find ('tr.line-item').length;
    $('.add-line-item-button').trigger ('click');
    var newLineItemCount = $('.quote-table').find ('tr.line-item').length;
    deepEqual (lineItemCount + 1, newLineItemCount, 
        "lineItemCount not incremented after add line item button pressed.");

    /*
    Click new global adjustment button, assert new global adjustment is created and
    that subtotal and totals display properly.
    */
    var adjustmentCount = $('.quote-table').find ('tr.adjustment').length;
    var oldSubtotal = $('.quote-table').find ('.subtotal').val ();
    var oldTotal = $('.quote-table').find ('.total').val ();
    var isFirstAdjustment = $('.quote-table').find (".subtotal-row").is (":hidden");


    $('.add-adjustment-button').trigger ('click');
    var newAdjustmentCount = $('.quote-table').find ('tr.adjustment').length;
    var newSubtotal = $('.quote-table').find ('.subtotal').val ();
    var newTotal = $('.quote-table').find ('.total').val ();
    deepEqual (adjustmentCount + 1, newAdjustmentCount, 
        "adjustmentCount not incremented after add adjusmtent button pressed.");

    if (isFirstAdjustment) {
        deepEqual (newSubtotal, newTotal, 
            "new subtotal not equal to new total.");
        ok (!$('.quote-table').find (".subtotal-row").is (":hidden"), 
            "subtotal is not shown after global adjustment added.");
    } else {
        deepEqual (oldSubtotal, newSubtotal, 
            "new subtotal not equal to old subtotal.");
    }
    deepEqual (oldTotal, newTotal, 
        "adjustmentCount not incremented after add adjusmtent button pressed.");

    /*
    Change price of first line item, check new line total and subtotal against predicted 
    values.
    */
    var $firstLineItem = $('.quote-table').find ('tr.line-item').first ();

    var oldTotal = 
        extractCurrency ($('.quote-table').find ('.total'));
    var oldSubtotal = 
        extractCurrency ($('.quote-table').find ('.subtotal'));
    var newPrice = 5;

    var oldLineTotal = 
        extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var quantity = $firstLineItem.find ('input.quantity').val ();
    var adjustment = $firstLineItem.find ('input.adjustment');
    var adjustmentType = $firstLineItem.find ('input.adjustment-type').val ();

    $firstLineItem.find ('input.price').val (newPrice * 100);
    $firstLineItem.find ('input.price').trigger ('change');

    var predictedLineTotal;
    if (adjustmentType === 'linear') {
        var adjustment = extractCurrency (adjustment);
        predictedLineTotal = newPrice * quantity + adjustment;
    } else {
        adjustment = adjustment.val ().replace (/&#37;/, '');
        predictedLineTotal = newPrice * quantity + newPrice * quantity * adjustment;
    }

    var predictedSubtotal = (oldSubtotal - oldLineTotal) + predictedLineTotal;

    var newLineTotal = extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var newSubtotal = extractCurrency ($('.quote-table').find ('.subtotal'));

    deepEqual (newLineTotal, predictedLineTotal, 
        "predicted line total and new line total are not equal.");
    deepEqual (newSubtotal.toFixed (2), predictedSubtotal.toFixed (2), 
        "predicted subtotal and new subtotal are not equal.");
});

module("readonly");


}) ();
