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

$.widget('x2.summationReportGridResizing', $.x2.reportGridResizing, {
    _create:function() {
        console.log ('gridReportGridResizing create');
        var that = this;
        this._super ();
    },
    _destroy:function() {
        this._super ();
        this.element.removeData('x2-summationReportGridResizing');
    },
    /**
     * Override parent method so to handle expand button column width as a special case
     */
    scanColWidths:function() {
        // temporarily set display of header table to block, causing widths of header cells to
        // automatically resize
        this.t1.table.css ({display: 'block'});
        this.colWidths = []; // clear previous stuff
        var colCount = this.t1.masterCells.length;
        if(this.options.ignoreLastCol)
            colCount--;
        if (this.t1.masterCells.eq (0).attr ('id').match (/subgrid-expand-button-column/)) { 
            var i = 1;
            var expandButtonWidth = 27;
            this.t1.masterCells.eq(0).width (expandButtonWidth);
            this.colWidths.push (expandButtonWidth);
        } else {
            var i = 0;
        } 
        for(;i<colCount;i++) {
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

/**
 * Subclass parent so that gridResizingClass can be swapped for x2.gridReportGridResizing
 */
$.widget("x2.summationReportGvSettings", $.x2.reportGridSettings, {
    options: {
        // swap class dependencies
        gridResizingClass: 'summationReportGridResizing',
        reportConfig: {}
    },
    _create:function() {
        //console.log ('summationReportGvSettings');
        this._setUpSubgridButtonBehavior ();
        this._super ();
    },
    /**
     * Set up behavior of sub grid expand/collapse buttons 
     */
    _setUpSubgridButtonBehavior: function () {
        var that = this;

        // show/request sub grid
        this.element.find ('.subgrid-expand-button').unbind ('click._setUpSubgridButtonBehavior')
            .bind ('click._setUpSubgridButtonBehavior', function () {
        
            var groupAttrValues = JSON.parse ($(this).attr ('data-group-attr-values'));
            var subgridRow$ = $(this).closest ('tr').next ('.x2-subgrid-row');
            if (subgridRow$.length) { // requested before, just show it
                subgridRow$.show ();
                $(this).hide ();
                $(this).next ().show ();
            } else { 
                that._requestSubgrid (groupAttrValues, $(this).closest ('tr'));
            }
        });

        // hide sub grid
        this.element.find ('.subgrid-collapse-button').unbind ('click._setUpSubgridButtonBehavior')
            .bind ('click._setUpSubgridButtonBehavior', function () {
        
            $(this).closest ('tr').next ('.x2-subgrid-row').hide ();
            $(this).hide ();
            $(this).prev ().show ();
        });
    },
    /**
     * Request drill down sub grid and insert into parent grid
     * @param object groupAttrValues 
     */
    _requestSubgrid: function (groupAttrValues, currRow$) {
        console.log ('_requestSubgrid');
        console.log (this.options.reportConfig);

        // add options to get params
        for (var key in this.options.reportConfig) {
            if (key.match (/.*FormModel$/)) {
                var formName = key;
                break;
            }
        }

        var params = $.extend (true, {}, this.options.reportConfig);
        params[formName]['groupAttrValues'] = groupAttrValues;
        params[formName]['generateSubgrid'] = 1;
        params[formName]['subgridIndex'] = currRow$.index ();
        console.log ('formName = ');
            console.log (formName);


        $.ajax ({
            url: window.location + '?' + $.param (params),
            dataType: 'json',
            success: function (data) {
                var subgridRow$ = $('<tr>', {
                    'class': 'x2-subgrid-row'
                }); 
                var subgridCell$ = $('<td>', {
                    colspan: currRow$.children ('td').length 
                }); 
                subgridRow$.append (subgridCell$);
                currRow$.after (subgridRow$);
                subgridCell$.html (data.report);
                currRow$.find ('.subgrid-expand-button').hide ();
                currRow$.find ('.subgrid-collapse-button').show ();
            }
        });
    }
});

})(jQuery);
