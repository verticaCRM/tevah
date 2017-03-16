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
 * Base class for front-end widget classes
 */

if (typeof x2 === 'undefined') x2 = {};
x2.Widget = (function () {

function Widget (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * @var mixed css selector for pill box container element, the DOM node itself, or jQuery
         */
        element: null,
        namespace: ''
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this.element$ = $(this.element);
    this.element$.data (Widget.dataKey, this);
}

Widget.dataKey = 'x2-widget';

Widget.prototype.resolveIds = function (selector) {
    return selector.replace (/#/, '#' + this.namespace);
};

Widget.prototype.resolveId = function (id) {
    return '#' + this.namespace + id;
};

/**
 * Returns Widget instance associated with jQuery object
 * @param string|object
 * @return Widget 
 */
Widget.getInstance = function (elem) {
    return $(elem).data (Widget.dataKey);
};

return Widget;

}) ();
