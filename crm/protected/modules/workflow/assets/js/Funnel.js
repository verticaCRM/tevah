/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
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

if (typeof x2 === 'undefined') x2 = {};

x2.Funnel = (function () {

var Point = x2.geometry.Point;

/**
 * Funnel used on the workflow funnel view page
 */
function Funnel (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        stageValues: null, // array of projected deal values for each stage
        totalValue: null, // formatted sum of stageValues
        recordsPerStage: null, // array of record counts per stage
        stageNameLinks: null, // array of links which open stage details
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.BaseFunnel.call (this, argsDict);

    this._stageHeight = 32; // temporary. replace when stage heights are depend on status

    this._init ();
}

Funnel.prototype = auxlib.create (x2.BaseFunnel.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

/**
 * Place stage counts on top of funnel
 */
Funnel.prototype._addStageCounts = function () {
    var that = this;
    /*var canvasTopLeft = new Point ({
        x: $(this.containerSelector).position ().left,
        y: $(this.containerSelector).position ().top,
    });*/

    for (var i = 0; i < this.stageCount; i++) {

        // create a container element for the stage count and position it near the centroid of the
        // stage trapezoid.
        var stageCountContainer = $('<span>', {
            'class': 'funnel-stage-count',
            html: '<b>' + this.recordsPerStage[i] + '</b>',
            css: {
                position: 'absolute',
                width: '100px',
                'text-align': 'center',
                left: this._stageCentroids[i].x - 50,
                top: this._stageCentroids[i].y - 10,
                'font-size': '30px',
                'margin-top': '-8px',
                'text-shadow': 'rgba(250,250,250,0.5) 0px 2px 0px'
            }
        });

        $(this.containerSelector).append (stageCountContainer);

        // click the stage name link when the corresponding stage count is clicked
        $(stageCountContainer).click ((function () {
            var j = i; 
            return function () {
                $('.stage-name-link-' + j).click ();
                return false;
            };
        }) ());
    }
};

/**
 * Place stage name links to the left of the funnel with y coordinate aligned with stage centroid 
 */
Funnel.prototype._addStageNameLinks = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        var link = $(this.stageNameLinks[i]);
        $(link).addClass ('stage-name-link-' + i);
        $(link).addClass ('stage-name-link');
        $(link).css ({
            top: this._stageCentroids[i].y - 7 
        });
        $(this.containerSelector).append (link);
    }

    // retrieve max width of stage name links and shift all links over by that amount
    var maxWidth = Math.max.apply (null, auxlib.map (function (a) {
        return $(a).width ();
    }, $.makeArray ($(this.containerSelector).find ('.stage-name-link'))));

    var extraSpace = 20;
    $(this.containerSelector).find ('.stage-name-link').each (function (i, elem) {
        $(elem).css ('left', -maxWidth - extraSpace);
    });

    var extraMargin = 18;
    $(this.containerSelector).css (
        'margin-left', maxWidth + extraSpace + extraMargin);

};

/**
 * Place stage values in a column to the right of the funnel with y coordinate aligned with stage 
 * centroid 
 */
Funnel.prototype._addStageValues = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        var stageValueContainer = $('<span>', {
            'class': 'funnel-stage-value',
            html: '<b>' + this.stageValues[i] + '</b>',
            css: {
                position: 'absolute',
                right: -(this._funnelW1 / 2) - 15,
                top: this._stageCentroids[i].y - 10,
            }
        });
        $(this.containerSelector).append (stageValueContainer);
    }
};

/**
 * Add totals row below the funnel 
 */
Funnel.prototype._addTotals = function () {
    var that = this;
    var totalRecordsContainer = $('<span>', {
        'class': 'funnel-total-records',
        html: this.translations['Total Records'] + ': <b>' +
            auxlib.reduce (function (a, b) { return a + b; }, 
            auxlib.map (function (a) { return parseInt (a, 10); }, this.recordsPerStage)) + '</b>',
        css: {
            position: 'absolute',
            left: $(this.containerSelector).find ('.stage-name-link').last ().css ('left'),
            top: this._funnelHeight + 10,
        }
    });
    $(this.containerSelector).append (totalRecordsContainer);

    var totalValue = $('<span>', {
        'class': 'funnel-total-value',
        html: this.translations['Total Amount'] + ': <b>' + this.totalValue + '</b>',
        css: {
            position: 'absolute',
            right: -(this._funnelW1 / 2) - 15,
            top: this._funnelHeight + 10,
        }
    });
    $(this.containerSelector).append (totalValue);
};


/**
 * Populate _stageHeights property with heights of individual stages 
 */
Funnel.prototype._calculateStageHeights = function () {
    var that = this;
    // calculate stage heights
    this._stageHeights = [];

    // each stage is given the same height
    for (var i = 0; i < this.stageCount; i++) {
        this._stageHeights.push (this._stageHeight);
    }
};

Funnel.prototype._calculateFunnelHeight = function () {
    this._funnelHeight = this._stageHeight * this.stageCount;
};

/**
 * Overrides parent method. Adds stage height calculation
 */
Funnel.prototype._calculatePreliminaryData = function () {
    var that = this; 
    this._calculateStageHeights (); 
    this._calculateFunnelHeight (); 
    x2.BaseFunnel.prototype._calculatePreliminaryData.call (this);
};

Funnel.prototype._init = function () {
    var that = this;

    x2.BaseFunnel.prototype._init.call (this);
    that._addStageCounts ();
    that._addStageNameLinks ();
    that._addStageValues ();
    that._addTotals ();
};

return Funnel;

}) ();

