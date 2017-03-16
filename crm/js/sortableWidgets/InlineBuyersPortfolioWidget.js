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

function saveBuyerListingFiles (portfolio_id, listing_id, buyer_id)
{
    console.log('set_up_listing_files_permissions_btn');
    form_data = $('#set_up_listing_files_permissions_frm').serialize();
    urlAction = '/crm/index.php/site/saveBuyerListingFiles';
    jQuery.ajax ({
        // public function actionShowListingFiles($listing_id, $portfolio_id, $buyer_id)
        //'url':'/crm/index.php/site/'+urlAction+'?listing_id='+listing_id+'&buyer_id='+portfolio_id,
        url: urlAction+'?listing_id='+listing_id+'&portfolio_id='+portfolio_id+'&buyer_id='+buyer_id,
        cache:false,
        dataType: "json",
        data:form_data,
        success: function (data) {
            if (data.status)
            {
                $('#listing_files_messages').addClass('message-success').removeClass('message-error');
            }
            else
            {
                $('#listing_files_messages').removeClass('message-success').addClass('message-error');
            }
            $('#listing_files_messages').html(data.message);

            if (data.status)
            {
                setTimeout(function() {
                    $('#buyer_listing_files').hide();
                    $('#listing_name').html('');
                    $('#listing_files_list').html('');
                    $('#listing_files_messages').html('');
                    $('#listing_files_messages').removeClass('message-success').removeClass('message-error');
                }, 2000); // <-- time in milliseconds
            }
        }
    });
}

function listingFiles (element, action_type, portfolio_id, listing_id, buyer_id, urlAction, event)
{
    event.preventDefault;

    if (urlAction == '')
    {
	  urlAction = 'getListingFiles';
    }
    $('#listing_name').html('Listing Files');
    $('#listing_files_list').html('');
    $('#listing_files_messages').html('');
    $('#listing_files_messages').removeClass('message-success').removeClass('message-error');
    $('#buyer_listing_files').show();
    $('#listing_files_loading').show();
    jQuery.ajax ({
	    // public function actionShowListingFiles($listing_id, $portfolio_id, $buyer_id)
        //'url':'/crm/index.php/site/'+urlAction+'?listing_id='+listing_id+'&buyer_id='+portfolio_id,
        'url': urlAction+'?listing_id='+listing_id+'&portfolio_id='+portfolio_id+'&buyer_id='+buyer_id,
        'cache':false,
        dataType: "json",
        success: function (data) {

            //show listing files
            listing_name = data.listing_name;
            $('#listing_name').html('Data room files available for list '+listing_name);
            listing_files = data.files;
            //console.log(listing_files.length);
            output = '<form id="set_up_listing_files_permissions_frm">';
            output += '<table class="items x2grid-resizable">';
            output += '<thead>';
            output += '<tr>';
            output += '<th width="45">View</th>';
            output += '<th>File</th>';
            output += '<th width="110">Date</th>';
            output += '</tr>';
            output += '</thead>';
            output += '<tbody>';
            if (listing_files.length > 0)
            {
	            $.each(listing_files, function(i, item) {

                    if (i % 2 === 0)
                    {
                     /* we are even */
                        tr_class = 'even';
                    }
                    else
                    {
                        /* we are odd */
                        tr_class = 'odd';
                    }

		            item_expiration_date = '';
		            item_available = '';

                    if (typeof item.buyerDetails['media_id'] != 'undefined')
		            {
			            buyerDetails = item.buyerDetails;
                        if (buyerDetails['private_end_date'] != '0000-00-00')
                        {
                            item_expiration_date = buyerDetails['private_end_date'];
                        }

			            if (buyerDetails['private'] == 1)
			            {
				            item_available = 'checked';
			            }
		            }
		             output += '<tr class="'+tr_class+'">';
				     output += '<td><input type="checkbox" '+item_available+' name="private_'+item.id+'" value="1"><input type="hidden" name="file_item[]" value="'+item.id+'"></td>';
				     output += '<td><a href="/crm/index.php/media/view/'+item.id+'" target="_blank">'+item.fileName+'</a></td>';
				     output += '<td><input type="text" class="datepicker" name="expiration_date_'+item.id+'" value="'+item_expiration_date+'" style="width:80px"></td>';
				     output += '</tr>';
				})
                output += '<tr>';
                output += '<td colspan="3"><input type="button" value="Assign permission" name="yt8" class="x2-button" id="set_up_listing_files_permissions_btn" onclick="saveBuyerListingFiles('+portfolio_id+','+listing_id+','+buyer_id+'); return false;" style="float: right;"></td>';
                output += '</tr>';

            }
            else
            {
               output += '<tr><td colspan="3">Please add files by the listing profile</td></tr>';

            }
            output += '</tbody>';
            output += '</table>';
            output += '</form>';

             $('#listing_files_list').html($.parseHTML(output));

			 	$('#listing_files_list .datepicker').datepicker({

                    weekStart: 1,
                    autoclose: true
                });
            $('#listing_files_loading').hide();

        }
    });

}
function listingActions(element, action_type, projectId, url, event) {

    event.preventDefault;

    var message;
    var urlAction;

    if (action_type == 'hide')
    {
        message = 'Are you sure you want to Hide this portfolio?';
        urlAction = 'showHidePortfolioItem';
    }
    else if (action_type == 'show')
    {
        message = 'Are you sure you want to UnHide this portfolio?';
        urlAction = 'showHidePortfolioItem';
    }
    else
    {
        message = 'Are you sure you want to Release this portfolio?';
        urlAction = 'updatePortfolioItemStatus';
    }

    fullUrl = url + '?action_type='+action_type+'&projectId='+projectId;
//console.log(fullUrl)
    if (confirm(message))
    {
		console.log('/crm/index.php/site/'+urlAction+'?action_type='+action_type+'&porfolioId='+projectId);
      jQuery.ajax ({
            'url':'/crm/index.php/site/'+urlAction+'?action_type='+action_type+'&porfolioId='+projectId,
            'cache':false,
            success: function (data) {
                $.fn.yiiGridView.update('buyersPortfolio-grid');
            }
        });
        return false;
    }
    else
    {
        return false;
    }



};

