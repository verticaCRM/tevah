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

if (typeof x2 === 'undefined') x2 = {};

x2.colorPicker = (function () {

function ColorPicker (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
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


ColorPicker.prototype.setUp = function (element, replaceHash /* optional */) {
    var that = this;
    if ($(element).next ('.sp-replacer').length) return; // already set up
    replaceHash = typeof replaceHash === 'undefined' ? false : replaceHash;

    $(element).spectrum ({
        move: function (color) {
            $(element).data ('ignoreChange', true);
        },
        hide: function (color) {

            that.removeCheckerImage ($(element));
            $(element).data ('ignoreChange', false);

            if (replaceHash) {
                var text = color.toHexString ().toUpperCase ().replace (/#/, '');
            } else {
                var text = color.toHexString ().toUpperCase ();
            }

            $(element).val (text);
            $(element).change ();
        }
    });
    
    $(element).show ();
    if ($(element).val () === '') {
        that.addCheckerImage ($(element));
    }

    $(element).blur (function () {
        var color = $(this).val ();

        // make color picker color match input field without triggering change events
        if (color !== '') { 
            that.removeCheckerImage ($(this));

            if (replaceHash) {
                var text = '#' + color;
            } else {
                var text = color;
            }

            // set the color of the color picker element
            $(element).spectrum ('set', text);
            // now hide and show it, triggering the hide event handler defined above, converting 
            // inputted color value to a hex value
            $(element).spectrum ('show');
            $(element).spectrum ('hide');
        }
    });

    $(element).change (function () {
        var text = $(this).val ();
        if (text === '') {
            that.addCheckerImage ($(this));
        }
    });

};

ColorPicker.prototype.removeCheckerImage = function (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', '');
};

ColorPicker.prototype.addCheckerImage = function (element) {
    $(element).next ('div.sp-replacer').find ('.sp-preview-inner').css (
        'background-image', 'url("' + yii.baseUrl + '/themes/x2engine/images/checkers.gif")');
};

/*
Private instance methods
*/

ColorPicker.prototype._initializeX2ColorPicker = function () {
    var that = this;
    $('.x2-color-picker').each (function () {
        that.setUp ($(this), !$(this).hasClass ('x2-color-picker-hash'));
    });
};

ColorPicker.prototype._init = function () {
    this._initializeX2ColorPicker ();
};

return new ColorPicker ();

}) ();
