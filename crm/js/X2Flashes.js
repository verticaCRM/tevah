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
 * exclusively to X2Engine.  *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/


x2.Flashes = (function () {

/**
 * Manages x2 gridview mass action actions and ui element behavior  
 */

function X2Flashes (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        containerId: null, // used to create container to hold flashes
        translations: [], 
        expandWidgetSrc: '', // image src
        collapseWidgetSrc: '', // image src
        closeWidgetSrc: '', // image src
        successFadeTimeout: 3000, // time before success flashes begin to fade out
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.$container = null;
    this._successFlashFadeTimeout = null;

    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/


/*
Private instance methods
*/

/***********************************************************************
* Flashes setup functions
***********************************************************************/

/**
 * Display flashes of a given type
 * @param string key the type of flash ('notice' | 'error' | 'success')
 * @param array of strings flashes flash messages which will be displayed
 */
X2Flashes.prototype._displayKeyFlashes = function (key, flashes) {
    var that = this;
    that.DEBUG && console.log ('x2.massActions._displayKeyFlashes');
    that.DEBUG && console.log ('flashes = ');
    that.DEBUG && console.log (flashes);

    var flashNum = flashes.length;
    var hideList = false;
    var $flashContainer = this[key + 'container'];
    $flashContainer.show ();
   that.DEBUG && console.log ('$flashContainer = ');
    that.DEBUG && console.log ($flashContainer);


    if (flashNum > 3) { // show header and make flash list expandable

        // add list header
        $flashContainer.append (
            $('<p>', {
                'class': 'flash-list-header left',
                text: that.translations[key + 'FlashList'] + ' ' + flashNum + ' ' +
                    that.translations[key + 'ItemName']
            }),
            $('<img>', {
                'class': 'flash-list-left-arrow',
                'src': that.expandWidgetSrc,
                'alt': '<'
            }),
            $('<img>', {
                'class': 'flash-list-down-arrow',
                'style': 'display: none;',
                'src': that.collapseWidgetSrc,
                'alt': 'v'
            })
        );

        // set up flashes list expand and collapse behavior
        $flashContainer.find ('.flash-list-left-arrow').
            click (function () {

            $(this).hide ();
            $(this).next ().show ();
            $flashContainer.find ('.x2-flashes-list').show ();
        });
        $flashContainer.find ('.flash-list-down-arrow').
            click (function () {

            $(this).hide ();
            $(this).prev ().show ();
            $flashContainer.find ('.x2-flashes-list').hide ();
        });

        hideList = true;
    }

    // build flashes list
    var $flashList = $('<ul>', {
        'class': 'x2-flashes-list' + (hideList ? '' : ' x2-flashes-list-no-style'),
        style: (hideList ? 'display: none;' : '')
    });
    $flashContainer.append ($flashList);
    for (var i in flashes) {
        that.DEBUG && console.log ('x2.massActions._displayKeyFlashes: i = ' + i);
        $flashContainer.find ('.x2-flashes-list').append ($('<li>', {
            text: flashes[i]
        }));
    }

    if (key === 'success') { // other types of flash containers have close buttons
        //if (that._successFlashFadeTimeout) window.clearTimeout (that._successFlashFadeTimeout);
        /*that._successFlashFadeTimeout = */setTimeout (
            function () { 
                $flashContainer.fadeOut (3000, function () {
                    $flashContainer.remove ();
                });
            }, that.successFadeTimeout);
    }
}

/**
 * Append flash section container div to parent element
 * @param string key the type of flash
 * @param object parent the jQuery object for the flashes container associated with key
 */
X2Flashes.prototype._appendFlashSectionContainer = function (key, parent) {
    var that = this; 
    var $flashContainer = 
        $('<div>', {
            'class': 'flash-' + key,
            style: 'display: none;'
        })
    $(parent).append ($flashContainer);

    // add close button, not needed for success flash container since it fades out
    if (key === 'notice' || key === 'error') {
        $flashContainer.append (
            $('<img>', {
                //id: key + '-container-close-button',
                'class': 'right',
                title: that.translations['close'],
                'src': that.closeWidgetSrc,
                alt: '[x]'
            })
        );
    
        // set up close button behavior
        $flashContainer.find ('img').click (function () {
            $flashContainer.fadeOut (function () {
                $flashContainer.remove ();
            });
        });
    }
    this[key + 'container'] = $flashContainer;
};

/**
 * Build the flash container, fill it with given flashes
 * @param dictionary flashes keys are the type of flash ('success', 'notice', 'error'), values
 *  are arrays of messages
 */
X2Flashes.prototype.displayFlashes = function (flashes) {
    var that = this; 
    that.DEBUG && console.log ('x2.massActions._displayFlashes: flashes = ');
    that.DEBUG && console.log (flashes);
    if (!flashes['success'] && !flashes['notice'] && !flashes['error']) return;

    this.$container.show ();
    // remove previous flashes container
    /*if ($('#x2-gridview-flashes-container').length) {
        $('#x2-gridview-flashes-container').remove ();
    }*/

    // fill container with flashes
    if (flashes['success'] && flashes['success'].length > 0) {
        that._appendFlashSectionContainer ('success', this.$container);
        var successFlashes = flashes['success'];
        that._displayKeyFlashes ('success', successFlashes);
    }
    if (flashes['notice'] && flashes['notice'].length > 0) {
        that._appendFlashSectionContainer ('notice', this.$container);
        var noticeFlashes = flashes['notice'];
        that._displayKeyFlashes ('notice', noticeFlashes);
    }
    if (flashes['error'] && flashes['error'].length > 0) {
        that._appendFlashSectionContainer ('error', this.$container);
        var errorFlashes = flashes['error'];
        that._displayKeyFlashes ('error', errorFlashes);
    }
    $('#content-container').css ('margin-bottom', this.$container.height ());

};

/**
 * Checks if flashes container should be made sticky and if so, makes it sticky
 */
X2Flashes.prototype._checkFlashesSticky = function () {
    var that = this; 

    if (this.$container.position ().top > 
        $('#content-container').position ().top + $('#content-container').height ()) {
         this.$container.removeClass ('fixed-flashes-container');
        $('#content-container').css ('margin-bottom', '');
        $(window).unbind ('scroll._checkFlashesSticky').
            bind ('scroll._checkFlashesSticky', function () { 
                return that._checkFlashesUnsticky (); });
    } 
};

/**
 * Checks if flashes container should be made unsticky and if so, unsticks it
 */
X2Flashes.prototype._checkFlashesUnsticky = function () {
    var that = this; 

    if (this.$container.offset ().top - $(window).scrollTop () >
        ($(window).height () - 5) - this.$container.height ()) {

        this.$container.addClass ('fixed-flashes-container');
        $('#content-container').css ('margin-bottom', this.$container.height ());
        $(window).unbind ('scroll._checkFlashesUnsticky').
            bind ('scroll._checkFlashesUnsticky', function () { that._checkFlashesSticky (); });
    } else {
        return false;
    }
};


/**
 * set up mass action ui behavior, this gets run on every grid update
 */
X2Flashes.prototype._init = function () {
    var that = this; 

    // build new flashes container
    this.$container = $('<div>', { 
        id: this.containerId,
        'class': 'flashes-container'
    });
    $('#content-container').append (this.$container);
    
    $('#content-container').attr (
        'style', 'padding-bottom: ' + this.$container.height () + 'px;');
    this.$container.width ($('#content-container').width () - 10);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        that.$container.width ($('#content-container').width () - 10);
    });

    that.DEBUG && console.log ('this.$container.positoin ().top = ');
    that.DEBUG && console.log (this.$container.position ().top);

    if (!that._checkFlashesUnsticky ()) {
        $(window).unbind ('scroll._X2Flashes', that._checkFlashesUnsticky).
            bind ('scroll._X2Flashes', function () { that._checkFlashesUnsticky (); });
    }
};

return X2Flashes;
}) ();
