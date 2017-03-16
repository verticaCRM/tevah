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

x2.Attachments = (function () {

function Attachments (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {
            filetypeError: '"{x}" is not an allowed filetype.'
        }
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    // array with disallowed extensions
    this._illegal_ext = ['exe','bat','dmg','js','jar','swf','php','pl','cgi','htaccess','py'];	
    this._fileIsUploaded = false;
    this._submitButtonSelector = '#submitAttach';
}

/**
 * @return bool True if a file with a valid extension has been uploaded, false otherwise
 */
Attachments.prototype.fileIsUploaded = function () {
    return this._fileIsUploaded;
};

Attachments.prototype.checkName = function (evt) {
    var elem = evt;

    var re = this.checkFileName (evt);

	// if re is 1, the extension isn't illegal
	if (re) {
		// enable submit
        this._fileIsUploaded = true;
		$(this._submitButtonSelector).removeAttr('disabled');
	} else {
        this._fileIsUploaded = false;
		// delete the file name, disable Submit, Alert message
		elem.value = '';
		$(this._submitButtonSelector).attr('disabled','disabled');

		var filenameError = this.translations.filetypeError;
		var ar_ext = this.getFileExt (evt);
		alert(filenameError.replace('{X}',ar_ext));
	}
};

Attachments.prototype.checkFileName = function (evt) {
    var elem = evt.target;

	// - www.coursesweb.net
	// get the file name and split it to separe the extension
	var name = elem.value;
	var ar_name = name.split('.');

	var ar_ext = ar_name[ar_name.length - 1].toLowerCase();

	// check the file extension
	var re = 1;
	for(var i in this._illegal_ext) {
		if(this._illegal_ext[i] == ar_ext) {
			re = 0;
			break;
		}
	}

    return re === 1;
};

Attachments.prototype.getFileExt = function (evt) {
    var name = evt.target.value;
	var ar_name = name.split('.');
	var ar_ext = ar_name[ar_name.length - 1].toLowerCase();
    return ar_ext;
};


return Attachments;

}) ();
