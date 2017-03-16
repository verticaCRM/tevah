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

x2.profile = (function () {

function Profile (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict; 
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._widgetLayoutMode = null; // current layout mode
    this._widgetLayoutSwitchThreshold = 950; 
    this._widgetLayoutModes = {'narrow': 0, 'wide': 1}; // types of layout modes

    var that = this;
    $(function () { that._main (); });
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
 * When a widget is shown, this can be called to check if the widget column should be shown
 */
Profile.prototype.checkAddWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= this._widgetLayoutSwitchThreshold) {
        this._addWidgetsColumn ();
    }
};

/**
 * When a widget is closed, this can be called to check if the widget column should be removed
 */
Profile.prototype.checkRemoveWidgetsColumn = function () {
    if (x2.layoutManager.contentWidth >= this._widgetLayoutSwitchThreshold &&
        SortableWidget.allWidgetsHidden ()) {

        this._removeWidgetsColumn ();
    }
};


/*
Private instance methods
*/

/**
 * Switch to wide layout 
 */
Profile.prototype._addWidgetsColumn = function () {

    // only add widget column if there are widgets shown
    if (!SortableWidget.allWidgetsHidden ()) {
        this._widgetLayoutMode = this._widgetLayoutModes['wide'];
        $('#content').addClass('wide-profile-widget-layout');
    }
};

/**
 * Switch to narrow layout 
 */
Profile.prototype._removeWidgetsColumn = function () {
    $('#content').removeClass('wide-profile-widget-layout');
    this._widgetLayoutMode = this._widgetLayoutModes['narrow'];
};

Profile.prototype._setUpProfileWidgetResponsiveness = function () {
    x2.layoutManager.addFnToResizeQueue (function (windowWidth, contentWidth) {

        // determine which layout to use
        if(contentWidth < this._widgetLayoutSwitchThreshold)
            var newWidgetLayoutMode = this._widgetLayoutModes['narrow'];
        else
            var newWidgetLayoutMode = this._widgetLayoutModes['wide'];
            
        // switch layout if necessary
        if(this._widgetLayoutMode !== newWidgetLayoutMode) {
            if(newWidgetLayoutMode === this._widgetLayoutModes['wide']) {
                this._addWidgetsColumn ();
            } else {
                this._removeWidgetsColumn ();
            }
        }
    });
    $(window).resize ();
};

Profile.prototype._main = function () {
    if (this.isMyProfile) {
        this._setUpProfileWidgetResponsiveness ();
    }
};



return new Profile;

}) ();
