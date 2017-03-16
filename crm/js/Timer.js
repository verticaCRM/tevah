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
 * Timer utility. Class gets registered when YII_DEBUG is true
 */

x2.Timer = (function () {

function Timer (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    this._start;
    this._end;
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

Timer.prototype.start = function () {
    this._start = +new Date ();
    return this;
};

Timer.prototype.stop = function () {
    this._end = +new Date ();
    return this;
};

Timer.prototype.reset = function () {
    this._start = this._end = null;
    return this;
};

Timer.prototype.read = function () {
    console.log ((this._end - this._start) / 1000);
    return this;
};

return Timer;

}) ();
