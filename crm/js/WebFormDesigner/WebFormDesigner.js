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




/*
Base prototype. Should not be instantiated. 
*/

function WebFormDesigner (argsDict) {
    var that = this;

	// properties that can be set with constructor arguments
	var defaultArgs = {
		translations: [], // used for various web form text
		iframeSrc: '', 
		externalAbsoluteBaseUrl: '', // used for specifying web form generation script source
		saveUrl: '', // used to save the web form settings
		savedForms: {}, // used to cache previously viewed forms
        fields: [],
        colorfields: [],
        deleteFormUrl: '',
        listId: null
	};

	auxlib.applyArgs (this, defaultArgs, argsDict);

    $(document).on ('ready', function () {
        that._init ();
    });
}


/*
Public static methods
*/

/*
Private static methods
*/

WebFormDesigner.sanitizeInput = function (value) {
    return encodeURIComponent(value.replace(/(^[ ]*)|([ ]*$)|([^a-zA-Z0-9#,])/g, ''));
}

/*
Public instance methods
*/

/*
Private instance methods
*/


/*
Set up form submission behavior.
*/
WebFormDesigner.prototype._setUpFormSubmission = function () {
    var that = this;
    $('#web-form-submit-button').on('click', function(evt) {
        evt.preventDefault ();
        that._refreshForm ();
        var formJSON = auxlib.formToJSON ($('#web-form-designer-form'));
        $.ajax({
            url: that.saveUrl,
            type: 'POST',
            data: formJSON,
            success: function (data, status, xhr) {
                that.saved (data, status, xhr);
            }
        });
        return false;
    });
};

/*
Sets up the web form designer
*/
WebFormDesigner.prototype._init = function () {
    var that = this;
    x2.DEBUG && console.log (this);

    // set up embedded code container behavior
    $('#embedcode').focus(function() {
        $(this).select();
    });
    $('#embedcode').mouseup(function(e) {
        e.preventDefault();
    });
    $('#embedcode').focus();

    // instantiate color pickers
    $.each(that.colorfields, function(i, field) {
        var selector = '#' + field;
        x2.colorPicker.setUp ($(selector));
        x2.DEBUG && console.log ('color change: that = ');
        x2.DEBUG && console.log (that);
        $(selector).on ('change', function () { that.updateParams (); });
    });
    
    // set up form field behavior
    $.each(that.fields, function(i, field) {
        $('#'+field).on('change', function () { that.updateParams (); });
    });

    // set up save web form button behavior
    $('#web-form-save-button').click(function(e) {

        // check form empty input
        if ($.trim($('#web-form-name').val()).length === 0) { // invalid, show errors
            $('#web-form-name').addClass('error');
            $('[for="web-form-name"]').addClass('error');
            $('#web-form-save-button').after('<div class="errorMessage">'+
                that.translations.nameRequiredMsg+'</div>');
            e.preventDefault(); //has no effect
            return false;
        } else { // name validated, remove error messages
            $('#web-form-name').removeClass('error');
            $('[for="web-form-name"]').removeClass('error');
            $('#web-form-save-button').next('.errorMessage').remove ();
        }
    });

    that._setUpFormSubmission ();

    // set up saved form selection behavior
    $('#saved-forms').on('change', function() {
        var id = $(this).val();
        that._showHideDeleteButton ();

        // clear old form, populate form with saved input
        that._clearFields();
        if (id != 0) {
            var match = $.grep(that.savedForms, function(el, i) {
                return id == el.id;
            });
            that._updateFields(match[0]);
        } 

        // update iframe and embedded code
        that.updateParams();
        $('#embedcode').focus();  
        $.each(that.colorfields, function(i, field) {
            if ($('#'+field).val () === '') {
                x2.colorPicker.addCheckerImage ($('#'+field));
            } else {
                x2.colorPicker.removeCheckerImage ($('#'+field));
            }
        });

        // extra behaviors set in child prototype
        that._afterSavedFormsChange ();
    });

    // set up iframe resizing behavior
    $('#iframe_example').data('src',that.iframeSrc);
    $('#iframe_example').resizable({
        start: function(event, ui) {

        },
        stop: function(event, ui) {
            that.updateParams();
            //$(this).removeAttr('style');
        },
        helper: 'ui-resizable-helper',
        resize: function(event, ui) {
        //    $('#iframe_example').width(ui.size.width);
        //    $('#iframe_example').height(ui.size.height);
        //    $('#iframe_example iframe').attr('width', ui.size.width);
        //    $('#iframe_example iframe').attr('height', ui.size.height);
        },
    });

    // set up reset form button behavior
    $('#reset-form').on('click', function(evt) {
        evt.stopPropagation ();
        $("#saved-forms").val("0").change();
        return false;
    });

    that._afterInit ();

    that.updateParams();

    //that._setUpFormDeletion ();
};

WebFormDesigner.prototype._showHideDeleteButton = function () {
    if ($('#saved-forms').find (':selected').val () === '0') {
        $('#delete-form').hide ();
    } else {
        $('#delete-form').show ();
    }
};

/**
 * Sets up behavior of 'Delete Form' button
 */
WebFormDesigner.prototype._setUpFormDeletion = function () {
    var that = this; 
    $('#delete-form').on ('click', function (evt) {
        var formId = $('#saved-forms').val ();
        auxlib.destroyErrorFeedbackBox ($('#saved-forms'));
        $.ajax ({
            url: that.deleteFormUrl,
            type: 'GET',
            data: {
                id: formId, 
            },
            dataType: 'json',
            success: function (data) {
                if (data[0]) {
                    $('#saved-forms').find ('[value="' + formId + '"]').remove ();
                    auxlib.createReqFeedbackBox ({
                        prevElem: $('#delete-form'),
                        message: data[1],
                        disableButton: $('#delete-form')
                    });
                    that._showHideDeleteButton ();
                } else {
                    auxlib.createErrorFeedbackBox ({
                        prevElem: $('#delete-form'),
                        message: data[1],
                    });
                }
            }
        });
    });
};

// override in child prototype
WebFormDesigner.prototype._afterSavedFormsChange = function () {};

// override in child prototype
WebFormDesigner.prototype._afterInit = function () {};


/*
Generates a new iframe with the user-set dimensions and with GET parameters corresponding
to the current form input.
*/
WebFormDesigner.prototype.updateParams = function (iframeContainer) {
    var that = this;
    x2.DEBUG && console.log (that);

    if ($(iframeContainer).data ('ignoreChange')) {
        return;
    }
    var params = [];
    if (that.listId !== null) {
        params.push('lid='+that.listId);
    }

    x2.DEBUG && console.log (that.fields);
    $.each(that.fields, function(i, field) {
        x2.DEBUG && console.log ('getting field: ' + field);
        var value = WebFormDesigner.sanitizeInput($('#'+field).val());
        if (value.length > 0) { params.push(field+'='+value); }
    });

    /* send iframe height to iframe contents view so that iframe contents can be set to correct
    height on iframe load */
    var iframeHeight = $('#iframe_example').height ();
    params.push ('iframeHeight=' + (Math.floor (iframeHeight)));

    var query = this._generateQuery(params);

    var iframeWidth;
    if ($('#iframe_example').find ('iframe').length) {
        iframeWidth = $('#iframe_example').width ();
    } else {
        iframeWidth = 200;
    }

    /* 
    */
    var embedCode = '<iframe name="web-form-iframe" src="' + that.iframeSrc + query +
        '" frameborder="0" scrolling="0" width="' + iframeWidth +  '" height="' + 
        iframeHeight + '"></iframe>';
    $('#embedcode').val(embedCode);

    $('#iframe_example').children ('iframe').remove ();
    $('#iframe_example').append (embedCode);
};

/*
Generates a GET parameter string from the given paramaters array
*/
WebFormDesigner.prototype._generateQuery = function (params) {
    var query = '';
    var first = true;

    for (var i = 0; i < params.length; i++) {
        if (params[i].search(/^[^=]+=[^=]+$/) != -1) {
            if (first) {
                query += '?'; first = false;
            } else {
                query += '&';
            }

            query += params[i];
        }
    }

    /* x2prostart */ 
    // add web form id to GET params so that fields can be retrieved
    query += '&webFormId=' + encodeURIComponent($('#saved-forms').val());
    /* x2proend */

    query = this._appendToQuery (query);

    return query;
};

/* x2prostart */
/*
Returns a dictionary containing custom fields form input values
*/
WebFormDesigner.prototype._getFieldList = function (form) {

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
/* x2proend */

/**
 * Use to refresh form data before submission
 */
WebFormDesigner.prototype._refreshForm = function () {
    /* x2prostart */ 
    var fieldList = this._getFieldList ();

    // set POST data for saving weblead form
    $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
    /* x2proend */ 
};

// override in child prototype
WebFormDesigner.prototype._appendToQuery = function (query) {
    return query;
};

/*
Clear form inputs.
*/
WebFormDesigner.prototype._clearFields = function () {
    var that = this;
    $('#web-form-name').val('');
    $.each(that.fields, function(i, field) {
        $('#'+field).val('');
    });
};

/*
Populate form with form settings
*/
WebFormDesigner.prototype._updateFields = function (form) {
    var that = this;

    that.DEBUG && console.log ('_updateFields');
    that.DEBUG && console.log (form.params);
    $('#web-form-name').val(form.name);
    if (form.params) {
        $.each(form.params, function(key, value) {
            if ($.inArray(key, that.fields) != -1) {
                $('#'+key).val(value);
            }
            if ($.inArray(key, that.colorfields) != -1) {
                $('#'+key).spectrum ("set", $('#'+key).val ());
            }
        });
    }

    this._updateExtraFields (form);
    this._updateCustomFields (form);
};

// override in child prototype
WebFormDesigner.prototype._updateExtraFields = function (form) {
    return;
};

/* x2prostart */
WebFormDesigner._enableTabsForCustomCss = function () {

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
/* x2proend */

/* x2prostart */
WebFormDesigner.prototype._onFieldUpdate = function () {
    var that = this;
    var fieldList = that._getFieldList ();
    $('#fieldList').val(encodeURIComponent(JSON.stringify(fieldList))); 
    $('#web-form-save-button').addClass ('highlight');
};
/* x2proend */

/* x2prostart */
/*
Make custom fields containes sortable, set up their behavior
*/
WebFormDesigner.prototype._setUpSortableCustomFieldsBehavior = function () {
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
/* x2proend */

// override in child prototype
WebFormDesigner.prototype._updateCustomFields = function (form) {
    /* x2prostart */ 
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
    /* x2proend */ 
};

// override in child prototype
WebFormDesigner.prototype._beforeSaved = function () {};

/*
Called on ajax success. Form saved successfully. Alert user and cache the form.
*/
WebFormDesigner.prototype.saved = function (data, status, xhr) {
    var that = this;

    this._beforeSaved ();
    var newForm = $.parseJSON(data);
    if (typeof newForm.errors !== "undefined") { return; }
    this._cacheSavedForm (newForm);
    that.updateParams();
    $('#web-form-save-button').removeClass ('highlight');
    alert(that.translations.formSavedMsg);
    that._showHideDeleteButton ();
}

/*
Cache saved forms on client for fast access on form switch
*/
WebFormDesigner.prototype._cacheSavedForm = function (newForm) {
    var that = this;

    newForm.params = $.parseJSON(newForm.params);
    var index = -1;
    $.each(that.savedForms, function(i, el) {
        if (newForm.id == el.id) {
            index = i;
        }
    });
    if (index != -1) {
        that.savedForms.splice(index, 1, newForm);
    } else {
        that.savedForms.push(newForm);
        $('#saved-forms').append('<option value="'+newForm.id+'">'+newForm.name+'</option>');
    }
    $('#saved-forms').val(newForm.id);
}

