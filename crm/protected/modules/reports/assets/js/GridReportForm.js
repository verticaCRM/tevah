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

x2.GridReportForm = (function () {

function GridReportForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.ReportForm.call (this, argsDict);
        console.log(argsDict);

}

GridReportForm.prototype = auxlib.create (x2.ReportForm.prototype);

GridReportForm.prototype._setUpCellDataTypeSelection = function () {
    $('#cell-data-type').change (function () {
        if ($(this).val () !== 'count') {
            $('#cell-data-field-container').slideDown ();
        } else {
            $('#cell-data-field-container').slideUp ();
        }
    });
};

GridReportForm.prototype._setUpWorkflowDropdown = function () {
    var that = this;
    this._settingsForm$.find ('.field-dropdown').change (function () {
        if (that._primaryModelType$.val () === 'Actions' &&
            $(this).val () === 'stageNumber') { 

            $(this).parent ().next ().show ().children ().removeAttr ('disabled');
        } else {
            $(this).parent ().next ().hide ().children ().attr ('disabled', 'disabled');
        }
    }).change ();
};

GridReportForm.prototype._init = function () {
    this._setUpCellDataTypeSelection (); 
    this._setUpWorkflowDropdown (); 
    x2.ReportForm.prototype._init.call (this);
};


return GridReportForm;

}) ();
