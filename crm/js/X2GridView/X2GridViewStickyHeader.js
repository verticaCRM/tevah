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
if (typeof x2.gridViewStickyHeader === 'undefined') {

x2.GridViewStickyHeader = (function () {

function GridViewStickyHeader (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        gridId: null,
        DEBUG: false && x2.DEBUG
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._isStuck;
    this._cachedTitleContainerOffsetTop;
    this._columnSelectorWasVisible;

    this._headerContainer =
        $('#' + this.gridId).find ('.x2grid-header-container');
    this._titleContainer = $('#x2-gridview-top-bar-outer');
    this._bodyContainer =
        $('#' + this.gridId).find ('.x2grid-body-container');
    this._pagerHeight =
        $('#' + this.gridId).find ('.pager').length ?
            $('#' + this.gridId).find ('.pager').height () : 7;
    this._stickyHeaderHeight =
        $(this._headerContainer).height () +
        $(this._titleContainer).height ();
    this._x2TitleBarHeight = $('#header-inner').height ();


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

GridViewStickyHeader.prototype.getIsStuck = function () {
    return this._isStuck;
};

GridViewStickyHeader.prototype.makeStickyForMobile = function () {
    this._stateBeforeMobile = this._isStuck;
    this.makeSticky ();
};

GridViewStickyHeader.prototype.makeUnstickyForMobile = function () {
    if (this._stateBeforeMobile)
        this.makeUnsticky ();
};

GridViewStickyHeader.prototype.makeSticky = function () {
    var bodyContainer = this._bodyContainer;
    var $titleBar =
        $('#x2-gridview-top-bar-outer').
            removeClass ('x2-gridview-fixed-top-bar-outer')
    $(bodyContainer).find ('table').
        removeClass ('x2-gridview-body-with-fixed-header');
    $(bodyContainer).find ('table').addClass ('x2-gridview-body-without-fixed-header');

    $('.column-selector').addClass ('stuck');
    $('#' + this.gridId + '-mass-action-buttons .more-drop-down-list').
        addClass ('stuck');
    this._isStuck = true;
};

GridViewStickyHeader.prototype.makeUnsticky = function () {
    var bodyContainer = this._bodyContainer;
    var $titleBar =
        $('#x2-gridview-top-bar-outer').addClass ('x2-gridview-fixed-top-bar-outer')
    $(bodyContainer).find ('table').addClass ('x2-gridview-body-with-fixed-header');
    $(bodyContainer).find ('table').removeClass ('x2-gridview-body-without-fixed-header');

    $('.column-selector').removeClass ('stuck');
    $('#' + this.gridId + '-mass-action-buttons .more-drop-down-list').
        removeClass ('stuck');
    this._isStuck = false;
};

/*
Bound to window scroll event. Check if the grid header should be made sticky.
*/
GridViewStickyHeader.prototype.checkX2GridViewHeaderSticky = function () {
    var that = this;

    if (this._isStuck) return;

    var headerContainer = this._headerContainer;
    var titleContainer = this._titleContainer;
    var bodyContainer = this._bodyContainer;
    var pagerHeight = this._pagerHeight;
    var stickyHeaderHeight = this._stickyHeaderHeight;
    var x2TitleBarHeight = this._x2TitleBarHeight;

    // check if none of grid view body is visible
    if (($(bodyContainer).offset ().top + $(bodyContainer).height ()) -
        ($(window).scrollTop () + stickyHeaderHeight + x2TitleBarHeight + 5) < 0) {

        //x2.gridviewStickyHeader.isStuck = true;
        this.DEBUG && console.log ('sticky');

        $(titleContainer).hide ();

        /* unfix header */
        //$(bodyContainer).hide ();
        /*var \$titleBar =
            $('#x2-gridview-top-bar-outer').removeClass (
                'x2-gridview-fixed-top-bar-outer')
        \$titleBar.attr (
            'style', 'margin-top: ' +
            (($(bodyContainer).height () - stickyHeaderHeight - pagerHeight) + 5) +
            'px');*/

        // hide mass actions dropdown
        /*if ($('#more-drop-down-list').length) {
            if ($('#more-drop-down-list').is (':visible')) {
                x2.gridviewStickyHeader.listWasVisible = true;
                $('#more-drop-down-list').hide ();
            } else {
                x2.gridviewStickyHeader.listWasVisible = false;
            }
        }*/

        if ($('.column-selector').length) {
            if ($('.column-selector').is (':visible')) {
                this._columnSelectorWasVisible = true;
                $('.column-selector').hide ();
            } else {
                this._columnSelectorWasVisible = false;
            }
        }

        $(window).unbind ('scroll.stickyHeader').
            bind ('scroll.stickyHeader',
                function () { that.checkX2GridViewHeaderUnsticky (); });

        this._cachedTitleContainerOffsetTop =
            $(titleContainer).offset ().top;
    } else {
        return false;
    }
};

/*
Bound to window scroll event. Check if the grid header should be made fixed.
*/
GridViewStickyHeader.prototype.checkX2GridViewHeaderUnsticky = function () {
    var that = this;
    var titleContainer = this._titleContainer;
    var x2TitleBarHeight = this._x2TitleBarHeight;


    // check if grid header needs to be made unsticky
    if ((($(window).scrollTop () + x2TitleBarHeight) -
        this._cachedTitleContainerOffsetTop) < 20) {
        //x2.gridviewStickyHeader.DEBUG && console.log ('unsticky');

        $(titleContainer).show ();

        /*var bodyContainer = x2.gridviewStickyHeader.bodyContainer;
        x2.gridviewStickyHeader.isStuck = false;*/

        /* fix header */
        /*var \$titleBar =
            $('#x2-gridview-top-bar-outer').
                addClass ('x2-gridview-fixed-top-bar-outer');
        \$titleBar.attr ('style', '');
        $(bodyContainer).show ();*/

        //for (var i = 0; i < 1000; ++i) console.log (i);

        // show mass actions dropdown
        /*if (x2.gridviewStickyHeader.listWasVisible &&
              $('#more-drop-down-list').length) {
            $('#more-drop-down-list').show ();
        }*/
        if (this._columnSelectorWasVisible &&
            $('.column-selector').length &&
            $('.column-selector-link').hasClass ('clicked')) {

            $('.column-selector').show ();
        }

        $(window).unbind ('scroll.stickyHeader').
            bind ('scroll.stickyHeader', function () { 
                that.checkX2GridViewHeaderSticky (); 
            });
    }
};


/*
Private instance methods
*/

GridViewStickyHeader.prototype._init = function () {
};

return GridViewStickyHeader;

}) ();

}

