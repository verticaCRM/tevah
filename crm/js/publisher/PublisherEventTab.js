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
 * Prototype for publisher tab with hours and minutes time fields 
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherEventTab = (function () {

function PublisherEventTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	x2.PublisherTab.call (this, argsDict);	
}

PublisherEventTab.prototype = auxlib.create (x2.PublisherTimeTab.prototype);

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
 * Hide the autocomplete container after submission
 */
PublisherEventTab.prototype.reset = function () {
    x2.PublisherTab.prototype.reset.call (this);
    if ($(this.resolveIds ('#association-type-autocomplete-container')).length) {
        $(this.resolveIds ('#association-type-autocomplete-container')).hide ();
    }
};


/**
 * @param Bool True if form input is valid, false otherwise
 */
PublisherEventTab.prototype.validate = function () {
    var errors = !x2.PublisherTab.prototype.validate.call (this);

    if ($(this.resolveIds ('#event-form-action-due-date')).val () === '') {
        errors |= true;
        $(this.resolveIds ('#event-form-action-due-date')).addClass ('error');
        x2.forms.errorSummaryAppend (this._element, this.translations['startDateError']);
    }

    return !errors;
};


/*
Private instance methods
*/

return PublisherEventTab;

}) ();

