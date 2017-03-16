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

x2.ConditionList = (function () {

function ConditionList (argsDict) {
    var that = this;
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
        containerSelector: '',
        visibilityOptions: {},
        operatorList: {},
        allTags: {},
        options: [],
        modelClass: '',
        value: []
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._container$ = $(this.containerSelector);
    this._sortList$ = this._container$.children ('.x2-cond-list');
    this._addCondButton$ = this._container$.find ('button');
    var fieldsOptions = {};
    fieldsOptions[this.modelClass] = this.options;
    this._fields = new x2.FieldsGeneric ({
        templateSelector: this.containerSelector + ' .x2fields-template',
        options: fieldsOptions,
        visibilityOptions: this.visibilityOptions,
        operatorList: this.operatorList,
        allTags: this.allTags
    });
    this._fields.addChangeListener (this.containerSelector, function () {
        that._reindexInputs (); 
    })
    this._fields.enableChangedOperator = true;
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

/**
 * Sets up behavior of add/delete condition buttons 
 */
ConditionList.prototype._setUpAddRemoveConditionBehavior = function () {
    var that = this;
    this._addCondButton$.click (function () {
        var field$ = that._fields.createAttrListItem(that.modelClass, that.options)
            .hide()
            .appendTo(that._sortList$.children ('ol'))
            .slideDown(200);
        var i = that._sortList$.children ('ol').children ().length - 1;
        var attr;
        field$.find (':input').each (function (index, elem) {
            attr = $(elem).attr ('name');
            if (typeof attr !== 'undefined' && attr !== false) {
                $(elem).attr ('name', $(elem).attr ('name').replace (/\[i\]/, '[' + i + ']'));
            }
        });
        return false;
    });

    // Listen for clicks on the "delete condition" buttom
    this._container$.on("click", "a.del", function() {
        $(this).closest("li").slideUp(200, function(){ 
            $(this).remove(); 
            that._reindexInputs (); 
        });
    });
};

/**
 * Used to retrieve condition input values for form submission
 * @deprecated condition list inputs now have names that allow form to be correctly serialized,
 *  simplifying AJAX form submission
 * @return object contains information about each condition (name, operator, and value)
 */
ConditionList.prototype.getAttributesConfig = function () {
    var that = this;
    var attributeRows = this._sortList$.children ('ol').children ('li');
    var attrConfig = [];
    if(attributeRows.length) {
        attributeRows.each(function(i, elem) {
            attrConfig.push({
                name:$(elem).find(".x2fields-attribute select, .x2fields-attribute input").
                    first().val(),
                operator:$(elem).find(".x2fields-operator select").first().val(),
                value:that._fields.getVal($(elem).
                    find(".x2fields-value :input[name='value']").first())
            });
        });
    }
    return attrConfig;
};

/**
 * Reindexes input names, ensuring that numeric indices are sequential and start at 0
 */
ConditionList.prototype._reindexInputs = function () {
    var that = this;
    this._sortList$.children ('ol').children ().each (function (i, elem) {
        $(elem).find (':input').each (function (j, elem) {
            $(elem).attr ('name', $(elem).attr ('name').replace (/\[[i0-9]+\]/, '[' + i + ']'));
        });
    });
};

ConditionList.prototype._addPreexistingValues = function () {
    var that = this;

    for (var i in this.value) {
        var val = this.value[i];
        var field$ = that._fields.createAttrListItem(that.modelClass, that.options)
        that._sortList$.children ('ol').append (field$);
        field$.find ('.x2fields-attribute :input').val (val.name).change ();
        field$.find ('.x2fields-operator :input').val (val.operator).change;
        field$.find ('.x2fields-value :input').val (val.value);
    }
    this._reindexInputs ();
};

ConditionList.prototype._init = function () {
    this._setUpAddRemoveConditionBehavior ();
    this._addPreexistingValues ();
};

return ConditionList;

}) ();
