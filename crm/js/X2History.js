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


var x2 = typeof x2 === 'undefined' ? {} : x2; 

x2.history = (function (window) {

/**
 * Provides method wrappers for History.js
 */
function History (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict; 
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._pushingState = false;
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
 * Prevents non-standard behavior of History.js which would trigger a statechange event when state
 * is pushed.
 */
History.prototype.pushState = function (stateObj, title, url) {
    console.log ('push state ' + url);
    console.log (stateObj);
    this._pushingState = true;
    window.History.pushState (stateObj, title, url);
    this._pushingState = false;
};

History.prototype.replaceState = function (stateObj, title, url) {
    console.log ('push state ' + url);
    console.log (stateObj);
    this._pushingState = true;
    window.History.replaceState (stateObj, title, url);
    this._pushingState = false;
};

/**
 * Prevents non-standard behavior of History.js which would trigger a statechange event when state
 * is pushed.
 */
History.prototype.bind = function (callback) {
    var that = this;
    window.History.Adapter.bind (window, 'statechange', function () {
        if (that._pushingState) return;
        callback.call (this, Array.prototype.slice.call (arguments));
    });
};

/*
Private instance methods
*/

return new History ();

}) (window);
