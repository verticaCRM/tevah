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



if (typeof x2 == "undefined") {
    x2 = {};
}

x2.EmailInbox = (function () {

function EmailInbox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        noneSelectedText: null,
        deleteConfirmTxt: null,
        pollTimeout: null,
        emailFolder: null,
        /**
         * @var bool loadMessagesOnPageLoad 
         */
        loadMessagesOnPageLoad: true,
        /**
         * @var bool notConfigured 
         */
        notConfigured: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._emailInboxGridViewManager = $('#email-list').data ('x2-emailInboxesGridSettings');
    this._init ();
}

/**
 * Initiate a request to perform an email action
 * and update the grid view
 */
EmailInbox.prototype.performEmailAction = function(action, args, complete) {
    var settings = {
        emailAction: action
    };
    $.extend (settings, args);
    this._emailInboxGridViewManager.showIndex ();
    $.fn.yiiGridView.update("email-list", {
        data: settings,
        complete: function () { 
            complete ();
        }
    });
}

/**
 * Change the currently selected folder
 */
EmailInbox.prototype.selectFolder = function(folder) {
    var that = this;
    that.emailFolder = folder;
    var overrideParams = 0;
    var newUrl = $.param.querystring (window.location.href, {emailFolder: folder}, overrideParams);
    $('#email-list .keys').attr ('title', newUrl);
    x2.history.pushState ({emailFolder: folder}, '', newUrl);

    that.performEmailAction ('selectFolder', {
        'emailFolder': folder
    }, function () {
        $('.current-folder').removeClass ('current-folder');
        $('.folder-link').each (function () { 
            that._emailInboxGridViewManager.showIndex ();
            var linkFolder = $(this).attr ('data-folder');
            if (linkFolder === folder) {
                $(this).addClass ('current-folder');
                return false;
            }
        });
    });
};

/**
 * Handle polling for new emails according to admin-defined settings
 */
EmailInbox.prototype.poll = function() {
    var that = this;
    $.fn.yiiGridView.update("email-list", {
        type: 'post',
        data: {
            emailAction: 'refresh'
        },
        complete: function(jqXHR, textStatus) {
            if (textStatus === 'success') 
                window.setTimeout (function () { that.poll (); }, 1000 * 60 * that.pollTimeout);
        }
    });
};

EmailInbox.prototype._setUpPolling = function () {
    var that = this;
    // Fetch the data provider and begin polling
    if (that.loadMessagesOnPageLoad) {
        $.fn.yiiGridView.update("email-list", {
            complete: function(xhr, status) {
                if (status === "success") {
                    $('#email-list .empty-text-progress-bar').width ('100%');
                    window.setTimeout(function () { that.poll (); }, 1000 * 60 * that.pollTimeout);
                }
            }
        });
    } else {
        window.setTimeout(function () { that.poll (); }, 1000 * 60 * that.pollTimeout);
    }
};

EmailInbox.prototype._setUpInboxMenu = function () {
    var that = this;
    var inboxMenu$ = $('#inbox-menu');    
    inboxMenu$.find ('.folder-link').click (function () {
        var folder = $(this).attr ('data-folder');
        that.selectFolder (auxlib.htmlDecode (folder));
        return false;
    });

};

/**
 * Bind state change event to preserve browser back button functionality across ajax-loaded
 * pages.
 */
EmailInbox.prototype._setUpHistoryStateChange = function () {
    var that = this;

    x2.history.bind (function () {
        var state = window.History.getState ();
        console.log ('EmailInbox: state.data = ');
        console.log (state.data);

        if (typeof state.data['emailFolder'] !== 'undefined') {
            var folder = state.data['emailFolder'];
            if (folder !== that.emailFolder)
                that.selectFolder (folder);
        } else if (that.emailFolder && that.emailFolder !== 'INBOX') {
            that.selectFolder ('INBOX');
        }
    });
};

EmailInbox.prototype._init = function () {
    if (!this.notConfigured) this._setUpPolling ();
    this._setUpInboxMenu ();
    this._setUpHistoryStateChange ();
};

return EmailInbox;

}) ();
