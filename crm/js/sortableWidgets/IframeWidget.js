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
 * Manages behavior of the iframe widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function IframeWidget (argsDict) {
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

IframeWidget.prototype = auxlib.create (SortableWidget.prototype);


/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Hide iframe to prevent lag 
 */
IframeWidget.prototype.onDragStart = function () {
    // prevent default text from shifting when iframe is hidden
    this.contentContainer.height (this.contentContainer.height ());

    this._iframeElem.hide ();
    SortableWidget.prototype.onDragStart.call (this);
};

IframeWidget.prototype.onDragStop = function () {
    this.contentContainer.height ('');
    this.contentContainer.width ('');
    this._iframeElem.show ();
    SortableWidget.prototype.onDragStop.call (this);
};

/*
Private instance methods
*/

/**
 * Show dialog with doc selection form when settings menu item is clicked 
 */
IframeWidget.prototype._setUpChangeUrlBehavior = function () {
    var that = this; 

    var selectedDocUrl = ''; // set by autocomplete
    var selectedDocLabel; // set by autocomplete

    this._changeUrlButton.unbind ('click._setUpChangeUrlBehavior'); 
    this._changeUrlButton.bind ('click._setUpChangeUrlBehavior', function () {

        auxlib.destroyErrorFeedbackBox ($(that._changeUrlDialog).find ('.iframe-url'));
        selectedDocUrl = '';

        // already created
        if ($(this).closest ('.ui-dialog').length) {
            $('#change-url-submit-button-' + that.widgetUID).removeClass ('highlight');
            $(this).dialog ('open');
            return;
        }

        // generate select a doc dialog
        that._changeUrlDialog.dialog ({
            title: that.translations['dialogTitle'],
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: that.translations['closeButton'],
                    click: function () { $(this).dialog ('close'); }
                },
                {
                    text: that.translations['selectButton'],
                    id: 'change-url-submit-button-' + that.widgetUID,
                    /*
                    Validate input and save/display error
                    */
                    click: function () {
                        var url = $.trim ($(that._changeUrlDialog).find ('.iframe-url').val ());
                        auxlib.destroyErrorFeedbackBox (
                            $(that._changeUrlDialog).find ('.iframe-url'));

                        if (url !== '') {
                            if (!url.match (/^http:\//)) url = 'http://' + url;
                            that.element.find ('iframe').attr ('src', url);
                            that.setProperty ('url', url);
                            $(this).dialog ('close');
                        } else {
                            auxlib.createErrorFeedbackBox ({
                                prevElem: $(that._changeUrlDialog).find ('.iframe-url'),
                                message: that.translations['urlError']
                            });
                        }
                    }
                }
            ],
            close: function () {
                that._changeUrlDialog.hide ();
            },
        });

        that._changeUrlDialog.find ('.iframe-url').keydown (function () {
            $('#change-url-submit-button-' + that.widgetUID).addClass ('highlight'); 
        });
        that._changeUrlDialog.find ('.iframe-url').change (function () {
            if ($(this).val () === '') 
                $('#change-url-submit-button-' + that.widgetUID).removeClass ('highlight'); 
        });
    }); 
};

/**
 * Update iframe height on widget resize 
 */
IframeWidget.prototype._resizeEvent = function () {
    var that = this; 
    that._iframeElem.attr ('height', that.contentContainer.height ());
};

/**
 * Save iframe height on resize stop 
 */
IframeWidget.prototype._afterStop = function () {
    var that = this; 
    that.setProperty ('height', that._iframeElem.attr ('height'));
};

/**
 * Places a div over the iframe so that it doesn't interfere with mouse dragging 
 */
IframeWidget.prototype._turnOnSortingMode = function () {
    this._iframeOverlay = $('<div>', {
        width: this.contentContainer.width (),
        height: this.contentContainer.height (),
        css: {
            position: 'absolute',
            'z-index': 100
        }
    });
    this.contentContainer.append (this._iframeOverlay);
    this._iframeOverlay.position ({
        my: 'left top',
        at: 'left top',
        of: this.contentContainer
    });
};

/**
 * removes iframe overlay created by _turnOnSortingMode ()
 */
IframeWidget.prototype._turnOffSortingMode = function () {
    this._iframeOverlay.remove ();
};

IframeWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._changeUrlSelector = this.elementSelector + ' .change-url-button';
    this._changeUrlButton = $(this._changeUrlSelector);
    this._changeUrlDialog = $('#change-url-dialog-' + this.widgetUID);
    this._iframeElem = this.contentContainer.find ('iframe');
    this._iframeSrc = '';
    this._setUpChangeUrlBehavior ();
};

