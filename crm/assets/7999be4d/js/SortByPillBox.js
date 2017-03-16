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

x2.SortByPillBox = (function () {

function SortByPillBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.PillBox.call (this, argsDict);
}

SortByPillBox.prototype = auxlib.create (x2.PillBox.prototype);

/**
 * Override parent method so that label gets looked up properly 
 */
SortByPillBox.prototype._addPreexistingValues = function () {
    for (var i in this.value) {
        var val = this.value[i];
        var key = this.value[i][0];
        this._addPill (val, this._getLabelOfVal (key));
    }
};


return SortByPillBox;


}) ();

x2.SortByPill = (function () {

function SortByPill (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;

    var defaultArgs = {
        translations: {
            ascending: '',
            descending: '',
            'delete': ''
        },
        value: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this._sortDirectionSelect$ = null;

    if (Object.prototype.toString.call (this.value) === '[object Array]') {
        this.direction = this.value[1];
        this.value = this.value[0];
    }
    x2.Pill.call (this, argsDict);
}

SortByPill.prototype = auxlib.create (x2.Pill.prototype);

///**
// * Add sort direction to pill data 
// */
//SortByPill.prototype.getData = function () {
//    return [x2.Pill.prototype.getData.call (this), this._sortDirectionSelect$.val ()];
//};

/**
 * Add sort order select element 
 */
SortByPill.prototype._setUpPillElements = function () {
    x2.Pill.prototype._setUpPillElements.call (this);
    var i = this.owner.element$.find ('.x2-pill').length;
    // overwrite input name set by parent
    this.element$.find ('input').attr ('name', this.owner.name + '[' + i + '][]');
    this.element$.addClass ('sort-by-pill');

    this._sortDirectionSelect$ = $('<select>', {
        name: this.owner.name + '[' + i + '][]',
    });
    if (this.direction)
        this._sortDirectionSelect$.val (this.direction);
    this._sortDirectionSelect$.append ($('<option>', {
        value: 'asc',
        text: this.translations.ascending
    }));
    this._sortDirectionSelect$.append ($('<option>', {
        value: 'desc',
        text: this.translations.descending
    }));

    this.element$.append (this._sortDirectionSelect$);
};

return SortByPill;

}) ();
