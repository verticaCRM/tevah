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

x2.MergeRecords = (function () {

function MergeRecords (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'mergeRecords'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
}

MergeRecords.prototype = auxlib.create (x2.MassAction.prototype);

MergeRecords.prototype.openDialog = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this)
    params['modelType'] = this.massActionsManager.modelName;
    var selection = params['gvSelection'];
    try{
        if(selection.length < 2){
            throw new Error("Invalid number of records for merge.");
        }
        var idArrayStr = "";
        for(i = 0; i < selection.length; i++){
            idArrayStr += "&idArray["+i+"]="+selection[i];
        }
        var url = window.location.protocol +"//"+ window.location.hostname + yii.scriptUrl + 
                '/site/mergeRecords?modelName='+params['modelType']+idArrayStr;
        window.location = url;
    }catch(Error){
        this.massActionsManager.massActionInProgress = false;
        alert("Please select more than one record to merge.");
    }
};

MergeRecords.prototype.afterExecute = function () {
    var that = this;
    this.massActionsManager.massActionInProgress = false;
    this.massActionsManager._updateGrid ();
};

return MergeRecords;
}) ();

