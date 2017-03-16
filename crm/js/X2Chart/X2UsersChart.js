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

/*
Child prototype of X2Chart
*/


function X2UsersChart (argsDict) {
    argsDict = $.extend (true, {prototype: X2UsersChart.prototype}, argsDict);

	X2Chart.call (this, argsDict);	

	var thisX2Chart = this;

	this.eventTypes = argsDict['eventTypes'];
	this.socialSubtypes = argsDict['socialSubtypes'];
	this.visibilityTypes = argsDict['visibilityTypes'];
	this.DEBUG = argsDict['DEBUG'];

	// color palette used for lines of feed chart
	this.colors;

	this.metricOptionsColors = {}; // used to pair colors with metrics
	//thisX2Chart.resetMetricOptionColors ();

	this.filterTypes = ['eventsFilter', 'socialSubtypesFilter', 'visibilityFilter'];

	this.filters = {};

	thisX2Chart.setUpFilters ();

	thisX2Chart.start ();

}

/************************************************************************************
Static Methods
************************************************************************************/


/************************************************************************************
Instance Methods
************************************************************************************/

X2UsersChart.prototype = auxlib.create (X2Chart.prototype);

/*
Generate color palette after number of selected users is determined
*/
X2UsersChart.prototype.postSetSettingsFromCookie = function (userCount) {
	var thisX2Chart = this;

    var selectedMetrics = thisX2Chart._firstMetric$.val ();
    if (selectedMetrics === null) return;

	// color palette used for lines of feed chart
	this.colors = thisX2Chart.getColorPalette ($(selectedMetrics).length);
	thisX2Chart.resetMetricOptionColors ();

};

/*
Generate an array of color hex values with a color for each user.
*/
X2UsersChart.prototype.getColorPalette = function (userCount) {
	var thisX2Chart = this;

	function leftPadZeros (str) {
		var length = str.length;
		for (var i = 0; i < 6 - length; ++i) str = '0' + str;
		return str;
	}

	//var userCount = thisX2Chart.getMetricTypes ().length;
	var minColor = 0 + 4194304;
	var maxColor = 16777216 - 4194304;
	var colorInterval = Math.floor ((maxColor - minColor) / userCount);
	var colors = [];
	var currColor = minColor;
    var hexCode, hexRed, hexGreen, hexBlue, modColor, colorSum, colorOffset;
	while (currColor < maxColor) {
		thisX2Chart.DEBUG && console.log ('currColor = ' + currColor);
        hexCode = leftPadZeros (currColor.toString (16));

		thisX2Chart.DEBUG && console.log ('hexCode = ' + hexCode);
        hexRed = parseInt (hexCode.slice (0, 2), 16);
        hexGreen = parseInt (hexCode.slice (2, 4), 16);
        hexBlue = parseInt (hexCode.slice (4, 6), 16);
        thisX2Chart.DEBUG && console.log (hexRed, hexGreen, hexBlue);

        /*// brighten overly dark colors
        colorSum = hexBlue + hexRed + hexGreen;
        if (colorSum < 300) {
            colorOffset = Math.floor ((300 - colorSum) / 3);
            hexBlue += colorOffset;
            hexRed += colorOffset;
            hexGreen += colorOffset;
            hexBlue = hexBlue > 255 ? 255 : hexBlue;
            hexGreen = hexGreen > 255 ? 255 : hexGreen;
            hexBlue = hexRed > 255 ? 255 : hexRed;
        }

        // darken overly bright colors
        if (colorSum > 620) {
            colorOffset = Math.floor ((colorSum - 620) / 3);
            hexBlue -= colorOffset;
            hexRed -= colorOffset;
            hexGreen -= colorOffset;
            hexBlue = hexBlue < 0 ? 0 : hexBlue;
            hexGreen = hexGreen < 0 ? 0 : hexGreen;
            hexBlue = hexRed < 0 ? 0 : hexRed;
        }
        thisX2Chart.DEBUG && console.log (hexRed, hexGreen, hexBlue);
        */

        // make muddy colors less muddy
        if (Math.abs (hexRed - 128) < 40 &&
            Math.abs (hexGreen - 128) < 40) {
            hexRed -= 40;
        } else if (Math.abs (hexGreen - 128) < 40 &&
            Math.abs (hexBlue - 128) < 40) {
            hexGreen -= 40;
        } else if (Math.abs (hexBlue - 128) < 40 &&
            Math.abs (hexRed - 128) < 40) {
            hexBlue -= 40;
        }


        var modColor = hexRed * Math.pow (16, 4) + hexGreen * 
            Math.pow (16, 2) + hexBlue;
        hexCode = leftPadZeros (modColor.toString (16));
		colors.push ('#' + hexCode);
		currColor += colorInterval;
	}

    // sort light to dark
    function colorCompare (colorA, colorB) {
        // remove '#' symbols
        colorA = colorA.slice (1);
        colorB = colorB.slice (1);
        var colorSumA, colorSumB;
        colorSumA = parseInt (colorA.slice (0, 2), 16) +
            parseInt (colorA.slice (2, 4), 16)
            parseInt (colorA.slice (4, 6), 16);
        colorSumB = parseInt (colorB.slice (0, 2), 16) +
            parseInt (colorB.slice (2, 4), 16)
            parseInt (colorB.slice (4, 6), 16);
        return colorSumA < colorSumB;
    }

    colors.sort (colorCompare);

    return colors;
};

