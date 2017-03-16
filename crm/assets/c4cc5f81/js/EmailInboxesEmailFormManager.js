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

x2.EmailInboxesEmailFormManager = (function () {

function EmailInboxesEmailFormManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        enableResizability: false,
        translations: {
            'Forwarded message': 'Forwarded message',
            'From': 'From',
            'Date': 'Date',
            'Subject': 'Subject',
            'To': 'To'
        }
    };
    this._emailInboxGridViewManager = $('#email-list').data ('emailInboxesGridSettings');
    argsDict.translations = $.extend (defaultArgs.translations, argsDict.translations);
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.InlineEmailEditorManager.call (this, argsDict);
}

EmailInboxesEmailFormManager.prototype = auxlib.create (x2.InlineEmailEditorManager.prototype);

EmailInboxesEmailFormManager.prototype.addForwardingHeader = function (
    message$, from, date, subject, to) {

    var header$ = $('<div>', {
        text: '---------- ' + this.translations['Forwarded message'] + ' ----------'
    })
    header$.append ($('<div>', {
        text: this.translations.From + ': ' + from
    }));
    header$.append ($('<div>', {
        text: this.translations['Date'] + ': ' + date
    }));
    header$.append ($('<div>', {
        text: this.translations['Subject'] + ': ' + subject
    }));
    header$.append ($('<div>', {
        text: this.translations['To'] + ': ' + to
    }));
    header$.append ($('<br><br><br>'));
    return header$.append (message$);
};

/**
 * Wrap html in styled blockquotes 
 */
EmailInboxesEmailFormManager.prototype.quoteText = function (text, date, author) {
    var quoteHeader$ = $('<div>', {
        text: date + ', ' + author + ': '
    });
    return quoteHeader$.after ($('<blockquote>').append (text));
};

/**
 * Overrides parent method 
 */
EmailInboxesEmailFormManager.prototype.clearForm = function () {
    x2.InlineEmailEditorManager.prototype.clearForm.call (this);
    $('#email-message').val ('');
    $('input[name="InlineEmail[to]"]').val('').blur ();
    $('input[name="InlineEmail[cc]"]').val('').blur ();
    $('input[name="InlineEmail[subject]"]').val('').blur ();
    $('input[name="InlineEmail[bcc]"]').val('').blur ();
};

/**
 * Overrides parent method 
 */
EmailInboxesEmailFormManager.prototype.afterSend = function (data) {
    x2.topFlashes.displayFlash (data.message, 'success');
    this.clearForm ();
    x2.Notifs.updateHistory();
    history.back ();
};

EmailInboxesEmailFormManager.prototype.closeForm = function () {
    if ($('#reply-form').is (':visible')) {
        this.clearForm ();
        $('#reply-form').hide ();
    }
};

/**
 * Overrrides parent method. Since the reply and compose pages are never accessed on page load, 
 * browser state can be used to access the previously viewed page.
 */
EmailInboxesEmailFormManager.prototype._setUpCloseFunctionality = function () {
    var that = this;
    this.element.find ('.cancel-send-button').click (function () {
        history.back (); 
        that.clearForm ();
        return false;
    });
};


return EmailInboxesEmailFormManager;

}) ();

