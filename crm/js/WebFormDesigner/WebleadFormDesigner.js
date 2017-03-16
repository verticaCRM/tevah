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




function WebleadFormDesigner (argsList) {
	WebFormDesigner.call (this, argsList);	
}

WebleadFormDesigner.prototype = auxlib.create (WebFormDesigner.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

WebleadFormDesigner.prototype._setUpGenerateAssociatedRecordMenu = function () {

    $('#generate-lead-checkbox').change (function () {
        if ($(this).is (':checked')) {
            $('#generate-lead-form').slideDown ();
        } else {
            $('#generate-lead-form').slideUp ();
        }
    });
};

/*
Private instance methods
*/

WebleadFormDesigner.prototype._updateExtraFields = function (form) {

    if(typeof form.generateLead !== 'undefined') {
        if (parseInt (form.generateLead, 10) === 1) {
            $('#generate-lead-checkbox').prop ('checked', true);
        } else {
            $('#generate-lead-checkbox').prop ('checked', false);
        }
        $('#generate-lead-checkbox').change ();
    }
    if(typeof form.generateAccount !== 'undefined') {
        if (parseInt (form.generateAccount, 10) === 1) {
            $('#generate-account-checkbox').prop ('checked', true);
        } else {
            $('#generate-account-checkbox').prop ('checked', false);
        }
        $('#generate-account-checkbox').change ();
    }

    if(typeof form.leadSource !== 'undefined') {
        $('#leadSource').val (form.leadSource);
    }

};

WebleadFormDesigner.prototype._init = function () {
    this._setUpGenerateAssociatedRecordMenu ();
    WebFormDesigner.prototype._init.call (this);
}


