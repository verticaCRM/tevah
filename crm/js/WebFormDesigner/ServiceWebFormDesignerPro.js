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


function ServiceWebFormDesignerPro (argsDict) {
	WebFormDesigner.call (this, argsDict);	
}

ServiceWebFormDesignerPro.prototype = auxlib.create (WebFormDesigner.prototype);

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

ServiceWebFormDesignerPro.prototype._appendToQuery = function (query) {

    var fieldList = this._getFieldList ();

    if(query.match (/[?]/)) {
        query += '&';
    } else {
        query += '?';
    }

    // set POST data for saving service web form
    $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 

    query += 'css=' + encodeURIComponent($('#custom-css').val());

    return query;
};

ServiceWebFormDesignerPro.prototype._updateExtraFields = function (form) {
    if(typeof form.css != 'undefined') {
        $('#custom-css').val(form.css);
    }
};

ServiceWebFormDesignerPro.prototype._afterInit = function () {
    var that = this;

    $('#custom-css').on('change', function() {
        that.updateParams();
    });

    WebFormDesigner._enableTabsForCustomCss ();
    that._setUpSortableCustomFieldsBehavior ();
};



