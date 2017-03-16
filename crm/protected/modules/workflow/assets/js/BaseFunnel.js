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

x2.BaseFunnel = (function () {

var Point = x2.geometry.Point;

/**
 * Base class for all funnels
 */
function BaseFunnel (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        DEBUG: false && x2.DEBUG,
        translations: [],
        stageCount: null, // the number of stages in the workflow
        containerSelector: null, // element selector for container which will hold the funnel 
        workflowStatus: null,
        colors: null, // rgb color string for each stage
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._$canvas; // canvas element
    this._ctx; // canvas drawing context
    this._stageHeights; // heights of individual stages
    this._stageHeight = 30; // temporary. replace when stage heights are depend on status
    //this._funnelHeight = 200; 
    this._funnelHeight; 
    this._funnelW1 = 250; // width of top of funnel
    this._funnelW2 = 130; // width of bottom of funnel

    // the coordinate of the upper left corner of the funnel. Changing this allows you to 
    // conveniently move the funnel to a different position within the canvas.
    this._upperLeftCoord = new Point ({x:0, y:0}); 

    this._stageCentroids;
    this._stageCoordinates;
    this._funnelCoordinates;

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

BaseFunnel.prototype.reinit = function () {
    $(this.containerSelector).children ().remove ();
    this._init ();
};

/*
Private instance methods
*/

/**
 * For debugging purposes. Draws the funnel outline. 
 */
BaseFunnel.prototype._drawBaseFunnel = function () {
    var that = this;
    var ctx = this._ctx;

    that.DEBUG && console.log ('drawing funnel');
    ctx.fillStyle = 'red';
    ctx.strokeStyle = 'red';
    ctx.lineWidth = 5;
    //ctx.lineJoin = 'miter';
    ctx.beginPath ();
    ctx.moveTo (this._funnelCoordinates[0].x, this._funnelCoordinates[0].y);
    ctx.lineTo (this._funnelCoordinates[1].x, this._funnelCoordinates[1].y);
    ctx.lineTo (this._funnelCoordinates[2].x, this._funnelCoordinates[2].y);
    ctx.lineTo (this._funnelCoordinates[3].x, this._funnelCoordinates[3].y);
    ctx.closePath ();
    ctx.fill ();
};

BaseFunnel.prototype._drawStage = function (stageIndex) {
    var that = this;
    var ctx = this._ctx;
    var coords = this._stageCoordinates[stageIndex];
    that.DEBUG && console.log ('drawing stage');
    that.DEBUG && console.log ('coords = ');
    that.DEBUG && console.log (coords);

    ctx.fillStyle = this.colors[stageIndex];
    //ctx.strokeStyle = this.colors[stageIndex];
    ctx.strokeStyle = '#757575';
    ctx.lineWidth = 1;
    //ctx.lineJoin = 'miter';
    ctx.beginPath ();
    ctx.moveTo (coords[0].x, coords[0].y);
    ctx.lineTo (coords[1].x, coords[1].y);
    ctx.lineTo (coords[2].x, coords[2].y);
    ctx.lineTo (coords[3].x, coords[3].y);
    ctx.closePath ();
    ctx.fill ();
    ctx.stroke ();
};

BaseFunnel.prototype._drawStages = function () {
    var that = this;
    for (var i = 0; i < this.stageCount; i++) {
        this._drawStage (i);
    }
};

/**
 * Draw the funnel 
 */
BaseFunnel.prototype._draw = function () {
    var that = this;

    //this._drawBaseFunnel ();
    this._drawStages ();
};

/**
 * Set up the canvas element. Instantiate the element and add it to the dom. Grap its drawing
 * context.
 */
BaseFunnel.prototype._createCanvas = function () {
    var that = this;

    var canvasWidth = this._funnelW1;
    var canvasHeight = this._funnelHeight + (this.stageCount * 1);

    var canvas = document.createElement ('canvas');
    canvas.height = canvasHeight;
    canvas.width = canvasWidth;

    if (typeof G_vmlCanvasManager !== 'undefined') {
        // use excanvas for browsers which don't support canvas natively
        G_vmlCanvasManager.initElement(canvas);
    }

    this._$canvas = $(canvas);
    this._$canvas.css ({
        width: canvasWidth,
        height: canvasHeight
    });

    $(this.containerSelector).append (this._$canvas);

    this._ctx = canvas.getContext ('2d');
};

/**
 * @param number angleA angle between side of trapezoid and the vertical (radians)
 * @param number delta 
 * @param number height height of the trapezoid
 * @param number w1 width of top of trapezoid
 * @param number w1 width of bottom of trapezoid
 * @param number x1 x coordinate of upper left corner
 * @param number y1 y coordinate of upper left corner
 * @return array Point objects corresponding to four corners of the trapezoid   
 *  (<upper left>, <bottom left>, <bottom right>, <upper right)
 */
BaseFunnel.prototype._buildTrapezoid = function (angleA, delta, height, w1, w2, x1, y1) {
    var that = this;

    // length of side of the trapezoid
    var length = Math.sqrt (Math.pow (height, 2) + Math.pow (delta, 2));

    // upper left
    var point1 = new Point ({x: x1, y: y1});


    // bottom left
    var point2 = point1.addAsVectors (new Point ({
        r: length,
        // vector point up and to the right instead of down and to the right because html5 canvas
        // is mirrored about the x axis
        theta: (Math.PI / 2) - angleA
    }));

    // bottom right
    var point3 = new Point ({
        y: point2.y, 
        x: point2.x + w2
    });

    // upper right
    var point4 = new Point ({
        x: point1.x + w1, 
        y: point1.y
    });

    return [point1, point2, point3, point4];
};

/**
 * Populate this._funnelCoordinates and this._stageCoordinates with points
 */
BaseFunnel.prototype._getBaseFunnelCoordinates = function () {
    var that = this;
    // the four corners of the funnel (<upper left>, <bottom left>, <bottom right>, <upper right)
    this._funnelCoordinates = []; 

    // the four corners of each stage (<upper left>, <bottom left>, <bottom right>, <upper right)
    this._stageCoordinates = []; 

    var delta = (this._funnelW1 - this._funnelW2) / 2;

    // angle between side of funnel and the vertical (radians)
    var angleA = Math.atan (delta / this._funnelHeight);

    // get coordinates of corners of funnel
    this._funnelCoordinates = this._buildTrapezoid (
        angleA, delta, this._funnelHeight, this._funnelW1, this._funnelW2, this._upperLeftCoord.x,
        this._upperLeftCoord.y);
    that.DEBUG && console.log (this._funnelCoordinates);

    // get coordinates of corners of each stage
    var prevW2 = this._funnelW1;
    var prevBottomLeft = this._funnelCoordinates[0];
    for (var i = 0; i < this.stageCount; i++) {
        var delta = Math.tan (angleA) * this._stageHeights[i];
        var w1 = prevW2;
        var w2 = w1 - (2 * delta); 
        this._stageCoordinates.push (this._buildTrapezoid (
            angleA, delta, this._stageHeights[i], w1, w2, prevBottomLeft.x, prevBottomLeft.y
        ));
        prevW2 = w2;

        prevBottomLeft = this._stageCoordinates[i][1];
    }
    
};

/**
 * Populate _stageHeights property with heights of individual stages 
 */
BaseFunnel.prototype._calculateStageHeights = function () {
    var that = this;
    // calculate stage heights
    this._stageHeights = [];

    // each stage is given the same height
    for (var i = 0; i < this.stageCount; i++) {
        this._stageHeights.push (this._stageHeight);
    }
    this._funnelHeight = this._stageHeight * this.stageCount;
};

/**
 * Populate this._stageCentroids with centroid of each of the stage trapezoids. These are used to
 * position the stage counts.
 */
BaseFunnel.prototype._getStageCentroids = function () {
    var that = this;
    this._stageCentroids = [];
    for (var i = 0; i < this.stageCount; i++) {
        this._stageCentroids.push (Point.getIntersect (
            this._stageCoordinates[i][0],
            this._stageCoordinates[i][2],
            this._stageCoordinates[i][1],
            this._stageCoordinates[i][3]
        ));
    }
};

/**
 * Calculate data needed before funnel can be drawn 
 */
BaseFunnel.prototype._calculatePreliminaryData = function () {
    var that = this; 
    this._getBaseFunnelCoordinates (); 
    this._getStageCentroids ();
};

BaseFunnel.prototype._init = function () {
    var that = this;

    //$(function () {
        that._calculatePreliminaryData ();
        that._createCanvas ();
        that._draw ();
    //});

};

return BaseFunnel;

}) ();