/*
Sets initial state of chart setting ui elements
*/
X2UsersChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if ((typeof thisX2Chart.lastChartSettings['dateRange'] === 'undefined' || 
	     thisX2Chart.lastChartSettings['dateRange'] !== 'Custom') &&
	    typeof thisX2Chart.lastChartSettings['startDate'] === 'undefined') {

        thisX2Chart.DEBUG && console.log ('setting default');
		// default start date 
		thisX2Chart._chartDatepickerFrom$.
			datepicker('setDate', new Date (new Date () - X2Chart.MSPERWEEK)); 

        thisX2Chart.saveChartSetting (
            'startDate', thisX2Chart._chartDatepickerFrom$.datepicker ('getDate').valueOf ());
	}

	// end date picker default
	if ((typeof thisX2Chart.lastChartSettings['dateRange'] === 'undefined' || 
	     thisX2Chart.lastChartSettings['dateRange'] !== 'Custom') &&
	    typeof thisX2Chart.lastChartSettings['endDate'] === 'undefined') {

		thisX2Chart.DEBUG && console.log ('setting default for eventsChart to date');
		// default start date 
		thisX2Chart._chartDatepickerTo$.
			datepicker('setDate', new Date ()); // default end date
        thisX2Chart.saveChartSetting (
            'endDate', thisX2Chart._chartDatepickerTo$.datepicker ('getDate').valueOf ());
	}

	// metric default
	thisX2Chart._firstMetric$.children ().first ().attr (
		'selected', 'selected');

	thisX2Chart._firstMetric$.multiselect2 ('refresh');

};

/*
Generate color palette based on number of chart data types
*/
X2UsersChart.prototype.preJqplotPlotPieData = function (chartData) {
	var thisX2Chart = this;

    //thisX2Chart.DEBUG && console.log ('preJqplotPlotPieData: ' + chartData.length);

	thisX2Chart.colors = thisX2Chart.getColorPalette (
	    chartData.length);
    thisX2Chart.resetMetricOptionColors ();
};

/*
Generate color palette based on number of chart data types
*/
X2UsersChart.prototype.preJqplotPlotLineData = function (chartData) {
	var thisX2Chart = this;

    if (chartData === null) return;

    thisX2Chart.preJqplotPlotPieData (chartData);

};