if (typeof x2 === 'undefined') x2 = {};

x2.InlineBuyersPortfolioWidget = (function () {

function InlineBuyersPortfolioWidget (argsDict) {
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
    this._relationshipsGridContainer$ = $('#buyersPortfolio-form');
    /* x2prostart */
    this._relationshipsGraph = null;
    this._inlineGraphContainer$ = $('#inline-buyersPortfolio-graph-container');
    this._inlineGraphViewButton$ = $('#inline-buyersPortfolio-graph-view-button');
    /* x2proend */
    this._gridViewButton$ = $('#porftolio-grid-view-button');
    this._form$ = $('#new-buyersPortfolio-form');
    this._relationshipManager = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    GridViewWidget.call (this, argsDict);
}

InlineBuyersPortfolioWidget.prototype = auxlib.create (GridViewWidget.prototype);


/**
 * submits relationship create form via AJAX, performs validation
 */
InlineBuyersPortfolioWidget.prototype._submitCreateRelationshipForm = function () {
    var that = this;
    $('.clistings-error').removeClass ('error');
    $('.clistings-error').hide ();
    var error = false;

    //get all listings that was checkec
    var checkedListingsValues = $('#BuyersPortfolio_all_listings .checkbox-column-checkbox:checked').map(function() {
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

    $('#new-buyersPortfolio-form').append('<input type="hidden" name="checkedListingsValues" value="'+checkedListingsValues+'" />');

    $.ajax ({
        url: this.createRelationshipUrl,
        type: 'POST',
        data: $('#new-buyersPortfolio-form').serializeArray (),
        success: function (data) {
            $.fn.yiiGridView.update('buyersPortfolio-grid');
        }
    });
};



/**
 * Sets up create form submission button behavior
 */
InlineBuyersPortfolioWidget.prototype._setUpCreateFormSubmission = function () {
    var that = this;

    $('#add-buyersPortfolio-button').on('click', function () {
        //console.log('add-buyersPortfolio-button');
        that._submitCreateRelationshipForm ();
        return false;
    });
};

InlineBuyersPortfolioWidget.prototype._changeMode = function (mode) {
    var form$ = $('#buyersPortfolio-form');
    if (mode === 'simple') {
        form$.addClass ('simple-mode');
        form$.removeClass ('full-mode');
    } else {
        form$.removeClass ('simple-mode');
        form$.addClass ('full-mode');
    }
};

InlineBuyersPortfolioWidget.prototype._setUpModeSelection = function () {
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


InlineBuyersPortfolioWidget.prototype._setUpNewRelationshipsForm = function () {
    var that = this;

    $('#new-buyersPortfolio-button').click (function () {

        $('#BuyersPortfolio_all_listings .checkbox-column-checkbox').prop('checked', false);

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
        $('#new-buyersPortfolio-form .buyer-checkbox-column-checkbox').prop('checked', status);
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

    $('#close_listing_files').click (function (event) {
        event.preventDefault();
        $('#buyer_listing_files').hide();
        $('#listing_name').html('');
        $('#listing_files_list').html('');
        $('#listing_files_messages').html('');
        $('#listing_files_messages').removeClass('message-success').removeClass('message-error');
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



InlineBuyersPortfolioWidget.prototype._init = function () {
    GridViewWidget.prototype._init.call (this);
    if (this.displayMode === 'grid') this.element.find ('.ui-resizable-handle').hide ();
    this._setUpPageSizeSelection ();
    this._setUpModeSelection ();
    /* x2prostart */

    if (this.hasUpdatePermissions) this._setUpNewRelationshipsForm ();
};


return InlineBuyersPortfolioWidget;

}) ();
