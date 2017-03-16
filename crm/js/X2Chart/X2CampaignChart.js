/********************************************************************************
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

/*
Child prototype of X2Chart
*/

function X2CampaignChart (argsDict) {
	this.dataStartDate = argsDict['dataStartDate'];

	X2Chart.call (this, argsDict);	

	var thisX2Chart = this;

	this.DEBUG = argsDict['DEBUG'];
	thisX2Chart.DEBUG && console.log ('dataStartDate = ' + this.dataStartDate);

    thisX2Chart.DEBUG && console.log ('X2CampaignChart: this.dataStartDate = ' + thisX2Chart.dataStartDate);

	// campaign not yet started: 
    // replace plot method in with method that displays empty chart
	if (thisX2Chart.chartSubtype === 'line' && 
        (this.dataStartDate === 0 || this.dataStartDate === null || 
		 typeof this.dataStartDate === 'undefined')) {

		X2CampaignChart.prototype.plotData = X2Chart.plotEmptyChart;
	}

	// color palette used for lines of campaign chart
	var colors = [
		'#7EB2E6', // pale blue
		'#CEC415', // mustard
        'rgb(80, 177, 56)', // highlight green
		'#BC0D2C' // pomegranate
	];

	this.metricOptionsColors = {}; // used to pair colors with metrics
	thisX2Chart._firstMetric$.find ('option').each (function () {
		thisX2Chart.metricOptionsColors[$(this).val ()] = colors.shift ();
	});

	/*
	set up event handlers which update action history chart on action 
	creation/deletion.
	*/
	$(document).on ('chartWidgetMaximized', function () {
		thisX2Chart.DEBUG && console.log ('max');
		thisX2Chart.feedChart.replot ({ resetAxes: false });
	});

	thisX2Chart.setDefaultSettings ();

	thisX2Chart.start ();

}

X2CampaignChart.prototype = auxlib.create (X2Chart.prototype);


/************************************************************************************
Static Methods
************************************************************************************/


/************************************************************************************
Instance Methods
************************************************************************************/


/*
Sets initial state of chart setting ui elements
*/
X2CampaignChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if (thisX2Chart.dataStartDate !== 0) { 
		// default start date is beginning of action history
		thisX2Chart._chartDatepickerFrom$.datepicker(
			'setDate', new Date (thisX2Chart.dataStartDate));
		// end date picker default
		thisX2Chart._chartDatepickerTo$.
			datepicker('setDate', new Date ()); // default end date
	}

	// metric default
	thisX2Chart._firstMetric$.children ().each (function () {
		$(this).attr ('selected', 'selected');
	});
	thisX2Chart._firstMetric$.multiselect2 ('refresh');

};

/*
Filter function used by groupChartData to determine how chart data should be grouped
*/
X2CampaignChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

    // filter by type
	if (dataPoint['type'] !== type) {
		return true;
	} else {
		return false;
	}
};

/*
Undopie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2CampaignChart.prototype.postPieChartTearDown = function () {
	var thisX2Chart = this;
	thisX2Chart._chart$.removeClass ('pie');
	thisX2Chart._chartLegend$.removeClass ('pie');
	thisX2Chart._binSizeButtonSet$.removeClass ('pie');
	thisX2Chart._datepickerRow$.removeClass ('pie');
	thisX2Chart._topButtonRow$.removeClass ('pie');
};

/*
Set up pie chart specific ui. Rebind filter ui element event handlers since the
filter elements get removed from the DOM when the chart subtype is switched.
*/
X2CampaignChart.prototype.postPieChartSetUp = function () {
	var thisX2Chart = this;
	thisX2Chart._chart$.addClass ('pie');
	thisX2Chart._chartLegend$.addClass ('pie');
	thisX2Chart._binSizeButtonSet$.addClass ('pie');
	thisX2Chart._datepickerRow$.addClass ('pie');
	thisX2Chart._topButtonRow$.addClass ('pie');

    thisX2Chart.DEBUG && console.log ('postPieChartSetUp: this.dataStartDate = ' + thisX2Chart.dataStartDate);

	if (thisX2Chart.dataStartDate === 0 || thisX2Chart.dataStartDate === null || 
		typeof thisX2Chart.dataStartDate === 'undefined') {

        // only pertains to line chart, delete it
		delete X2CampaignChart.prototype.plotData;
	}
};

/*
If the campaign hasn't started plot an empty line chart.
*/
X2CampaignChart.prototype.postLineChartSetUp = function () {
	var thisX2Chart = this;

    thisX2Chart.DEBUG && console.log ('postLineChartSetUp: this.dataStartDate = ' + thisX2Chart.dataStartDate);

	// campaign not yet started: 
    // replace plot method in with method that displays empty chart
	if (thisX2Chart.dataStartDate === 0 || thisX2Chart.dataStartDate === null || 
		typeof thisX2Chart.dataStartDate === 'undefined') {

		X2CampaignChart.prototype.plotData = X2Chart.plotEmptyChart;
	}
};



