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

x2.RelationshipsGraphQtipManager = (function () {

function RelationshipsGraphQtipManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        qtipSelector: '',
        translations: {
            loadingText: 'Loading...',
            'View record': 'View record'
        }
    };
    x2.QtipManager.call (this, argsDict);
    auxlib.applyArgs (this, defaultArgs, argsDict);
}

RelationshipsGraphQtipManager.prototype = auxlib.create (x2.QtipManager.prototype);

RelationshipsGraphQtipManager.prototype._getConfig = function (elem$) {
    var that = this;
    var elem = elem$.get (0);
    var type = d3.select (elem).attr ('data-type').toLowerCase ();
    var id = d3.select (elem).attr ('data-id');
    var template$ = $(
        '<div class="graph-qtip-inner">' +
            '<div class="qtip-record-details">' +
            '</div>' + 
            '<a href="' + yii.scriptUrl + '/' + type + '/' + id + 
                '" class="view-record-button x2-button">' + 
                this.translations['View record'] +
            '</a>' +
        '</div>');

    var config = {
        hide: {
            fixed: true,
            delay: 100
        },
        show: {
            delay: 800
        },
        content: {
            text: function (event, api) {
                $.ajax ({
                    url: yii.scriptUrl+'/'+type+'/qtip',
                    data: { 
                        id: id,
                    },
                    method: "get"
                }).then (function (content) {
                    var content$ = template$.clone ();
                    content$.find ('.qtip-record-details').append (content);
                    api.set ('content.text', content$.get (0).outerHTML);
                });
                return $('<div>', {
                    text: that.translations.loadingText,
                    style: 'padding: 3px 5px'
                });
            }, 
        },
        style: {
            classes: 'x2-qtip',
            tip: {
                corner: true,
            }
        },
        position: {
            viewport: $(window),
            my: 'top center',
            at: 'bottom center',
            target: $(elem),
            effect: false
        },
    };

    return config;
};

return RelationshipsGraphQtipManager;

}) ();
