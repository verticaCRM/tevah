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
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */

x2.TransactionalViewWidget = (function () {

function TransactionalViewWidget (argsDict) {
    var defaultArgs = {
        modelName: null,
        modelId: null,
        actionType: null,
        hideFullHeader: true
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
	GridViewWidget.call (this, argsDict);	
    TransactionalViewWidget.widgets[this.getWidgetKey ()] = this;
}

TransactionalViewWidget.widgets = {};

TransactionalViewWidget.prototype = auxlib.create (GridViewWidget.prototype);


/*
Public static methods
*/

TransactionalViewWidget.refreshAll = function () {
    for (var i in TransactionalViewWidget.widgets) {
        TransactionalViewWidget.widgets[i]._refreshGrid (); 
    }
};

TransactionalViewWidget.refreshByActionType = function (actionType) {
    actionType = typeof actionType === 'undefined' ? '' : actionType; 
    switch (actionType) {
        case '':     
        case 'action':     
            x2.TransactionalViewWidget.refresh ('ActionsWidget'); 
            break;
        case 'call':     
            x2.TransactionalViewWidget.refresh ('CallsWidget'); 
            break;
        case 'event':     
            x2.TransactionalViewWidget.refresh ('EventsWidget'); 
            break;
        case 'note':     
            x2.TransactionalViewWidget.refresh ('CommentsWidget'); 
            break;
        case 'products':     
            x2.TransactionalViewWidget.refresh ('ProductsWidget'); 
            break;
        case 'time':     
            x2.TransactionalViewWidget.refresh ('LoggedTimeWidget'); 
            break;
    }
};

TransactionalViewWidget.refresh = function (type) {
    for (var widgetKey in TransactionalViewWidget.widgets) {
        var regex = new RegExp ('^' + type + '_.*$');
        if (widgetKey.match (regex)) {
            TransactionalViewWidget.widgets[widgetKey]._refreshGrid (); 
        }
    }
};

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

TransactionalViewWidget.prototype._setUpCreateButtonBehavior = function () {
    var that = this;
    this._createButton$.unbind ('click._setUpCreateButtonBehavior').
        bind ('click._setUpCreateButtonBehavior', function () {

        new x2.QuickCreate ({
            modelType: 'Actions',
            data: {
                actionType: that.actionType,
                secondModelName: that.modelName,
                secondModelId: that.modelId
            },
            dialogAttributes: {
                title: that.translations.dialogTitle
            },
            enableFlash: false,
            success: function () {
                //that._refreshGrid ();
                //x2.actionHistory.update ();
            }
        });
    });
};

TransactionalViewWidget.prototype._setUpTitleBarBehavior = function () {
    this._createButton$ = this.element.find ('.create-button');
    this._setUpCreateButtonBehavior ();
    GridViewWidget.prototype._setUpTitleBarBehavior.call (this);
};

return TransactionalViewWidget;

}) ();
