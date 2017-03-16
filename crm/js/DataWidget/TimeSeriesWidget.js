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
 * Welcome to the TimeSeriesWidget!
 * This widget will create a histogram from a list of time-oriented data
 * this chart expects the data in the following form:
 * 
 * chartData: {
 *     timeField: [144123114, 144123271, 144123578 ...]
 *     labelField: ['won', 'working', 'won' ...]
 *     timeFrame: {
 *         start: 144123092
 *         end: 144129042
 *     }
 * }
 * 
 * timeField is the list of unix time stamps to be histogrammed
 * labelField (optional) is what category each time stamp belongs to 
 * timeFrame is an object letting the chart know what range to plot
 * 
 * 
 */


x2.TimeSeriesWidget = (function() {

var MAX_CATEGORIES = 20;
var MAX_TICKS = 6;
var MAX_POINTS = 20;
var REFRESH_MINUTES = 5;

function TimeSeriesWidget (argsDict) {
    var defaultArgs = {

        filterType: 'trailing',
        filter: 'week',
        timeBucket: 'day',
        displayType: 'line',
        filterFrom: null,
        filterTo: null,
        subchart: false,

        formattedData: [],
        sortedData: [],
        ticks: [],
        gauge: false,

        primaryModelType: '',

        tickFormats: {
            custom: 'MMM D',
            year: 'MMM D YYYY',
            quarter: 'MMM D',
            month: 'MMM D',
            week: 'MMM D',
            day: 'MMM D HH:mm',
            full: 'lll'
        },

        tickCounts: {
            custom: 6,
            year: 6,
            quarter: 6,
            month: 6,
            week: 7,
            day: 4
        },

        allowedBuckets: {
            day: ['hour'],
            week: ['day', 'hour'],
            month: ['week', 'day', 'hour'], 
            quarter: ['month', 'week', 'day'],
            year: ['quarter', 'month', 'week', 'day'],
            custom: ['year', 'quarter', 'month', 'week', 'day', 'hour']
        }

    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.DataWidget.call (this, argsDict); 

}

TimeSeriesWidget.prototype = auxlib.create (x2.DataWidget.prototype);


/**************************************************
 * Set up all handlers for the config bar
 **************************************************/
TimeSeriesWidget.prototype.setUpConfigBar = function(){
    x2.DataWidget.prototype.setUpConfigBar.call(this);
    this.filterMenu = this.element.find('.filter-menu');
    this.bucketMenu = this.element.find('.time-bucket');

    var that = this;

    /**********************************
     * Chart type Menu
     **********************************/
    var options = ['line', 'pie', 'gauge', 'bar', 'area'];

    auxlib.map(function(d){ 
        that.configBar.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.display-type').removeClass('active');
            $(this).addClass('active');

            that.setProperty('displayType', d);
            that.displayType = d;

            that.draw();
        });
    }, options);

    /**********************************
     * Subchart Button
     **********************************/
    that.configBar.find('#subchart').click(function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        that.subchart = !that.subchart;
        that.setProperty('subchart', that.subchart);
        that.draw();
    });



    /**********************************
     * Time Filter menu
     **********************************/
    that.configBar.find('#filter').click(function(e) {
        that.filterMenu.slideToggle();
        $(this).toggleClass('active');
    });

    var options = ['trailing', 'this'];

    auxlib.map(function(d){ 
        that.filterMenu.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.filter-option').removeClass('active');
            $(this).addClass('active');
            
            that.filterType = d;

            that.fetchData(that.receiveData, {filterType: d});
        });
    }, options);


    /**********************************
     * Time Filter Options
     **********************************/
    var options = ['day', 'week', 'month', 'quarter', 'year', 'custom'];

    auxlib.map(function(d){ 
        that.filterMenu.find('.filter-option#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.filter-option').removeClass('active');
            $(this).addClass('active');

            that.filter = d;

            that.fetchData(that.receiveData, {filter: d});
            if( d == 'custom') {
                that.filterMenu.find('input').addClass('active-input');
            } else {
                that.filterMenu.find('input').removeClass('active-input');
            }
            
        });
    }, options);

    /**********************************
     * Bucket size menu
     **********************************/
    var options = ['hour', 'day', 'week', 'month'];

    auxlib.map(function(d){ 
        that.bucketMenu.find('#'+d).click(function(e){
            e.preventDefault();
            $(this).siblings('.bucket-option').removeClass('active');
            $(this).addClass('active');

            that.setProperty('timeBucket', d);
            that.timeBucket = d;

            that.bucketData();

            that.draw();
        });
    }, options);

    /**********************************
     * Custom Date Picker
     **********************************/
    this.filterMenu.find('.filter-field').datepicker();
    this.filterMenu.find('.filter-field').focus(function() {
    });

    var options = ['filterFrom', 'filterTo'];

    auxlib.map(function(d){ 
        that.filterMenu.find('#'+d).change(function(){
            // var timestamp = moment ($(this).val()).unix()
            var timestamp = $(this).val(); 
            that.setProperty(d, timestamp);
            that[d] = timestamp;
            that.getTimeFrame();

            that.filterMenu.find('#custom').trigger('click');
        });
    }, options);

    /**********************************
     * Select all options
     **********************************/
     if( this.subchart )
         this.configBar.find('#subchart').addClass('active');
     this.configBar.find('#'+this.displayType).addClass('active');
     this.bucketMenu.find('#'+this.timeBucket).addClass('active');
     this.filterMenu.find('.filter-option#'+this.filter).addClass('active');
     this.filterMenu.find('.filter-option#'+this.filterType).addClass('active');

     this.filterMenu.find('#filterFrom').val(this.filterFrom);
     this.filterMenu.find('#filterTo').val(this.filterTo);

     this.filterMenu.appendTo(this.configBar);


}

