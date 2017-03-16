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
 * Prototype for creating temporary overlays which can be positioned over iframes to 
 * prevent resizing errors. 
 */

if (typeof x2 === 'undefined') x2 = {};

x2.IframeFixOverlay = (function () { 

function IframeFixOverlay (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        /**
         * @param {Object|String} jQuery element or selector. The overlay will be placed over
         *  this element.
         */ 
        DEBUG: x2.DEBUG && false,
        elementToCover: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    // construct overlay
    this._elem = $('<div>', {
        width: $(this.elementToCover).width (),
        height: $(this.elementToCover).height (),
        css: {
            position: 'absolute',
            background: this.DEBUG ? 'red' : '',
            'z-index': 10000
        }
    });
    $(this.elementToCover).after (this._elem);
    $(this._elem).position ({
        my: 'left top',
        at: 'left top',
        of: this.elementToCover
    });
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

/**
 * Updates size of overlay to match dimensions of covered element 
 */
IframeFixOverlay.prototype.resize = function () {
    $(this._elem).width ($(this.elementToCover).width ());
    $(this._elem).height ($(this.elementToCover).height ());
};

/**
 * Remove overlay. 
 */
IframeFixOverlay.prototype.destroy = function () {
    $(this._elem).remove ();
};

/*
Private instance methods
*/

return IframeFixOverlay;

}) ();
