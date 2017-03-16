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

if (typeof x2 === 'undefined') x2 = {};

TwitterFeedWidget = (function () {

function TwitterFeedWidget (argsDict) {
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        lastTweetId: null,
        screenName: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    SortableWidget.call (this, argsDict);
}

TwitterFeedWidget.prototype = auxlib.create (SortableWidget.prototype);

TwitterFeedWidget.prototype._loadMoreTweets = function (reload) {
    reload = typeof reload === 'undefined' ? false : reload; 
    var that = this;
    var data = {
        twitterFeedAjax: true,
    };
    if (!reload) {
        data.maxTweetId = this.lastTweetId;
    }
    if (this.screenName) {
        data.twitterScreenName = this.screenName;
    }
    $.ajax ({
        url: window.location.href,
        type: 'GET',
        data: data,
        success: function (data) {
            that._listView$.replaceWith (data);
            that._listView$ = that.element.find ('.list-view');
            if (reload) {
                that._origListViewHeight = that._listView$.height ();
            }
            that._listView$.parent ().css ({
                'max-height': that._origListViewHeight + 'px',
                'overflow-y': 'auto'
            });
        },
        error: function (data) {
            x2.topFlashes.displayFlash (data.responseText, 'error', 'clickOutside', true);
        }
    });

};

TwitterFeedWidget.prototype._setUpPaginationButtonBehavior = function () {
    var that = this;
    this._paginationButton$.click (function () {
        that._loadMoreTweets (); 
    });
};

TwitterFeedWidget.prototype._setUpScreenNameSelection = function () {
    var that = this;
    var screenNameSelector$ = $('#screen-name-selector');
    screenNameSelector$.change (function () {
        that.screenName = $.trim ($(this).val ()); 
        that._listView$.parent ().attr ('style', '');
        that._loadMoreTweets (true);
    });
};

TwitterFeedWidget.prototype._init = function () {
    SortableWidget.prototype._init.call (this);
    this._paginationButton$ = this.element.find ('.load-more-tweets-button');
    this._listView$ = this.element.find ('.list-view');
    this._origListViewHeight = this._listView$.height ();
    this._setUpPaginationButtonBehavior ();
    this._setUpScreenNameSelection ();
};

return TwitterFeedWidget;

}) ();



