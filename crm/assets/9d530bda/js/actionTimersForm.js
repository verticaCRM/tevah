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

if (typeof x2 == "undefined")
    x2 = {};
if (typeof x2.actionTimersForm == "undefined")
    x2.actionTimersForm = {};

x2.actionTimersForm.elements = {};

x2.actionTimersForm.getElement = function(selector) {
    if(typeof this.container == 'undefined') {
        this.container = $("#action-timers-form");
    }
    if(typeof this.elements[selector] == 'undefined') {
        this.elements[selector] = this.container.find(selector);
    }
    return this.elements[selector];
}

x2.actionTimersForm.recalculateLine = function (line) {
    var start = Math.round(line.find('.time-at-timestamp').datetimepicker('getDate').getTime()/1000);
    var end = Math.round(line.find('.time-at-endtime').datetimepicker('getDate').getTime()/1000);
    line.find('input.timer-total').val(end-start).trigger('change');
}

x2.actionTimersForm.recalculateTotal = function() {
    var total = 0;
    this.getElement('tr.timer-record').each(function(){
        total += parseInt($(this).find('input.timer-total').val());
    });
    this.getElement('input.timer-total.all-timers-total').val(total).trigger('change');
}

jQuery(document).ready(function () {
    $("table.action-timers-form").on("click","a.delete-timer",function(){
        $(this).parents('tr').each(function(index){
            $(this).find('input.timer-total').val("0");
        }).remove();
        x2.actionTimersForm.recalculateTotal();
    });
    
    $("table.action-timers-form").on("change",".time-input",function(){
        x2.actionTimersForm.recalculateLine($(this).parents('tr.timer-record').first());
    });

    $("table.action-timers-form").on("change","input.timer-total",function() {
        var that = $(this);
        var t_s = that.val();
        var seconds = t_s%60;
        var minutes = Math.floor(t_s/60)%60;
        var hours = Math.floor(t_s/3600);
        that.siblings("span.timer-total").each(function(){
            if(t_s < 0) {
                $(this).addClass("negative").text("< 0");
            } else {
                var pad = function(i) {
                    return (i < 10) ? "0" + i : i;
                }
                $(this).removeClass("negative").text(pad(hours)+":"+pad(minutes)+":"+pad(seconds));
            }
        });
        if(!$(this).hasClass('all-timers-total')) {
            x2.actionTimersForm.recalculateTotal();
        }
    });
});

