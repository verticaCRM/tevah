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
 * Manages behavior of a sortable widget
 */

SortableWidget.sortableWidgets = []; // instances of SortableWidget

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function SortableWidget (argsDict) {
    var defaultArgs = {
        deleteWidgetUrl: '',
        widgetClass: '', // the name of the associated widget class
        setPropertyUrl: '', // the url used to call the set profile widget property action
        settingsModelName: null, // The name of the model with the settings field
        settingsModelId: null,   // The id of the model with the settings field
        profileId: null, // the id of the profile associated with this widget
        widgetType: '', // (profile)
        widgetUID: null, 
        DEBUG: x2.DEBUG && false,
        enableResizing: false,
        translations: {},
        urls: {}
    };

    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.elementSelector = '#' + this.widgetClass + '-widget-container-' + this.widgetUID;

    x2.Widget.call (this, $.extend ({}, argsDict, {
        element: this.elementSelector 
    }));

    this.element = $(this.elementSelector); // the widget container

    // the widget content container (excludes the top bar)
    this.contentContainer = $('#' + this.widgetClass + '-widget-content-container-'+this.widgetUID);

    this._settingsMenuContentSelector = this.elementSelector  + ' .widget-settings-menu-content';

    SortableWidget.sortableWidgets.push (this);


    this._init ();
}

SortableWidget.prototype = auxlib.create (x2.Widget.prototype);

/*
Public static methods
*/

/**
 * @return boolean True if all widgets are hidden, false otherwise 
 */
SortableWidget.allWidgetsHidden = function () {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i].element.is (':visible'))
            return false;
    }
    return true;
};

/**
 * Calls turnOnSortingMode () methods of all instantiated sortable widgets except excludedWidget
 * @param object excludedWidget instance of SortableWidget
 */
SortableWidget.turnOnSortingMode = function (excludedWidget) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i] !== excludedWidget)
            SortableWidget.sortableWidgets[i]._turnOnSortingMode ();
    }
};

/**
 * Calls turnOffSortingMode () methods of all instantiated sortable widgets except excludedWidget
 * @param object excludedWidget instance of SortableWidget
 */
SortableWidget.turnOffSortingMode = function (excludedWidget) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i] !== excludedWidget)
            SortableWidget.sortableWidgets[i]._turnOffSortingMode ();
    }
};

/**
 * @param object elem jQuery object corresponding to a widget container
 * @return mixed return value of getWidgetByClass ()
 */
SortableWidget.getWidgetFromWidgetContainer = function (elem) {
    var widgetKey = $(elem).attr ('id').replace (/-widget-container-(\w+)?$/, '_$1');

    var widget = SortableWidget.getWidgetByKey (widgetKey);
    return widget;
};

/**
 * @return string key which uniquely identifies widget
 */
SortableWidget.prototype.getWidgetKey = function () {
    return this.widgetClass + '_' + this.widgetUID;
};

/**
 * @param string widgetKey
 * @return mixed sortable widget instance if instance with specified class is found, null otherwise 
 */
SortableWidget.getWidgetByKey = function (widgetKey) {
    for (var i in SortableWidget.sortableWidgets) {
        if (SortableWidget.sortableWidgets[i].widgetClass + '_' +
            SortableWidget.sortableWidgets[i].widgetUID === 
            widgetKey)

            return SortableWidget.sortableWidgets[i];
    }
    return null;
};

/**
 * Call refresh method for each widget instance 
 */
SortableWidget.refreshWidgets = function () {
    for (var i in SortableWidget.sortableWidgets) {
        SortableWidget.sortableWidgets[i].refresh ();
    }
};

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Calls an action which sets a property of the profile widget layout JSON attribute
 *
 * @param string key the name of the JSON property
 * @param string value the value to set the JSON property to
 * @param function callback if set, called after the ajax request returns
 */
SortableWidget.prototype.setProperty = function (key, value, callback) {
    $.ajax ({
        url: this.setPropertyUrl,
        type: 'POST',
        data: {
            widgetClass: this.widgetClass,
            key: key,
            value: value,
            widgetUID: this.widgetUID,
            widgetType: this.widgetType,
            settingsModelName: this.settingsModelName,
            settingsModelId: this.settingsModelId,
        },
        success: function (data) {
            if (data === 'success') {
                if (typeof callback !== 'undefined') callback ();
            }
        }
    });
};

/**
 * Change widget label 
 * @param string newLabel 
 */
SortableWidget.prototype.changeLabel = function (newLabel) {
    var that = this; 
    this.setProperty ('label', newLabel, function () {
        that.element.find ('.widget-title').text (newLabel);
    });
};

/**
 * Call this to ensure that widget is rendered properly 
 */
SortableWidget.prototype.refresh = function () {};

SortableWidget.prototype.reinit = function () {
    this._init ();
};

