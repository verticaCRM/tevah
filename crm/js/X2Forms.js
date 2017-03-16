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

x2.Forms = (function () {

function X2Forms (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        translations: {}
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    this.defaultTextColor = 'rgb(93,93,93)';

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
 * Set up x2 helper tooltips
 */
X2Forms.prototype.setUpQTips = function () {
    if (typeof $().qtip !== 'undefined') {
        $('.x2-hint').qtip({
            events: {
                show: function (event, api) {
                    var tooltip = api.elements.tooltip;
                    var windowWidth = $(window).width ();
                    var elemWidth = $(api.elements.target).width ();
                    var elemLeft = $(api.elements.target).offset ().left;
                    var tooltipWidth = $(api.elements.tooltip).width ();

                    if (elemLeft + elemWidth + tooltipWidth > windowWidth) {

                        // flip tooltip if it would go off screen
                        api.set ({
                            'position.my': 'top right',
                            'position.at': 'bottom right'
                        });
                    } else {
                        api.set ({
                            'position.my': 'top left',
                            'position.at': 'bottom right'
                        });
                    }
                }
            }

        });
        $('.x2-info').qtip(); // no format qtip (.x2-hint turns text blue)
    }
};

/**
 * Initializes all elements with the class 'x2-multiselect-dropdown' as multiselect dropdown 
 * elements. The value of the element's 'data-selected-text' attribute will be used as the
 * text in the multiselect element indicating the number of options selected.
 */
X2Forms.prototype.initializeMultiselectDropdowns = function () {
    var that = this;
    $('.x2-multiselect-dropdown').each (function () {

        // don't reinitialize
        if ($(this).next ().hasClass ('.ui-multiselect')) return;

		
        var selectedText = $(this).attr ('data-selected-text'); 
		
		
		var hideSelectAll = $(this).attr ('data-hide-selectall'); 
		if (typeof(hideSelectAll) != "undefined" && hideSelectAll !== null)
        $(this).multiselect2 ({ 
			header: '<li><a class="ui-multiselect-none" href="#"><span class="ui-icon ui-icon-closethick"></span><span>Uncheck All</span></a></li>',
            selectedText: '# ' + selectedText + ' ' + that.translations['selected'],
           // checkAllText: that.translations['Check All'], 
            uncheckAllText: that.translations['Uncheck All'], 
            'classes': 'x2-multiselect-dropdown-menu'
        });
		else
			$(this).multiselect2 ({
			selectedText: '# ' + selectedText + ' ' + that.translations['selected'],
			checkAllText: that.translations['Check All'], 
            uncheckAllText: that.translations['Uncheck All'], 
            'classes': 'x2-multiselect-dropdown-menu'
        });
			
    });
};

X2Forms.prototype.initializeMultiselects = function () {
    var that = this;
    $('.x2-multiselect').each (function () {

        // don't reinitialize
        if ($(this).next ().hasClass ('.ui-multiselect')) return;

        $(this).multiselect ({ 
            searchable: false 
        });
    });
};

/**
 * Creates an error message element which can be appended to a form and cleared with clearForm ().
 * @return object jQuery element containing specified error message
 */
X2Forms.prototype.errorMessage = function (message) {
    return $('<span>', {
        'class': 'x2-forms-error-msg',
        text: message
    });
};


/**
 * Returns a jQuery element corresponding to an error box. The error box will
 * contain the specified errorHeader and a bulleted list of the specified error
 * messages.
 * @param string errorHeader 
 * @param string errorHeader 
 * @param object css
 */
X2Forms.prototype.errorSummary = function (errorHeader, errorMessages, css) {
    var css = typeof css === 'undefined' ? [] : css; 
    var errorBox = $('<div>', {'class': 'error-summary-container'}).append (
        $("<div>", { 'class': "error-summary"}).append ($("<ul>")));
    if (errorHeader !== '') {
        errorBox.find ('.error-summary').prepend ($('<p>', { text: errorHeader }));
    }
    $(errorBox).css (css);
    this._appendErrorMessages (errorBox, errorMessages);
    return errorBox;
};

/**
 * Appends error messages to error summary if one exists. Otherwise, a new error summary is created
 * with the given error messages and appended to the form
 * @param object form$
 * @param array|string errorMessages
 */
X2Forms.prototype.errorSummaryAppend = function (form$, errorMessages) {
    var errorSummary$ = form$.find ('.error-summary');
    if (!errorSummary$.length) {
        form$.append (this.errorSummary ('', errorMessages));
    } else {
        this._appendErrorMessages (errorSummary$.parent (), errorMessages);
    }
};

X2Forms.prototype.clearErrorMessages = function (form) {
    $(form).find ('.x2-forms-error-msg').remove ();
    $(form).find ('.error-summary-container').remove ();
    $(form).find ('.errorSummary').remove ();
    $(form).find ('.error').removeClass ('error');
};

/**
 * Clears all inputs in form. Removes 'error' class from all inputs. Also clears all error message
 * elements created by errorMessage ().
 * @param Object element containing the form
 * @param Bool preserveDefaults if true, values of form inputs will be set to their default values.
 *  Otherwise, they are set to the empty string.
 */
X2Forms.prototype.clearForm = function (container, preserveDefaults) {
    var preserveDefaults = typeof preserveDefaults === 'undefined' ? false : preserveDefaults; 
    if (preserveDefaults) {
        $(container).find ('textarea, input, select').each (function () {
            var defaultVal = $(this).attr ('data-default') || $(this)[0].defaultValue;

            if (typeof defaultVal === 'undefined') {
                $(this).val (''); 
            } else {
                $(this).val (defaultVal); 
            }
        });
    } else {
        $(container).find ('textarea, input, select').val ('');
    }
    $(container).find ('[type="checkbox"]').prop ("checked", false);
    $(container).find ('.error').removeClass ('error');
    $(container).find ('.x2-forms-error-msg').remove ();
    $(container).find ('.error-summary-container').remove ();
    $(container).find ('.errorSummary').remove ();
};

/**
 * Sets data-default attributes of all form inputs in specified container to their current value.
 * These values get restored in the clearForm () method.
 * @param Object element containing the form
 */
X2Forms.prototype.setDefaults = function (container) {
    $(container).find ('textarea, input, select').each (function () {
        var defaultVal = $(this).val ();
        $(this).attr ('data-default', defaultVal);
    });
};

/**
 * Disables/enables a subsection of a form
 * @param Object container the element containing the form subsection
 * @param Bool disable if true, disables the subsection. enables the subsection otherwise
 */
X2Forms.prototype.disableEnableFormSubsection = function (container, disable) {
    var disable = typeof disable === 'undefined' ? true : disable; 

    if (disable) { 
        $(container).find ('textarea, input, select').attr ('disabled', 'disabled');
    } else {
        $(container).find ('textarea, input, select').removeAttr ('disabled');
    }
}


X2Forms.prototype.toggleFormSection = function(section) {
    var that = this;
    if($(section).hasClass('showSection'))
        $(section).find('.tableWrapper').slideToggle(400,function(){
            $(this).parent('.formSection').toggleClass('showSection');
            that.saveFormSections();
        });
    else {
        $(section).toggleClass('showSection').find('.tableWrapper').slideToggle(400);
        that.saveFormSections();
    }
};

X2Forms.prototype.saveFormSections = function() {
    var formSectionStatus = [];
    $('div.x2-layout .formSection').each(function(i,section) {
        formSectionStatus[i] = $(section).hasClass('showSection')? '1' : '0';
    });
    var formSettings = '['+formSectionStatus.join(',')+']';
    $.ajax({
        url: yii.scriptUrl+'/site/saveFormSettings',
        type: 'GET',
        data: 'formName='+window.formName+'&formSettings='+encodeURI(formSettings)
    });
};


X2Forms.prototype.hideDefaultText = function(field) {
    if(field.defaultValue==field.value) {
        field.value = ''
        field.style.color = 'black'
    }
};

/**
 * Displays short default text if field text matches long default text
 */
X2Forms.prototype.showShortDefaultText = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    
    var shortDefaultText = $(field).attr ('data-short-default-text');
    var longDefaultText = $(field).attr ('data-long-default-text');
    if(field.value === longDefaultText) {
        field.value = shortDefaultText;
        field.style.color = this.defaultTextColor;
    }
};

/**
 * Displays long default text if field text matches short default text
 */
X2Forms.prototype.showLongDefaultText = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    
    var longDefaultText = $(field).attr ('data-long-default-text');
    var shortDefaultText = $(field).attr ('data-short-default-text');
    if(field.value === shortDefaultText) {
        field.value = longDefaultText;
        field.style.color = this.defaultTextColor
    }
};

/**
 * Toggles text, using short/long versions of default text
 */
X2Forms.prototype.toggleTextResponsive = function(field) {
    if (!$(field).attr ('data-short-default-text') ||
        !$(field).attr ('data-long-default-text')) {

        return;
    }
    var shortDefault = $(field).attr ('data-short-default-text');
    var longDefault = $(field).attr ('data-long-default-text');
    if ($('body').hasClass ('x2-mobile-layout')) {
        if (field.value === shortDefault) {
            field.value = '';
            field.style.color = 'black';
        } else {
            field.value = shortDefault;
            field.style.color = this.defaultTextColor;
        }
    } else {
        if (field.value === longDefault) {
            field.value = '';
            field.style.color = 'black';
        } else {
            field.value = longDefault;
            field.style.color = this.defaultTextColor;
        }
    }
};

/**
 * Used to hide/show default text of input
 */
X2Forms.prototype.toggleText = function(field, focus) {
    if(field.defaultValue==field.value) {
        field.value = '';
        field.style.color = 'black';
    } else if(field.value=='') {
        field.value = field.defaultValue;
        field.style.color = this.defaultTextColor;
    }
};


/**
 * Like toggleText except that it uses the attribute data-default-text to store the
 * placeholder value. This can be used for fields that are already populated on page load.
 * Instead, all fields with the class 'x2-default-field' are automatically initialized on page load.
 * @param object jQuery object
 */
X2Forms.prototype.enableDefaultText = function (element) {
    var that = this;
    if (!$(element).attr ('data-default-text')) {
        return;
    }
    var defaultText = $(element).attr ('data-default-text');
    if ($(element).val () === '') {
        $(element).val (defaultText); 
        $(element).css ({color: this.defaultTextColor});
    }
    $(element).off ('blur.defaultText').
        on ('blur.defaultText', function () {

        if ($(element).val () === '') {
            $(element).val (defaultText); 
            $(element).css ({color: that.defaultTextColor});
        }
    });
    $(element).off ('focus.defaultText').
        on ('focus.defaultText', function () {

        if ($(element).val () === $(element).attr ('data-default-text')) {
            $(element).val (''); 
            $(element).css ({color: 'black'});
        }
    });
};

/**
 * Clears default values to prepare form for submission
 */
X2Forms.prototype.clearDefaultValues = function (form$) {
    form$.find ('.x2-default-field').each (function () {
        if ($(this).val () === $(this).attr ('data-default-text')) {
            $(this).val ('');
        }
    });
};

X2Forms.prototype.restoreDefaultValues = function (form$) {
    form$.find ('.x2-default-field').each (function () {
        if ($(this).val () === '') { 
            $(this).val ($(this).attr ('data-default-text'));
        }
    });
};

X2Forms.prototype.formFieldFocus = function(elem) {
    var field = $(elem);
    if(field.val() == field.attr('title')) {
        field.val('');
        field.css('color','#000');
    }
};

X2Forms.prototype.formFieldBlur = function(elem) {
    var field = $(elem);
    if(field.val() == '') {
        field.val(field.attr('title'));
        field.css('color',this.defaultTextColor);
    }
};

X2Forms.prototype.submitForm = function(formName) {
    document.forms[formName].submit();
};

/**
 * Show form and scroll down to it
 */
X2Forms.prototype.toggleForm = function(formName,duration) {
    if($(formName).is(':hidden')) {
        $('html,body').animate({
            scrollTop: ($('#action-form').offset().top-200)
        }, 300);
    }
    $(formName).toggle('blind',{},duration);

};

X2Forms.prototype.hide = function(field) {
    $(field).hide(); 
    $('#save-changes').addClass('highlight'); 
};

X2Forms.prototype.show = function(field) {
    $(field).show();
};

X2Forms.prototype.renderContactLookup = function(item) {
    var label = "<a style=\"line-height: 1;\">" + item.label + "<span style=\"font-size: 0.6em;\">";

    if(item.email) {        // add email if defined
        label += "<br>";
        label += item.email;
    }

    if(item.city || item.state || item.country || item.email) {
        label += "<br>";

        if(item.city)
            label += item.city;
        if(item.state) {
            if(item.city)
                label += ", ";
            label += item.state;
        }
        if(item.country) {
            if(item.city || item.state)
                label += ", ";
            label += item.country;
        }
    }
    if(item.assignedTo){
        label += "<br>" + item.assignedTo;
    }
    label += "</span>";
    label += "</a>";

    return label;
};

X2Forms.prototype.fileUpload = function(fileField, action_url, remove_url) {
    var that = this;

    // Create the iframe
    var iframe = $("<iframe>");
    $(iframe).attr("id", "upload_iframe");
    $(iframe).attr("name", "upload_iframe");
    $(iframe).attr("width", "0");
    $(iframe).attr("height", "0");
    $(iframe).attr("border", "0");
    $(iframe).attr("style", "width: 0; height: 0; border: none;");

    // Add to document
    var form = $(fileField).closest ('form');

    $(form).parent ().append (iframe);
    window.frames['upload_iframe'].name = "upload_iframe";
    var iframeElem = $(iframe).eq (0);

    // Add event
    var eventHandler = function () {
        $(iframe).off ('load', eventHandler);

        // Message from server
        var iframeElem = $(iframe)[0];
        if(iframeElem.contentDocument) {
            var content = iframeElem.contentDocument.body.innerHTML;
        } else if(iframeElem.contentWindow) {
            var content = iframeElem.contentWindow.document.body.innerHTML;
        } else if(iframeElem.document) {
            var content = iframeElem.document.body.innerHTML;
        }

        var response = $.parseJSON(content)

        if(response['status'] === 'success') {

            // success uploading temp file
            // save it's name in the form so it gets attached when the user clicks send
            var file = $('<input>', {
                'type': 'hidden',
                'name': 'AttachmentFiles[id][]',
                'class': 'AttachmentFiles',
                'value': response['id'] // name of temp file
            });

            var temp = $('<input>', {
                'type': 'hidden',
                'name': 'AttachmentFiles[types][]',
                'value': 'temp'
            });

            var parent = fileField.parent().parent().parent();

            parent.parent().find('.error').html(''); // clear error messages
            
            // save copy of file upload span before we start making changes
            var newFileChooser = parent.clone(); 

            parent.removeClass('next-attachment');
            parent.addClass ('upload-file-container');
            parent.append(file);
            parent.append(temp);
            parent.find('.filename').html(response['name']);

            var remove = parent.find('.remove');

            remove.click(function() {
                that.removeAttachmentFile ($(this).parent(), remove_url); 
                return false;
            });

            fileField.parent().parent().remove();

            parent.after(newFileChooser);
            that.initX2FileInput();

        } else {
            fileField.parent().parent().parent().find('.error').html(response['message']);
            fileField.val("");
        }

        // remove iframe
        setTimeout(function () {
            iframeElem.parentNode.removeChild(iframeElem);
        }, 250);
    };

    $(iframeElem).on ('load', eventHandler);

    // Set properties of form
    $(form).attr ("target", "upload_iframe");
    $(form).attr ("action", action_url);
    $(form).attr ("method", "post");
    $(form).attr ("enctype", "multipart/form-data");
    $(form).attr ("encoding", "multipart/form-data");

    $(form).submit();
};

// remove an attachment that is stored on the server as a temp file
X2Forms.prototype.removeAttachmentFile = function(attachment, remove_url) {
    var id = attachment.find(".AttachmentFiles");
    $.post(remove_url, {'id': id.val()});

    attachment.remove();
};

// set up x2 file input
// call this function everytime an x2 file input is created
X2Forms.prototype.initX2FileInput = function() {
    // bind hover and click effects
    $('input.x2-file-input[type=file]').hover(function() {
        var button = $('input.x2-file-input[type=file]').next();
        if(button.hasClass('active') == false) {
            $('input.x2-file-input[type=file]').next().addClass('hover');
        }
    }, function() {
        $('input.x2-file-input[type=file]').next().removeClass('hover');
    });

    $('input.x2-file-input[type=file]').mousedown(function() {
        $('input.x2-file-input[type=file]').next().removeClass('hover');
        $('input.x2-file-input[type=file]').next().addClass('active');
    });

    $('body').mouseup(function() {
        $('input.x2-file-input[type=file]').next().removeClass('active');
    });

    // position the saving icon for uploading files
    // width
    var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('width'), 10)/2;
    var halfIconWidth = parseInt($('#choose-file-saving-icon').css('width'), 10)/2;
    var iconLeft = chooseFileButtonCenter - halfIconWidth;
    $('#choose-file-saving-icon').css('left', iconLeft + 'px');

    // height
    var chooseFileButtonCenter = parseInt($('input.x2-file-input[type=file]').css('height'), 10)/2;
    var halfIconHeight = parseInt($('#choose-file-saving-icon').height(), 10)/2;
    var iconTop = chooseFileButtonCenter - halfIconHeight;
    $('#choose-file-saving-icon').css('top', iconTop + 'px');

};

