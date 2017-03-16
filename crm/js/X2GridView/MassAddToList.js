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

x2.MassAddToList = (function () {

function MassAddToList (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'addToList'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.progressBarLabel = this.translations['added'];
    this.dialogTitle = this.massActionsManager.translations['addToList'];
    this.goButtonLabel = this.massActionsManager.translations['add'];
}

MassAddToList.prototype = auxlib.create (x2.MassAction.prototype);

MassAddToList.prototype.addListOption = function (listId, listName) {
    $('#addToListTarget').append ($('<option>', {
        val: listId,
        text: listName
    }));
};

MassAddToList.prototype.setListId = function (listId) {
    $('#addToListTarget').val(listId);
};

MassAddToList.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    params['listId'] = $('#addToListTarget').val();
    return params;
};

MassAddToList.prototype.afterExecute = function () {
    this.dialogElem$.dialog ('close');
    this.massActionsManager.massActionInProgress = false;
};

return MassAddToList;

}) ();

