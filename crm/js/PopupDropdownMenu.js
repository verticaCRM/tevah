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
 * Creates a popup dropdown menu 
 */

function PopupDropdownMenu (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        containerElemSelector: '', // the container to be turned into a popup dropdown menu
        openButtonSelector: '',// the button which opens/closes the popup dropdown menu
        onClose: function () {}, // function to be called when menu is closed
        autoClose: true, // if true, menu is closed on click inside
        closeOnClickOutside: true, // if true, menu is closed on click outside

        // used to determine which elements can be clicked without closing the drop down 
        onClickOutsideSelector: null, 
        defaultOrientation: 'right', 
        css: {} // css to be applied to the popup dropdown menu on open
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (this.onClickOutsideSelector === null) {
        this.onClickOutsideSelector = this.containerElemSelector + ', ' + this.openButtonSelector;
    }
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

/**
 * close menu 
 */
PopupDropdownMenu.prototype.close = function () {
    var that = this; 
    that._containerElem.attr ('style', '');
    that._containerElem.hide ();

    that.onClose ()
};

/*
Private instance methods
*/

PopupDropdownMenu.prototype._positionMenuLeft = function () {
    var that = this;
    that._containerElem.position ({
        my: 'right top', 
        at: 'left+22 bottom',
        of: that._openButton,
        using: function (css) {
            that._containerElem.css ($.extend (css, that.css));
        }
    });
    that._containerElem.addClass ('flipped');
};

PopupDropdownMenu.prototype._positionMenuRight = function () {
    var that = this;
    that._containerElem.css ($.extend ({
        top: that._openButton.offset ().top + 26,
        left: that._openButton.offset ().left - 3
    }, that.css));
    that._containerElem.removeClass ('flipped');
};

/**
 * position menu below button 
 */
PopupDropdownMenu.prototype._positionMenu = function () {
    var that = this; 

    if (that.defaultOrientation === 'left') {
        if (that._openButton.offset ().left - that._containerElem.width () > 0) {

            that._positionMenuLeft ();
            return;
        } else if (
            that._openButton.offset ().left + that._containerElem.width () > $(window).width ()) {

            that._positionMenuRight ();
            return;
        }

    } else {
        if (
            that._openButton.offset ().left + that._containerElem.width () > $(window).width ()) {

            that._positionMenuLeft ();
            return;
        } else if (that._openButton.offset ().left - that._containerElem.width () > 0) {

            that._positionMenuRight ();
            return;
        }
    }
    that._positionMenuLeft ();
};

/**
 * Sets up event which opens/closes dropdown menu 
 */
PopupDropdownMenu.prototype._setUpOpenButtonBehavior = function () {
    var that = this; 
    that._openButton.unbind ('click.PopupDropdownMenu._setUpOpenButtonBehavior').
        bind ('click.PopupDropdownMenu._setUpOpenButtonBehavior', function (evt) {

        evt.preventDefault ();
        if (!that._containerElem.is (':visible')) {
            that._positionMenu ();
            that._containerElem.fadeIn (100);
            if (that.autoClose) {
                $(document).one ('click', function () {
                    that.close ();
                });
            }
            if (that.closeOnClickOutside) {
                auxlib.onClickOutside (
                    $(that.onClickOutsideSelector), 
                    function () { that.close (); }, true);
            }
        } else {
            that.close ();
            return true;
        }
        return false;
    });
};

PopupDropdownMenu.prototype._init = function () {
    var that = this; 
    that._openButton = $(this.openButtonSelector);
    that._containerElem = $(this.containerElemSelector);
    that._containerElem.addClass ('popup-dropdown-menu');
    that._containerElem.css (this.css);
    that._setUpOpenButtonBehavior ();

    // hide menu on resize
    $(window).resize (function (e) {
        if (that._containerElem.is (':visible')) {
            that.close ();
        }
    });
};


(function () {

/*
Auto-instantiates popup dropdown menus.
For this to be used, the elements must be set up as follows:

-there must be a button element with an id and the class 'x2-popup-dropdown-button'
-there must be a menu container element with an id and the class 'x2-popup-dropdown-menu'.
 this element must directly follow the button element
*/
$('.x2-popup-dropdown-button').each (function () {
    var containerElemSelector;
    if ($(this).next ('.x2-popup-dropdown-menu').length) {
        containerElemSelector = '#' + $(this).next ().attr ('id');
    } else if ($(this).attr ('data-menu-selector')) {
        containerElemSelector = $(this).attr ('data-menu-selector');
    }
    auxlib.assert (typeof containerElemSelector !== 'undefined');

    new PopupDropdownMenu ({
        containerElemSelector: containerElemSelector,
        openButtonSelector: '#' + $(this).attr ('id'),
        defaultOrientation: 'left'
    });
});

}) ();
