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

x2.MassMoveToFolder = (function () {

function MassMoveToFolder (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.EmailMassAction.call (this, argsDict);
    this.goButtonLabel = this.massActionsManager.translations['move'];
    this.dialogTitle = this.massActionsManager.translations['moveToFolder'];
    this.dialogTitlePlural = this.dialogTitle;
    this.dialogTitleSingular = this.massActionsManager.translations['moveOneToFolder'];
}

MassMoveToFolder.prototype = auxlib.create (x2.EmailMassAction.prototype);

MassMoveToFolder.prototype.showUI = function () {
    if (this.massActionsManager._getSelectedRecords ().length === 1) {
        this.dialogTitle = this.dialogTitleSingular;
        this.dialogElem$.find ('span').first ().hide ();
        this.dialogElem$.find ('span').first ().next ().show ();

    } else {
        this.dialogElem$.find ('span').first ().show ();
        this.dialogElem$.find ('span').first ().next ().hide ();
        this.dialogTitle = this.dialogTitlePlural;
    }
};

MassMoveToFolder.prototype.getExecuteParams = function () {
    var that = this;
    var params = x2.EmailMassAction.prototype.getExecuteParams.call (this);
    var targetFolder = this.dialogElem$.find ('.email-folder-dropdown').val ();
    params['targetFolder'] = targetFolder;
    return params;
};

return MassMoveToFolder;

}) ();

