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

x2.EmailMassAction = (function () {

function EmailMassAction (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
}

EmailMassAction.prototype = auxlib.create (x2.MassAction.prototype);

EmailMassAction.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this)
    params = $.extend (params, $.deparam.querystring (window.location.href));
    return params;
};

/**
 * Execute mass action on checked records
 */
EmailMassAction.prototype.execute = function () {
    var that = this;
    var selectedRecords = that.massActionsManager._getSelectedRecords ();
    $.ajax({
        url: that.massActionsManager.massActionUrl,
        type:'GET',
        data:this.getExecuteParams (),
        success: function (data) { 
            var response = JSON.parse (data);
            var returnStatus = response[0];
            if (response['success']) {
                that.afterExecute ();
            } 
            that.massActionsManager._displayFlashes (response);
        }
    });
};


return EmailMassAction;

}) ();
