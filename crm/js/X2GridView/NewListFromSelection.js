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

x2.NewListFromSelection = (function () {

function NewListFromSelection (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'createList'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.goButtonLabel = this.massActionsManager.translations['create'];
    this.dialogTitle = this.massActionsManager.translations['newList'];
}

NewListFromSelection.prototype = auxlib.create (x2.MassAction.prototype);

NewListFromSelection.prototype.validateMassActionDialogForm = function () {
    var that = this;
    var newListName = this.dialogElem$.find ('.new-list-name');
    auxlib.destroyErrorFeedbackBox ($(newListName));
    var listName = $(newListName).val ();
    if(listName === '' || listName === null) {
        auxlib.createErrorFeedbackBox ({
            prevElem: $(newListName),
            message: that.translations['blankListNameError']
        });
        $('#mass-action-dialog-loading-anim').remove ();
        this.dialogElem$.dialog ('widget').find ('.x2-dialog-go-button').show ();
        return false;
    }
    return true;
};

NewListFromSelection.prototype.afterExecute = function () {
    var that = this;
    var newListName = this.dialogElem$.find ('.new-list-name');
    $(newListName).val ('');
    this.dialogElem$.dialog ('close');
    this.massActionsManager.massActionInProgress = false;
};

NewListFromSelection.prototype.getExecuteParams = function () {
    var that = this;
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    var newListName = this.dialogElem$.find ('.new-list-name');
    var listName = $(newListName).val ();
    params['listName'] = listName;
    return params;
};

/**
 * This complicated method is used to switch mass actions (from new list to add to list) after
 * the first batch is completed.
 * @return MassAddToList
 */
NewListFromSelection.prototype.convertToAddToList = function (listId, dialogState) {
    var that = this;
    var addToList = this.massActionsManager.massActionObjects['addToList'];
    var newListName = this.dialogElem$.find ('.new-list-name');
    var listName = $(newListName).val ();
    addToList.addListOption (listId, listName);
    addToList.setListId (listId);
    addToList.progressBar = this.progressBar;
    addToList.recordCount = this.recordCount;
    dialogState.superExecuteParams.listId = listId;
    dialogState.superExecuteParams.massAction = addToList.massActionName;

    return addToList;
};

/**
 * Overrides parent method so that after the first batch is completed, requests are made to add to
 * that list. This is accomplished by swapping out the mass action objects after the first 
 * response. This method also handles the case where the list could not be created successfully.
 */
NewListFromSelection.prototype._nextBatch = function (dialog, dialogState) {
    var that = this;
    this._beforeNextBatch ();
    dialogState.batchOperInProgress = true;
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'POST',
        data: $.extend (dialogState.superExecuteParams, {
            uid: dialogState.uid
        }),
        dataType: 'json',
        success: function (data) { 
            dialogState.batchOperInProgress = false;
            var response = data;
            that.massActionsManager._displayFlashesList (
                response, $(dialog).find ('.super-mass-action-feedback-box'));

            if (response['successes'] === -1) { // list could not be created
                dialog.dialog ('close');
                return;
            }

            if (response['failure']) {
                dialogState.loadingAnim$.hide ();
                $(dialog).append ($('<span>', {
                    text: response['errorMessage'],
                    'class': 'error-message'
                }));
                return;
            } else if (response['complete']) {
                $(dialog).dialog ('close');
            } else if (response['batchComplete']) {
                that.progressBar.incrementCount (response['successes']);
                dialogState.uid = response['uid'];
                listId = response['listId'];

                if (!dialogState.stop && !dialogState.pause) { 
                    that.convertToAddToList (listId, dialogState)._nextBatch (dialog, dialogState);
                } else {
                    dialogState.loadingAnim$.hide ();
                    if (dialogState.stop) {
                        that.massActionsManager._updateGrid (function () {
                            that.afterSuperExecute ();
                        });
                        return;
                    }

                    var interval = setInterval (function () { 
                        if (dialogState.stop || !dialogState.pause) {
                            clearInterval (interval);
                        } 
                        if (!dialogState.stop && !dialogState.pause) {
                            dialogState.loadingAnim$.show ();
                            that.convertToAddToList (listId, dialogState)._nextBatch (
                                dialog, dialogState);
                        }
                    }, 500)
                }
            }
        }
    });
};


return NewListFromSelection;

}) ();