/*
Filter function used by groupChartData to determine how chart data should be grouped
*/
X2UsersChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

    // group by type, filter out types specified in filters
	if ((!(type === 'anyone' || type === '') && dataPoint['user'] !== type) ||
		(type === '' && dataPoint['user'] !== null) ||
		($.inArray (dataPoint['type'], thisX2Chart.filters['eventsFilter']) !== -1 &&
		 $.inArray ('all', thisX2Chart.filters['eventsFilter']) !== -1) ||
		($.inArray (dataPoint['subtype'], 
			thisX2Chart.filters['socialSubtypesFilter']) !== -1) ||
		($.inArray (dataPoint['visibility'], 
			thisX2Chart.filters['visibilityFilter']) !== -1)) {
		return true;
	} else {
		return false;
	}
};

/*
Map colors to users
*/
X2UsersChart.prototype.resetMetricOptionColors = function () {
	var thisX2Chart = this;
	thisX2Chart.metricOptionsColors = {}; 
	thisX2Chart._firstMetric$.find ('option').each (
		function (index) {

		var colorIndex = index % thisX2Chart.colors.length;
		thisX2Chart.metricOptionsColors[$(this).val ()] = 
			thisX2Chart.colors[colorIndex];
	});
};

X2UsersChart.prototype.selectMetricOptionColorsForSelected = function (firstMetricVal) {
	var thisX2Chart = this;
	thisX2Chart.metricOptionsColors = {}; 
	for (var i in firstMetricVal) {
		var colorIndex = i % thisX2Chart.colors.length;
		thisX2Chart.metricOptionsColors[firstMetricVal[i]] = 
			thisX2Chart.colors[colorIndex];
	}
};

/*
Limit number of selected users for line chart.
*/
X2UsersChart.prototype.postMetricSelectionSetup = function () {
	var thisX2Chart = this;

	thisX2Chart._firstMetric$.bind ("multiselect2beforeclose", 
		function (evt, ui) {

			var firstMetricVal = $(this).val ();
			var maxSelected = 21;
			if (firstMetricVal !== null) {
				thisX2Chart.applyMultiselectSettings (
					$(this), firstMetricVal.slice (0, maxSelected));
				/*if (firstMetricVal.length > maxSelected) {
					thisX2Chart.selectMetricOptionColorsForSelected (firstMetricVal);
				} else {
					thisX2Chart.resetMetricOptionColors ();
				}*/
			}
			thisX2Chart.DEBUG && console.log (
				'postMetricSelectionSetup: metric1.val = ' + firstMetricVal);
		});
};

/*
Undo pie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2UsersChart.prototype.postPieChartTearDown = function (uiSetUp) {
	var thisX2Chart = this;
	thisX2Chart._chart$.removeClass ('pie');
	thisX2Chart._chartLegend$.removeClass ('pie');
	thisX2Chart._datepickerRow$.removeClass ('pie');
	thisX2Chart._topButtonRow$.removeClass ('feed-pie');
	thisX2Chart._createSettingButton$.removeClass ('pie');
	thisX2Chart._predefinedSettings$.removeClass ('pie');
	thisX2Chart._firstMetricContainer$.show ();
	thisX2Chart._binSizeButtonSet$.show ();
	var filterToggleContainer = thisX2Chart._filterToggleContainer$.remove ();
	thisX2Chart._firstMetricContainer$.after (filterToggleContainer);
    thisX2Chart.bindFilterEvents ();
};

/*
Set up pie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2UsersChart.prototype.postPieChartSetUp = function (uiSetUp) {
	var thisX2Chart = this;
	thisX2Chart._chart$.addClass ('pie');
	thisX2Chart._chartLegend$.addClass ('pie');
	thisX2Chart._datepickerRow$.addClass ('pie');
	thisX2Chart._topButtonRow$.addClass ('feed-pie');
	thisX2Chart._createSettingButton$.addClass ('pie');
	thisX2Chart._predefinedSettings$.addClass ('pie');
	thisX2Chart._firstMetricContainer$.hide ();
	thisX2Chart._binSizeButtonSet$.hide ();
	var filterToggleContainer = thisX2Chart._filterToggleContainer$.remove ();
	thisX2Chart._datepickerRow$.append (filterToggleContainer);
    thisX2Chart.bindFilterEvents ();
};



