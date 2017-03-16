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
 * Manages behavior of profile widgets as a set. Behavior of individual profile widgets is managed
 * in separate Widget prototypes.
 */

/**
 * Constructor 
 * @param dictionary argsDict A dictionary of arguments which can be used to override default values
 *  specified in the defaultArgs dictionary.
 */
function ProfileWidgetManager (argsDict) {
    var defaultArgs = {
        cssSelectorPrefix: 'profile-', 
        widgetType: 'profile',
        connectedContainerSelector: '', // class shared by all columns containing sortable widgets
        createProfileWidgetUrl: '',
        /* x2prostart */
        createChartingWidgetUrl: ''
        /* x2proend */
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

	TwoColumnSortableWidgetManager.call (this, argsDict);	
}

ProfileWidgetManager.prototype = auxlib.create (TwoColumnSortableWidgetManager.prototype);

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
 * Override parent method. In addition to parent behavior, check if widget layout should be changed 
 */
ProfileWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    TwoColumnSortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (
        this, widgetSelector);
    x2.profile.checkRemoveWidgetsColumn ();
};

/*
Private instance methods
*/

ProfileWidgetManager.prototype._setUpAddProfileWidgetMenu = function () {
};

/**
 * Show text in hidden profile widget menu indicating that there aren't any hidden widgets 
 */
ProfileWidgetManager.prototype._hideShowHiddenProfileWidgetsText = function () {
    if (this.hiddenWidgetsMenuIsEmpty ())
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-'+this.cssSelectorPrefix+'widgets-text').show ();
    else
        $(this._hiddenWidgetsMenuSelector).find ('.no-hidden-'+this.cssSelectorPrefix+'widgets-text').hide ();
};

ProfileWidgetManager.prototype._afterCloseWidget = function () {
    this._hideShowHiddenProfileWidgetsText ();
};

/**
 * Check if layout should be rearranged after widget is added to layout 
 */
ProfileWidgetManager.prototype._afterShowWidgetContents = function () {
    this._hideShowHiddenProfileWidgetsText ();
    x2.profile.checkAddWidgetsColumn (); 
};

ProfileWidgetManager.prototype._createProfileWidget = function (widgetType, callback) {
    var that = this;
    $.ajax ({
        url: this.createProfileWidgetUrl,
        data: {
            'widgetType': widgetType,
            'widgetLayoutName': this.widgetType
        },
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                $(that._widgetsBoxSelector).append (data.widget);
                hideShowHiddenWidgetSubmenuDividers ();
                that._afterShowWidgetContents ();
                callback ();
            }
        }
    });
};

/* x2prostart */
/**
 * Creates a charting widget on the dashboard. 
 * Since the options are the charting layouts in reports, 
 * We use all the information necessary to call add to dashbaord
 * in the reports controller
 */
ProfileWidgetManager.prototype._createChartingWidget = function (settings,callback) {
    var that = this;

    $.ajax ({
        url: this.createChartingWidgetUrl,
        data: {
            widgetClass: settings['widgetClass'],
            widgetUID: settings['widgetUID'],
            destination: 'profile',
            widgetType: 'data',
            settingsModelName: 'Reports',
            settingsModelId: settings['modelId']
        },
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data !== 'failure') {
                $(that._widgetsBoxSelector).append (data.widget);
                hideShowHiddenWidgetSubmenuDividers ();
                that._afterShowWidgetContents ();
                callback ();
            }
        }
    });
};

/* x2proend */

ProfileWidgetManager.prototype._setUpCreateWidgetDialog = function () {
    var that = this;
    var dialog$ = $('#create-'+this.cssSelectorPrefix+'widget-dialog').dialog ({
        title: this.translations['createProfileWidgetDialogTitle'],
        autoOpen: false,
        width: 500,
        buttons: [
            {
                text: that.translations['Cancel'],
                click: function () {
                    $(this).dialog ('close');
                }
            },
            {
                text: that.translations['Create'],
                'class': 'highlight',
                click: function () {
                    var widgetType = $(this).find ('#widgetType').val ();
                    var callback = function (){
                        dialog$.dialog ('close'); }; 

                    /* x2prostart */
                    // Create a special case for a datawidget
                    if (widgetType == 'DataWidget') {
                        var settings = JSON.parse($(this).find('#chartName').val());
                        that._createChartingWidget(settings, callback);
                        return;
                    }

                    /* x2proend */

                    that._createProfileWidget (widgetType, callback);
                }
            }
        ]
    });

    /* x2prostart */
    dialog$.find('#widgetType').change(function (){
        dialog$.find('#chart-name-container').toggle ($(this).val() == 'DataWidget');
    })
    /* x2proend */

    // create-profile-widget-button
    $('#create-'+this.cssSelectorPrefix+'widget-button').click (function () {
        dialog$.dialog ('open');
    });

};


ProfileWidgetManager.prototype._init = function () {
    this._setUpAddProfileWidgetMenu ();
    this._setUpCreateWidgetDialog ();
    TwoColumnSortableWidgetManager.prototype._init.call (this);
};