/**
 * Hide input and place a loading gif in its place 
 * @param object the input element
 */
X2Forms.prototype.inputLoading = function (elem, position) {
    position = typeof position === 'undefined' ? true : position; 
    var throbber$ = $('<div>', {
        'class': 'x2-loading-icon load8 input-loading-icon x2-loader',
        'style': 'float: left; position:absolute;',
    });
    throbber$.append ($('<div>', {
        'class': 'loader',
        'style': 'margin: auto;',
    }));
    throbber$.width ($(elem).width ());
    $(elem).before (throbber$);
    if (position) {
        $(elem).prev ().position ({
            my: 'center',
            at: 'center',
            of: $(elem)
        });
    } 
    $(elem).css ({'visibility': 'hidden'});
    return throbber$;
};

/**
 * Remove loading gif created by inputLoading ()
 * @param object the input element
 */
X2Forms.prototype.inputLoadingStop = function (elem) {
    $(elem).prev ().remove ();
    $(elem).css ({'visibility': ''});
};


X2Forms.prototype.getElementWidth = function (elem) {
    // determine width, using a clone if necessary
    if (!$(elem).is (':visible')) {
        var dummyElem = $(elem).clone ();
        $('body').append (dummyElem);
        var elemWidth = $(dummyElem).width () + 15;
        //var elemHeight = $(dummyElem).height ();
        dummyElem.remove ();
    } else {
        var elemWidth = $(elem).width ();
        //var elemHeight = $(elem).height ();
    }
    return elemWidth;
};

