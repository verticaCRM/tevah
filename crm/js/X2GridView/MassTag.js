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

x2.MassTag = (function () {

function MassTag (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'tag',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.progressBarLabel = this.translations['tagged'];
    this.dialogTitle = this.massActionsManager.translations['tagSelected'];
    this.goButtonLabel = this.massActionsManager.translations['tag'];
    this._init ();
}

MassTag.prototype = auxlib.create (x2.MassAction.prototype);

MassTag.prototype._init = function () {
    this.tagContainer = new MassActionTagsContainer ({
        containerSelector: 
            '#' + this.dialogElem$.attr ('id') + ' .x2-tag-list'
    });
};

MassTag.prototype.validateMassActionDialogForm = function () {
    var that = this;
    var tags = that.tagContainer.getTags ();
    that.DEBUG && console.log ('tags.length = ' + tags.length);
    if (tags.length === 0) {
        that.DEBUG && console.log ('executeTagSelected validation error');
        this.dialogElem$.append (
            auxlib.createErrorBox ('', [that.translations['emptyTagError']]));
        $('#mass-action-dialog-loading-anim').remove ();
        this.dialogElem$.dialog ('widget').find ('.x2-dialog-go-button').show ();
        return false;
    } 
    return true;
};

MassTag.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    params['modelType'] = this.massActionsManager.modelName;
    params['tags'] = this.tagContainer.getTags ();
    return params;
};

return MassTag;

}) ();
