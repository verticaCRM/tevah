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

x2.fileUtil = (function () {

function FileUtil (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
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
 * @param array extensions Array of valid file extensions
 */
FileUtil.prototype.checkFileType = function (fileName, extensions) {
    var re = new RegExp ('\\.' + extensions.join ('|') + '$');
    return fileName.match (re);
};

/**
 * Validate file input, display errors, enable/disable form submission
 * @param object fileElem jQuery object corresponding to file input element
 * @param array extensions 
 * @param object submitButton jQuery object corresponding to form submit button
 */
FileUtil.prototype.validateFile = function (fileElem, extensions, submitButton) {
    if (this.checkFileType ($(fileElem).val (), extensions)) {
        $(fileElem).removeClass ('error');
        $(fileElem).next ('.x2-file-error').remove ();
        $(submitButton).removeAttr ('disabled');
        return true;
    } else {
        $(fileElem).addClass ('error');
        $(fileElem).next ('.x2-file-error').remove ();
        $(fileElem).after ($('<div>', {
            'class': 'x2-file-error',
            'text': ($(fileElem).val () === '' ? 'Select a file' : 'Invalid file type')
        }));
        $(submitButton).attr ('disabled', 'disabled');
        return false;
    }
};

return new FileUtil;

}) ();