/**
 * Initialize all the fields that have default values.
 */
X2Forms.prototype.initializeDefaultFields = function () {
    var that = this;
    $('.x2-default-field').each (function () { that.enableDefaultText ($(this)); });
};

/**
 * @param mixed headerCell selector or jQuery element for headerCell in column of table
 * @param function fn function to apply to each cell in same column as headerCell in body of table
 */
X2Forms.prototype.forEachCellInColumn = function (headerCell, fn) {
    var headerRow$ = $(headerCell).closest ('tr'); 
    var table$ = $(headerCell).closest ('table');
    var columnNumber = headerRow$.find ('th').index (headerCell);
    var columns = headerRow$.find ('th').length;
    var tableCells = $.makeArray (table$.find ('tbody td'));
    if (columns < 1) return false;
    var i = columnNumber;
    while (i < tableCells.length) {
        fn.apply (tableCells[i]);
        i += columns;
    }
};

/*
Private instance methods
*/

X2Forms.prototype._setUpFormElementBehavior = function () {
    var that = this;
    $('div.x2-layout .formSectionShow, .formSectionHide').click(function() {
        that.toggleFormSection($(this).closest('.formSection'));
        that.saveFormSections();
    });

    $('a#showAll, a#hideAll').click(function() {
        $('a#showAll, a#hideAll').toggleClass('hide');
        if($('#showAll').hasClass('hide')) {
            $('div.x2-layout .formSection:not(.showSection)').each(function() {
                if($(this).find('a.formSectionHide').length > 0)
                    that.toggleFormSection(this);
            });
        } else {
            $('div.x2-layout .formSection.showSection').each(function() {
                if($(this).find('a.formSectionHide').length > 0)
                    that.toggleFormSection(this);
            });
        }
    });

    $('.inlineLabel').find('input:text, textarea').focus(function() { 
            that.formFieldFocus(this); 
        }).blur(function() { 
            that.formFieldBlur(this); 
        });
    
    this.setUpQTips ();
};

