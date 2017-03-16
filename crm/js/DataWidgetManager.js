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

/* @edition:pro */

/**
 * Class to manage the widgets on a chart dashboard
 * Quite an ugly class- I apologize
 * @author Alex Rowe
 */
x2.DataWidgetManager = (function() {

function DataWidgetManager (argsDict) {
    var defaultArgs = {
        onReport: false,
        translations: {},
        dashboardSelector: '.chart-dashboard',
        connectedContainerSelector: '.connected-sortable-data-container',
        cssSelectorPrefix: 'data-',
        widgetType: 'data',
        showWidgetContentsUrl: yii.scriptUrl + '/profile/getWidgetContents'		,
        widgetList: []	
    };

    ProfileWidgetManager.call(this, defaultArgs);		    
    auxlib.applyArgs (this, defaultArgs, argsDict);

}

DataWidgetManager.prototype = auxlib.create(ProfileWidgetManager.prototype);

DataWidgetManager.prototype._init = function(){
    ProfileWidgetManager.prototype._init.call(this);

    var menu = new PopupDropdownMenu ({
        containerElemSelector: '#x2-hidden-data-widgets-menu-container',
        openButtonSelector: '#hidden-data-widgets-button'
    });

    this.dashboard = $('.chart-dashboard');
    this.setUpToolBar();

}


DataWidgetManager.prototype.setUpFullScreen = function() {
    $('#dashboard-fullscreen-button').click( function() {
        $('#content').fullscreen();
    });
}


/**
 * SortableWidgetManager Override
 */
DataWidgetManager.prototype._afterShowWidgetContents = function() {
    this._hideShowHiddenProfileWidgetsText();
};

/**
 * SortableWidgetManager Override
 */
DataWidgetManager.prototype.addWidgetToHiddenWidgetsMenu = function (widgetSelector) {
    SortableWidgetManager.prototype.addWidgetToHiddenWidgetsMenu.call (this, widgetSelector);
    this._hideShowHiddenProfileWidgetsText();
}

DataWidgetManager.prototype.refreshWidgets = function() {
    var widget;
    for (var i in this.widgetList) {
        widget = this.widgetList[i];
        if ($(widget.contentSelector).length !== 0) {
            widget.refresh();
        }
    }
}

DataWidgetManager.prototype.setUpToolBar = function () {
    var that = this;

    this.dashboard.find('#refresh-charts-button').click(function() {
        that.refreshWidgets ();
    });

    this.dashboard.find('#minimize-dashboard').click(function() {
        that.dashboard.find('.dashboard-inner').slideToggle();
        $(this).find('.fa').toggleClass('fa-caret-down');
        $(this).find('.fa').toggleClass('fa-caret-left');
        that.dashboard.toggleClass('minimized');

        if ($(this).find('.fa').hasClass('fa-caret-down')) {
            that.refreshWidgets();			
        }
    });

    this.dashboard.find('#get-charts-help').click(function() {
        that.dashboard.find('#charts-help-dialog').dialog();
    });

    if (!x2.reportForm) {
        this.popupDropdownMenu = new PopupDropdownMenu ({
            containerElemSelector: '#report-list',
            openButtonSelector: '#create-chart-button',
        });
        return;
    }

    $('.page-title').first().find('#report-update-button').click(function() {
            $(this).removeClass('highlight');
            that.dashboard.find('#create-chart-button').removeClass('disabled-link');
            that.dashboard.find('#save-chart-message').hide();
    });

    this.dashboard.find('#create-chart-button').click( function() {	
        if (x2.reportForm.isSaved()) {
            if (!$('#generated-report').length) {
                x2.reportForm.
                    _settingsForm$.
                    find('.x2-button[type="submit"]').
                    trigger('click');
            }
            x2.chartCreator.open();
        } else {
            x2.topFlashes.displayFlash(that.translations.saveChart, 'error');
            that.dashboard.find('#create-chart-button').addClass('disabled-link');
            $('.page-title').find('#report-update-button').addClass('highlight');
        }
    });

}

return DataWidgetManager;

})();