/**
 * Should be called when widget drag starts 
 */
SortableWidget.prototype.onDragStart = function () {
    if (this._settingsBehaviorEnabled) // hide settings menu
        this.popupDropdownMenu.close ();
};

/**
 * Should be called when widget drag stops 
 */
SortableWidget.prototype.onDragStop = function () {};

/*
Private instance methods
*/

/**
 * Called by _setUpMinimizationBehavior after widget is maximized. Can be overridden in child
 * prototype.
 */
SortableWidget.prototype._afterMaximize = function () {};

/**
 * Sets up behavior of the minimization/maximization button
 */
SortableWidget.prototype._setUpMinimizationBehavior = function () {
    var that = this;
    that.DEBUG && console.log ('_setUpMinimizationBehavior');
    $(this.element).find ('.widget-minimize-button').unbind ('click.widgetMinimize');
    $(this.element).find ('.widget-minimize-button').bind ('click.widgetMinimize', 
        function (evt) {

        evt.preventDefault ();
        that.DEBUG && console.log (that.contentContainer); 
        var minimize = $(that.contentContainer).is (':visible');
        that.setProperty ('minimized', (minimize ? 1 : 0), function () {
            if (minimize) {
                $(that.contentContainer).slideUp ();
                $(that.element).find ('.widget-minimize-button').
                    children ().first ().show ();
                $(that.element).find ('.widget-minimize-button').
                    children ().last ().hide ();
            } else {
                $(that.contentContainer).slideDown ();
                that._afterMaximize ();
                $(that.element).find ('.widget-minimize-button').
                    children ().first ().hide ();
                $(that.element).find ('.widget-minimize-button').
                    children ().last ().show ();
            }
        });
    });
};

/**
 * Sets up behavior of the close button
 */
SortableWidget.prototype._setUpCloseBehavior = function () {
    var that = this;
    $(this.element).find ('.widget-close-button').unbind ('click.widgetClose');
    $(this.element).find ('.widget-close-button').bind ('click.widgetClose', function (evt) {
         
        evt.preventDefault ();
        that.DEBUG && console.log ('close'); 

        that.setProperty ('hidden', 1, function () {
            $(that.element).hide ();
            that._tearDownWidget ();
            // remove sort item class to prevent sort jitter
            $(that.element).removeClass (
                x2[that.widgetType + 'WidgetManager'].getWidgetContainerSelector ().
                replace (/\./, ''));
            x2[that.widgetType + 'WidgetManager'].addWidgetToHiddenWidgetsMenu (that.element);
            $(that.element).children ().remove ();
        });
    });
};

/**
 * override in child prototype 
 */
SortableWidget.prototype._tearDownWidget = function () {};

/**
 * Hides/shows title bar buttons on mouseleave/mouseover 
 */
SortableWidget.prototype._setUpTitleBarBehavior = function () {
    var that = this; 
    that._cursorInWidget = false;
    if ($(this.element).find ('.widget-minimize-button').length ||
        $(this.element).find ('.widget-close-button').length) {

        $(this.element).mouseover (function () {
            that._cursorInWidget = true;
            $(that.element).find ('.submenu-title-bar .x2-icon-button').show ();
        });
        $(this.element).mouseleave (function () {
            that._cursorInWidget = false;
            if (!(that._settingsBehaviorEnabled &&
                  $(that._settingsMenuContentSelector).is (':visible'))) {
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
            }
        });
    }

    if (this.element.find ('.relabel-widget-button').length) {
        this._setUpWidgetRelabelling ();
    }
    if (this.element.find ('.delete-widget-button').length) {
        this._setUpWidgetDeletion ();
    }
};

/**
 * Sets up behavior of widget deletion settings menu button
 */
SortableWidget.prototype._setUpWidgetDeletion = function () {
    var that = this;
    var deletionDialog$ = $('#delete-widget-dialog-' + this.widgetUID);          
    deletionDialog$.dialog ({
        title: this.translations['Are you sure you want to delete this widget?'],
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
                text: this.translations['Delete'],
                'class': 'urgent',
                click: function () {
                    $.ajax ({
                        url: that.deleteWidgetUrl,
                        data: {
                            widgetLayoutName: that.widgetType,
                            widgetKey: that.widgetClass + '_' + that.widgetUID,
                            modelName: that.modelName,
                            modelId: that.modelId,
                        },
                        type: 'POST',
                        success: function (data) {
                            if (data === 'success') {
                                $(that.element).remove ();
                                delete that;
                                deletionDialog$.dialog ('close');
                                x2[that.widgetType + 'WidgetManager'].
                                    afterDelete (that.element);
                            }
                        }
                    });
                }
            }
        ]
    });
    this.element.find ('.delete-widget-button').click (function () {
        deletionDialog$.dialog ('open');
    });
};

