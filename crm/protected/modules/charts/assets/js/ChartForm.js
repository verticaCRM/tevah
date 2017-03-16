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

/* @edition:pro */

//$.fn.populate = function(data){
//    console.log(this);
//    this.html('');
//    var that = this;
//    $.each(data, function(key, val){
//      that.append('<option value="' + val + '">' + val + '</option>');
//    })
//};

x2.ChartForm = (function () {

function serialToJSON(serial) {
    var obj = {};
    for(var i in serial) {
        var name = serial[i].name.replace(/.*\[/g,'').replace(']','');
        obj[name] = serial[i].value;
    }

    return obj;
}

function ChartForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        controllerUrl: yii.scriptUrl+'/reports/'
    };
    
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.X2Form.call (this, argsDict);
}

ChartForm.prototype = auxlib.create (x2.X2Form.prototype);

ChartForm.prototype._init = function () {
    this.setUpFormBehavior();
};

ChartForm.prototype.setUpFormBehavior = function () {
    var that = this;
    // this.select('reportId').change(function() {
    //     that.populateReportSelections( $(this).val() ); 
    // });
    this._form$.find('#submit-button').click(function(e) {
        e.preventDefault();
        that.submitForm();
    });
};

ChartForm.prototype.submitForm = function() {
    var that = this;

    // Convert form to serial array
    var serial = this._form$.serializeArray();

    //Convert form to a json
    var form = serialToJSON(serial);

    // Add the report Id from the chart creator
    form.reportId = x2.chartCreator.report.id;

    // Add a key of the form model name
    var json = {};
    json[this.formModelName] = form;

    $.ajax({
        url: this.controllerUrl + 'createChart',
        data: {
            attributes: JSON.stringify(json)
        },
        dataType: 'json',
        success: function(data) {
            if( data.widget ){
                x2.chartCreator.closeDialog();
                x2.forms.clearForm(that._form$, true);
                that._form$.find('.confirmed').removeClass('confirmed');

                $(data.widget).appendTo(
                    $('#data-widgets-container-inner')
                ).css('opacity', 0.0)
                .animate({
                    opacity: 1.0
                }, 400);

            } else {
                that.highlightErrors(data);  
            }
        }
    });
}

ChartForm.prototype.highlightErrors = function(errors) {
    x2.forms.clearErrorMessages(this._form$);

    var errorList = [];
    for (var key in errors) {
        this._form$.find("#"+key).addClass('error');
        errorList = errorList.concat(errors[key]);
    }

    this._form$.append ( 
        x2.forms.errorSummary ('', errorList) 
    );


    // this._form$.find('.row.error').remove();

    // for(var field in errors) {
    //     this.select(field).addClass('error');
    //     var error = errors[field];

    //     for (var line in error ){
    //         // console.log(this._$fpr,);
    //         $('<div class="row error"></div>').
    //             appendTo( this._form$ ).
    //             html(error[line]);
    //     }
    // }
}


// ChartForm.prototype.populateReportSelections = function(reportId) {
//     var that = this;

//     $.ajax({
//         url:  this.controllerUrl + 'columns',
//         data: {
//             reportId: reportId
//         },
//         dataType: "json",
//         success: function (data) {
//             console.log(data);
//             var select$ = that.select('reportColumn');
//             select$.populate(data);
//         }
//     });

//     $.ajax({
//         url:  this.controllerUrl + 'rows',
//         data: {
//             reportId: reportId
//         },
//         dataType: "json",
//         success: function (data) {
//             console.log(data);
//             var select$ = that.select('reportRow');
//             select$.populate(data);
//         }
//     });

// }

ChartForm.prototype.select = function (id) {
    return this._form$.find('#'+this.formModelName+'_'+id);
};

// ChartForm.prototype.getDialogParams = {
//     autoOpen: true,
//     resizable: true,
// };



// ChartForm.prototype.setUpChartTypeBehavior = function(){
//     that = this;
// 	this._form$.find('#ChartFormModel_chartTypeField').
//         children().change( function(){
// 	       $.ajax({
//             url: 'reports/chartForm',
//             data: {
//                 form: 'GaugeForm' },
//             success: function(data) {
//                 that._form$.append(data) }, 
//             });
//         });
// };

return ChartForm;

}) ();
