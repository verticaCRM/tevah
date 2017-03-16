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

x2.InlineListingDetailsWidget = (function () {

function InlineListingDetailsWidget (argsDict) {
    var defaultArgs = {
        hideFullHeader: true,
        DEBUG: x2.DEBUG && false,
        recordId: null,
        recordType: null,
        displayMode: null,
        height: null,
        ajaxGetModelAutocompleteUrl: '',
        defaultsByRelatedModelType: {}, // {<model type>: <dictionary of default attr values>}
        createUrls: {}, // {<model type>: <string>}
        dialogTitles: {}, // {<model type>: <string>}
        tooltips: {}, // {<model type>: <string>}
        hasUpdatePermissions: null,
        createRelationshipUrl: null,

        // used to determine which models the quick create button is displayed for
        modelsWhichSupportQuickCreate: []
    };
    this._relationshipsGridContainer$ = $('#listingDetails-form');
    /* x2prostart */
    this._relationshipsGraph = null;
    this._inlineGraphContainer$ = $('#inline-listingDetails-graph-container');
    this._inlineGraphViewButton$ = $('#inline-listingDetails-graph-view-button');
    /* x2proend */
    this._gridViewButton$ = $('#porftolio-grid-view-button');
    this._form$ = $('#new-listingDetails-form');
    this._relationshipManager = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    GridViewWidget.call (this, argsDict);
}

InlineListingDetailsWidget.prototype = auxlib.create (GridViewWidget.prototype);


/**
 * submits relationship create form via AJAX, performs validation
 */
InlineListingDetailsWidget.prototype._submitCreateRelationshipForm = function () {
    var that = this;
    $('.clistings-error').removeClass ('error');
    $('.clistings-error').hide ();
    var error = false;

    //get all listings that was checkec
    var checkedListingsValues = $('#ListingDetails_all_listings .checkbox-column-checkbox:checked').map(function() {
        return this.value;
    }).get();

    if (checkedListingsValues.length < 1) {
        that.DEBUG && console.log ('no listings selected');
        error = true;
    }
    if (error) {
        $('.clistings-error').addClass ('error');
        $('.clistings-error').show();
        return false;
    }
    that._form$.slideUp (200);

    $('#new-listingDetails-form').append('<input type="hidden" name="checkedListingsValues" value="'+checkedListingsValues+'" />');

    $.ajax ({
        url: this.createRelationshipUrl,
        type: 'POST',
        data: $('#new-listingDetails-form').serializeArray (),
        success: function (data) {
            $.fn.yiiGridView.update('listingDetails-grid');
        }
    });
};



/**
 * Sets up create form submission button behavior
 */
InlineListingDetailsWidget.prototype._setUpCreateFormSubmission = function () {
    var that = this;

    $('#add-listingDetails-button').on('click', function () {
        //console.log('add-listingDetails-button');
        that._submitCreateRelationshipForm ();
        return false;
    });
};

InlineListingDetailsWidget.prototype._changeMode = function (mode) {
    var form$ = $('#listingDetails-form');
    if (mode === 'simple') {
        form$.addClass ('simple-mode');
        form$.removeClass ('full-mode');
    } else {
        form$.removeClass ('simple-mode');
        form$.addClass ('full-mode');
    }
};

InlineListingDetailsWidget.prototype._setUpModeSelection = function () {
    var that = this;
    this.element.find ('a.simple-mode, a.full-mode').click (function () {
        if ($(this).hasClass ('disabled-link')) return false;
        var newMode = $(this).hasClass ('simple-mode') ? 'simple' : 'full';
        that.setProperty ('mode', newMode);
        $(this).siblings ().removeClass ('disabled-link');
        $(this).addClass ('disabled-link');
        that._changeMode (newMode);
        return false;
    });
};


InlineListingDetailsWidget.prototype._setUpNewRelationshipsForm = function () {
    var that = this;

    $('#new-listingDetails-button').click (function () {

        $('#ListingDetails_all_listings .checkbox-column-checkbox').prop('checked', false);

        if (that._form$.is (':visible')) {
            that._form$.slideUp (200);
        } else {
            that.contentContainer.attr ('style', '');
            that._form$.slideDown (200);
        }
    });

    //select all / check when a listing is clickec
    $('#clistings_buyer_gvCheckbox_all').change (function () {
        var status = $(this).is(":checked") ? true : false;
        $('#new-listingDetails-form .buyer-checkbox-column-checkbox').prop('checked', status);
    });
    $('.buyer-checkbox-column-checkbox').change (function () {
        if ($(this).is(":checked")){
            var isAllChecked = 0;
            $(".buyer-checkbox-column-checkbox").each(function(){
                if(!this.checked)
                    isAllChecked = 1;
            })
            if(isAllChecked == 0){ $("#clistingsgridC_selectAllCheckbox").prop("checked", true); }
        }
        else {
            $("#clistingsgridC_selectAllCheckbox").prop("checked", false);
        }
    });

    //search/filter for table
    $('.search_input').keyup(function(e){
        if(e.keyCode == 13)
        {
            $.ajax ({
                url: yii.scriptUrl + '/relationships/viewInlineGraph',
                data: {
                    recordId: this.recordId,
                    recordType: this.recordType,
                    height: that.height
                },
                success: function (data) {
                    that._inlineGraphContainer$.html (data);
                    that._relationshipsGraph = x2.relationshipsGraph;
                    that._displayInlineGraph ();
                }
            });
        }
    });


    this._setUpCreateFormSubmission ();
};



InlineListingDetailsWidget.prototype._init = function () {
    GridViewWidget.prototype._init.call (this);
    if (this.displayMode === 'grid') this.element.find ('.ui-resizable-handle').hide ();
    this._setUpPageSizeSelection ();
    this._setUpModeSelection ();
    /* x2prostart */

    if (this.hasUpdatePermissions) this._setUpNewRelationshipsForm ();
};


return InlineListingDetailsWidget;

}) ();



