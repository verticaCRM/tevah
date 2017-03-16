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


function WebleadFormDesignerPro (argsDict) {
	WebFormDesigner.call (this, argsDict);	
}

WebleadFormDesignerPro.prototype = auxlib.create (WebleadFormDesigner.prototype);

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

/*
Append additional query parameters
*/
WebleadFormDesignerPro.prototype._appendToQuery = function (query) {

    var fieldList = this._getFieldList ();
    
    if(query.match (/[?]/)) {
        query += '&';
    } else {
        query += '?';
    }

    query += 'css=' + encodeURIComponent($('#custom-css').val());
    return query;
};

/*
Clear form input
*/
WebleadFormDesignerPro.prototype._clearFields = function () {
    var that = this;
    $('#web-form-name').val('');
    $('#custom-html').val('');
    $('#custom-css').val('');
    $('#tags').val('');
    $.each(that.fields, function(i, field) {
        $('#'+field).val('');
    });
};

/*
Insert form input
*/
WebleadFormDesignerPro.prototype._updateExtraFields = function (form) {

    if(typeof form.css !== 'undefined') {
        $('#custom-css').val(form.css);
    }
    if(typeof form.header !== 'undefined') {
        $('#custom-html').val(form.header);
    }
    if(typeof form.userEmailTemplate !== 'undefined') {
        $('#user-email-template').val(form.userEmailTemplate);
    }
    if(typeof form.webleadEmailTemplate !== 'undefined') {
        $('#weblead-email-template').val(form.webleadEmailTemplate);
    }

    WebleadFormDesigner.prototype._updateExtraFields.call (this, form);
};

WebleadFormDesignerPro.prototype._beforeSaved = function () {
    $('#add-custom-html-button').removeClass ('highlight');
    auxlib.destroyErrorBox ($('#custom-html-input-container'));
};

WebleadFormDesignerPro.prototype._afterSavedFormsChange = function () {
    auxlib.destroyErrorBox ($('#custom-html-input-container'));
    $('#add-custom-html-button').removeClass ('highlight');
};

WebleadFormDesignerPro.prototype._afterInit = function () {
    var that = this;

    $('#custom-css').on('change', function() {
        that.updateParams();
    });

    /*
    Indicate to user that they have changes to save
    */
    $('#custom-html').on('keydown change', function(evt) {
        x2.DEBUG && console.log ('change'); 
        if ($('#custom-html').val () !== '') {
            $('#web-form-save-button').addClass ('highlight');
        } else {
            $('#web-form-save-button').removeClass ('highlight');
        }
    });

    WebFormDesigner._enableTabsForCustomCss ();
    that._setUpSortableCustomFieldsBehavior ();

};

/*
Returns a dictionary containing custom fields form input values + tags.
*/
WebleadFormDesignerPro.prototype._getFieldList = function (form) {
    var fieldList = [];
    $('#sortable2').find('li').each(function() {
        var f = {};
        f['fieldName'] = $(this).attr('name');
        f['required'] = $(this).find('input[type="checkbox"]').is(':checked');
        f['label'] = $(this).find('input[type="text"]').val();
        f['position'] = $(this).find('select.field-position').val();
        f['type'] = $(this).find('select.field-type').val();
        fieldList.push(f);
    });
    if ($('#tags').val () !== '') {
        fieldList.push ({
            fieldName: 'tags',  
            type: 'tags',  
            required: false,  
            position: 'top',  
            label: $('#tags').val ()
        });
    }
    return fieldList;
};



