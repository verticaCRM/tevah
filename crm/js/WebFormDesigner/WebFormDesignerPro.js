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

/*
Base prototype. Should not be instantiated.
*/

function WebFormDesignerPro (argsDict) {
	WebFormDesigner.call (this, argsDict);
}

WebFormDesignerPro.prototype = auxlib.create (WebFormDesigner.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

WebFormDesignerPro._enableTabsForCustomCss = function () {

    // enable tabs for CSS textarea
    $(document).delegate('#custom-css, #custom-html', 'keydown', function(e) {
      var keyCode = e.keyCode || e.which;

      if (keyCode == 9) {
        e.preventDefault();
        var start = $(this).get(0).selectionStart;
        var end = $(this).get(0).selectionEnd;

        // set textarea value to: text before caret + tab + text after caret
        $(this).val($(this).val().substring(0, start)
                    + "\t"
                    + $(this).val().substring(end));

        // put caret at right position again
        //$(this).get(0).selectionStart =
        $(this).get(0).selectionEnd = start + 1;
      }
    });
};

/*
Public instance methods
*/

/*
Private instance methods
*/

WebFormDesignerPro.prototype._onFieldUpdate = function () {
    var that = this;
    var fieldList = that._getFieldList ();
    $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
    $('#web-form-save-button').addClass ('highlight');
};

/*
Make custom fields containes sortable, set up their behavior
*/
WebFormDesignerPro.prototype._setUpSortableCustomFieldsBehavior = function () {
    var that = this;
    $( "#sortable1" ).sortable({
        placeholder: "ui-state-highlight",
        connectWith: ".connectedSortable",
        receive: function(event, ui) {
            ui.item.find('div').css('display', 'none');
            that._onFieldUpdate ();
        },
        update: function(event, ui) {
            that._onFieldUpdate ();
        }
    });
    $( "#sortable2" ).sortable({
        placeholder: "ui-state-highlight",
        connectWith: ".connectedSortable",
        receive: function(event, ui) {
            ui.item.find('div').css('display', 'block');
            that._onFieldUpdate ();
        },
        update: function(event, ui) {
            that._onFieldUpdate ();
        }
    });
};


/*
Populate custom fields form with custom fields settings
*/
WebFormDesignerPro.prototype._updateCustomFields = function (form) {
    if(typeof form.fields != 'undefined' && form.fields != null) {
        try {
            var savedFieldList = JSON.parse(decodeURIComponent(form.fields));
        } catch (e) {
            return;
        }
        var fieldList = $('.connectedSortable li');

        // clear form fields
        $('#sortable2 li').each(function() {
            $(this).appendTo('#sortable1');
            $(this).find('div').css('display', 'none');
        });

        // load form fields from saved form
        var savedField;
        for(var i=0; i<savedFieldList.length; i++) {
            savedField = savedFieldList[i];
            if (savedField.type === 'tags') { // tag field uses a separate input
                $('#tags').val (savedField.label);
                continue;
            }
            var f = $('#sortable1 li[name="' + savedField.fieldName + '"]');
            f.appendTo('#sortable2');
            f.find('div').css('display', 'block');
            f.find('input[type="checkbox"]').prop('checked', savedField.required);
            f.find('input[type="text"]').val(savedField.label);
            f.find('.field-position').val(savedField.position);
            f.find('.field-type').val(savedField.type);
        }
    }
};

/*
Returns a dictionary containing custom fields form input values
*/
WebFormDesignerPro.prototype._getFieldList = function (form) {

    var fieldList = [];
    $('#sortable2').find('li').each(function() {
        var f = new Object;
        f['fieldName'] = $(this).attr('name');
        f['required'] = $(this).find('input[type="checkbox"]').is(':checked');
        f['label'] = $(this).find('input[type="text"]').val();
        f['position'] = $(this).find('select.field-position').val();
        f['type'] = $(this).find('select.field-type').val();
        fieldList.push(f);
    });
    return fieldList;
};

WebFormDesignerPro.prototype._refreshForm = function () {
    var fieldList = this._getFieldList ();

    // set POST data for saving weblead form
    $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
};
