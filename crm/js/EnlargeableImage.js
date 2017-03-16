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
 * EnlargeableImage prototype
 * Image enlargement is done by overriding css height styling.
 * An enlargeable image, when clicked is enlarged and placed inside a modal
 */

x2.EnlargeableImage = (function () {

function EnlargeableImage (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        elem: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
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

/**
 * closes the modal and removes the node
 */
EnlargeableImage.prototype.close = function () {
    var that = this;
    $(that._modal).remove ();
};

/*
Private instance methods
*/

/**
 * @return bool true if height of image is larger, false otherwise
 */
EnlargeableImage.prototype._heightIsLarger = function () {
    return $(this.elem).height () > $(this.elem).width ();
};

/**
 * Clone the image and place it in a modal which closes when the user clicks outside the image
 * or when the close button is clicked.
 */
EnlargeableImage.prototype._createEnlargedImageModal = function () {
    var that = this;    

    // construct modal
    this._modal = $('<div>', {
        'class': 'x2-enlargeable-image-modal'
    }).append ($('<img>', {
        'src': $(this.elem).attr ('src'),
    })).append ($('<input>', {
        type: 'image',
        src: yii.themeBaseUrl+'/images/icons/Close_Widget.png'
    }));

    // set max height and width based on larger dimension
    if (this._heightIsLarger ()) {
        this._modal.find ('img').css ({
            'max-height': '70%',
            'max-width': '80%'
        });
    } else {
        this._modal.find ('img').css ({
            'max-width': '70%',
            'max-height': '80%'
        });
    }

    // close button behavior
    this._modal.find ('input').unbind ('click');
    this._modal.find ('input').bind ('click', function () {
        that.close ();
    });

    // close on click outside image
    auxlib.onClickOutside (this._modal.find ('img'), function () { that.close (); });

    $('body').append (this._modal);
};

EnlargeableImage.prototype._init = function () {
    this._modal = null;
    var that = this;

    // for styling
    $(this.elem).addClass ('x2-enlargeable-image');

    // open modal on image click
    $(this.elem).unbind ('click');
    $(this.elem).bind ('click', function () {
        that._createEnlargedImageModal (); 
        return false;
    });
};

return EnlargeableImage;

}) ();


