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

x2.MassUpdateFields = (function () {

function MassUpdateFields (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        massActionName: 'updateFields'
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.MassAction.call (this, argsDict);
    this.progressBarLabel = this.translations['updated'];
    this.progressBarDialogTitle = this.translations['updateFieldprogressBarDialogTitle'];
    this.dialogTitle = this.massActionsManager.translations['updateField'];
    this.goButtonLabel = this.massActionsManager.translations['update'];
    this._init ();
}

MassUpdateFields.prototype = auxlib.create (x2.MassAction.prototype);

/**
 * @return object fields Returns selected field and field value in format expected by mass actions
 *  action
 */
MassUpdateFields.prototype.getFields = function () {
    var that = this;
    var updateFieldDialogSelector = '#' + this.dialogElem$.attr ('id');
    var fieldFieldSelector = $(updateFieldDialogSelector + ' .update-field-field-selector');
    var fieldName = $(fieldFieldSelector).val ();
    var fields = {};
    if ($(fieldFieldSelector).next ().find ('.star-rating-control').length) { // CStarInput Widget

        // count stars
        fields[fieldName] = $(fieldFieldSelector).next ().find ('.star-rating-control').
			find ('.star-rating-on').length;
    } else {
        var tempName,
            tempVal,
            children = $(fieldFieldSelector).next().children();

        if (fieldName === 'associationName') {
            // Gather the association data
            if (children[0].name)
                tempName = children[0].name.replace(/^.*\[(.*)\]/, "$1");
            if (tempVal = $(children[0]).find(':selected').val())
                fields[tempName] = tempVal;
            fields[fieldName] = children.find('input').val();
        } else {
            var inputField = children.first ();
            if ($(inputField).attr ('type') === 'hidden') {
                inputField = $(inputField).next ();
            }
            if ($(inputField).length) tempVal = $(inputField).val ();
            fields[fieldName] = tempVal;
        }
    }
    return fields;
};

MassUpdateFields.prototype.getExecuteParams = function () {
    var params = x2.MassAction.prototype.getExecuteParams.call (this);
    params['fields'] = this.getFields ();
    return params;
};

MassUpdateFields.prototype.openDialog = function () {
    var that = this;
    this.dialogElem$.find ('.update-field-field-selector').unbind ('change').change (function () {

        that.DEBUG && console.log ('update-field-field-selector: change');
        var inputName = $(this).val ();
        that._getUpdateFieldInput (inputName);
    });
    x2.MassAction.prototype.openDialog.call (this);
};

/**
 * Used by update field mass action to dynamically construct field form
 * @param string inputName the name of the X2Fields field
 */
MassUpdateFields.prototype._getUpdateFieldInput = function (inputName) {
    var that = this; 
    that.DEBUG && console.log ('removing old input');
    
    var updateFieldDialogSelector = '#' + that.gridId + '-update-field-dialog';

    this.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container').children ().remove ();
    this.dialogElem$.find ('.update-fields-inputs-container').
        find ('.update-fields-field-input-container').append ($('<div>', {
            'class': 'x2-loading-icon updating-field-input-anim'
        }));
    $.ajax({
        url: that.massActionsManager.updateFieldInputUrl,
        dataType: 'html',
        type:'get',
        data:{
            modelName: that.massActionsManager.modelName,
            inputName: inputName,
        },
        success: function (response) { 
            that.DEBUG && console.log ('getUpdateFieldInput: ajax ret: ' + response);
            if (response !== '') { // success
                that.dialogElem$.find ('.update-fields-inputs-container').
                    find ('.update-fields-field-input-container').children ().remove ();
                that.DEBUG && console.log ('replacing old input');
                that.dialogElem$.find ('.update-fields-inputs-container').
                    find ('.update-fields-field-input-container').html (response);
            }
        }
    });
};

MassUpdateFields.prototype._init = function () {
    var that = this;
    //$(function () { setTimeout (function () { that.superExecute (); }, 500); });
};

return MassUpdateFields;

}) ();

