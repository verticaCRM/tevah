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

x2.RecordAliasesWidget = (function () {

function RecordAliasesWidget (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    x2.Widget.call (this, argsDict);
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        baseUrl: '',
        aliasOptions: {},
        aliasTypeIcons: {},
        recordId: null,
        translations: {
            dialogTitle: 'Create Alias',
            cancel: 'Cancel',
            create: 'Create'
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._hideShowButton$ = this.element$.find ('.view-aliases-button');
    this._dropdown$ = this.element$.find ('.alias-dropdown');
    this._addAliasButton$ = this.element$.find ('.new-alias-button');
    this._dialog$ = this.element$.find ('.add-alias-dialog');
    this._init ();
}

RecordAliasesWidget.prototype = auxlib.create (x2.Widget.prototype);

RecordAliasesWidget.prototype._showDropdown = function () {
    var that = this;
    this._dropdown$.show ();
    auxlib.onClickOutside (this.element$.selector + ', .ui-dialog, .ui-button-text', function () {
        that._hideDropdown ();
    }, true);
};

RecordAliasesWidget.prototype._hideDropdown = function () {
    this._dropdown$.attr ('style', '');
    this._dropdown$.hide ();
};

RecordAliasesWidget.prototype._setUpHideShowBehavior = function () {
    var that = this;
    this.element$.find ('.record-name, .view-aliases-button').click (function () {
        if (!that._dropdown$.is (':visible')) {
            that._showDropdown ();
        } else {
            that._hideDropdown ();
        }
        return false;
    });
};

/**
 * Add new alias to dropdown 
 */
RecordAliasesWidget.prototype._addAlias = function (aliasType, alias, id) {
    var newAliasTitle = this.aliasOptions[aliasType];
    var li$ = this._dropdown$.find ('.alias-template').clone ();
    li$.show ().
        removeClass ('alias-template').
        attr ('data-alias-type', newAliasTitle).
        attr ('data-id', id);
    li$.find ('.record-alias').html (alias);
    li$.find ('.record-alias').before (this.aliasTypeIcons[aliasType]);
    this._dropdown$.children ('span').append (li$);
    listItems$ = this._dropdown$.find ('li').not ('.new-alias-button, .alias-template');
    sortedListItems$ = listItems$.sort (function (a, b) {
        var a$ = $(a);
        var b$ = $(b);
        var aliasTypeA = a$.attr ('data-alias-type').toLowerCase ();
        var aliasTypeB = b$.attr ('data-alias-type').toLowerCase ();
        if (aliasTypeA < aliasTypeB) {
            return -1;
        } else if (aliasTypeA === aliasTypeB) {
            if (a$.html () < b$.html ()) {
                return -1;
            } else if (a$.html () === b$.html ()) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    });
    this._dropdown$.find ('li').not ('.new-alias-button, .alias-template').remove ();
    this._dropdown$.children ('span').append (sortedListItems$);
    this._setUpAliasDeletion ();
};

/**
 * Submit alias creation form
 */
RecordAliasesWidget.prototype._createAlias = function () {
    var that = this;
    var data = this._dialog$.serialize ();
    var dataObj = $.deparam (data);
    var aliasType = dataObj['RecordAliases']['aliasType'];
    var alias = dataObj['RecordAliases']['alias'];

    $.ajax ({
        url: this.baseUrl + '/createRecordAlias',
        data: data,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                that._addAlias (aliasType, data.success.alias, data.success.id);
                that._dialog$.dialog ('close');
                x2.forms.clearForm (that._dialog$, true);
                that._dialog$.find ('.alias-type-cell').first ().click ();
            } else {
                that._dialog$.html (data.failure);
                that._bindFormEvents ();
            }
        }
    })
};

RecordAliasesWidget.prototype._deleteAlias = function (aliasId) {
    var that = this;
    $.ajax ({
        url: this.baseUrl + '/deleteRecordAlias?id=' + aliasId,
        success: function (data) {
            if (data === 'success') {
                that._dropdown$.find ('li').filter (function () { 
                    return $(this).attr ('data-id') == aliasId;
                }).remove ();
            } else {
            }
        }
    })
};

RecordAliasesWidget.prototype._openDialog = function () {
    var that = this;
    this._dialog$.dialog ({
        title: this.translations.dialogTitle,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: this.translations.cancel,
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations.create,
                click: function () {
                    that._createAlias ();
                },
                'class': 'highlight'
            }
        ],
        close: function () {
            $(this).dialog ('destroy');
        }
    });

};

RecordAliasesWidget.prototype._setUpDialog = function () {
    var that = this;
    this._addAliasButton$.click (function () {
        that._openDialog ();
        that._dropdown$.hide ();
    });
};

/**
 * Bind event handlers to alias creation form elements 
 */
RecordAliasesWidget.prototype._bindFormEvents = function () {
    var that = this;
    this._dialog$.find ('input[type="radio"]').change (function () {
        that._dialog$.find ('.selected').removeClass ('selected');
        $(this).closest ('.alias-type-cell').children ().addClass ('selected');
    });
    this._dialog$.find ('.alias-type-cell').click (function (evt) {
        $(this).find ('input').prop ('checked', function (i, val) {
            return !val;
        });
        $(this).find ('input').change ();
    });
    this._dialog$.find ('.alias-type-cell input').click (function (evt) {
        evt.stopPropagation ();
    });

};

RecordAliasesWidget.prototype._setUpAliasDeletion = function () {
    var that = this; 
    this._dropdown$.find ('.delete-alias-button').click (function () {
        var aliasId = $(this).closest ('li').attr ('data-id');
        auxlib.confirm (function () {
            that._deleteAlias (aliasId);
        }, {
            title: that.translations.confirmDeletionTitle, 
            message: that.translations.confirmDeletion, 
            cancel: that.translations.cancel,
            confirm: that.translations.OK
        });
    });
};

RecordAliasesWidget.prototype._showSkypeTooltip = function (li$) {
    if (li$.attr ('data-hasqtip')) return;

    var that = this;
    li$.qtip ({
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/site/getSkypeLink',
                    data: { 
                        'usernames[]': $.trim (li$.find ('.record-alias').html ())
                    },
                    method: "get"
                }).then (function (content) {
                    api.set ('content.text', content);
                });
                return that.translations.skypeQtipLoadingText;
            }, 
        },
        style: {
            classes: 'skype-qtip',
            def: false,
            tip: {
                corner: true,
            }
        },
        show: {
            ready: true,
            event: 'click'
        },
        hide: {
            event: 'mouseleave',
            fixed: true,
            delay: 200
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: li$,
            effect: false
        }
    });
};

RecordAliasesWidget.prototype._setUpSkypeLinks = function () {
    var that = this;
    this._dropdown$.on ('click', 'li', function () {
        console.log ('click'); 
       console.log ('$(this) = ');
        console.log ($(this));

        console.log ($(this).attr ('data-alias-type'));
        if ($(this).attr ('data-alias-type') === 'Skype') {
            that._showSkypeTooltip ($(this));
        }
    });
};

RecordAliasesWidget.prototype._init = function () {
    this._setUpHideShowBehavior ();
    this._setUpDialog ();
    this._setUpAliasDeletion ();
    this._bindFormEvents ();
    this._setUpSkypeLinks ();
};

return RecordAliasesWidget;

}) ();
