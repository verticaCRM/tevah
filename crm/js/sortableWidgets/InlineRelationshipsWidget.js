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

x2.InlineRelationshipsWidget = (function () {

function InlineRelationshipsWidget (argsDict) {
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
    this._relationshipsGridContainer$ = $('#relationships-form');
    /* x2prostart */ 
    this._relationshipsGraph = null;
    this._inlineGraphContainer$ = $('#inline-relationships-graph-container');
    this._inlineGraphViewButton$ = $('#inline-graph-view-button');
    /* x2proend */ 
    this._gridViewButton$ = $('#rel-grid-view-button');
    this._form$ = $('#new-relationship-form');
    this._relationshipManager = null;

    auxlib.applyArgs (this, defaultArgs, argsDict);

    GridViewWidget.call (this, argsDict);
}

InlineRelationshipsWidget.prototype = auxlib.create (GridViewWidget.prototype);

/**
 * Set up quick create button for given model class
 * @param string modelType 
 */
InlineRelationshipsWidget.prototype.initQuickCreateButton = function (modelType) {
    var that = this;
    if (this._relationshipManager && 
        this._relationshipManager instanceof x2.RelationshipsManager) {

        this._relationshipManager.destructor ();
    }

    if ($.inArray (modelType, this.modelsWhichSupportQuickCreate) > -1) {
        $('#quick-create-record').css ('visibility', 'visible');
    } else {
        $('#quick-create-record').css ('visibility', 'hidden');
        return;
    }

    this._relationshipManager = new x2.RelationshipsManager ({
        element: $('#quick-create-record'),
        modelType: this.recordType,
        modelId: this.recordId,
        relatedModelType: modelType,
        createRecordUrl: this.createUrls[modelType],
        attributeDefaults: this.defaultsByRelatedModelType[modelType] || {},
        dialogTitle: this.dialogTitles[modelType],
        tooltip: this.tooltips[modelType],
        afterCreate: function (attributes) {
            $.fn.yiiGridView.update('relationships-grid');
            if (that._graphLoaded ()) {
                that._relationshipsGraph.connectNodeToInitialFocus (
                    modelType, attributes.id, 
                    typeof attributes.name === 'undefined' ? attributes.id : attributes.name);
            }
        }
    });

};

/**
 * Requests a new autocomplete widget for the specified model class, replacing the current one
 * @param string modelType
 */
InlineRelationshipsWidget.prototype._changeAutoComplete = function (modelType) {
    x2.forms.inputLoading ($('#inline-relationships-autocomplete-container'));
    $.ajax ({
        type: 'GET',
        url: this.ajaxGetModelAutocompleteUrl,
        data: {
            modelType: modelType
        },
        success: function (data) {
            // remove span element used by jQuery widget
            $('#inline-relationships-autocomplete-container input').
                first ().next ('span').remove ();
            // replace old autocomplete with the new one
            $('#inline-relationships-autocomplete-container input').first ().replaceWith (data); 
            $('#inline-relationships-autocomplete-container').find ('script').remove ();
 
            // remove the loading gif
            x2.forms.inputLoadingStop ($('#inline-relationships-autocomplete-container'));
        }
    });
};

/**
 * submits relationship create form via AJAX, performs validation 
 */
InlineRelationshipsWidget.prototype._submitCreateRelationshipForm = function () {
    var that = this; 
    $('.record-name-autocomplete').removeClass ('error');
    var error = false;

    if ($('#RelationshipModelId').val() === '') {
        that.DEBUG && console.log ('model id is not set');
        error = true;
    } else if (isNaN (parseInt($('#RelationshipModelId').val(), 10))) {
        that.DEBUG && console.log ('model id is NaN');
        error = true;
    } else if($('.record-name-autocomplete').val() === '') {
        that.DEBUG && console.log ('second name autocomplete is not set');
        error = true;
    }
    if (error) {
        $('.record-name-autocomplete').addClass ('error');
        return false;
    }
    that._form$.slideUp (200);

    var recordId = $('#RelationshipModelId').val ();
    var recordType = $('#relationship-type').val ();
    var recordName = that._form$.find ('.record-name-autocomplete').val ();

    $.ajax ({
        url: this.createRelationshipUrl,
        type: 'POST', 
        data: $('#new-relationship-form').serializeArray (),
        success: function (data) {
            if(data === 'duplicate') {
                alert('Relationship already exists.');
            } else if(data === 'success') {
                $.fn.yiiGridView.update('relationships-grid');
                var count = parseInt ($('#relationship-count').html (), 10);
                $('#relationship-count').html (count + 1);
                that._form$.find ('.record-name-autocomplete').val ();
                $('#RelationshipModelId').val('');
                $('#firstLabel').val('');
                $('#secondLabel').val('');
                /* x2prostart */ 
                if (that._graphLoaded ()) {
                    that._relationshipsGraph.connectNodeToInitialFocus (
                        recordType, recordId, recordName);
                }
                /* x2proend */ 
            }
        }
    });
};

/**
 * Sets up create form submission button behavior 
 */
InlineRelationshipsWidget.prototype._setUpCreateFormSubmission = function () {
    var that = this;

    $('#add-relationship-button').on('click', function () {
        that._submitCreateRelationshipForm ();
        return false;
    });
};


InlineRelationshipsWidget.prototype._changeMode = function (mode) {
    var form$ = $('#relationships-form');
    if (mode === 'simple') {
        form$.addClass ('simple-mode');
        form$.removeClass ('full-mode');
    } else {
        form$.removeClass ('simple-mode');
        form$.addClass ('full-mode');
    }
};

InlineRelationshipsWidget.prototype._setUpModeSelection = function () {
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

/* x2prostart */
InlineRelationshipsWidget.prototype._displayInlineGraph = function () {
    this._inlineGraphContainer$.show ();
    this._relationshipsGridContainer$.hide ();
    this._inlineGraphViewButton$.hide ();
    this._gridViewButton$.show ();
    this.element.find ('.ui-resizable-handle').show ();
    this.setProperty ('displayMode', 'graph');
    this.displayMode = 'graph';
    this._setUpResizeBehavior ();
};
/* x2proend */

InlineRelationshipsWidget.prototype._displayGrid = function () {
    /* x2prostart */ 
    this._inlineGraphContainer$.hide ();
    /* x2proend */ 
    this._relationshipsGridContainer$.show ();
    /* x2prostart */ 
    this._inlineGraphViewButton$.show ();
    /* x2proend */ 
    this._gridViewButton$.hide ();
    this.element.find ('.ui-resizable-handle').hide ();
    $(this.contentContainer).attr ('style', '');
    this.setProperty ('displayMode', 'grid');
    this.displayMode = 'grid';
};

/* x2prostart */
InlineRelationshipsWidget.prototype._graphLoaded = function () {
    if ($.trim (this._inlineGraphContainer$.html ()) !== '') {
        this._relationshipsGraph = x2.relationshipsGraph;
        return true;
    }
    return false;
};
/* x2proend */

/* x2prostart */
InlineRelationshipsWidget.prototype._getInlineGraph = function () {
    if (this._graphLoaded ()) {
        this._displayInlineGraph ();
        return;
    }
    var that = this;
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
};
/* x2proend */

/* x2prostart */
InlineRelationshipsWidget.prototype._setUpGraphViewButton = function () {
    var that = this;
    this._inlineGraphViewButton$.click (function () {
        that._getInlineGraph ();
    });
    this._gridViewButton$.click (function () {
        that._displayGrid ();
    });
};
/* x2proend */

InlineRelationshipsWidget.prototype._afterStop = function () {
    var that = this; 
    var savedHeight = that.element.height ();
    if (this._form$.is (':visible'))
        savedHeight -= this._form$.height () + 12;
    that.setProperty ('height', savedHeight);
};

/* x2prostart */
InlineRelationshipsWidget.prototype._resizeEvent = function () {
    var that = this;
    if (that.displayMode === 'graph') {
        var newHeight = $(this.contentContainer).height ();
        if (this._form$.is (':visible'))
            newHeight -= this._form$.height () + 12;
        $('#relationships-graph-container').height (newHeight);
    }
};
/* x2proend */

InlineRelationshipsWidget.prototype._setUpNewRelationshipsForm = function () {
    var that = this;
    $('#relationship-type').change (function () {
        that.initQuickCreateButton ($(this).val ()); 
        that._changeAutoComplete ($(this).val ());
    }).change ();
    
    $('#secondLabel').hide();
    $('#myName').hide();
    $('#RelationshipLabelButton').bind('click', function(){
        $('#RelationshipLabelButton').toggleClass('fa fa-long-arrow-right');
        $('#RelationshipLabelButton').toggleClass('fa fa-long-arrow-left');
        $('#myName').toggle(200);
        $('#secondLabel').toggle( 200);
        var val = $('#mutual').val();
        val = (val == 'true') ? 'false' : 'true';

        $('#mutual').val(val);
    });

    $('#new-relationship-button').click (function () {
        if (that._form$.is (':visible')) {
            that._form$.slideUp (200);
        } else {
            that.contentContainer.attr ('style', '');
            that._form$.slideDown (200);
        }
    });

    this._setUpCreateFormSubmission ();
};

/* x2prostart */
/**
 * Sets up widget resize behavior 
 */
InlineRelationshipsWidget.prototype._setUpResizeBehavior = function () {
    if (this._setUpResizeBehavior.setUp) return;
    this.resizeHandle = $('#relationships-graph-resize-handle');
    if (!this.resizeHandle.length) return;

    this._setUpResizeBehavior.setUp = true;
    var that = this; 
    this.resizeHandle.addClass ('ui-resizable-handle');
    this.resizeHandle.addClass ('ui-resizable-s');
    $(this.contentContainer).resizable ({
        handles: {
            s: $('#relationships-graph-resize-handle')
        },
        minHeight: 50,
        start: function () {
            $('body').attr ('style', 'cursor: se-resize');
        },
        stop: function () {
            that._afterStop ();
            $('body').attr ('style', '');
        },
        resize: function () { that._resizeEvent (); }
    });
};
/* x2proend */

InlineRelationshipsWidget.prototype._init = function () {
    GridViewWidget.prototype._init.call (this);
    if (this.displayMode === 'grid') this.element.find ('.ui-resizable-handle').hide ();
    this._setUpPageSizeSelection ();
    this._setUpModeSelection ();
    /* x2prostart */ 
    this._setUpGraphViewButton ();
    /* x2proend */ 

    if (this.hasUpdatePermissions) this._setUpNewRelationshipsForm ();
};


return InlineRelationshipsWidget;

}) ();



