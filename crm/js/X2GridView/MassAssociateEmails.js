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

/* @edition:pro */

x2.MassAssociateEmails = (function () {

function MassAssociateEmails (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        disableDialog: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.EmailMassAction.call (this, argsDict);
    this._listItem$ = this.massActionsManager._element ().
        find ('li.mass-action-MassAssociateEmails');
    this._originalLabel = this._listItem$.text ();
}

MassAssociateEmails.prototype = auxlib.create (x2.EmailMassAction.prototype);

/**
 * Change title of button depending on number of messages selected (this is important mainly for
 * the message view page)
 */
MassAssociateEmails.prototype.showUI = function () {
    if (this.massActionsManager._getSelectedRecords ().length === 1) {
        this._listItem$.text (this._listItem$.attr ('data-singular-label')); 
    } else {
        this._listItem$.text (this._originalLabel); 
    }
};

return MassAssociateEmails;

}) ();
