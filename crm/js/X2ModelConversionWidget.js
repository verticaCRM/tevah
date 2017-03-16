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
 * Front end of X2ModelConversionWidget.php
 */

x2.X2ModelConversionWidget = (function () {

function X2ModelConversionWidget (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        // button to convert record
        buttonSelector: null,
        translations: {},
        // id of model to convert
        modelId: null,
        // class to convert model to
        targetClass: null,
        // conversion error summary
        errorSummary: null,
        conversionIncompatibilityWarnings: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._button$ = $(this.buttonSelector);
    this._conversionWarningDialog$ = $('#' + this.namespace + 'conversion-warning-dialog');
    this._init ();
}

X2ModelConversionWidget.prototype = auxlib.create (x2.Widget.prototype);

/**
 * Call convert action or, if incompatibility warnings are present, display a confirmation dialog 
 */
X2ModelConversionWidget.prototype._convert = function () {
    var that = this;
    var pathname = window.location.href; 

    pathname = pathname.replace('/id/'+this.modelId, '/');
    pathname = pathname.replace('/'+this.modelId, '/');

    // no incompatibilities present. convert the lead
    if (!this.conversionIncompatibilityWarnings.length) {
        window.location = pathname + 'convert?id=' + this.modelId + '&targetClass=' + this.targetClass;
        return false;
    }

    if (this._conversionWarningDialog$.closest ('.ui-dialog').length) {
        this._conversionWarningDialog$.dialog ('open');
    } else {
        // show the warning dialog to the user
        this._conversionWarningDialog$.dialog ({
            title: this.translations.conversionWarning,
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: this.translations.convertAnyway,
                    click: function () {
                        window.location = pathname + 'convert?force=1&id=' + that.modelId + '&' +
                            'targetClass=' + that.targetClass;
                    }
                },
                {
                    text: this.translations.Cancel,
                    click: function () {
                        that._conversionWarningDialog$.dialog ('close');
                    }
                }
            ]
        });
    }
    return false;
};

X2ModelConversionWidget.prototype._setUpButtonBehavior = function () {
    var that = this;
    this._button$.click (function () {
        that._convert ();
    });
};

X2ModelConversionWidget.prototype._init = function () {
    this._setUpButtonBehavior ();
    if (this.conversionFailed) {
        $('#main-column').append (this.errorSummary);
    }
};

return X2ModelConversionWidget;

}) ();
