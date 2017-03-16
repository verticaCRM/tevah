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

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherActionTab = (function () {

function PublisherActionTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	x2.PublisherTab.call (this, argsDict);	
}

PublisherActionTab.prototype = auxlib.create (x2.PublisherTab.prototype);

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
 * Hide the reminder container after submission
 */
PublisherActionTab.prototype.reset = function () {
    x2.PublisherTab.prototype.reset.call (this);
    $(this.resolveIds ('#action-reminder-inputs')).slideUp ();
};


/*
Private instance methods
*/

/**
 * Set up reminder subsection dropdown container
 */
PublisherActionTab.prototype._setUpActionReminders = function () {
    var that = this;
    $(this.resolveIds ('#Actions_reminder')).change (function () {
        if ($(this).is (':checked') ) {
            $(that.resolveIds ('#action-reminder-inputs')).slideDown ();
        } else {
            $(that.resolveIds ('#action-reminder-inputs')).slideUp ();
        }
        return false;
    });
};

PublisherActionTab.prototype._init = function () {
    var that = this;

    this._setUpActionReminders ();
    x2.PublisherTab.prototype._init.call (this);
};

return PublisherActionTab;

}) ();

