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

function X2GridViewTopPagerManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        gridSelector: '',
        gridId: '',
        namespacePrefix: ''
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

X2GridViewTopPagerManager.prototype.reinit = function () { this._init (); };

/**
 * Check if grid view is on the first page 
 * @return bool 
 */
X2GridViewTopPagerManager.prototype._checkFirstPage = function () {
    var that = this;
    return $('#' + that.gridId).find ('.pager').find ('.previous').hasClass ('hidden');
};

/**
 * Check if grid view is on the last page 
 * @return bool 
 */
X2GridViewTopPagerManager.prototype._checkLastPage = function () {
    var that = this;
    return $('#' + that.gridId).find ('.pager').find ('.next').hasClass ('hidden');
};

/**
 * Check if pager button should be disabled and if so, disable it
 */
X2GridViewTopPagerManager.prototype._checkDisableButton = function (prev) {
    var that = this;
    if (prev && that._checkFirstPage ()) {
        $('#' + that.gridId + '-top-pager .top-pager-prev-button').addClass ('disabled');
    } else if (!prev && that._checkLastPage ()) {
        $('#' + that.gridId + '-top-pager .top-pager-next-button').addClass ('disabled');
    }
}

/**
 * Set up behavior of pager buttons 
 */
X2GridViewTopPagerManager.prototype._setUpButtonBehavior = function () {
    var that = this;

    that._checkDisableButton (true);
    that._checkDisableButton (false);
    $('#' + that.gridId + '-top-pager .top-pager-prev-button').unbind ('click');
    $('#' + that.gridId + '-top-pager .top-pager-prev-button').bind ('click', function () {
        that.DEBUG && console.log ('prev');
        $('#' + that.gridId).find ('.pager').find ('.previous').find ('a').click ();
        that._checkDisableButton (true);
    });
    $('#' + that.gridId + '-top-pager .top-pager-next-button').unbind ('click');
    $('#' + that.gridId + '-top-pager .top-pager-next-button').bind ('click', function () {
        that.DEBUG && console.log ('next');
        $('#' + that.gridId).find ('.pager').find ('.next').find ('a').click ();
        that._checkDisableButton (false);
    });
};

X2GridViewTopPagerManager.prototype._init = function () {
    var that = this;
    if (!$('#' + that.gridId).find ('.pager').length) {
        $('#' + that.gridId + '-top-pager').hide ()
        return;
    }
    that._setUpButtonBehavior ();
};
