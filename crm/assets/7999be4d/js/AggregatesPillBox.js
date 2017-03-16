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

x2.AggregatesPillBox = (function () {

function AggregatesPillBox (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.PillBox.call (this, argsDict);
}

AggregatesPillBox.prototype = auxlib.create (x2.PillBox.prototype);

/**
 * Override parent method so that label gets looked up properly 
 */
AggregatesPillBox.prototype._addPreexistingValues = function () {
    var groupedValues = {};
    for (var i in this.value) {
        var val = this.value[i];
        groupedValues[val] = groupedValues[val] ? groupedValues[val] : [];
        var matches = val.match (/^([^(]+)/);
        if (matches) {
            groupedValues[val].push (matches[1]); 
        }
        
    }
    for (var val in groupedValues) {
        var fns = groupedValues[val];
        var attr = val.replace (/^[^(]+\((.*)\)$/, '$1');
        this._addPill ([attr, fns], this._getLabelOfVal (attr));
    }
};


return AggregatesPillBox;


}) ();

x2.AggregatesPill = (function () {

function AggregatesPill (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {
            max: '',
            min: '',
            avg: '',
            sum: '',
            'delete': ''
        },
        value: null
    };
    this._aggregateFns = ['min', 'max', 'avg', 'sum'];
    this._aggregateFnCheckboxes$ = null;
    auxlib.applyArgs (this, defaultArgs, argsDict);
    if (Object.prototype.toString.call (this.value) === '[object Array]') {
        this.fns = this.value[1];
        this.value = this.value[0];
    } else {
        this.fns = [];
    }
    x2.Pill.call (this, argsDict);
}

AggregatesPill.prototype = auxlib.create (x2.Pill.prototype);

///**
// * Add sort direction to pill data 
// */
//AggregatesPill.prototype.getData = function () {
//    var selectedAggregateFns = [];
//    for (var i in this._aggregateFns) {
//        var fn = this._aggregateFns[i];
//        if (this._aggregateFnCheckboxes$.find (
//            'input[name="aggregate-fn-' + fn + '"]').is (':checked')) {
//
//            selectedAggregateFns.push (fn);
//        }
//    }
//    return [x2.Pill.prototype.getData.call (this), selectedAggregateFns];
//};

/**
 * Add sort order select element 
 */
AggregatesPill.prototype._setUpPillElements = function () {
    x2.Pill.prototype._setUpPillElements.call (this);
    var i = this.owner.element$.find ('.x2-pill').length;
    // overwrite input name set by parent
    this.element$.find ('input').attr ('disabled', 'disabled');

    this.element$.addClass ('aggregates-pill');
    this._aggregateFnCheckboxes$ = $('<span>', { 'class': 'aggregate-fn-checkboxes-container' });

    for (var j in this._aggregateFns) {
        var fn = this._aggregateFns[j];
        this._aggregateFnCheckboxes$.append ($('<label>', {
            text: this.translations[fn] + ':'
        }));
        this._aggregateFnCheckboxes$.append ($('<input>', {
            type: 'checkbox',
            name: this.owner.name + '[]',
            value: fn + '(' + this.value + ')'
        }));
    }

    // set preexisting values
    for (i in this.fns) {
        var fn = this.fns[i];
        this._aggregateFnCheckboxes$.find ('[value="' + fn + '(' + this.value + ')"]').
            prop ('checked', true);
    }

    this.element$.append (this._aggregateFnCheckboxes$);
};

/**
 * Sets up behavior 
 */
AggregatesPill.prototype._setUpPillBehavior = function () {
    var that = this;
    x2.Pill.prototype._setUpPillBehavior.call (this);
};


return AggregatesPill;

}) ();
