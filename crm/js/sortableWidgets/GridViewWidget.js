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
 * Manages behavior of grid widgets
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function GridViewWidget (argsDict) {
    var defaultArgs = {
        showHeader: true,
        hideFullHeader: false,
        compactResultsPerPage: false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
	SortableWidget.call (this, argsDict);	
    this.gridElem$ = this.element.find ('.x2-gridview');
}

GridViewWidget.prototype = auxlib.create (SortableWidget.prototype);


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

GridViewWidget.prototype.refresh = function () {
    this._refreshGrid ();
};

GridViewWidget.prototype._refreshGrid = function () {
    var that = this;
    x2[that.widgetType + 'WidgetManager'].refreshWidget (this.getWidgetKey ());
};

/**
 * Instantiate grid settings dialog and set up behavior of grid settings widget menu option
 */
GridViewWidget.prototype._setUpGridSettings = function () {
    var that = this;
    var settingsDialog$ = $('#grid-settings-dialog-' + this.getWidgetKey ());          
    settingsDialog$.dialog ({
        title: this.translations['Grid Settings'],
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: this.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: this.translations['Save'],
                click: function () {
                    var elem$ = $(this);
                    that.setProperty (
                        'dbPersistentGridSettings',
                        $(this).find ('[name="dbPersistentGridSettings"]').is (':checked') ? 1 : 0,
                        function () { elem$.dialog ('close'); });
                }
            }
        ]
    });
    this.element.find ('.grid-settings-button').click (function () {
        settingsDialog$.dialog ('open');
    });
};

GridViewWidget.prototype.toggleHeader = function (show) {
    if (this.hideFullHeader)
        this.element.find ('.items').first ().toggle(show);
    else {
        this.element.find ('.items').first ().toggle(true);
        this.element.find ('.page-title, tr.filters').toggle(show);
    }        
}

GridViewWidget.prototype._setUpShowHeaderButton = function () {
    var that = this;

    that.toggleHeader (that.showHeader);

    this.element.find ('.widget-settings-menu-content .hide-settings').click (function () {
        that.showHeader = !that.showHeader;
        that.setProperty ('showHeader', that.showHeader ? 1 : 0);
        that.toggleHeader (that.showHeader);
    });
}

GridViewWidget.prototype._setUpTitleBarBehavior = function () {
    if (this.element.find ('.grid-settings-button').length) {
        this._setUpGridSettings ();
    }
    SortableWidget.prototype._setUpTitleBarBehavior.call (this);
};

GridViewWidget.prototype._setUpSettingsBehavior = function () {
    // detach the CGridView summary and move it to the widget settings menu
    if (this.compactResultsPerPage) {
        var settingsMenu$ = $(this.elementSelector + ' .widget-settings-menu-content');
        settingsMenu$.find ('.results-per-page-container').empty ().append (
            this.contentContainer.find ('.summary').detach ());
        settingsMenu$.find ('.results-per-page-container .summary').children ().show ();

    }

    SortableWidget.prototype._setUpSettingsBehavior.call (this);
};

GridViewWidget.prototype._setUpPageSizeSelection = function () {
    var that = this;
    if (this.compactResultsPerPage) {
        var settingsMenu$ = $(this.elementSelector + ' .widget-settings-menu-content');
        settingsMenu$.find ('.results-per-page-container select').change (function () {
            that.setProperty ('resultsPerPage', $(this).val ());
        });
    }
};


GridViewWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._setUpShowHeaderButton ();
    this._setUpPageSizeSelection ();
};
