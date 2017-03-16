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

$.widget('x2.summationReportNestedGridResizing', $.x2.reportGridResizing, {
    _create:function() {
        x2.DEBUG && console.log ('summationReportNestedGridResizing create');
        var that = this;
        this._super ();
    },
    _destroy:function() {
        this._super ();
        this.element.removeData('x2-summationReportNestedGridResizing');
    },
});

/**
 * Subclass parent so that gridResizingClass can be swapped for x2.gridReportGridResizing
 */
$.widget("x2.summationReportNestedGvSettings", $.x2.gvSettings, {
    options: {
        // swap class dependencies
        gridResizingClass: 'summationReportNestedGridResizing',
        reportConfig: {}
    },
});

})(jQuery);
