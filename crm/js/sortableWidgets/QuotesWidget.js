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
function QuotesWidget (argsDict) {
    var defaultArgs = {
        modelName: null,
        modelId: null,
        actionType: null,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	x2.TransactionalViewWidget.call (this, argsDict);	
}

QuotesWidget.prototype = auxlib.create (x2.TransactionalViewWidget.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/

QuotesWidget.prototype._setUpCreateButtonBehavior = function () {
    var that = this;
    this._createButton$.unbind ('click._setUpCreateButtonBehavior').
        bind ('click._setUpCreateButtonBehavior', function () {

        x2.inlineQuotes.toggle (); 
    });
};

