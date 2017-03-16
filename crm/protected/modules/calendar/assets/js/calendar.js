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

x2.calendarManager = (function () {

function CalendarManager (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        calendar : '#calendar',   
        translations: {
        }
    };
    //this._emailInvitationDialog$ = null;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._init ();
}

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/*
Private instance methods
*/
CalendarManager.prototype.insertDate = function(date, view, publisherName){
    if(typeof publisherName === 'undefined'){
        publisherName = '';
    }
    
    if (typeof view === 'undefined'){
        view = $(this.calendar).fullCalendar('getView');
    }

    // Preserve hours previously set in case the user is just switching
    // the day of the event:
    var newDate = {
        begin: new Date(date.getTime()),
        end: new Date(date.getTime())
    };
    var oldDate = {
        begin: auxlib.getElement(publisherName+' #event-form-action-due-date').datetimepicker('getDate'),
        end: auxlib.getElement(publisherName+' #event-form-action-complete-date').datetimepicker('getDate')
    };
    if(view.name == 'month' || view.name == 'basicWeek') {
        $(auxlib.keys(oldDate)).each(function(key, val){
            if(oldDate[val]) {
                newDate[val].setHours(oldDate[val].getHours())
                newDate[val].setMinutes(oldDate[val].getMinutes())
            }
        });
    }

    var dateformat = auxlib.getElement(publisherName+' #publisher-form').data('dateformat');
    var timeformat = auxlib.getElement(publisherName+' #publisher-form').data('timeformat');
    var ampmformat = auxlib.getElement(publisherName+' #publisher-form').data('ampmformat');
    var region = x2.publisher.getForm ().data('region');

    if(typeof(dateformat) == 'undefined') {
        dateformat = 'M d, yy';
    }
    if(typeof(timeformat) == 'undefined') {
        timeformat = 'h:mm TT';
    }
    if(typeof(ampmformat) == 'undefined') {
        ampmformat = true
    }
    if(typeof(region) == 'undefined') {
        region = '';
    }


    auxlib.getElement(publisherName+' #event-form-action-due-date').datetimepicker("destroy");
    auxlib.getElement(publisherName+' #event-form-action-due-date').datetimepicker(
        jQuery.extend(
            {
                showMonthAfterYear:false
            }, 
            jQuery.datepicker.regional[region], {
                'dateFormat':dateformat,
                'timeFormat':timeformat,
                'ampm':ampmformat,
                'changeMonth':true,
                'changeYear':true, 
                'defaultDate': newDate.begin
            }
        )
    );
    auxlib.getElement(publisherName+' #event-form-action-due-date').datetimepicker('setDate', newDate.begin);

    auxlib.getElement(publisherName+' #event-form-action-complete-date').datetimepicker("destroy");
    auxlib.getElement(publisherName+' #event-form-action-complete-date').datetimepicker(
        jQuery.extend(
            {
                showMonthAfterYear:false
            }, 
            jQuery.datepicker.regional[region], {
                'dateFormat':dateformat,
                'timeFormat':timeformat,
                'ampm':ampmformat,
                'changeMonth':true,
                'changeYear':true,
                'defaultDate': newDate.end
            }
        )
    );
    auxlib.getElement(publisherName+' #event-form-action-complete-date').datetimepicker('setDate', newDate.end);

    auxlib.getElement(publisherName+' #event-action-description').click ();
    auxlib.getElement(publisherName+' #event-action-description').select();
    auxlib.getElement(publisherName+' #event-action-description').focus();
    
    return false;
}


// Called by the event editor 
CalendarManager.prototype.giveSaveButtonFocus = function(){
    $('.ui-dialog-buttonpane').find ('button').removeClass ('highlight');
    $('.ui-dialog-buttonpane').find('button:contains("Save")')
    .addClass('highlight')
    .focus();
}

// Function to formate a javascript Date  object into yyyymmdd
CalendarManager.prototype.yyyymmdd = function(date) {
    var yyyy = date.getFullYear().toString();
    var mm = (date.getMonth()+1).toString(); // getMonth() is zero-based
    var dd  = date.getDate().toString();
    return yyyy +"-"+ (mm[1]?mm:"0"+mm[0]) +"-"+ (dd[1]?dd:"0"+dd[0]); // padding
};

CalendarManager.prototype.dayNumberClick = function (target) {
    var date = $(target).closest ('td').attr ('data-date').split ('-');

    $(this.calendar).fullCalendar ('gotoDate', date[0], date[1] - 1, date[2]);
    $(this.calendar).fullCalendar ('changeView', 'agendaDay');
    return false;
};

CalendarManager.prototype.updateWidgetSetting = function(setting, value){
    return $.ajax({
        url: this.widgetSettingUrl,
        data: {
            widget: 'SmallCalendar',
            setting: setting,
            value: value
        }
    });
}

//CalendarManager.prototype.emailInvitationDialog = function () {
//    this._emailInvitationDialog$ = $('#email-inviation-dialog');
//};

CalendarManager.prototype._init = function () {
    $(this.calendar).find('.day-number-link').click (function () { return false; });
};

return new CalendarManager ();

}) ();


(function () {

/**
 * Add method to layout manager to set up responsiveness of calendar title bar
 */
x2.LayoutManager.prototype.setUpCalendarTitleBarResponsiveness = function () {
    var that = this;

    function hideTitleBar (titleBar) {
        $(titleBar).css ({height: ''});
        $(titleBar).find ('.responsive-menu-items').css ({display: ''});
    }

    function showTitleBar (titleBar) {
        $(titleBar).animate ({ height: ($(titleBar).height () * 2) + 'px' }, 300);
        $(titleBar).find ('.responsive-menu-items').show ();
        $(titleBar).find ('.responsive-menu-items').css ({display: 'block'});
    }

    $('.fc-header.responsive-page-title .mobile-dropdown-button').unbind ('click').
        bind ('click', function () {

        var titleBar = $(this).parents ('.responsive-page-title');
        if ($(titleBar).find ('.responsive-menu-items').is (':visible')) {
            that._minimizeResponsiveTitleBar (titleBar);
        } else {
            auxlib.onClickOutside ($('.fc-header'), function () {
                that._minimizeResponsiveTitleBar (titleBar);
            }, true, 'setUpCalendarTitleBarResponsiveness');
            $(window).one ('resize._setUpTitleBarResponsiveness', function () {
                if ($(titleBar).find ('.responsive-menu-items').is (':visible')) {
                    that._minimizeResponsiveTitleBar (titleBar);
                }
            });
            that._expandResponsiveTitleBar (titleBar);
        }
    });

};

}) ();
