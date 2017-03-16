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
 * Sublclasses classes defined in x2gridview.js
 */

(function($) {

/**
 * Abstract base class for report grid resizing classes. This should not be instantiated.
 */
$.widget('x2.reportGridResizing', $.x2.gridResizing, {
    options:{
        minColWidth:60,
    },

    /**
     * Allows columns of sibling summation grid view to be updated as user drags columns in main 
     * report grid view
     */
	_create:function() {
        //that.DEBUG && console.log ('gridReportGridResizing create');
        var that = this;
        this._super ();
        // keep track of summation row
        this.summationRow = 
            $(this.element).closest ('.x2-gridview').siblings ('.x2-gridview').find ('tr');
        // initialize widths of summation row columns
        //that.DEBUG && console.log ('updating col alignment');
        this.summationRow.find ('td').each (function (i) {
            //that.DEBUG && console.log (this);
            that.updateColWidth.call (that, i); 
        });
    },
    /**
     * whenever main report grid view columns are resized, resize summation grid's columns to match
     */
	updateColWidth:function(index) {
        if (this.summationRow.length) {
            $.makeArray (this.summationRow.find ('td'))[index].width = this._super (index);
            this.options.owner.checkScrollBarHideShow ();
        } else {
            this._super (index);
        }
	},

    /**
     * Override parent method so that default width accomodates width of column label
     */
    scanColWidths:function() {
        if (typeof this.t1.masterCells.eq (0).attr ('style') !== 'undefined') {
            // widths already set by user
            this._super ();
            return;
        }

        // temporarily set display of header table to block, causing widths of header cells to
        // automatically resize
        this.t1.table.css ({display: 'block'});
        this.colWidths = []; // clear previous stuff
        var colCount = this.t1.masterCells.length;
        if(this.options.ignoreLastCol)
            colCount--;
        for(var i=0;i<colCount;i++) {
            var cell = this.t1.masterCells.eq(i);
            if (typeof $(cell).attr ('style') !== 'undefined') {
                var w = Math.max(this.options.minColWidth,cell.width());
            } else {
                var w = Math.max(this.options.minColWidth,cell.width() + 15);
            }
            this.colWidths.push(w);
        }

        // remove temporary styling
        this.t1.table.css ({display: ''});
    },
});

$.widget("x2.reportColDragging", $.x2.colDragging, {
	_create:function() {
        var that = this;
        this._super ();
        // keep track of summation row
        this.summationRow = 
            $(this.element).closest ('.x2-gridview').siblings ('.x2-gridview').find ('tr');
    },
	_afterMouseStop:function() {
        var that = this;
        if (this.summationRow.length) {
            var cells = this.summationRow.find ('td');
            var startCol = this.dragged.index;
            var endCol = this.hoverIndex;
            $(cells.eq (startCol)).insertBefore (cells.eq (endCol));
            that.DEBUG && console.log ('endCol = ');
            that.DEBUG && console.log (endCol);
        }

        //cells.eq[this.dragged.index
    }
});

/**
 * Subclass parent so that gridResizingClass can be swapped for x2.gridReportGridResizing
 */
$.widget("x2.reportGridSettings", $.x2.gvSettings, {
    options: {
        // swap class dependencies
        gridResizingClass: 'reportGridResizing',
        reportConfig: {},
        currPageRawData: {},
        headers: []
    },

	_create:function() {
        var that = this;
        // swap class dependencies
        this.options.colDraggingClass = 'reportColDragging';
        this.summationGrid$ = $(this.element).siblings ('.x2-gridview');
        this._super ();
        if (this.summationGrid$.length)  {
            this._setUpGridViewScrollBehavior ();
            this._movePagerDown ();
        }
    },
    /**
     * Adds Summation grid links to update links
     */
    getUpdateLinks: function () {
        return $.merge (this._super (), this.summationGrid$.gvSettings ('getUpdateLinks'));
    },
    _movePagerDown: function () {
        var that = this;
        that.DEBUG && console.log ('moving pager down');
        var pager = $(this.element).find ('.pager').detach ();
        // remove previously appended pager
        if (this.summationGrid$.children ().last ().hasClass ('pager')) {
            this.summationGrid$.children ().last ().remove ();
        }
        // add grid id to pager so when pagination buttons are clicked, click handler in
        // jquery.yiigridview.js can make a request with the correct parameters
        pager.data ('grid-id', this.element.attr ('id'));
        this.summationGrid$.append (pager);
    },
    /**
     * Force synchronization of horizontal scroll position of summation grid and this grid
     */
    _setUpGridViewScrollBehavior: function () {
        var that = this;
        that.DEBUG && console.log (this.element);
        that.DEBUG && console.log (this.summationGrid$);
        var bodyContainer$ = $(this.element).find ('.x2grid-body-container');
        var summationBodyContainer$ = this.summationGrid$.find ('.x2grid-body-container');
        bodyContainer$.off ('scroll._setUpGridViewScrollBehavior').
            on ('scroll._setUpGridViewScrollBehavior', function () {
                that.DEBUG && console.log ('scroll');
                summationBodyContainer$.scrollLeft ($(this).scrollLeft ());
            }).scroll ();
        summationBodyContainer$.off ('scroll._setUpGridViewScrollBehavior').
            on ('scroll._setUpGridViewScrollBehavior', function () {
                bodyContainer$.scrollLeft ($(this).scrollLeft ());
            });

        // hide/show the scroll bar as the window resizes by moving the summation grid up and down
        $(window).unbind ('resize._setUpGridViewScrollBehavior').
            bind ('resize._setUpGridViewScrollBehavior', function () {
                that.checkScrollBarHideShow ();
            }).resize ();

    },
    /**
     * Checks if scroll bar on main grid view body is hidden or shown and adjusts position of 
     * summation grid accordingly
     */
    checkScrollBarHideShow: function () {
        var that = this;
        var bodyContainer$ = $(this.element).find ('.x2grid-body-container');
        if (bodyContainer$.width () <
            bodyContainer$.get (0).scrollWidth) {

            // scroll bar is visible, move summation grid up to cover it
            that.summationGrid$.css ({
                'margin-top': '-10px'
            });
        } else {
            that.summationGrid$.css ({
                'margin-top': '0px'
            });
        }
    }
});

})(jQuery);
