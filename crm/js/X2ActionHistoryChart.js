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



function X2ActionHistoryChart (argsDict) {
	X2Chart.call (this, argsDict);	

	var thisX2Chart = this;

	this.DEBUG = argsDict['DEBUG'];
	this.dataStartDate = argsDict['dataStartDate'];

	thisX2Chart.DEBUG && console.log ('dataStartDate = ' + this.dataStartDate);

	var colors;
	// color palette used for lines of action history chart
	colors = [
		'#7EB2E6', // pale blue
		'#CEC415', // mustard
		'#BC0D2C', // pomegranate
		'#45B41D', // apple green
		'#AB074F', // dark hot pink
		//'#156A86', // dark blue
		'#1B8FB5', // dark blue
		'#FFC382',
		'#3D1783', // dark purple
		//'#5A1992',// deep purple
		'#AACF7A',
		'#7BB57C', // olive green
		//'#69B10A', // dark lime green
		//'#8DEB10',
		'#C87010', // red rock
		'#1D4C8C', // dark blue-purple
		'#FFF882',
		'#FF9CAD',
		'#BAFFA1',
		//'#CA8613', // orange brown
		//'#C6B019', // dark sand
		'#19FFF4',
		'#A4F4FC',
		'#99C9FF',
		'#D099FF',
		'#FCA74B',
		'#E1A1FF',
	];

	this.metricOptionsColors = {}; // used to pair colors with metrics
	$('#' + this.chartType + '-first-metric').find ('option').each (function () {
		thisX2Chart.metricOptionsColors[$(this).val ()] = colors.shift ();
	});

	this.cookieTypes = [
		'startDate', 'endDate', 'dateRange', 'binSize', 'firstMetric', 'showRelationships'];

	/* 
	set up event handlers which update action history chart on action 
	creation/deletion.
	*/
	$('#' + thisX2Chart.chartType + '-chart-container #' + thisX2Chart.chartType + 
	  '-rel-chart-data-checkbox').on ('change', function () {
	  	thisX2Chart.DEBUG && console.log ('checked rel checkbox');
		if (this.checked) {
			thisX2Chart.actionParams['showRelationships'] = 'true';
			thisX2Chart.getEventsBetweenDates (true);
			$.cookie (thisX2Chart.cookiePrefix + 'showRelationships', 'true');
		} else {
			thisX2Chart.actionParams['showRelationships'] = 'false';
			thisX2Chart.getEventsBetweenDates (true);
			$.cookie (thisX2Chart.cookiePrefix + 'showRelationships', 'false');
		}
	});													   

	/*
	set up event handlers which update action history chart on action 
	creation/deletion.
	*/
	$(document).on ('chartWidgetMaximized', function () {
		thisX2Chart.DEBUG && console.log ('max');
		thisX2Chart.feedChart.replot ({ resetAxes: false });
	});
	$(document).on ('newlyPublishedAction', function () {
		thisX2Chart.DEBUG && console.log ('new action');
		thisX2Chart.getEventsBetweenDates (true); 
	});
	$(document).on ('deletedAction', function () {
		thisX2Chart.DEBUG && console.log ('deleted action');
		thisX2Chart.getEventsBetweenDates (true); 
	});


	//thisX2Chart.setDefaultSettings ();

	thisX2Chart.start ();

}

X2ActionHistoryChart.prototype = auxlib.create (X2Chart.prototype);

/*
Sets initial state of chart setting ui elements
*/
X2ActionHistoryChart.prototype.setDefaultSettings = function () {
	var thisX2Chart = this;

	// start date picker default
	if (thisX2Chart.dataStartDate) { 
		// default start date is beginning of action history
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').datepicker(
			'setDate', new Date (thisX2Chart.dataStartDate));
	} else {
		$('#' + thisX2Chart.chartType + '-chart-datepicker-from').datepicker(
			'setDate', new Date ());
	}

	// end date picker default
	$('#' + thisX2Chart.chartType + '-chart-datepicker-to').
		datepicker('setDate', new Date ()); // default end date

	// metric default
	$('#' + thisX2Chart.chartType + '-first-metric').children ().each (function () {
		$(this).attr ('selected', 'selected');
	});
	$('#' + thisX2Chart.chartType + '-first-metric').multiselect2 ('refresh');

};

/*
Filter function used by groupChartData to determine how chart data should be grouped
*/
X2ActionHistoryChart.prototype.chartDataFilter = function (dataPoint, type) {
	var thisX2Chart = this;

	if ((!(type === 'any' || type === '') && dataPoint['type'] !== type) ||
		(type === '' && dataPoint['type'] !== null)) {
		return true;
	} else {
		return false;
	}
};

/*
Returns dictionary with keys equal to metric types and value equal to metric type
labels
*/
X2ActionHistoryChart.prototype.getMetricTypes = function () {
	var thisX2Chart = this;

	var metricTypes = [];
	$('#' + thisX2Chart.chartType + '-first-metric').children ().each (function () {
		if (thisX2Chart.chartSubtype === 'pie' &&
			$(this).val () === 'any') return;
		metricTypes.push([$(this).val (), $(this).html ()]);
	});

	return metricTypes;
};

/*
Add pie chart specific css rules
*/
X2ActionHistoryChart.prototype.postPieChartTearDown = function () {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').removeClass ('action-history-pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').removeClass ('pie');
	$('#' + thisX2Chart.chartType + '-rel-chart-data-checkbox-container').removeClass ('pie');
};

/*
Remove pie chart specific css rules
*/
X2ActionHistoryChart.prototype.postPieChartSetUp = function () {
	var thisX2Chart = this;
	$('#' + thisX2Chart.chartType + '-chart').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-chart-legend').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-bin-size-button-set').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-datepicker-row').addClass ('action-history-pie');
	$('#' + thisX2Chart.chartType + '-top-button-row').addClass ('pie');
	$('#' + thisX2Chart.chartType + '-rel-chart-data-checkbox-container').addClass ('pie');
};



