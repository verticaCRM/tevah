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
x2.LayoutEditor = (function() {

function LayoutEditor(argsDict) {
    var defaultArgs = {
        defaultWidth: 52,
        settingName: '',
        columnWidth: null,
        margin: null,
        minWidths: [25, 25], // Minimum width for left and right columns

        // selections that are resized with the first column
        column1: [
        ],

        // selections that are resized with the second column
        column2: [
        ],
        //Element that is resized / dragged
        draggable: '',

        //overall container for the widget
        container: '',
        
        // middle icon indicator
        indicator: '.indicator',

        // Button to open the editor
        editLayoutButton: '#edit-layout',

        // Button to close the editor
        closeButton: '.close-button',

        // Button to reset the columnWidth
        resetButton: '.reset-button',

        //URL for the misc settings action
        miscSettingsUrl: null 
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.closeButton = $(this.container).find (this.closeButton);
    this.indicator = $(this.container).find (this.indicator);
    this.resetButton = $(this.container).find (this.resetButton);

    this.resize();
    this.setUpResizability();
    this.setUpButtons();

}


LayoutEditor.prototype.resize = function() {
    var width = this.columnWidth - this.margin;
    for(var i in this.column1) {
        var elem$ = $(this.column1[i]);
        if (!elem$.is ($(this.draggable)) && elem$.css ('box-sizing') === 'border-box')
            $(this.column1[i]).width (width - 5 + '%');
        else
            $(this.column1[i]).width (width + '%');
    }

    width = 100 - this.columnWidth - this.margin;
    for(var i in this.column2) {
        $(this.column2[i]).width (width + '%');
    }

}

/**
 * Save layout settings 
 */
LayoutEditor.prototype.ajaxCall= function () {
    $.ajax({
        url: this.miscSettingsUrl,
        type: 'post',
        data: {
            settingName: this.settingName,
            settingVal: this.columnWidth
        }
    });
}

LayoutEditor.prototype.setUpButtons = function (){
    var that = this;
    $(this.editLayoutButton).add (this.closeButton).click(function(){
        $(that.container).slideToggle();
        return false;
    });

    $(this.resetButton).click(function(){
        that.columnWidth = that.defaultWidth - that.margin;
        that.resize();
        that.ajaxCall();

        if (typeof SortableWidget !== 'undefined' && 
            typeof SortableWidget.sortableWidgets !== 'undefined') {
            for (var i in SortableWidget.sortableWidgets) {
                SortableWidget.sortableWidgets[i].refresh();
            }
        }
        
    });
}

/**
 * Resize all the proper elements based on 
 * percentage rather than pixels. This function calculates 
 * the percentage then resizes all the proper elements
 */
LayoutEditor.prototype.resizeEventHandler = function (event, ui) {
    var that = this;
    // get the width of the parent 
    var parentWidth = $(that.draggable).parent().width();
    
    $(that.draggable + ' .ui-resizable-handle').width(parentWidth - 100);
    
    var percentWidth = ui.size.width / parentWidth * 100;

    // Check for min widths
    if (percentWidth < that.minWidths[0]) {
        percentWidth = that.minWidths[0];
    }

    if (100 - percentWidth < that.minWidths[1]) {
        percentWidth = 100 - that.minWidths[1];
    }

    this.columnWidth = percentWidth;
    that.resize();
};

LayoutEditor.prototype.setUpResizability = function (){
    var that = this;

    $(this.draggable).resizable({
        handles: 'e',
        start: function() {
            $(that.indicator).find('span').css('opacity', 0.0);
        },
        resize: function (event, ui) { that.resizeEventHandler (event, ui); },
        stop: function (event, ui) {
            that.resizeEventHandler (event, ui);
            that.ajaxCall();
        }
    });
}

return LayoutEditor;

})();
