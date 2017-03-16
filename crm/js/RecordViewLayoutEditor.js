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
;

/**
 * Class to manage the profile layout editor
 */
x2.RecordViewLayoutEditor = (function() {

function RecordViewLayoutEditor (argsDict) {
    var defaultArgs = {
        mainColumnSelector: null,
        responsiveCssSelector: null,
        mainColumnResponsiveRange: [0, 0],
        singleColumnThresholdNoWidgets: 1130,
        singleColumnThreshold: 1407,
        //minWidth: 400,
        dimensions: {
            singleColumnThresholdNoWidgets: 1130, 
            singleColumnThreshold: 1407, 
            extraContentWidth: 170, 
            rightWidgetWidth: 280, 
            formLayoutWidthThreshold: 610 
        }
    };
    this._hasEdited = false;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.dimensions = auxlib.map (function (a) { return parseFloat (a, 10); }, this.dimensions);
    //this.dimensions.formLayoutWidthThreshold += 20;
    x2.LayoutEditor.call (this, argsDict);
}

RecordViewLayoutEditor.prototype = auxlib.create (x2.LayoutEditor.prototype);

RecordViewLayoutEditor.prototype.resize = function() {
    x2.LayoutEditor.prototype.resize.call (this);
}

RecordViewLayoutEditor.prototype.getFormLayoutResponsiveThreshold = function (rightWidgets) {
    rightWidgets = typeof rightWidgets === 'undefined' ? true : rightWidgets; 
    var extraContentWidth = this.dimensions.extraContentWidth;
    var columnWidthRatio = this.columnWidth / 100;
    if (rightWidgets) {
        extraContentWidth += this.dimensions.rightWidgetWidth;
    }
    return extraContentWidth + this.dimensions.formLayoutWidthThreshold + 
        (this.dimensions.formLayoutWidthThreshold * (1 - columnWidthRatio)) / columnWidthRatio;
}

RecordViewLayoutEditor.prototype.getSingleColumnThreshold = function (noWidgets) {
    if (noWidgets) {    
        return this.dimensions.singleColumnThresholdNoWidgets;
    } else {
        return this.dimensions.singleColumnThreshold;
    }
};

/**
 * Converts between detail view single column and multi-column layouts
 */
RecordViewLayoutEditor.prototype.detailViewResizeHandler = function () {
    var windowWidth = window.innerWidth || $(window).width ();

    //console.log ('thresholds');
    //console.log (this.getSingleColumnThreshold (noWidgets));
    //console.log (this.getFormLayoutResponsiveThreshold (!noWidgets));

    var noWidgets = $('body').hasClass ('no-widgets');
    if (windowWidth > this.getSingleColumnThreshold (noWidgets) &&
        windowWidth < this.getFormLayoutResponsiveThreshold (!noWidgets)) {

        $(this.mainColumnSelector).addClass ('force-single-column');
    } else {
        $(this.mainColumnSelector).removeClass ('force-single-column');
    }
};

RecordViewLayoutEditor.prototype.resizeEventHandler = function (event, ui) {
    var that = this;
    if (!this._hasEdited) {
        $(this.responsiveCssSelector).remove ();
        this._hasEdited = true;
        $(window).bind ('resize.RecordViewLayoutEditor.resizeEventHandler', function () {
            that.detailViewResizeHandler ();
        });
    }
    x2.LayoutEditor.prototype.resizeEventHandler.apply (this, arguments);
};

return RecordViewLayoutEditor;

})();
