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
 * Corresponding JS Class for Datawidget.php
 */
x2.DataWidget = (function() {
function DataWidget (argsDict) {
    var defaultArgs = {
        chartData: [],
        chartId: -1,
        errors: false,
        legend: [],
        height: false,
        reportId: null,
        // chartSettingUrl: yii.scriptUrl + '/reports/chartSetting',
        fetchDataUrl: yii.scriptUrl + '/reports/fetchData',
        cloneChartUrl: yii.scriptUrl + '/reports/cloneChart',
        addToDashboardUrl: yii.scriptUrl + '/reports/addToDashboard',
        extraOptions: {},
        locale: 'en'
    };


    auxlib.applyArgs (this, defaultArgs, argsDict);

    if (!this.legend) {
        this.legend = [];
    }
    
    SortableWidget.call (this, argsDict);
}

DataWidget.prototype = auxlib.create (SortableWidget.prototype);

/**
 * Sets up the add to dashboard button behavior
 */
DataWidget.prototype.setUpAddToDashButton = function (){
    var that = this;

    var containerSelector = '.add-to-dashboard-dropdown';
    var buttonSelector = '.add-to-dashboard';

    var containerIdPrefix ='add-to-dashboard-';
    var dropdownId = containerIdPrefix + this.widgetUID;

    this.element.find(containerSelector).
        appendTo ($('#content')).
        attr ('id', dropdownId).
        hide();

    new PopupDropdownMenu({
        containerElemSelector: '#'+dropdownId,
        openButtonSelector: this.elementSelector + ' .add-to-dashboard',
    });

    this.dashboardDropdown = $('#'+dropdownId);

    var bindClick = function(selector, destination) {
        that.dashboardDropdown.find (selector).click (function(){
            $.ajax({
                url: that.addToDashboardUrl,
                data: that.ajaxIdentity({
                    destination: destination
                }),
                success: function() {
                    x2.topFlashes.displayFlash(that.translations.addedToDashboard, 'success');
                }
            });
        });
    }

    bindClick('#add-to-charts', 'data');
    bindClick('#add-to-profile', 'profile');

    // addToDashboard.l
    //     click(function() {
    //         $.ajax({
    //             url: that.addToDashboardUrl,
    //             data: that.ajaxIdentity(),
    //             success: function() {
    //                 x2.topFlashes.displayFlash(that.translations.addedToDashboard, 'success');
    //             }
    //         })
    //     });


}

DataWidget.prototype.setUpConfigBar = function (){
    var ulMenu = $(this.element).find('.widget-settings-menu-content');

    this.configBar = $(this.element).find('.config-bar');

    var reroutes = ['delete', 'relabel', 'edit'];

    this.configBar.find('#delete').click(function() {
        ulMenu.find('.delete-widget-button').trigger('click');
    });

    this.configBar.find('#relabel').click(function() {
        ulMenu.find('.relabel-widget-button').trigger('click');
    });

    this.configBar.find('#edit').click(function() {
        ulMenu.find('.edit-widget-button').trigger('click');
    });

    var gearButton = $(this.element).find('.widget-settings-button');
    gearButton.unbind();

    var configBar = this.configBar;
    gearButton.click(function(e) {
        e.preventDefault();
        configBar.slideToggle();
    });

    var that = this;
    this.configBar.find("#clone").click(function() {
        that.cloneWidget();
    });

}

DataWidget.prototype.cloneWidget = function() {
    var that = this;
    $.ajax({
        url: this.cloneChartUrl,
        data: this.ajaxIdentity(),
        dataType: 'json',
        success: function(data) {
            if(data.widget) {
                $('#'+that.widgetType+'-widgets-container-2').append($(data.widget));
            }
        }
    });
}


DataWidget.prototype.fetchData = function(callback, settings) {
    var that = this;

    if (typeof settings === 'undefined') {
        settings = {};
    }

    $.ajax({
        url: yii.scriptUrl + '/reports/fetchData',
        data: that.ajaxIdentity({
            settings: settings
        }),
        dataType: 'json', 
        success: function(data) {
            callback.call(that, data);
        }
    });
}

DataWidget.prototype.getMaxLabelWidth = function() {
    var ticks = d3.select(this.contentSelector).selectAll('.c3-axis-x .tick text');

    var widths = [];
    ticks.each(function(){
        widths.push(this.getComputedTextLength());
    });
    var max = d3.max(widths);
    return max;
}

DataWidget.prototype.generate = function(argsDict) { 
    var that = this;
    var defaultDict = {
        bindto: this.contentSelector,
        data: {
            type: this.displayType
        },
        legend: {
            item: {
                onclick: function(item) {
                    that.toggleLegend(item);
                }
            }
        }
    };

    var chartSettings = $.extend(true,  argsDict, defaultDict);

    this.chart = c3.generate(chartSettings);
    this.legend.map(function(d) {
        that.chart.hide(d);
    });

}


/**
 * Toggles the visibility of a legend Item, and makes the 
 * appropriate call to change the chart setting
 */
DataWidget.prototype.toggleLegend = function(item) {
    var index = $.inArray (item, this.legend);

    if (index >= 0) {
        this.legend.splice(index, 1);
        this.chart.show(item);
    } else {
        this.legend.push(item)
        this.chart.hide(item);
    }

    this.setProperty('legend', this.legend);
}

DataWidget.prototype.onDragStop = function() {
    $('.chart-dashboard').find('.connected-sortable-'+this.widgetType+'-container').height('100%');
    $(this.element).removeClass('dragging');
}

DataWidget.prototype.onDragStart = function() {
    SortableWidget.prototype.onDragStart.call(this);
    $(this.element).addClass('dragging');
    var height = $('.chart-dashboard').height();
    $('.chart-dashboard').find('.connected-sortable-'+this.widgetType+'-container').height(height+250);
}

DataWidget.prototype.setUpResizability = function() {
    this.element.resizable({
        handles: 'n, s',
        stop: function(event, ui) {
            that.element.height('auto');
            that.chart.resize (ui.size);
            that.setProperty('height', ui.size.height);
        }

    });

}

DataWidget.prototype._formatParameters = function(){
    for (var i in this) {
        if (this[i] == "false") {
            this[i] = false;
        } if (this[i] == "true") {
            this[i] = true;
        }
    }
}

DataWidget.prototype._init = function(){
    SortableWidget.prototype._init.call(this);
    this._formatParameters();
    
    var that = this;

    // this.setUpResizability();

    this.setUpAddToDashButton();
    this.setUpConfigBar();

    this.contentSelector = this.contentContainer.selector;
    this.d3contentContainer = d3.selectAll(this.contentContainer.toArray());
    
    if( this.errors ) {
        this.contentContainer.append('<div class="errorScreen">'+ this.errors +'</div>');
    }

};

return DataWidget;

})();
