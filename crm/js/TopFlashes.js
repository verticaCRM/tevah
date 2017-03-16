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
$(function() {
    
x2.topFlashes = (function () {

function TopFlashes (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        successFadeTimeout: 1700
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

TopFlashes.prototype.displayFlash = function (message, type, closeMethod, encode) {
    if ($.inArray (type, ['error', 'success', 'warning', 'loading']) === -1) {
        throw new Error ('invalid flash type');
    }
    closeMethod = typeof closeMethod === 'undefined' ? 'fade' : closeMethod; 
    encode = typeof encode === 'undefined' ? true : encode; 
    var that = this;

    var flashContainer$ = $('<div>', {
        id: 'top-flashes-container',
        'class': 'flash-' + type
    });

    if (encode) {
        flashContainer$.append ($('<div>', {
            text: message,
            id: 'top-flashes-message'
        }));
    } else {
        flashContainer$.append ($('<div>', {
            html: message,
            id: 'top-flashes-message'
        }));
    }

    this.clearFlash ();
    this.container$.append (flashContainer$);

    switch (closeMethod) {
        case 'fade':
            setTimeout (
                function () { 
                    flashContainer$.fadeOut (3000, function () {
                        flashContainer$.remove ();
                    });
                }, that.successFadeTimeout);
            break;
        case 'clickOutside':
            auxlib.onClickOutside (flashContainer$, function () {
                flashContainer$.remove ();
            }) 
            break;
    }
};

TopFlashes.prototype.clearFlash = function () {
    this.container$.children ().remove ();
};

TopFlashes.prototype._init = function () {
    this.container$ = $('<div>', {
        id: 'top-flashes-container-outer'
    });
    $('#page-container').append (this.container$);
};

return new TopFlashes;

}) ();

});