/**************************************************
 * Sort data into categories
 **************************************************/
TimeSeriesWidget.prototype.sortData = function() {
    var data = this.chartData;
    var cat =[];



    for (var i in data.timeField) {
        var value = data.timeField[i];

        if (auxlib.keys(cat).length > MAX_CATEGORIES || !data.labelField) {
            var unixTimeField = auxlib.map(function(d){return d*1000}, data.timeField);
            this.sortedData = {all: unixTimeField};
            return;
        }
        
        var label = data.labelField[i];

        if (typeof cat[ label ] === 'undefined') {
            if (label == null || label.length == 0) {
                label = 'null';
            }
            cat[ label ] = [ ]; 
        }
        
        cat[ label ].push( value*1000 );
    }


    this.sortedData = cat;
    return cat;
}

/**************************************************
 * Calculate the x scale tickes
 **************************************************/
TimeSeriesWidget.prototype.xScale = function(d3time, amount, data){
    var timeFrame = this.getTimeFrame();


    var domain = [timeFrame.start, timeFrame.end];
    var x = d3.time.scale().domain(domain);

    var ticks = x.ticks(d3time, amount);

    // Fallback to 2 ticks if the timeframe is 0 for example
    if (ticks.length  == 0)
        ticks = x.ticks(2);

    return ticks;
}

/**************************************************
 * Calculate the start and end of the time frame
 **************************************************/
TimeSeriesWidget.prototype.getTimeFrame = function() {

    var unixStart = this.chartData.timeFrame.start;
    var unixEnd = this.chartData.timeFrame.end;

    // Round to the neared timebuckets
    var start = moment (unixStart * 1000).subtract(1, this.timebucket).startOf(this.timeBucket);
    var end = moment (unixEnd * 1000).add(1, this.timeBucket).startOf (this.timeBucket);


    return {start: start, end: end};
}

/**************************************************
 * Redraw entire chart with new data
 **************************************************/
TimeSeriesWidget.prototype.receiveData = function(data) {
    this.chartData = data;
    this.sortData();
    this.bucketData();

    this.draw();
}

/**************************************************
 * Refresh on new data arriving
 **************************************************/
TimeSeriesWidget.prototype.refreshData = function(data) { 
    this.chartData = data;
    this.sortData();
    this.bucketData();

    this.load();
}



/**************************************************
 * Format an array of points into histogram data
 **************************************************/
TimeSeriesWidget.prototype.formatData = function(data, ticks) {

    // Convert timestamps to date objects
    var timeData = auxlib.map(function(d) {
        return new Date(d);
    }, data);

    // This sorts the data into the buckets. Very important function!
    var histData = d3.layout.
        histogram ().
        bins (ticks) (timeData);

    // We just need the height of each list of sorted objects
    var ydata = auxlib.map( function(d) {
        return d.length;
    }, histData);

    return ydata;
}

/*****************************************
 * Put data into buckets 
 *****************************************/
TimeSeriesWidget.prototype.bucketData = function() {

    // d3 time object for 1 hour, 1 day, 1 month..
    var d3time = d3.time[this.timeBucket];
    var amount = 1;

    // Create the ticks based on the 
    var ticks = this.xScale(d3time, amount);

    var data = {};
    for (var i in this.sortedData) {
        data[i] = this.formatData( this.sortedData[i], ticks);
    }

    if (auxlib.length(data) == 0) {
        data.all = auxlib.emptyNumArray(ticks.length);
    }
    
    // ticks.shift();
    data.ticks = ticks;

    this.formattedData = data;
    return data;
}

/*****************************************
 * Average data over span for gauge 
 *****************************************/
