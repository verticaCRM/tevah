
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
 * Prototype for publisher tab. 
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherTab = (function () {

function PublisherTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {},
        id: null, // id of element containing tab contents
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);

    x2.Widget.call (this, argsDict);

    this._elemSelector = this.resolveIds ('#' + this.id);
    this.publisher = null;
    this._init ();
}

PublisherTab.prototype = auxlib.create (x2.Widget.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

PublisherTab.prototype.submit = function (publisher, form) {
    var that = this;

    x2.forms.clearErrorMessages ($(form));

    // submit tab contents
    $.ajax ({
        url: publisher.publisherCreateUrl,
        type: 'POST',
        data: form.serialize (),
        dataType: 'json',
        success: function (data) {
            if (typeof data['redirect'] !== 'undefined') {

                window.location = data['redirect']
                return;
            }
            if (typeof data['error'] !== 'undefined') {
                $(form).find ('.form').append (x2.forms.errorSummary ('', data));
                $(that._elemSelector).find ('[name="Actions\\[associationName\\]"]').
                    addClass ('error');
                $(form).find ('input.hightlight').removeClass ('highlight');
            } else {
                publisher.updates();
                publisher.reset();
                if ($(that._elemSelector).closest ('.ui-dialog').length) {
                    // if tab is in a transactional widget dialog
                    $(that._elemSelector).closest ('.ui-dialog').remove ();
                }
            }
        }
    });

};

/**
 * Clears tab's form inputs 
 */
PublisherTab.prototype.reset = function () {
    var that = this;
    x2.forms.clearForm (this._element, true);
};

/**
 * Disables tab's form inputs 
 */
PublisherTab.prototype.disable = function () {
    var that = this;
    x2.forms.disableEnableFormSubsection (this._element, true);
};

/**
 * Enables tab's form inputs 
 */
PublisherTab.prototype.enable = function () {
    var that = this;
    that.DEBUG && console.log ('enable');
    x2.forms.disableEnableFormSubsection (this._element, false);
};

/**
 * Blurs tab
 */
PublisherTab.prototype.blur = function () {
    $(this._elemSelector).find ('.action-description').animate({"height":22},300);
};

/**
 * Focus tab 
 */
PublisherTab.prototype.focus = function () {
};


/**
 * @param Bool True if form input is valid, false otherwise
 */
PublisherTab.prototype.validate = function () {
    x2.forms.clearErrorMessages (this._element);
    var actionDescription$ = this._element.find ('.action-description');

    if (actionDescription$.hasClass ('x2-required') && actionDescription$.val () === '') {

        actionDescription$.parent ().addClass ('error');
        x2.forms.errorSummaryAppend (this._element, this.translations['beforeSubmit']);
        return false;
    } else {
        return true;
    }
};

PublisherTab.prototype.run = function () {
    var that = this;

    that._element = $(that._elemSelector);
    x2.forms.setDefaults (that._element);
    that._setUpActionDescriptionBehavior ();
};

/*
Private instance methods
*/

/**
 * Expand action description textarea on click
 */
PublisherTab.prototype._setUpActionDescriptionBehavior = function () {
    var that = this;
    that.DEBUG && console.log ('_setUpActionDescriptionBehavior');
    this._element.find ('.action-description').click (function () {
        that.DEBUG && console.log ('_setUpActionDescriptionBehavior.click'); 
        $(this).height (80);
    });
};

PublisherTab.prototype._init = function () {
    var that = this;
    $(function () {
        that.run ();
    });
};

return PublisherTab;

}) ();
