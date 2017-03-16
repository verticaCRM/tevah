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

$.widget("x2.emailInboxesGridSettings", $.x2.gvSettings, {
    options: {
        messageView: false,
        loadingMailbox: false,
        myInboxId: null,
        translations: {
            tabSettingsDialogTitle: '',
            Cancel: '',
            Save: ''
        }
    },
    /**
     * Called after message is deleted from message view page
     */
    afterMessageDelete: function () {
        this._backToIndex ();
    },
    _create:function() {
        if (this.options.messageView) {
            var uid = $.deparam.querystring (window.location.href)['uid'];
            x2.history.replaceState ({uid: uid}, '', window.location.href);
        } else {
            x2.history.replaceState ({}, '', window.location.href);
        }

        this._super ();
        this._tabSettingsDialog$ = null;
        this._setUpMessageFlagging ();
        this._setUpSearch ();
        this._setUpMessageView ();
        var that = this;
        $(function () {
            if (that.options.messageView) that._viewMessage (null, false);
        });
        this._setUpHistoryStateChange ();
        this._setUpRefresh ();
        this._setUpNewMessageButtonBehavior ();
        this._setUpDownloadLinks ();
        this._setUpTabBehavior ();
        if (this.options.loadingMailbox) this._setUpEmptyGridLoadingBar ();
    },
    _setUpTabBehavior: function () {
        var that = this;
        var tabSettingsButton$ = $('#email-inbox-tab-plus');

        // open settings dialog
        tabSettingsButton$.unbind ('click._setUpTabBehavior0').
            bind ('click._setUpTabBehavior0', function () {

            that._openTabSettingsDialog (); 
            return false;
        });

        this.element.find ('.email-inbox-tab').
            unbind ('click._setUpTabBehavior').
            bind ('click._setUpTabBehavior', function () {

            if ($(this).is ('.selected-tab, #email-inbox-tab-plus')) return false;

            // reload page with selected mailbox
            var id = $(this).find ('a').attr ('data-id');
            window.location = yii.scriptUrl + '/emailInboxes/emailInboxes/index?id=' + id;
            return false;
        });

        // instantiate multiselect
        var tabsMultiselect$ = this.element.find ('.email-inbox-tabs-multiselect').multiselect ({
            searchable: false,
            // prevent personal inbox from being removed
            irremovableOptions: [parseInt (that.options.myInboxId, 10)],
            disableMassActions: true,
            'class': 'x2-multiselect-ui-element'
        });
    },
    _openTabSettingsDialog: function () {
        if (this._tabSettingsDialog$) this._tabSettingsDialog$.dialog ('open');
        var that = this;

        this._tabSettingsDialog$ = $('#email-inbox-tab-settings-dialog');
        this._tabSettingsDialog$.dialog ({
            title: this.options.translations.tabSettingsDialogTitle,
            width: 500,
            autoOpen: true, 
            buttons: [
                {
                    text: this.options.translations.Cancel,
                    click: function () {
                        $(this).dialog ('close');
                    }
                },
                {
                    text: this.options.translations.Save,
                    'class': 'highlight',
                    click: function () {
                        that._saveTabSettings ();
                        $(this).dialog ('close');
                    }
                }
            ]
        });
    },
    _saveTabSettings: function () {
        var that = this;
        $.ajax ({
            type: 'POST',
            url: yii.scriptUrl + '/emailInboxes/emailInboxes/saveTabSettings',
            data: this._tabSettingsDialog$.find ('select').serialize (),
            success: function () {
                $.fn.yiiGridView.update(that._element ().attr ('id'));
            }
        });
    },
    _setUpEmptyGridLoadingBar: function () {
        var progressBar$ = this._element ().find ('.empty-text-progress-bar');
        var progressBarContainer$ = progressBar$.closest ('tr');
        progressBar$.width ('3%'); // starting width

        // animation helper functions 
        var maxWait = 1.7;
        function randomDuration () {
            return Math.random () * maxWait * 1000;
        }
        var maxDelta = 2;
        function randomWidthDelta () {
            return Math.random () * maxDelta;
        }

        // animate loading bar
        var maxWidth = 91 + Math.random () * 4;
        setTimeout (function trickle () {
            var newWidth = Math.min (maxWidth,
                progressBar$.width () / progressBarContainer$.width () * 100 + randomWidthDelta ());
            progressBar$.width (newWidth + '%');
            if (newWidth < maxWidth) {
                setTimeout (trickle, randomDuration ());
            }
        }, randomDuration ());
    },
    /**
     * Sets up behavior of attachment download links 
     */
    _setUpDownloadLinks: function () {
        $('#message-container').off ('click._setUpMessageView', '.attachment-download-link').on (
            'click._setUpMessageView', '.attachment-download-link', function (evt) {
    
            var url = $(this).attr ('data-href');
            window.location.href = url;
            return false;
        });
    },
    /**
     * Sets up behavior of refresh button 
     */
    _setUpRefresh: function () {
        var that = this;
        this._element ().find ('.mailbox-refresh-button').unbind ('click._setUpRefresh')
            .bind ('click._setUpRefresh', function () {

            $.fn.yiiGridView.update(that._element ().attr ('id'), {
                data: {
                    emailAction: 'refresh'
                }
            });

        });
    },
    /**
     * Bind state change event to preserve browser back button functionality across ajax-loaded
     * pages.
     */
    _setUpHistoryStateChange: function () {
        var that = this;
        if (this.options.ajaxUpdate) return;

        x2.history.bind (function () {
            var state = window.History.getState ();

            if (typeof state.data['newMessage'] !== 'undefined') {
                this.currMessageUid = null;
                that._openNewMessageEditor (false);
            } else if (typeof state.data['reply'] !== 'undefined') {
                this.currMessageUid = null;
                that._openReplyEditor ();
            } else if (typeof state.data['uid'] !== 'undefined') {
                that._viewMessage (state.data['uid'], false, false);
            } else {
                this.currMessageUid = null;
                that.showIndex ();
            }
        });
    },
    /**
     * Retrieves actual grid element (this.element, after an ajax update, will point to an 
     * element that no longer exists)
     * @return object 
     */
    _element: function () {
        return $('#' + this.element.attr ('id'));
    },
    /**
     * Show mailbox index 
     */
    showIndex: function () {
        var element$ = $('#' + this.element.attr ('id'));
        $('#inbox-body-container').show ();
        $('#reply-form').hide ();
        element$.find ('.mailbox-refresh-button').show ();
        element$.find ('.mailbox-back-button').hide ();
        element$.find ('.summary').show ();
        element$.find ('.x2-gridview-top-pager').show ();
        $('#message-container').hide ();
        $('#' + this.options.namespacePrefix + 'C_gvCheckbox_all').show ();
        element$.children ('.x2grid-body-container').show ();
        element$.children ('.pager').show ();
        //this._getMassActionsManager ().checkUIShow ();
        $('#email-inbox-tabs').show ();

        // uncheck the messages checkbox so that mass actions work
        var checkBox$ = $(
            '#' + this.options.namespacePrefix + 'C_gvCheckbox_' + this.currMessageUid);
        if (checkBox$.is (':checked'))
            $('#' + this.options.namespacePrefix + 'C_gvCheckbox_' + this.currMessageUid).click ();
    },
    /**
     * Hide mailbox index 
     */
    _hideIndex: function () {
        var element$ = this._element ();
        $('#inbox-body-container').show ();
        $('#reply-form').hide ();
        element$.find ('.mailbox-refresh-button').hide ();
        element$.find ('.mailbox-back-button').show ();
        element$.find ('.summary').hide ();
        element$.find ('.x2-gridview-top-pager').hide ();
        $('#message-container').show ();
        $('#' + this.options.namespacePrefix + 'C_gvCheckbox_all').hide ();
        element$.children ('.x2grid-body-container').hide ();
        element$.children ('.pager').hide ();
        //this._getMassActionsManager ().showUI ();
        $('#email-inbox-tabs').hide ();

        // check the messages checkbox so that mass actions work
        var checkBox$ = $(
            '#' + this.options.namespacePrefix + 'C_gvCheckbox_' + this.currMessageUid);
        if (!checkBox$.is (':checked'))
            $('#' + this.options.namespacePrefix + 'C_gvCheckbox_' + this.currMessageUid).click ();

    },
    /**
     * Sets up behavior of star buttons 
     */
    _setUpMessageFlagging: function () {
        var that = this;
        $('.flagged-toggle').unbind ('click._setUpMessageFlagging').
            bind ('click._setUpMessageFlagging', function (evt) {

            that._setImportant ($(this).attr ('data-uid'), !$(this).hasClass ('flagged'), $(this));
            evt.stopPropagation ();
        });
    },
    /**
     * Initiate an ajax request to set a message flag
     * @param bool val if true, message will be starred, otherwise star will be removed
     * @param object flag$
     */
    _setImportant: function (uid, val, flag$) {
        var that = this;
        var flag = val ? 'important' : 'notimportant';
        if (val) {
            flag$.addClass ('flagged');
        } else {
            flag$.removeClass ('flagged');
        }
        $.ajax({
            url: yii.baseUrl + '/index.php/emailInboxes/markMessages',
            type: 'post',
            data: {
                uids: uid,
                flag: flag,
                emailFolder: x2.emailInbox.emailFolder
            },
            success: function(data) {
                if (data === 'success') { 
                    // TODO:
                    // update should occur if user is filtering by flagged setting
                    //$.fn.yiiGridView.update(that.element.attr ('id'), {});
                }
            }
        });
    },
    /**
     * Submits search form 
     */
    _search: function (advanced) {
        advanced = typeof advanced === 'undefined' ? false : advanced; 
        var that = this;
        var data = advanced ? 
            $('#email-search-form').serialize () : $('#email-search-box').serialize ();
        $.fn.yiiGridView.update(this._element ().attr ('id'), {
            type: "get",
            url: window.location.href,
            data: data,
            complete: function(xhr, status) {
                if (status === "success") {
                    x2.history.pushState ({}, '', that._removeUidFromUrl ());
                    that.showIndex ();
                }
            }
        });
    },
    _removeUidFromUrl: function () {
        var params = $.deparam.querystring ();
        delete params['uid'];
        var overwriteMode = 2
        var newUrl = $.param.querystring (window.location.href, params, overwriteMode);
        return newUrl;
    },
    /**
     * Causes navigation back to the mailbox index 
     */
    _backToIndex: function () {
        x2.history.pushState ({}, '', this._removeUidFromUrl ());
        this.showIndex ();
    },
    /**
     * Sets up behavior of search buttons 
     */
    _setUpSearch: function () {
        var that = this;
        var advancedSearchForm$ = $('#advanced-search-form');
        var emailSearchForm$ = $('#email-search-form');

        // open/close advanced search form
        $('#open-advanced-search-form-button').unbind ('click._setUpSearch')
            .bind ('click._setUpSearch', function (evt) {

            if (advancedSearchForm$.is (':visible')) {
                advancedSearchForm$.hide ();
            } else {
                advancedSearchForm$.show ();
                auxlib.onClickOutside ('#email-search-form , #ui-datepicker-div', function () {
                    advancedSearchForm$.hide ();
                }, true);
            }
            evt.stopPropagation ();
        });

        var searchSubmitButton$ = $('#email-search-submit');
        var advancedSearchSubmitButton$ = $('#email-advanced-search-submit');

        // submit search form
        searchSubmitButton$.unbind ('click._setUpSearch')
            .bind ('click._setUpSearch', function (evt) {

            if (advancedSearchForm$.is (':visible')) {
                that._search (true);
            } else {
                that._search ();
            }
            return false;
        });
        advancedSearchSubmitButton$.unbind ('click._setUpSearch')
            .bind ('click._setUpSearch', function (evt) {

            that._search (true);
            return false;
        });
    },
    /**
     * extract encoded message body from div, decode, and insert into iframe
     */
    _formatDisplayedMessage: function () {
        var messageContainer$ = $('#message-container');

        // decode and remove email message
        var messageBody = auxlib.htmlDecode (
            messageContainer$.find ('.message-body-temp').html ());
        messageContainer$.find ('.message-body-temp').hide ();

        // add email message to iframe
        var messageIframe$ = $(messageContainer$).find ('iframe');
        var iframeDocument = messageIframe$.get (0).contentWindow.document;
        iframeDocument.open ();
        iframeDocument.write (messageBody);
        iframeDocument.close ();

        // resize and style iframe
        var iframeBody$ = $(iframeDocument).find ('body');
        iframeBody$.attr ('style', 'overflow-y: hidden;');
        this._hideIndex ();
        messageIframe$.height (iframeBody$.height () + 10);
        this._refreshQtips ();
    },
    /**
     * Request message and display it
     * @param int uid 
     * @param bool fetchData
     */
    _viewMessage: function (uid, fetchData, pushState) {
        var that = this;
        pushState = typeof pushState === 'undefined' ? true : pushState; 
        fetchData = typeof fetchData === 'undefined' ? true : fetchData; 
        
        var messageContainer$ = $('#message-container');
        // push browser state since page is being changed to message view
        if (uid !== null && pushState) { 
            var newUrl = $.param.querystring (window.location.href, {uid: uid});
            x2.history.pushState ({uid: uid}, '', newUrl);
        } else {
            var uid = $.deparam.querystring (window.location.href)['uid'];
        }
        this.currMessageUid = uid;
        var id = $.deparam.querystring (window.location.href)['id'];
        if (fetchData) {
            var throbber$ = auxlib.pageLoading ();
            $.ajax ({
                type: 'get',
                url: yii.baseUrl + '/index.php/emailInboxes/viewMessage',
                data: {
                    id: id,
                    uid: uid,
                    emailFolder: x2.emailInbox.emailFolder
                }, 
                success: function (data) {
                    throbber$.remove ();
                    messageContainer$.html (data);
                    that._formatDisplayedMessage ();
                }
            });
        } else {
            that._formatDisplayedMessage ();
        }
    },
    /**
     * Extracts message attributes from message view markup
     * @return object 
     */
    _getMessageAttributes: function () {
        var messageContainer$ = $('#message-container');
        var toField = auxlib.htmlDecode (
            $.trim (messageContainer$.find ('.to-field').attr ('data-to')));
        var fromField = auxlib.htmlDecode (
            $.trim (messageContainer$.find ('.from-field').attr ('data-from')));
        var subject = auxlib.htmlDecode (
            $.trim (messageContainer$.find ('.message-subject').html ()));
        var messageBody = auxlib.htmlDecode (
            messageContainer$.find ('.message-body-temp').html ());
        var date = auxlib.htmlDecode (
            $.trim (messageContainer$.find ('.date-field').html ()));

        var attachments = [];
        messageContainer$.find ('.message-attachment').each (function () {
            // extract attachment filename and part 
            attachments.push ([
                $(this).find ('.attachment-filename').text (),
                $.deparam ($(this).find ('.attachment-download-link').attr ('data-href'))['part']
            ]);
        });

        return {
            toField: toField,
            fromField: fromField,
            subject: subject,
            messageBody: messageBody,
            date: date,
            attachments: attachments
        }
    },
    /**
     * Opens the reply editor 
     */
    _openReplyEditor: function (action, pushState) {
        pushState = typeof pushState === 'undefined' ? true : pushState; 
        if (pushState) x2.history.pushState ({reply: 1}, '', window.location.href);

        var replyForm$ = $('#reply-form');
        var messageAttrs = this._getMessageAttributes ();
        var inlineEmailManager = x2.inlineEmailEditorManager;
        inlineEmailManager.reinstantiateEditorWhenShown = false;

        replyForm$.show (); 

        var body$ = inlineEmailManager.quoteText (
            messageAttrs.messageBody, messageAttrs.date, messageAttrs.fromField);

        if (action !== 'forward') {
            inlineEmailManager.setToField (messageAttrs.fromField);
        } else {
            body$ = inlineEmailManager.addForwardingHeader (
                body$, messageAttrs.fromField, messageAttrs.date, messageAttrs.subject,
                messageAttrs.fromField);

            // add currently viewed message's attachments to forwarded message
            for (var i in messageAttrs.attachments) {
                var filename = messageAttrs.attachments[i][0];
                var part = messageAttrs.attachments[i][1];
                x2.emailEditor.newAttachment (
                    this.currMessageUid + ',' + part, 'emailInboxes', filename);
            }
        }
        inlineEmailManager.
            showEmailForm (false, false, false).focus ();

        inlineEmailManager.
            setSubjectField ('Re: ' + messageAttrs.subject).
            prependToBody ($('<br/><br/><br/>').after (body$)).
            hideShowSubjectField (true);
        $('#inbox-body-container').hide ();
    },
    /**
     * Opens the new message editor 
     */
    _openNewMessageEditor: function (pushState) {
        pushState = typeof pushState === 'undefined' ? true : pushState; 
        if (pushState)
            x2.history.pushState ({newMessage: 1}, '', window.location.href);

        var replyForm$ = $('#reply-form');
        var inlineEmailManager = x2.inlineEmailEditorManager;
        inlineEmailManager.reinstantiateEditorWhenShown = false;
        replyForm$.show (); 

        inlineEmailManager.
            hideShowSubjectField (false).
            showEmailForm (false, false, false).focus ();
        $('#inbox-body-container').hide ();
    },
    /**
     * Sets up behavior of new message button 
     */
    _setUpNewMessageButtonBehavior: function () {
        var that = this;
        var newMessageButton$ = $('#new-message-button');
        newMessageButton$.unbind ('click._setUpNewMessageButtonBehavior').
            bind ('click._setUpNewMessageButtonBehavior', function () {

            that._openNewMessageEditor (); 
        });
    },
    rebindContactLinkEventHandler: function () {
        // don't view message if clicking contact record link
        this._element ().find ('.x2grid-body-container .items tr .contact-name').unbind (
            'click._setUpMessageView_contactLinkPropagation').bind (
            'click._setUpMessageView_contactLinkPropagation', function (evt) {

            evt.stopPropagation ();
        });
    },
    /**
     * Sets up click-to-view-message behavior  
     */
    _setUpMessageView: function () {
        var that = this;
        var element$ = this._element ();

        this.rebindContactLinkEventHandler ();

        element$.find ('.x2grid-body-container .items tr').unbind (
            'click._setUpMessageView_viewMessage').
            bind ('click._setUpMessageView_viewMessage', function () {

            var uid = $(this).find ('.check-box-cell input').val ();

            $(this).removeClass ('unseen-message-row');
            $(this).addClass ('seen-message-row');
            that._viewMessage (uid);
        });
        
        // prevent unintentional message views
        element$.find ('.check-box-cell, .flagged-cell').unbind ('click._setUpMessageView')
            .bind ('click._setUpMessageView', function (evt) {

            evt.stopPropagation ();
        });

        // back button behavior
        element$.find ('.mailbox-back-button').unbind ('click._setUpMessageView')
            .bind ('click._setUpMessageView', function () {
    
            that._backToIndex ();         
            return false;
        });

        // reply options menu behavior
        $('#message-container').off ('click._setUpMessageView', '.message-reply-more-button').on (
            'click._setUpMessageView', '.message-reply-more-button', function (evt) {

            var moreMenu$ = $('#message-container .reply-more-menu');
            if (moreMenu$.is (':visible')) {
                moreMenu$.hide ();
            } else {
                moreMenu$.show ().position ({
                    my: 'left top',
                    at: 'left bottom',
                    of: $(this).next ()
                });
                auxlib.onClickOutside ('#message-container .reply-more-menu', function () {
                    moreMenu$.hide ();
                }, true);
            }
            evt.stopPropagation ();
        });

        // reply button behavior
        $('#message-container').off ('click._setUpMessageView', '.message-reply-button').on (
            'click._setUpMessageView', '.message-reply-button', function (evt) {

            that._openReplyEditor ();
            return false;
        });

        // forward button behavior
        $('#message-container').off ('click._setUpMessageView', '.message-forward-button').on (
            'click._setUpMessageView', '.message-forward-button', function (evt) {

            that._openReplyEditor ('forward');
            return false;
        });
    }
});

})(jQuery);
