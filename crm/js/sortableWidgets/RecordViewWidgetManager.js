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
 * Manages behavior of record view widgets as a set. Behavior of individual record view widgets is 
 * managed in separate Widget prototypes.
 */

x2.RecordViewWidgetManager = (function () {

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function RecordViewWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: '', 
        widgetType: 'recordView',
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
        modelId: null,
        modelType: null
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
	TwoColumnSortableWidgetManager.call (this, argsDict);	
}

RecordViewWidgetManager.prototype = auxlib.create (TwoColumnSortableWidgetManager.prototype);

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
 * Overrides parent method. In addition to parent behavior, check if widget layout should be 
 * changed 
 */
RecordViewWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
};

/*
Private instance methods
*/

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
RecordViewWidgetManager.prototype._afterShowWidgetContents = function () {
    this._hideShowHiddenProfileWidgetsText ();
    x2.profile.checkAddWidgetsColumn (); 
};

/**
 * Overrides parent method. 
 */
RecordViewWidgetManager.prototype._afterShowWidgetContents = function () {
    hideShowHiddenWidgetSubmenuDividers ();
};


/**
 * Override parent method. Add model id and type to GET params
 */
RecordViewWidgetManager.prototype._getShowWidgetContentsData = function (widgetClass) {
    var that = this;
    return {
        widgetClass: widgetClass, 
        widgetType: that.widgetType,
        modelId: that.modelId,
        modelType: that.modelType
    };
};

RecordViewWidgetManager.prototype._setUpRecordViewTypeToggleBehavior = function () {
    var menuItem$ = $('#view-record-action-menu-item');
    var that = this;
    menuItem$.find ('.journal-view-checkbox').click (function () {
        var enable = $(this).is (':checked') ? 1 : 0; 
        var publisher$ = $('#PublisherWidget-widget-container-');
        if (enable) {
            if (!publisher$.children ().length) {  
                that._showWidgetContents (publisher$.data ('x2-widget').getWidgetKey ());
            } else {
                publisher$.show ();
            }
        } else {
            $('#PublisherWidget-widget-container-').hide ();
        }
        auxlib.saveMiscLayoutSetting ('enableJournalView', enable); 
    });
    menuItem$.find ('.transactional-view-checkbox').click (function () {
        var enable = $(this).is (':checked') ? 1 : 0; 
        if (enable) {
            $('.transactional-view-widget').each (function () {
                if (!$(this).children ().length) {  
                    that._showWidgetContents ($(this).data ('x2-widget').getWidgetKey ());
                } else {
                    $(this).show ();
                }
            });
        } else {
            $('.transactional-view-widget').hide ();
        }
        auxlib.saveMiscLayoutSetting (
            'enableTransactionalView', enable); 
    });

    var prevMode = $('#record-view-type-menu').is (':visible');
    $('#view-record-action-menu-item > span').click (function () {
        auxlib.saveMiscLayoutSetting ('viewModeActionSubmenuOpen', !prevMode ? 1 : 0);
        prevMode = !prevMode;
    });
//    auxlib.onClickOutside ($('#view-record-action-menu-item'), function () {
//        if ($('#record-view-type-menu').is (':visible')) {
//            $(this).children ('span').click ();
//            $('#record-view-type-menu').hide (); 
//        }
//    });
};

RecordViewWidgetManager.prototype._activate = function () {
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).addClass ('sortable-widget-drag');
    TwoColumnSortableWidgetManager.prototype._activate.apply (this, arguments);
};

RecordViewWidgetManager.prototype._deactivate = function () {
    $(this._widgetsBoxSelector + ',' + this._widgetsBoxSelector2).
        removeClass ('sortable-widget-drag');
    TwoColumnSortableWidgetManager.prototype._activate.apply (this, arguments);
};

RecordViewWidgetManager.prototype._init = function () {
    this._hiddenWidgetsMenuSelector = '#x2-hidden-widgets-menu';
    this._hiddenWidgetsMenuItemSelector = 
        '.x2-hidden-widgets-menu-item.' + this.widgetType + '-widget';
    this._setUpRecordViewTypeToggleBehavior ();
    this._widgetsBoxSelector = '#' + this.cssSelectorPrefix + 'widgets-container-2';
    this._widgetsBoxSelector2 = '#' + this.cssSelectorPrefix + 'widgets-container-inner';

    SortableWidgetManager.prototype._init.call (this);
};

return RecordViewWidgetManager;

}) ();
