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

/* @edition:pro */

/**
 * Prototype for publisher products tab
 */

if(typeof x2 == 'undefined')
    x2 = {};
if(typeof x2.publisher == 'undefined')
    x2.publisher = {};

x2.PublisherProductsTab = (function () {

function PublisherProductsTab (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        lineItems: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    var that = this;
    that.DEBUG && console.log ('this.lineItems = ');
    that.DEBUG && console.log (this.lineItems);


	x2.PublisherTab.call (this, argsDict);	
}

PublisherProductsTab.prototype = auxlib.create (x2.PublisherTab.prototype);

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

/**
 * Validate line items form
 */
PublisherProductsTab.prototype.validate = function() {
    var that = this;
    that.DEBUG && console.log ('PublisherProductsTab: beforeSubmit');
    if (!x2.PublisherTab.prototype.validate.call (this) ||
        !this.lineItems.validateAllInputs ()) {

        return false;
    }
    return true; // form is sane: submit!
};


/*
Private instance methods
*/


return PublisherProductsTab;

}) ();