/**
 * Sets up behavior of widget rename settings menu button
 */
SortableWidget.prototype._setUpWidgetRelabelling = function () {
    var that = this;
    var relabellingDialog$ = $('#relabel-widget-dialog-' + this.widgetUID);          
    relabellingDialog$.dialog ({
        title: this.translations['Rename Widget'],
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
                text: this.translations['Rename'],
                'class': 'widget-rename-submit-button',
                click: function () {
                    that.setProperty (
                        'label', relabellingDialog$.find ('.new-widget-name').val (), function () {

                        that.element.find ('.widget-title').html (
                            relabellingDialog$.find ('.new-widget-name').val ()
                        );
                        relabellingDialog$.dialog ('close');
                    });
                }
            }
        ]
    });
    relabellingDialog$.find ('.new-widget-name').keydown (function () {
        relabellingDialog$.closest ('.ui-dialog').find ('.widget-rename-submit-button').
            addClass ('highlight'); 
    });
    this.element.find ('.relabel-widget-button').click (function () {
        relabellingDialog$.dialog ('open');
    });
};

/**
 * Instantiates popup dropdown menu. Expects {settingsMenu} to be in the widget template
 */
SortableWidget.prototype._setUpSettingsBehavior = function () {
    var that = this; 
    this.popupDropdownMenu = new PopupDropdownMenu ({
        containerElemSelector: this.elementSelector + ' .widget-settings-menu-content',
        openButtonSelector: this.elementSelector + ' .widget-settings-button',
        defaultOrientation: 'left',
        onClose: function () {
            if (!that._cursorInWidget)
                $(that.element).find ('.submenu-title-bar .x2-icon-button').hide ();
        }
    });
};

SortableWidget.prototype._turnOnSortingMode = function () {};

SortableWidget.prototype._turnOffSortingMode = function () {};

/**
 * called by _setUpResizeBehavior () 
 */
SortableWidget.prototype._onResize = function () {};

/**
 * called by _setUpResizeBehavior () 
 */
SortableWidget.prototype._afterStop = function () {
    var that = this; 
    that.setProperty ('height', that.element.height ());
};

/**
 * Sets up widget resize behavior 
 */
SortableWidget.prototype._setUpResizeBehavior = function () {
    var that = this; 
    $(this.contentContainer).resizable ({
        handles: 's', 
        minHeight: 50,
        start: function () {
            /* 
            Make the handle bigger to prevent iframe from triggeing mouseleave event.
            Also prevents widget controls from being hidden during resize.
            */
            that.resizeHandle.css ({ 
                'height': '1000px',
                'position': 'relative',
                'top' : '-500px'
            });
        },
        stop: function () {
            that.resizeHandle.css ({
                'height': '',
                'position': '',
                'top': '',
            });
            that._afterStop ();
        },
        resize: function () { that._resizeEvent (); }
    });
    this.resizeHandle = that.contentContainer.find ('.ui-resizable-handle');
};

SortableWidget.prototype._resizeEvent = function () {};

/**
 * Detects presence of UI elements (and sets properties accordingly), calls their setup methods
 */
SortableWidget.prototype._callUIElementSetupMethods = function () {
    if ($(this.element).find ('.widget-minimize-button').length) {
        this._setUpMinimizationBehavior ();
        this._minimizeBehaviorEnabled = true;
    } else {
        this._minimizeBehaviorEnabled = false;
    }

    if ($(this.element).find ('.widget-close-button').length) {
        this._setUpCloseBehavior ();
        this._closeBehaviorEnabled = true;
    } else {
        this._closeBehaviorEnabled = false;
    }

    if ($(this.element).find ('.widget-settings-button').length) {
        this._setUpSettingsBehavior ();
        this._settingsBehaviorEnabled = true;
    } else {
        this._settingscloseBehaviorEnabled = false;
    }

    if (this.enableResizing) {
        this._setUpResizeBehavior ();
    }
};

/**
 * Returns a dictionary of variables needed to identifiy this widget's layout
 */
SortableWidget.prototype.ajaxIdentity = function(argsDict) {
    var defaultDict =  {
        widgetUID: this.widgetUID,
        widgetClass: this.widgetClass,
        settingsModelName: this.settingsModelName,
        settingsModelId: this.settingsModelId,
        widgetType: this.widgetType
    };

    for (var i in argsDict) {
        defaultDict[i] = argsDict[i];
    }

    return defaultDict;
}

/**
 * Sets up the widget 
 */
SortableWidget.prototype._init = function () {
    var that = this;
    that.DEBUG && console.log ('SortableWidget: _init');
    that.DEBUG && console.log ('this = ');
    that.DEBUG && console.log (this);

    that._setUpTitleBarBehavior ();
    that._callUIElementSetupMethods ();
};
