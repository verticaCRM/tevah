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

/**
 * Handles creation of record quick create dialogs
 */

x2.QuickCreate = (function () {

function QuickCreate (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        /**
         * @var string modelType name of X2Model child that has X2QuickCreateBehavior  
         */
        modelType: null,
        /**
         * @var object dialogAttributes dialog settings 
         */
        dialogAttributes: {},
        /**
         * @var object data to pass along with request for quick create form
         */
        data: {},
        /**
         * @var object attributes default attributes of new record
         */
        attributes: {},
        /**
         * @var function success callback called after successful record creation
         */
        success: function () {},
        enableFlash: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (!QuickCreate.createRecordUrls[this.modelType]) throw new Error ('invalid model type');
    this.createRecordUrl = QuickCreate.createRecordUrls[this.modelType];
    this.dialogTitle = QuickCreate.dialogTitles[this.modelType];
    this.openQuickCreateDialog ();
}

QuickCreate.createRecordUrls = {};
QuickCreate.dialogTitles = {};

/**
 * Open record creation dialog 
 */
QuickCreate.prototype.openQuickCreateDialog = function () { 

    var that = this;

    this._dialog = $('<div>');
    this._dialog.dialog ($.extend ({
        title: this.dialogTitle,
        autoOpen: false,
        resizable: true,
        width: '650px',
        show: 'fade',
        hide: 'fade',
        close: function () {
            that._dialog.dialog ('destroy');
            that._dialog.remove ();
        }
    }, this.dialogAttributes));

    var data = $.extend (this.data, {
        x2ajax: true,
        validateOnly: true,
    });
    for (var attrName in this.attributes) {
        data[this.modelType + '[' + attrName + ']'] = this.attributes[attrName];
    }

    $.ajax ({
        type: 'post',
        url: this.createRecordUrl, 
        data: data,
        success: function(response) {
            that._dialog.append(response);
            that._dialog.dialog('open');
            
            auxlib.onClickOutside (
                '.ui-dialog, .ui-datepicker',
                function () { 
                    if ($(that._dialog).closest ('.ui-dialog').length) 
                        that._dialog.dialog ('close'); 
                }, true);
            that._dialog.find('.formSectionHide').remove();
            var submit = that._dialog.find('[type="submit"]');
            var form = that._dialog.find('form');
            $(form).submit (function () {
                that._handleFormSubmission (form);
                return false;
            });
        }
    });
};

QuickCreate.prototype.closeDialog = function () {
    that._dialog.empty ().remove ()
};


QuickCreate.prototype._handleFormSubmission = function (form) {
    if (form.find ('.error').length) return;
    var that = this;
    var formdata = form.serializeArray();

    formdata = formdata.concat ([{
    /* this form data object indicates this is an ajax request 
       note: yii already uses the name 'ajax' for it's ajax calls, so we use 'x2ajax' */
        name: 'x2ajax',
        value: '1'
    }, {
        name: 'quickCreateOnly',
        value: '1'
    }]);

    $.ajax ({
        type: 'post',
        url: this.createRecordUrl, 
        data: formdata, 
        dataType: 'json',
        success: function(response) {
            that._dialog.empty ();
            if (response['status'] === 'success' || response[0] === 'success') {
                that._dialog.remove ();
                if (that.enableFlash)
                    x2.topFlashes.displayFlash (response.message, 'success', 'clickOutside', false);
                that.success (response.attributes);
            } else if (response['status'] === 'userError') {
                if(typeof response['page'] !== 'undefined') {
                    that._dialog.append(response['page']);
                    that._dialog.find('.formSectionHide').remove();
                    that._dialog.find('.create-account').remove();
                    var submit = that._dialog.find('input[type="submit"]');
                    var form = that._dialog.find('form');
                    $(submit).unbind ('click').bind ('click', function() {
                        return that._handleFormSubmission (form);
                    }, true);
                }
            }
        }
    });
};

return QuickCreate;

}) ();