/**
 * Appends error messages to the given error summary container
 * @param object errorBox
 * @param array|string errorMessages
 */
X2Forms.prototype._appendErrorMessages = function (errorBox, errorMessages) {
    if (typeof errorMessages === 'string') {
        $(errorBox).find ('.error-summary').
            find ('ul').append ($("<li> " + errorMessages + " </li>"));
    } else {
        for (var i in errorMessages) {
            var msg = errorMessages[i];
            $(errorBox).find ('.error-summary').
                find ('ul').append ($("<li> " + msg + " </li>"));
        }
    }
};

/**
 * Auto-instantiate CKEditor rich textareas
 */
X2Forms.prototype.setUpRichTextareas = function () {
    $('.x2-rich-textarea').each (function () {
        createCKEditor ($(this).attr ('id'), {
            height: $(this).attr ('height') + 'px',
            width: $(this).attr ('width') + 'px',
            fullPage: true
        });
    });
};

X2Forms.prototype.setUpCollapsibles = function () {
    $('.x2-collapsible-outer > .x2-collapse-handle').unbind ('click.setUpCollapibles').
        bind ('click.setUpCollapibles', function () {

        var collapsibleOuter$ = $(this).parent ();
        var collapsible$ = collapsibleOuter$.find ('.x2-collapsible');
        var collapseButton$ = collapsibleOuter$.find ('.x2-collapse-button');
        var expandButton$ = collapsibleOuter$.find ('.x2-expand-button');
        if (collapsibleOuter$.hasClass ('collapsed')) {
            collapsibleOuter$.find ('.x2-collapsible').slideDown (200);
            collapsibleOuter$.removeClass ('collapsed');
            expandButton$.show ();
            collapseButton$.hide ();
        } else {
            collapsibleOuter$.find ('.x2-collapsible').slideUp (200);
            collapsibleOuter$.addClass ('collapsed');
            expandButton$.hide ();
            collapseButton$.show ();
        }
    });
};

X2Forms.prototype.disableButton = function (button$) {
    button$.attr ('disabled', 'disabled');
    button$.addClass ('x2-disabled-button');
};

X2Forms.prototype.enableButton = function (button$) {
    button$.removeAttr ('disabled');
    button$.removeClass ('x2-disabled-button');
};

X2Forms.prototype._init = function () {
    var that = this;
    $(function () { 
        that._setUpFormElementBehavior (); 
        that.initializeDefaultFields ();
        that.initializeMultiselectDropdowns ();
        that.initializeMultiselects ();
        that.setUpRichTextareas ();
        that.setUpCollapsibles ();
    });
};

return X2Forms;

}) ();
