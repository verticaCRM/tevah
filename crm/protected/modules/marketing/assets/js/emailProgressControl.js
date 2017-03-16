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

x2.EmailProgressControl = (function() {

    function EmailProgressControl(argsDict) {
        var defaultArgs = {

            sentCount: 0,
            totalEmails: null,
            listItems: [],
            sendUrl: '',
            campaignId: null,
            translations: {
                resume: '',
                pause: '',
                error: '',
                confirm: ''
            },

            paused:  false,
            currentlySending:  false, // Tells whether there's currently a send operation in progress
            nErrors:  0, // Number of errors
            containerSelector:  "#emailProgressControl",
            elements: {}
        };

        auxlib.applyArgs (this, defaultArgs, argsDict);
        this.init();
    }


    /**
     * Initial set-up of the email widget
     */
    EmailProgressControl.prototype.init = function() {
        var that = this;
        this.setUpSelectors();

        this.bar.progressbar({
            value: that.sentCount,
            max: that.totalEmails,
            change: function() {
                that.updateTextCount();
            }
        });

        this.updateTextCount();
        this.setUpButtons();

        // And now finally:
        if(that.listItems.length > 0 && !this.paused)
            that.start();
        else
            that.pause();

    }

    /**
     * Sets up the queries that the widget uses
     */
    EmailProgressControl.prototype.setUpSelectors = function() {
        this.container = $(this.containerSelector);
        
        // Progress bar
        this.bar = this.getElement("#emailProgressControl-bar");
        
        // Control div container
        this.controls = this.getElement("#emailProgressControl-toolbar");
        
        // Div displaying number of sends total
        this.progressText = this.getElement("#emailProgressControl-text");
        
        // Last message
        this.textStatus = this.getElement('#emailProgressControl-textStatus');
        
        // Displays error messages:
        this.errorBox = this.getElement('#emailProgressControl-errors');
        
        this.throbber = this.getElement('#emailProgressControl-throbber');
    }

    /**
     * Sets up the button click behaviors
     */
    EmailProgressControl.prototype.setUpButtons = function() {
        var that = this;

        // Pause Button
        this.toggleButton = this.controls.find('.startPause');
        this.toggleButton.click(function() {
            if(that.paused) {
                that.start();
            } else {
                that.pause();
            }
        });
        this.controls.find('.refresh').click(this.refresh);

        // Stop Button
        $("#campaign-toggle-button").bind("click",function(e){
            e.preventDefault();
            var element = this;
            if(that.paused) {
                $(element).parents("form").submit();
            } else {
                that.afterSend = function() {
                    $(element).parents("form").submit();
                }
            }
        });

        // Ask the user if they would really like to cancel the current campaign
        $("#campaign-complete-button").bind("click.confirm",function(e){
            e.preventDefault();
            var element = this;
            var proceed = that.listItems.length == 0;

            if(!proceed)
                proceed = confirm(that.translations['confirm']);

            if(proceed) {
                if(that.emailProgressControl.paused) {
                    $(element).parents("form").submit();
                } else {
                    that.afterSend = function() {
                        $(element).parents("form").submit();
                    }
                }
            } else {
                that.afterSend = function(){};
            }
        });
    }


    EmailProgressControl.prototype.getElement = function (selector) {
        if(typeof this.elements[selector] == 'undefined') {
            this.elements[selector] = this.container.find(selector);
        }
        return this.elements[selector];
    }

    /**
     * This function will always be called after an email has finished sending.
     */
    EmailProgressControl.prototype.afterSend = function() {};

    /**
     * Start or resume sending email by making AJAX requests to the server.
     */
    EmailProgressControl.prototype.start = function () {
        this.paused = false;
        this.showThrobber();
        this.toggleButton.find('.button-text').text(this.translations['pause']);
        this.toggleButton.find('.fa-pause').show();
        this.toggleButton.find('.fa-play').hide();
        this.send();
    }

    EmailProgressControl.prototype.pause = function () {
        this.paused = true;
        this.hideThrobber();
        this.toggleButton.find('.button-text').text(this.translations['resume']);
        this.toggleButton.find('.fa-pause').hide();
        this.toggleButton.find('.fa-play').show();
    }

    EmailProgressControl.prototype.errorMessage = function(message) {
        this.getElement('#emailProgressControl-errorContainer').show();
        this.errorBox.append(message+'<br />');
    }

    EmailProgressControl.prototype.refresh = function () {
        if(typeof x2.campaignChart != "undefined")
            x2.campaignChart.chart.getEventsBetweenDates();
        $.fn.yiiGridView.update("campaign-grid", {
            data: {
                "id_page": 1
            }
        })

    }

    /**
     * Recursive AJAX function that works its way through the email queue.
     *
     * This is where both making the AJAX request and updating the progress bar/text
     * should happen.
     */
    EmailProgressControl.prototype.send = function () {
        var that = this;
        if(this.listItems.length == 0) {
            // Halt; all done.
            this.textStatus.text(this.translations['complete']);
            this.pause();
            return;
        }
        this.currentlySending = true;
        var listItem = this.listItems.shift();
        $.ajax({
            url: that.sendUrl+'?campaignId='+that.campaignId+'&itemId='+listItem,
            dataType:'json',
            beforeSend: function () {that.showThrobber();}
        }).done(function(response){
            that.currentlySending = false;
            // Update text status
            that.textStatus.text(response.message);
            if(!(response.error && response.fullStop)) {
                // Update progress bar:
                that.sentCount++;
                that.bar.progressbar({'value':that.sentCount});
                if(response.undeliverable) { // List it as undeliverable and keep going
                    that.errorMessage(response.message);
                }
                if(!that.paused) { // Send the next one!
                    that.send();
                }
            } else { // full stop
                that.pause();
                that.listItems.push(listItem); // Add the item back in at the end
                that.errorMessage('<span class="emailFail">'+response.message+'</span>');
            }
        }).fail(function(jqXHR,textStatus,message) {
            that.pause();
            that.currentlySending = false;
            that.listItems.push(listItem); // Add the item back in at the end
            that.errorMessage('<span class="emailFail">'+that.text['Could not send email due to an error in the request to the server.']+' ('+textStatus+' '+jqXHR.errorCode+' '+message+')</span>');
        }).always(function() {
            that.hideThrobber();
            that.afterSend();
        });
    }

    EmailProgressControl.prototype.updateTextCount = function() {
        this.progressText.text(this.sentCount + '/' + this.totalEmails);
    }

    EmailProgressControl.prototype.showThrobber = function () {
        this.throbber.show();
    }

    EmailProgressControl.prototype.hideThrobber = function () {
        this.throbber.hide();
    }


    return EmailProgressControl;
})();

