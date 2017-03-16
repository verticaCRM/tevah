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
 * Manages behavior of the doc viewer profile widget
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function DocViewerProfileWidget (argsDict) {
    var defaultArgs = {
        getItemsUrl: '', // used to populate autocomplete
        getDocUrl: '', // url to request a doc
        docId: '', // the id of the doc currently being viewed
        editDocUrl: '', // url to edit a doc
        canEdit: false, // has permission to edit current doc
        checkEditPermissionUrl: ''
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	SortableWidget.call (this, argsDict);	
}

DocViewerProfileWidget.prototype = auxlib.create (IframeWidget.prototype);


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

DocViewerProfileWidget.prototype._setUpDefaultTextBehavior = function () {
    var that = this;
    this.element.find ('.default-text-container a').click (function (evt) {
        evt.preventDefault ();
        that._selectADocButton.click ();
        return false;
    });
};

/**
 * Show dialog with doc selection form when settings menu item is clicked 
 */
DocViewerProfileWidget.prototype._setUpSelectADocBehavior = function () {
    var that = this; 

    var selectedDocUrl = ''; // set by autocomplete
    var selectedDocId; // set by autocomplete
    var selectedDocLabel; // set by autocomplete

    this._selectADocButton.unbind ('click.selectADoc'); 
    this._selectADocButton.bind ('click.selectADoc', function () {

        auxlib.destroyErrorFeedbackBox ($(that._selectADocDialog).find ('.selected-doc'));
        selectedDocUrl = '';

        // already created
        if ($(this).closest ('.ui-dialog').length) {
            $('#doc-select-button').removeClass ('highlight');
            $(this).dialog ('open');
            return;
        }

        // generate select a doc dialog
        that._selectADocDialog.dialog ({
            title: that.translations['dialogTitle'],
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: that.translations['selectButton'],
                    id: 'doc-select-button',
                    /*
                    Validate input and save/display error
                    */
                    click: function () {
                        if (selectedDocUrl !== '') {
                            that.element.find ('iframe').attr ('src', selectedDocUrl);
                            $(this).dialog ('close');
                            that.setProperty ('docId', selectedDocId);
                            that.docId = selectedDocId;
                            that.changeLabel (selectedDocLabel);
                            that.element.find ('.default-text-container').remove ();
                            that._checkEditPermission ();
                        } else {
                            auxlib.createErrorFeedbackBox ({
                                prevElem: $(that._selectADocDialog).find ('.selected-doc'),
                                message: that.translations['docError']
                            });
                        }
                    }
                },
                {
                    text: that.translations['closeButton'],
                    click: function () { $(this).dialog ('close'); }
                }
            ],
            close: function () {
                that._selectADocDialog.hide ();
            },
            drag: function () {
                $(that._selectADocDialog).find ('.selected-doc').autocomplete ('widget').
                    position ({
                        my: 'left top',
                        at: 'left bottom',
                        of: $(that._selectADocDialog).find ('.selected-doc')
                    });
            }
        });
    }); 

    // instantiate autocomplete with doc items
    $(this._selectADocDialog).find ('.selected-doc').autocomplete ({
        'minLength':'1',
        'source': this.getItemsUrl,
        'select': function (event, ui) {
            $(this).val (ui.item.value);
            selectedDocUrl = that.getDocUrl + '?id=' + ui.item.id;
            selectedDocId = ui.item.id;
            selectedDocLabel = ui.item.label;

            $('#doc-select-button').addClass ('highlight');
            return false; 
        }
    });

    $(this._selectADocDialog).find ('.selected-doc').autocomplete ('widget').
        css ({
            'z-index': 1400
    });
};

DocViewerProfileWidget.prototype._setUpEditBehavior = function () {
    var that = this; 
    $(this.element).find ('.widget-edit-button').unbind ('click.widgetEdit');
    $(this.element).find ('.widget-edit-button').bind ('click.widgetEdit', function (evt) {
        evt.preventDefault ();
        window.location = that.editDocUrl + '?id=' + that.docId;
        return false;
    });
};

/**
 * Detects presence of UI elements (and sets properties accordingly), calls their setup methods
 */
DocViewerProfileWidget.prototype._callUIElementSetupMethods = function () {
    if ($(this.element).find ('.widget-edit-button').length) {
        this._setUpEditBehavior ();
        this._editBehaviorEnabled = true;
    } else {
        this._editBehaviorEnabled = false;
    }

    SortableWidget.prototype._callUIElementSetupMethods.call (this);
};

/**
 * Hides/shows title bar buttons on mouseleave/mouseover 
 */
DocViewerProfileWidget.prototype._setUpTitleBarBehavior = function () {
    var that = this; 
    that._cursorInWidget = false;
    if ($(this.element).find ('.widget-minimize-button').length ||
        $(this.element).find ('.widget-close-button').length) {

        $(this.element).mouseover (function () {
            that._cursorInWidget = true;
            $(that.element).find ('.submenu-title-bar .x2-icon-button').each (function () {
                if ($(this).hasClass ('widget-edit-button') && !that.canEdit) {
                    return true;
                } else {
                    $(this).show ();
                }
            });
        });
        $(this.element).mouseleave (function () {
            that._cursorInWidget = false;
            if (!(that._settingsBehaviorEnabled &&
                  $(that.elementSelector  + ' .widget-settings-menu-content').is (':visible'))) {
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
            }
        });
    }
    if (this.element.find ('.delete-widget-button').length) {
        this._setUpWidgetDeletion ();
    }
};

DocViewerProfileWidget.prototype._hideShowEditButton = function () {
    if (this.canEdit && this._cursorInWidget)
        this._editButton.show ();
    else
        this._editButton.hide ();
};

DocViewerProfileWidget.prototype._checkEditPermission = function () {
    var that = this; 
    $.ajax ({
        method: 'GET',
        url: this.checkEditPermissionUrl,
        data: {
            id: this.docId
        },
        success: function (data) {
            if (data === 'true') {
                that.canEdit = true;
            } else {
                that.canEdit = false;
            }
            that._hideShowEditButton ();
        }
    });
};

DocViewerProfileWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._selectADocButtonSelector = this.elementSelector + ' .select-a-document-button';
    this._selectADocButton = $(this._selectADocButtonSelector);
    this._selectADocDialog = $('#select-a-document-dialog-' + this.widgetUID);
    this._editButton = $(this.element).find ('.widget-edit-button');
    this._iframeElem = this.contentContainer.find ('iframe');
    this._iframeSrc = '';
    this._setUpSelectADocBehavior ();
    this.element.find ('.default-text-container').show ();

    if (this.docId === '') {
        this._setUpDefaultTextBehavior ();
    }
};
