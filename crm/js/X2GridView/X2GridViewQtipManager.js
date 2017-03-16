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
 * QTipManager prototype
 */

x2 = typeof x2 === 'undefined' ? {} : x2;

x2.X2GridViewQtipManager = (function () {

function GridViewQtipManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.QtipManager.call (this, argsDict);
    var defaultArgs = {
        loadingText: 'Loading...',
        dataAttrTitle: false,
        modelType: 'contacts',
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

GridViewQtipManager.prototype = auxlib.create (x2.QtipManager.prototype);

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
 * Initializes qtip objects for all links returned by query on qtipSelector
 */
GridViewQtipManager.prototype.refresh = function () {
    var that = this; 
    that.DEBUG && console.log ('refresh');
	$(that.qtipSelector).each(function (i) {
		var recordId = $(this).attr("href").match(/\d+$/);

		if(recordId !== null && recordId.length) {
			$(this).qtip(that._getConfig ($(this), recordId[0]));
		}
	});
};

/*
Private instance methods
*/

GridViewQtipManager.prototype._getConfig = function (elem, recordId) {
    var that = this;
    var config = {
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+that.modelType+'/qtip',
                    data: { 
                        id: recordId,
                        suppressTitle: that.dataAttrTitle ? 1 : 0
                    },
                    method: "get"
                }).then (function (content) {
                    api.set ('content.text', content);
                });
                return that.loadingText;
            }, 
        },
        style: {
            classes: 'x2-qtip',
            tip: {
                corner: true,
            }
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        }
    };
    if (that.dataAttrTitle) {
        config.content.title = $(elem).attr ('data-qtip-title');
    }
    return config;
};

return GridViewQtipManager;

}) ();
