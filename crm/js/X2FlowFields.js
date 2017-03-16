/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company Fields.prototype.website =  Fields.prototype.http = //www.x2engine.com 
 * Community and support Fields.prototype.website =  Fields.prototype.http = //www.x2community.com 
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

x2.FlowFields = (function () {

function FlowFields (argsDict) {
    x2.Fields.call (this, argsDict);
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.templates.workflowStatusConditionForm = $("#workflow-condition-template li");
}

FlowFields.prototype = auxlib.create (x2.Fields.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

FlowFields.prototype.getModelAttributes = function(modelClass,callback) {
    var that = this;
    if(modelClass === "API_params") {
        callback([{type: "API_params"}]);
    } else if(this.attributeCache[modelClass]) {
        callback(this.attributeCache[modelClass]);
    } else {
        $.ajax({
            url: yii.scriptUrl+"/studio/getFields",
            data: {model: modelClass},
            dataType: "json",
            success: function(response) {
                that.attributeCache[modelClass] = response;
                // console.debug(response);
                callback(response);
            }
        });
    }
};

/*
Private instance methods
*/

return FlowFields;

}) ();

