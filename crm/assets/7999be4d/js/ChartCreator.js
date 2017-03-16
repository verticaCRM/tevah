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

// // console.log('asfa');
// x2.ChartCreator = (function() {

// 	$.fn.column = function(parent) {
// 		if (typeof parent === 'undefined')
// 			parent = $(this).closest('table');

// 		var col = $(this).parent().children().index($(this))+1;
// 		var rows = $(parent).find('table tr');
// 		var nth = ':nth-child('+col+')';
// 		return rows.find('td'+nth+', th'+nth);
// 	}


// 	function ChartCreator(argsDict){
// 		var defaultArgs = {
// 			reportSelector: '#generated-report',
// 			summationSelector: '#summation-grid'
// 		};
// 		auxlib.applyArgs(this,  defaultArgs, argsDict);

// 		this.$report = $(this.reportSelector).add(this.summationSelector);
// 	}

// 	ChartCreator.prototype.isSummation = function (element){
// 		if ( $(this.summationSelector).find(element).length !== 0 ) {
// 			return true;
// 		}

// 		return false;
// 	}

// 	ChartCreator.prototype.rowIndex = function(row){
// 		if ( this.isSummation(row) && $(row).is('tr') ) {
// 			return 'total';
// 		}

// 		return $(row).
// 			parent().
// 			children().
// 			index( $(row) ) ;
// 	}

// 	// ChartCreator.prototype.selectRow = function(){
// 	// 	var rows = this.$report.find('tr');
// 	// 	this.enterSelection(rows);

// 	// 	// var that = this; 
// 	// 	// var rows = this.$report.find('tbody tr').hover(
// 	// 	// 	function() {
// 	// 	// 		$(this).addClass('active-selection').css('opacity',0.5);
// 	// 	// 	},
// 	// 	// 	function() {
// 	// 	// 		$(this).removeClass('active-selection').css('opacity',1);
// 	// 	// 	}
// 	// 	// ).click(function() {
// 	// 	// 	var rowIndex = that.rowIndex(this);

// 	// 	// 	that.insertSelection(rowIndex);

// 	// 	// });
// 	// };


// 	ChartCreator.prototype.enterSelection = function(type) {
// 		var that = this;

// 		var selection;
// 		if (type == 'row') {
// 			selection = that.$report.find('tr');
// 		} else if (type == 'column') {
// 			selection = that.$report.find('td, th');
// 		}

// 		handlers = {};
// 		handlers.addClass = function() {
// 			selector = $(this);
// 			if (type == 'column' ) {
// 				selector = $(this).column( that.$report );
// 			}

// 			selector.addClass('active-selection');
// 		};

// 		handlers.removeClass = function() {
// 			selector = $(this);
// 			if (type == 'column' ) {
// 				selector = $(this).column( that.$report );
// 			}
// 			selector.removeClass('active-selection');
// 		};

// 		handlers.insertSelection = function() {
// 			var index = that.rowIndex(this);
// 			that.selectedIndex = index;
// 		}

// 		selection.hover( handlers.addClass, handlers.removeClass ).click( handlers.insertSelection );
// 		this.handlers = handlers;

// 	}

// 	ChartCreator.prototype.exitSelection = function() {
// 		for(var i in this.handlers) {
// 			this.$report.find('tr, th, td').unbind('click', this.handlers[i]);
// 			this.$report.find('tr, th, td').unbind('mouseenter', this.handlers[i]);
// 			this.$report.find('tr, th, td').unbind('mouseleave', this.handlers[i]);
// 		}

// 		this.handlers = {};
// 	}

// 	// ChartCreator.prototype.selectColumn = function() {
// 	// 	var that = this;
// 	// 	// var cells = this.$report.find('tbody td').column();
// 	// 	// var cells = this.$report.find('td, th').hover(
// 	// 	// 	function() {
// 	// 	// 		$(this).column(that.$report).addClass('active-selection').css('opacity', 0.5);
// 	// 	// 	},
// 	// 	// 	function() {
// 	// 	// 		$(this).column(that.$report).removeClass('active-selection').css('opacity', 1);
// 	// 	// 	}
// 	// 	// ).click(function() {
// 	// 	// 	var colIndex = that.rowIndex(this);
// 	// 	// 	that.insertSelection(colIndex);
// 	// 	// });

// 	// }


// 	ChartCreator.prototype.activate = function(){
// 		console.log(this._reportContainer);
// 		// this.selectColumn();
// 		// this.selectRow();
// 		this.enterSelection('column');
// 	}



// 	return ChartCreator;
// })();

