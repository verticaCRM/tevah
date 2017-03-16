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
 * Manages behavior of email inbox contact and non-contact tooltips
 */

x2.EmailInboxesQtipManager = (function () {

function EmailInboxesQtipManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.X2GridViewQtipManager.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        translations: {
            Email: 'Email',
            'Create contact': 'Create contact',
            'action': 'action'
        }
    };
    this.nonContactClass = '.non-contact-entity-tag';
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

EmailInboxesQtipManager.prototype = auxlib.create (x2.X2GridViewQtipManager.prototype);

/**
 * refresh both contact and non-contact tooltips 
 */
EmailInboxesQtipManager.prototype.refresh = function () {
    var that = this;
    x2.X2GridViewQtipManager.prototype.refresh.call (this);

	$(this.nonContactClass).each(function (i) {
		var email = $(this).attr("data-email");
		var name = $(this).text ();

        $(this).qtip(that._getNonContactConfig ($(this), email, name));
	});
};

/**
 * add shared config to contact tooltip 
 */
EmailInboxesQtipManager.prototype._getConfig = function (elem, recordId) {
    var that = this;
    var template$ = $(
        '<div>' +
            '<div class="contact-details">' +
            '</div>' + 
            '<div class="contact-action-button x2-button qtip-button">' + 
                '<span class="fa fa-plus highlight-text"></span>' +
                '<span>' + this.translations['action'] + '</span>' +
            '</button' +
        '</div>');

    var config = $.extend (this._getSharedConfig (elem), {
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+that.modelType+'/qtip',
                    data: { 
                        id: recordId,
                        suppressTitle: that.dataAttrTitle ? 1 : 0
                    },
                    method: "get"
                }).then (function (content) {
                    var content$ = template$.clone ();
                    content$.find ('.contact-details').html (content);
                    api.set ('content.text', content$.get (0).outerHTML);
                });
                return $('<div>', {
                    'class': 'qtip-loading-text',
                    text: that.loadingText
                });
            }, 
        },
        style: {
            classes: 'x2-qtip email-inboxes-qtip',
            tip: {
                corner: true,
            }
        }
    });
    return config;
};

/**
 * @return object config settings shared by contact and non-contact tooltips 
 */
EmailInboxesQtipManager.prototype._getSharedConfig = function (elem) {
    return {
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        },
        hide: {
            fixed: true,
            delay: 100
        },
        show: {
            delay: 800
        }
    }
};

EmailInboxesQtipManager.prototype._getNonContactConfig = function (elem, email, name) {
    var that = this;

    var template$ = $(
        '<div>' +
            '<div>' +
                '<h2 class="non-contact-name"></h2>' +
                '<div>' + 
                    this.translations['Email'] + 
                        ':&nbsp;<strong class="non-contact-email"></strong>' + 
                '</div>' + 
            '</div>' + 
            '<button class="new-contact-from-entity-button x2-button">' + 
                this.translations['Create contact'] +
            '</button' +
        '</div>');

    var config = $.extend (this._getSharedConfig (elem), {
        content: {
            text: function (event, api) {
                var content$ = template$.clone ();
                content$.find ('.non-contact-name').text (name);
                content$.find ('.non-contact-email').text (email);
                return content$.html ();
            }, 
        },
        style: {
            classes: 'x2-qtip non-contact-qtip',
            tip: {
                corner: true,
            }
        }
    });
    if (that.dataAttrTitle) {
        config.content.title = $(elem).attr ('data-qtip-title');
    }
    return config;
};

EmailInboxesQtipManager.prototype._setUpQuickCreateButtonBehavior = function () {
    var that = this;

    // close tooltip and open quick create dialog when quick create button is clicked
    $(document).off ('click._setUpQuickCreateButtonBehavior', '.new-contact-from-entity-button').
        on ('click._setUpQuickCreateButtonBehavior', '.new-contact-from-entity-button', 
            function () {

        var qtip = $(this).closest ('.qtip').data ('qtip');
        var link = qtip.options.position.target;
        var email = $(link).attr ('data-email');
        var fullName = $.trim ($(link).text ());
        var attributes = {};
        attributes.email = email;
        var pieces = fullName.split (/[ ]+/);
        if (pieces.length === 2) {
            attributes.firstName = pieces[0];
            attributes.lastName = pieces[1];
        } else {
            attributes.firstName = fullName;
        }
        new x2.QuickCreate ({
            modelType: 'Contacts',
            attributes: attributes,
            success: function (modelId) {
                that._convertNonContactLinks (modelId);
            }
        });
        qtip.hide ();
        return false;
    });

    // set up quick action creation
    $(document).off ('click._setUpQuickCreateButtonBehavior2', '.contact-action-button').
        on ('click._setUpQuickCreateButtonBehavior2', '.contact-action-button', 
            function () {

        var qtip = $(this).closest ('.qtip').data ('qtip');
        var link = qtip.options.position.target;
		var recordId = $(link).attr("href").match(/\d+$/)[0];
        var attributes = {};
        attributes.associationId = recordId;
        attributes.associationType = 'contacts';
        attributes.associationName = $(link).text ();
        new x2.QuickCreate ({
            modelType: 'Actions',
            attributes: attributes,
            success: function (modelId) {
            }
        });
        qtip.hide ();
        return false;
    });
};

/**
 * Converts non-contact links into a contact links
 * @param object attributes attributes of newly created record
 */
EmailInboxesQtipManager.prototype._convertNonContactLinks = function (attributes) {
    var that = this;
    var newLink$ = $('<a>', {
        href: yii.scriptUrl + '/contacts/id/' + attributes.id, 
        text: attributes.firstName + ' ' + attributes.lastName,
        'class': 'contact-name'
    });
    $(newLink$).qtip (that._getConfig (newLink$, attributes.id));
    $('.non-contact-entity-tag').each (function () {
        var link$ = $(this);
        if (link$.attr ('data-email') === attributes.email) {
            link$.qtip ('destroy', true);
            link$.replaceWith (newLink$.clone ());
        }
    });
    $('#email-list').data ('x2-emailInboxesGridSettings').rebindContactLinkEventHandler ();
    this.refresh ();
};

EmailInboxesQtipManager.prototype._init = function () {
    this._setUpQuickCreateButtonBehavior ();
};


return EmailInboxesQtipManager;

}) ();
