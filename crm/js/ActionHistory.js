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

x2.ActionHistory = (function () {

function ActionHistory (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        relationshipFlag: false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Widget.call (this, argsDict);
    this._init ();
}

ActionHistory.prototype = auxlib.create (x2.Widget.prototype);

ActionHistory.prototype.update = function () {
    var that = this;
    $.fn.yiiListView.update('history',{ data:{ relationships: that.relationshipFlag }});
};

ActionHistory.prototype._setUpEvents = function () {
    var that = this;
    $(document).on('change','#history-selector',function(){
        $.fn.yiiListView.update('history',{ data:{ history: $(this).val() }});
    });
    $(document).on('click','#history-collapse',function(e){
        e.preventDefault();
        $('#history .description').toggle();
    });
    $(document).on('click','#show-history-link',function(e){
        e.preventDefault();
        $.fn.yiiListView.update('history',{ data:{ pageSize: 10000 }});
    });
    $(document).on('click','#hide-history-link',function(e){
        e.preventDefault();
        $.fn.yiiListView.update('history',{ data:{ pageSize: 10 }});
    });
    $(document).on('click','#show-relationships-link',function(e){
        e.preventDefault();
        if(that.relationshipFlag){
            that.relationshipFlag=0;
        }else{
            that.relationshipFlag=1;
        }
        that.update ();
    });
};

ActionHistory.prototype._init = function () {
    this._setUpEvents ();
};

return ActionHistory;

}) ();
