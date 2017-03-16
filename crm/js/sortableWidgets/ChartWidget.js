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

/**
 * Manages behavior of a chart widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function ChartWidget (argsDict) {
    var defaultArgs = {
        chartType: '',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

ChartWidget.prototype = auxlib.create (SortableWidget.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

ChartWidget.prototype.refresh = function () {
    var that = this;
    x2[this.chartType + that.widgetUID].chart.replot ();
};

/*
Private instance methods
*/

/**
 * Overrides parent method. Chart must be replotted after widget is maximized.
 */
ChartWidget.prototype._afterMaximize = function () {
    var that = this;
    if (typeof x2[this.chartType + that.widgetUID].chart !== 'undefined')
        x2[this.chartType + that.widgetUID].chart.replot ();
};

ChartWidget.prototype._tearDownWidget = function () {
    var that = this;
    if (typeof x2[this.chartType + that.widgetUID].chart !== 'undefined')
        x2[this.chartType + that.widgetUID].chart.tearDown ();
    delete x2[this.chartType + that.widgetUID].chart;
};

/**
 * Enables chart subtype selection. 
 */
ChartWidget.prototype._setUpSubtypeSelection = function () {
    var that = this; 
    this.element.find ('.chart-subtype-selector').on ('change', function (evt) {
        var selectedSubType = $(this).val ();
        x2[that.chartType + that.widgetUID].chart.setChartSubtype (
            selectedSubType, true, false, true);    
        that.setProperty ('chartSubtype', selectedSubType);
    });
};

/**
 * Instantiates popup dropdown menu. Expects {settingsMenu} to be in the widget template
 */
ChartWidget.prototype._setUpSettingsBehavior = function () {
    var that = this; 
    this.popupDropdownMenu = new PopupDropdownMenu ({
        containerElemSelector: this.elementSelector + ' .chart-controls-container',
        openButtonSelector: this.elementSelector + ' .widget-settings-button',
        onClickOutsideSelector: 
            this.elementSelector + ' .widget-settings-button, ' +
            this.elementSelector + ' .chart-controls-container, ' +
            '.ui-datepicker-header, .ui-multiselect-header, .ui-multiselect-checkboxes',
        autoClose: false,
        defaultOrientation: 'left',
        onClose: function () {
            if (!that._cursorInWidget)
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
        },
        css: {
            'max-width': '100%'
        }
    });
};

ChartWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._setUpSubtypeSelection ();
};

