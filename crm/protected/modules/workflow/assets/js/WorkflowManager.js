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

if (typeof x2 === 'undefined') x2 = {};

x2.WorkflowManager = (function () {

function WorkflowManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        modelId: null,
        modelName: '',
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.WorkflowManagerBase.call (this, argsDict);

    this._init ();
}

WorkflowManager.REQUIRE_ALL = 1;

WorkflowManager.prototype = auxlib.create (x2.WorkflowManagerBase.prototype);

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
 * JS equivalent of Workflow::checkStageRequirement ()
 * @param number stageNumber
 * @param array workflowStatus
 * @return bool 
 */
WorkflowManager.prototype.checkStageRequirement = function (stageNumber, workflowStatus) {
    var requirementMet = true;

    // check if all stages before this one are complete
    if(parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) ===
       WorkflowManager.REQUIRE_ALL) {    

        for(var i=1; i<stageNumber; i++) {
            if(!workflowStatus['stages'][i]['complete']) {
                requirementMet = false;
                break;
            }
        }
    } else if(parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) < 0) { 
        // or just check if the specified stage is complete

        if(!workflowStatus['stages']
            [ -1 * parseInt (workflowStatus['stages'][stageNumber]['requirePrevious'], 10) ]
            ['complete']) {

            requirementMet = false;
        }
    }
    return requirementMet;
};
        
WorkflowManager.prototype.startWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.startStageUrl,
        dataType: 'json',
        type: "GET",
        data: "workflowId="+workflowId+"&stageNumber="+stageNumber+"&modelId="+
            that.modelId + '&type=' + that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};

WorkflowManager.prototype.completeWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.completeStageUrl,
        type: 'GET',
        dataType: 'json',
        data: "workflowId="+workflowId+"&stageNumber="+stageNumber+"&modelId="+
            that.modelId + '&type=' + that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};

WorkflowManager.prototype.workflowCommentDialog = function (workflowId,stageNumber,callback) {
    var that = this;

    $('#workflowCommentDialog').dialog(
        'option','title',that.translations['Comment Required']);

    $('#workflowCommentWorkflowId').val(workflowId);
    $('#workflowCommentStageNumber').val(stageNumber);
    
    $('#workflowComment').css('border','1px solid black');
    $('#workflowComment').val('')
    $('#workflowCommentDialog').dialog('open');
    $('#workflowCommentDialog').data ('callback', callback);
};

WorkflowManager.prototype.completeWorkflowStageComment = function (callback) {
    var that = this;
    var comment = $.trim($('#workflowComment').val());
    if(comment.length < 1) {
        $('#workflowComment').css('border','1px solid red');
    } else {
        $.ajax({
            url: that.completeStageUrl,
            type: 'GET',
            dataType: 'json',
            data: 'workflowId='+$('#workflowCommentWorkflowId').val()+'&stageNumber='+
                $('#workflowCommentStageNumber').val()+
                '&modelId='+that.modelId+"&type="+that.modelName+'&comment='+
                encodeURI(comment) + '&renderFlag=0',
            success: function(response) {
                callback (response['workflowStatus'], response['flashes']);
                x2.Notifs.updateHistory();
            }
        });
        $('#workflowCommentDialog').dialog('close');
    }
};

WorkflowManager.prototype.revertWorkflowStage = function (workflowId,stageNumber,callback) {
    var that = this;
    $.ajax({
        url: that.revertStageUrl,
        type: 'GET',
        dataType: 'json',
        data: 'workflowId='+workflowId+'&stageNumber='+stageNumber+
            '&modelId='+that.modelId+"&type="+that.modelName + '&renderFlag=0',
        success: function(response) {
            callback (response['workflowStatus'], response['flashes']);
            x2.Notifs.updateHistory();
        }
    });
};
        

/*
Private instance methods
*/

WorkflowManager.prototype._setUpCommentDialog = function () {
    var that = this;
    $("#workflowCommentDialog").dialog({
        autoOpen:false,
        resizable: false,
        modal: true,
        show: "fade",
        hide: "fade",
        width:400,
        buttons:[
            {
                click: function() {
                    that.completeWorkflowStageComment(
                        $('#workflowCommentDialog').data ('callback')); 
                    return false;
                },
                text: that.translations['Submit'],
                "class": "highlight"
            },
            {
                text: that.translations['Cancel'],
                click: function() {
                    $(this).dialog("close");
                }
            }
        ]
    });
};

/**
 * Forces a UI refresh 
 */
WorkflowManager.prototype._afterSaveStageDetails = function () {
    $("#workflowSelector").change();
};
        
WorkflowManager.prototype._init = function () {
    var that = this;

    this._setUpStageDetailsDialog ();
    this._setUpCommentDialog ();
};

return WorkflowManager;

}) ();

