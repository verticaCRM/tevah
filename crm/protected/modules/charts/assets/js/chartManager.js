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


x2.chartManager = (function () { 

/**
 * Manages all charts on page 
 */
function ChartManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        charts: [] // jqplot chart instances
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

ChartManager.prototype.addChart = function (chart) {
    this.charts.push (chart);
};

/*
Private instance methods
*/

/**
 * Replots stacked bar width, changing width of bars based on layout type 
 */
ChartManager.prototype._replotStackedBarChart = function (elem) {
    $.map (elem.series, function (elem) {
        elem.barWidth = x2.layoutManager.isMobileLayout () ? 80 : 100;
    });
    elem.replot ({resetAxes: false}); 
};

/**
 * Replots all charts 
 */
ChartManager.prototype._replotCharts = function () {
    var that = this;
    $.map (that.charts, function (elem, index) { 
        if (elem.stackSeries) {
            that._replotStackedBarChart (elem);
        } else {
            elem.replot ({resetAxes: false}); 
        }
    });
};

/**
 * Calls replots on window resize, with delay
 */
ChartManager.prototype._setUpResizeBehavior = function () {
    var that = this;
    var timeout;

    $(window).on ('resize', function () {
        if (timeout) clearTimeout (timeout);                    
        timeout = setTimeout (function () {
            that._replotCharts ();
        }, 200);
    });
};

ChartManager.prototype._init = function () {
    this._setUpResizeBehavior ();
};

return new ChartManager ();

}) ();