TimeSeriesWidget.prototype.averageData = function() {
    var that = this;

    var sum = 0;
    var current = 0;
    var label = '';
    for(var i in this.formattedData) { 
        if (i == 'ticks') 
            continue;

        if ($.inArray (i, this.legend) >= 0) {
            continue;
        }

        if (label) label += ', ';
        label += i;
        
        sum += this.formattedData[i].reduce(function(prev, cur, index) {
            if (index == that.formattedData[i].length - 1) {
                current += cur;
            }

            return prev + cur;
        });
    }

    var length = this.formattedData['ticks'].length ;
    length = (length == 0) ? 1 : length;
    var average = sum / length;

    return {
        average: average, 
        current: current,
        label: label
    };
}

/*****************************************
 * Generate color spectrum for gauge
 *****************************************/
TimeSeriesWidget.prototype.colorScale = function() {
    var values = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

    var colorScale = d3.scale.linear()
      .domain([0,50])
      .interpolate(d3.interpolateRgb)
      .range(['#FF0000', '#F6C600']);

    var colorScale2 = d3.scale.linear()
      .domain([50,100])
      .interpolate(d3.interpolateRgb)
      .range(['#F6C600', '#60B044']);

    var colors = auxlib.map(function(d) { 
        if( d <= 50)
            return colorScale(d);
        else 
            return colorScale2(d);
    }, values);


    return {values: values, colors: colors};
}

/*****************************************
 * Load Wrapper to account for gauge type
 *****************************************/
TimeSeriesWidget.prototype.load = function() {
    if (this.displayType == 'gauge') {
        var gaugeData = this.averageData();
        var columns = [[gaugeData.label, gaugeData.current]];
        this.chart.load ({columns: columns})
    } else {
        var json = this.formattedData;
        this.chart.load({json: json});
    }
}

/*****************************************
 * Gauge Draw function
 *****************************************/
TimeSeriesWidget.prototype.drawGauge = function() {
    var gaugeData = this.averageData();
    var color = this.colorScale();
    this.generate({
        data: {
            columns: [[gaugeData.label, gaugeData.current]],
        },
        gauge: {
            label: {
                format: function(value, ratio) {
                   return value;
                },
            },
            min: 0,
            max: Math.round(gaugeData.average * 200) / 100,
            units: this.primaryModelType +" "+ this.translations['this '+ this.timeBucket],
        },
        color: {
            pattern: color.colors,
            threshold: {
                values: color.values,
                unit: 'percent',
                max: Math.round(gaugeData.average * 200) / 100,
            }
        },
        padding: {
            bottom: 25
        }

    });


}

/*****************************************
 * Primary Draw function
 *****************************************/
TimeSeriesWidget.prototype.draw = function() {
    var type = this.displayType;
    var that = this;

    /**
     * call the special gauge render function if gauge is selected
     */
    if( type == 'gauge') {
        this.drawGauge();
        return;
    }

    /**
     * Group data if area is selected
     */
    var groups = [];
    if (this.displayType == 'area')
        groups = [auxlib.keys(this.formattedData)];

    /**
     * Limit ticks if there are greater than 35
     */
    var tickCount = 'auto';
    if (this.formattedData.ticks.length > MAX_TICKS) {
        tickCount = this.tickCounts[this.filter];
    }

    /**
     * Hide points if there are more than 20
     */
    var points = true;
    if (this.formattedData.ticks.length > MAX_POINTS) {
        points = false;
    }

    /**********************************
     * Chart Generation
     *********************************/
    this.generate({
        data: {
            x: 'ticks',
            json: this.formattedData,
            groups: groups
        },
        axis: {
            x: {
                label: this.chartData.labels.timeField,
                type: 'timeseries',
                tick: {
                    count: tickCount,
                    culling: {
                        max: 10
                    },
                    format: function (d) {
                        return moment(d).format (
                            that.tickFormats[that.filter]
                        );
                    }
                }
            }
        },
        bar: {
            width: {
                ratio: 0.8
            }
        },
        tooltip: {
            format: {
                title: function (d) { 
                    return moment(d).format (
                        that.tickFormats['full']
                    );
                }
            }
        },
        point: {
            show: points
        },
        subchart: {
            show: this.subchart
        }
    });
    
}

TimeSeriesWidget.prototype.refresh = function() { 
    this.fetchData(this.refreshData);
}

TimeSeriesWidget.prototype.setUpRefresh = function() { 
    var that = this;
    function loop() {
        setTimeout(function() {
            that.refresh();
            loop()
        }, 1000*60*REFRESH_MINUTES);
    };

    loop();
}


TimeSeriesWidget.prototype._init = function() {
    x2.DataWidget.prototype._init.call(this);

    if (this.errors){
        return;
    }
    moment.locale(this.locale);
    this.setUpRefresh();
    this.sortData();
    this.bucketData()
    this.draw();
};

return TimeSeriesWidget;
})();
