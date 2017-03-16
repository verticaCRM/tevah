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

x2.MassDelete = (function () {

function MassDelete (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'delete'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.progressBarLabel = this.translations['deleted'];
    this.progressBarDialogTitle = this.translations['deleteprogressBarDialogTitle'];
    this.dialogTitle = this.massActionsManager.translations['deleteSelected'];
    this.goButtonLabel = this.massActionsManager.translations['delete'];
}

MassDelete.prototype = auxlib.create (x2.MassAction.prototype);

MassDelete.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    params['modelType'] = this.massActionsManager.modelName;
    return params;
};

MassDelete.prototype.createDoubleConfirmationDialog = function (execute, cancel) {
    var that = this;
    var doubleConfirmDialog$ = $(this.massActionsManager.gridSelector).find (
        '.double-confirmation-dialog');
    doubleConfirmDialog$.dialog ({
        title: this.translations['doubleConfirmDialogTitle'], 
        autoOpen: true,
        width: 500,
        modal: true,
        buttons: [
            {
                text: this.translations['delete'],
                'class': 'double-confirm-dialog-go-button',
                click: function () {
                    var password = doubleConfirmDialog$.find ('[name="password"]').val ();
                    auxlib.destroyErrorBox (doubleConfirmDialog$);
                    if (password !== '') {
                        $.ajax ({
                            url: that.massActionsManager.massActionUrl, 
                            type: 'POST',
                            data: {
                                'passConfirm': true,
                                'password': doubleConfirmDialog$.find ('[name="password"]').val (),
                            }, 
                            dataType: 'json',
                            success: function (data) {
                                if (data[0]) {
                                    doubleConfirmDialog$.dialog ('destroy');
                                    execute (data[1]);
                                } else {
                                    doubleConfirmDialog$.append (
                                        auxlib.createErrorBox ('', [data[1]]));
                                }
                            }
                        });
                    } else {
                        doubleConfirmDialog$.append (
                            auxlib.createErrorBox ('', [that.translations['passwordError']]));
                    }
                }
            },
            {
                text: this.translations['cancel'],
                click: function () { 
                    doubleConfirmDialog$.dialog ('close'); 
                }
            }
        ],
        close: function () {
            doubleConfirmDialog$.dialog ('destroy'); 
            cancel ();
        }
    });
};


MassDelete.prototype.superExecute = function () {
    var that = this;
    that.dialogElem$.dialog ('close');
    this.createDoubleConfirmationDialog (function (uid) {
        x2.MassAction.prototype.superExecute.call (that, uid);
    }, function () {
        that.massActionsManager.massActionInProgress = false;
    });
};

return MassDelete;

}) ();

