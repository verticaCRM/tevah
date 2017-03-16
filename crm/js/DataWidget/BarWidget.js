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
 * ChartData Structure: 
 * {   
 *     data {
 *          ['categories', 'item2', 'item2'...],
 *          ['admin', 123, 4123, ...]
 *          ['chames', 513, 712, ...]
 *          ...
 *     },
 *     labels: {
 *          categories: 'assignedTo',
 *          values: 'count'
 *          groups: 'Lead Source' 
 *     }
 * }
 */

x2.BarWidget  = (function() {

var MAX_TICKS = 20;

function BarWidget (argsDict) {
    var defaultArgs = {
        displayType: 'string',
        orientation: 'string',
        stack: false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.DataWidget.call (this, argsDict); 
}

BarWidget.prototype = auxlib.create (x2.DataWidget.prototype);

BarWidget.prototype.setUpConfigBar = function(){
    x2.DataWidget.prototype.setUpConfigBar.call(this);

    var that = this;

    var options = ['bar', 'line', 'pie', 'area'];

    auxlib.map(function(d){ 
        that.configBar.find('#'+d).click(function(){
            $(this).siblings('.display-type').removeClass('active');
            $(this).addClass('active');

            that.displayType = d;
            that.draw();
            that.setProperty('displayType', d);
        });
    }, options);

    that.configBar.find('#orientation').click (function(){
        if (that.orientation == 'rows') {
            that.orientation = 'columns';
        } else {
            that.orientation = 'rows';
        }

        that.setProperty('orientation', that.orientation);
        that.draw();
    });

    that.configBar.find('#stack').click (function(){
        that.stack = !that.stack;

        $(this).toggleClass('active', that.stack);

        that.setProperty('stack', that.stack);
        that.chart.groups(that.getData().groups);
        // that.draw();
    });

    this.configBar.find('#stack').toggleClass ('active', that.stack);
    this.configBar.find('#'+this.displayType).addClass('active');
}

/**
 * this function is called when the chart is told to refresh
 */
BarWidget.prototype.refresh = function() {
    // fetch data with refreshData as a callback
    this.fetchData(this.refreshData);
}

BarWidget.prototype.refreshData = function(data) {
    this.chartData = data;
    this.chart.load(this.getData().data);
}


BarWidget.prototype.getData = function() {
    var data = {
        x: 'categories',
        groups: []
    };

    data[this.orientation] = this.chartData.data;

    if (this.stack) {
        if (this.orientation == 'rows') {
            data.groups = [this.chartData.data[0]];
        } else {
            data.groups = [auxlib.map(function(d){return d[0]}, this.chartData.data)];
        }
    }

    return data;
}


BarWidget.prototype.draw = function() {

    var displayPoints = true;

    if (this.chartData.data[0].length >= MAX_TICKS || 
        this.chartData.data.length >= MAX_TICKS) {
        var tickCount = MAX_TICKS;
        displayPoints = false;
    }

    this.generate({
        data: this.getData(),
        bar: {
            width: {
                ratio: 0.75
            }
        },
        axis: {
            x: {
                label: this.chartData.labels[this.orientation],
                tick: {
                    count: tickCount,
                    culling: {
                        max: MAX_TICKS
                    }
                },
                type: 'category',
            },
            y: {
                label: this.chartData.labels.values
            }
        },
        point: {
            show: displayPoints
        },
    });

    // this.fitTickLabels();
}

/***********************************************************************
* Determine the height of the chart based on label widths, and redraw
************************************************************************/
// BarWidget.prototype.fitTickLabels = function() {
//     console.log('here');
//     if (this.redraw) return;

//     var width = this.getMaxLabelWidth();
//     var containerWidth = this.contentContainer.width();
//     var columnWidth = containerWidth / this.chartData.data[0].length - 30;
//     console.log(width);
//     console.log(columnWidth);

//     if (width > columnWidth) {
//         this.redraw = true;
//         // this.height = 1/Math.sin(45)*width + 10;
//         // this.rotate = 45;
//         this.draw();
//         this.redraw = false;        
//     }

// }

BarWidget.prototype._init = function() {
    x2.DataWidget.prototype._init.call(this);

    if (this.errors){
        return;
    }

    this.draw();


};

return BarWidget;
})();
