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

(function () {

x2.profileSettings.debug = false && x2.DEBUG;

x2.profileSettings.highlightSave = function () {
	$('#save-changes').addClass('highlight'); 
}
        
function convertTextColor(colorString){
    var redHex = colorString.slice(1,2);
    var greenHex = colorString.slice(3,4);
    var blueHex = colorString.slice(5,6);
        
    var red = parseInt(redHex, 16);
    var green = parseInt(greenHex, 16);
    var blue = parseInt(blueHex, 16);
        
    if((red*0.299 + green*0.587 + blue*0.114) > 186){
        return '#000000';
    } else {
        return '#ffffff';
    }
}

/*
Set the url to the sound file and play the sound
*/
function setSound(sound, id, filename, uploadedBy) {
    if(filename!=null){
        if(uploadedBy){
            $('#'+sound).attr('src',yii.baseUrl+'/uploads/media/'+uploadedBy+'/'+filename);
        }else{
            $('#'+sound).attr('src',yii.baseUrl+'/uploads/'+filename);
        }

        var soundFile = $("#"+sound)[0];
        if (Modernizr.audio) soundFile.play();
    }
}

function deleteSound(sound, id){
    $.ajax({
        url: yii.scriptUrl+'/profile/deleteSound?sound='+sound,
        type: 'get',
        data: 'id='+id,
        success: function(){
            $('#'+sound+'_'+id).hide();
        }
    });
}

/*
change the background image
*/
function setBackground(filename) {
    if(filename=='') {
            $('body').css('background-image','none').removeClass("no-borders");
    } else {
        $('body').css('background-image','url("'+yii.baseUrl+'/uploads/'+filename+'")').
            toggleClass("no-borders",($('#backgroundTiling').val() === 'stretch'));
        $(window).trigger('resize');
    }
}

function deleteBackground(id,filename) {
	$.ajax({
		url: yii.scriptUrl+'/profile/deleteBackground',
		type: 'get',
		data: 'id='+id,
		success: function(response) {
			if(response=='success') {
				$('#background_'+id).hide();

		        // if this is the current background,
				if($.inArray (filename, $('#header').css('background-image')) > -1) {

					// remove it from the page
					if($('#backgroundColor').val() === '') {
						$('#header').addClass('defaultBg').css('background-image','');
					} else {
						$('#header').removeClass('defaultBg').css('background-image','');
                    }
				}
			}
		}
	});
}

/*
background uploader
*/
function showAttach () {
	var e = document.getElementById ('attachments');
	if(e.style.display === 'none') {
		e.style.display = 'block';
	} else {
		e.style.display = 'none';
    }
}

/*
Enables submit if the file has the correct extension, alerts the user if it does not
*/
var ar_ext = ['png', 'jpg','jpe','jpeg','gif','svg']; // array with allowed extensions
function checkName(id) {
// - www.coursesweb.net
	// get the file name and split it to separate the extension
    var selector = "#" + id;
	var name = $(selector).val();
	var ar_name = name.split('.');

	// check the file extension
	var re = 0;
	for(var i = 0; i < ar_ext.length; i++) {
		if(ar_ext[i] === ar_name[1].toLowerCase()) {
			re = 1;
			break;
		}
	}
	// if re is 1, the extension is in the allowed list
	if(re === 1) { // enable submit
	    $(selector).parents ('.upload-box').find ('.submit-upload').removeAttr('disabled','disabled');
	} else { // delete the file name, disable Submit, Alert message
		$(selector).val('');
	    $(selector).parents ('.upload-box').find ('.submit-upload').attr('disabled','disabled');
		alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
	}
}

/*
Enables submit if the file has the correct extension, alerts the user if it does not
*/
var s_ext = ['mp3', 'wav', 'aiff'];
function checkSoundName(id) {
    var selector = "#" + id;
    var name = $(selector).val();
    var ar_name = name.split('.');

    var re = 0;
    for (var i = 0; i < s_ext.length; i++) {
        if(s_ext[i] === ar_name[1].toLowerCase()) {
            re = 1;
            break;
        }
    }
    if(re == 1){
	    $(selector).parents ('.upload-box').find ('.submit-upload').
            removeAttr('disabled','disabled');
    } else { // delete the file name, disable Submit, Alert message
	    $(selector).val('');
	    $(selector).parents ('.upload-box').find ('.submit-upload').attr('disabled','disabled');
	    alert('\".'+ ar_name[1]+ '\" is not an file type allowed for upload');
    }
}

/*
Helper function which gets called when the user changes the predefined theme setting
*/
function changeThemeAttr () {
    x2.profileSettings.debug && console.log ($(this));
    if ($(this).attr ('id') === 'themeName') return;
    if (!checkPredefThemeEditPermissions ()) {
        $('#themeName').find (':selected').removeAttr ('selected');
        $('#themeName').find ('#custom-theme-option').attr ('selected', 'selected');
    }
};


/*
Shows/hides the upload box with the specified id.
Parameters:
    boxId - the id of the upload box
*/
function toggleUploadBox (boxId) {
    var selector = '#' + boxId;
    if (!$(selector).is (":visible")) {
        $(selector).slideDown ();
        $('html,body').animate({
            scrollTop: ($(selector).offset().top - 100)
        }, 300);
    } else {
        $(selector).slideUp ();
    }
}

function setupPrefsEventListeners () {

    /*
    Convert relevent input fields to color pickers.
    Trigger change event when color is picked.
    */
    $('.color-picker-input').each (function (index, element) {
        x2.colorPicker.setUp ($(element), true);
    });

	$('#menuBgColor').change(function() {
        if ($(this).data ('ignoreChange')) {
            return;
        }
        var text = $(this).val();
		if(text === '') {
			$('#header').css('background','').addClass('defaultBg');
		} else {
			$('#header').removeClass('defaultBg').css('background','#' + text);
		}
		x2.profileSettings.highlightSave();
	});

    function selectPreferredColor (CSSProperty, CSSdefault, colorInputElem, targetElem) {
        if ($(colorInputElem).data ('ignoreChange')) {
            return;
        }
		var text = $(colorInputElem).val();
		if(text === '') {
			$(targetElem).css(CSSProperty, CSSdefault);
		} else {
			$(targetElem).css(CSSProperty, '#' + text);
		}
		x2.profileSettings.highlightSave();
    }

	$('#backgroundColor').change(function() {
        selectPreferredColor ('background-color', '#efeee8', $(this), $('body'));
	});

	$('#menuTextColor').change(function() {
        selectPreferredColor (
            'color', '#fff', $(this), $('ul.main-menu > li > a, ul.main-menu > li > span'));
	});

	$('#pageHeaderBgColor').change(function() {
        selectPreferredColor ('background', '#fff', $(this), $('.page-title'));
	});

	$('#pageHeaderTextColor').change(function() {
        selectPreferredColor ('color', '#fff', $(this), $('.page-title, .page-title h2'));
	});

    $('#activityFeedWidgetBgColor').change(function() {
        selectPreferredColor ('background-color', '#fff', $(this), $('#chat-box'));
	});

    $('#gridViewRowColorOdd').change(function() {
        selectPreferredColor (
            'background', '', $(this), $('div.grid-view table.items tr.odd'));
	});

    $('#gridViewRowColorEven').change(function() {
        selectPreferredColor (
            'background', '#F5F4DE', $(this), $('div.grid-view table.items tr.even'));
	});

    $('.color-picker-input').blur (function () {
        var text = $(this).val ();

        // make color picker color match input field without triggering change events
        if (text !== '') { 
            x2.colorPicker.removeCheckerImage ($(this));
            $(this).next ('div.sp-replacer').find ('.sp-preview-inner').css (
                'background', '#' + text);
        }
    });

	$('#backgroundTiling').change(function() {
		var val = $(this).val();
		var noBorders = false;
		switch(val) {
			case 'repeat-x':
			case 'repeat-y':
			case 'repeat':
				$("body").css({
                    "background-attachment":"",
                    "background-size":"",
                    "background-position":"",
                    "background-repeat":val
                });
				break;
			case 'center':
				$("body").css({
                    "background-attachment":"",
                    "background-size":"",
                    "background-repeat":"no-repeat",
                    "background-position":"center center"
                });
				break;
			case 'stretch':
				$("body").css({
                    "background-attachment":"fixed",
                    "background-size":"cover",
                    "background-position":"",
                    "background-repeat":""
                });
				noBorders = true;
				break;
		}
		$("body").toggleClass("no-borders",noBorders);

		x2.profileSettings.highlightSave();
	}).change();

    /*
    Unhide tags menu behavior
    */
    $('.unhide').mouseenter(function(){
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span class="hide-link-span">'+
            '<a href="#" class="hide-link" style="color:#06C;">[+]</a></span>';
        $(content).hide().delay(500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url: x2.profileSettings.normalizedUnhideTagUrl + '?tag=' + tag,
              success: function(){
                  $(elem).closest('.tag').fadeOut(500);
              }
           });

        });
    }).mouseleave(function(){
        $('.hide-link-span').remove();
    });

    /*
    Maximize/minimize sub-menus
    */
    $('.prefs-title-bar').click (function () {
        var $body = $(this).siblings ('.prefs-body');
        if ($body.is (':visible')) {
            $(this).find ('.prefs-expand-arrow').show ();
            $(this).find ('.prefs-collapse-arrow').hide ();
            $body.slideUp ();
            if ($(this).attr ('id') === 'tags-title-bar') {
                auxlib.saveMiscLayoutSetting ('unhideTagsSectionExpanded', 0);
            } else {
                auxlib.saveMiscLayoutSetting ('themeSectionExpanded', 0);
            }
        } else {
            $(this).find ('.prefs-expand-arrow').hide ();
            $(this).find ('.prefs-collapse-arrow').show ();
            $body.slideDown ();
            /*$body.find ('.x2-select').each (function () {
                $(this).data ('Select').reinitWidth (); 
            });*/
            if ($(this).attr ('id') === 'tags-title-bar') {
                auxlib.saveMiscLayoutSetting ('unhideTagsSectionExpanded', 1);
            } else {
                auxlib.saveMiscLayoutSetting ('themeSectionExpanded', 1);
            }
        }
    });

    $('#prefs-create-theme-button').click (function () {
        toggleUploadBox ('create-theme-box');
        $('#new-theme-name').focus ();
    });

    $('#upload-background-img-button').click (function () {
        toggleUploadBox ('upload-background-img-box');
    });

    $('#upload-login-sound-button').click (function () {
        toggleUploadBox ('upload-login-sound-box');
    });

    $('#upload-notification-sound-button').click (function () {
        toggleUploadBox ('upload-notification-sound-box');
    });

    // file selected by user
    $('#background-img-file').change (function () {
        checkName ($(this).attr ("id"));
    });

    // file selected by user
    $('#notification-sound-file, #login-sound-file').change (function () {
        checkSoundName ($(this).attr ("id"));
    });

    /*
    Set theme name to custom upon edit if user does not have edit permissions for 
    current predefined theme.
    */
    $('.theme-attr').bind ('change', changeThemeAttr);

    // select a background from drop down
    $('#backgroundImg').change (function (event) {
        setBackground ($(event.target).val ());
    });

    // select a login sound from drop down
    $('#loginSounds').change (function (event) {
        var setSoundParams = $(event.target).val ().split (',');
        setSound ('loginSound',setSoundParams[0],setSoundParams[1],setSoundParams[2]); 
        return false;
    });

    // select a notification sound from drop down
    $('#notificationSounds').change (function (event) {
        var setSoundParams = $(event.target).val ().split (',');
        setSound ('notificationSound',setSoundParams[0],setSoundParams[1],setSoundParams[2]); 
        return false;
    });

    /*
    Minimizes the upload box.
    */
    $('.upload-box').find ('button.cancel-upload').click (function () {
        $(this).parents ('.upload-box').slideUp ();
        return false;
    });

}

/*
Sets up behavior for theme save button
*/
function setupThemeSaving () {

    /*
    Save theme via Ajax.
    */
    function saveTheme () {
        if ($('prefs-save-theme-button').attr ('disabled')) return;
        var themeAttributes = {};
        $.each ($("#theme-attributes").find ('.theme-attr'), function () {
            x2.profileSettings.debug && console.log ($(this));
            var themeAttrName = $(this).attr ('name').match (/\[(\w+)\]/)[1];
            themeAttributes[themeAttrName] = $(this).val ();
        });
        themeAttributes['owner'] = yii.profile.username;
        //themeAttributes['private'] = $('.prefs-theme-privacy-setting').val ();
        x2.profileSettings.debug && console.log (themeAttributes);
        $.ajax ({
            url: "saveTheme",
            data: {
                'themeAttributes': JSON.stringify (themeAttributes)
            },
            success: function (data) {
                x2.profileSettings.debug && console.log (data);
                auxlib.createReqFeedbackBox ({
                    prevElem: $('#prefs-save-theme-hint'), 
                    disableButton: $('#prefs-save-theme-button'), 
                    message: data,
                    delay: 3000
                });
            }
        });
    }
    
    $('#prefs-save-theme-button').click (function () {
        saveTheme ();
        $('#settings-form').submit();
    });

    $('#save-changes').click (function () {
        saveTheme();
        $('#settings-form').submit();
    });


}


/*
Sets up behavior for theme creation sub-menu.
*/
function setupThemeCreation () {

    /*
    Theme name validation
    */
    $('#create-theme-submit-button').click (function (event) {
        var themeName = $('#new-theme-name').val ();
        x2.profileSettings.debug && console.log (themeName);
        if (themeName === '') {
            x2.profileSettings.debug && console.log ('error');
            $('#new-theme-name').addClass ('error');
        } else {
            $(this).attr ('disabled', 'disabled');
            createTheme (themeName); 
            $('#settings-form').submit();
        }
    });

    /*
    Save new theme to server via Ajax. Reset current theme. Handle errors.
    */
    function createTheme (themeName) {
        x2.profileSettings.debug && console.log (themeName);
        if ($('prefs-create-theme-button').attr ('disabled')) return;

        // build theme attribute dictionary to send to server
        var themeAttributes = {};
        $.each ($("#theme-attributes").find ('.theme-attr'), function () {
            x2.profileSettings.debug && console.log ($(this).attr ('name'));
            var themeAttrName = $(this).attr ('name').match (/\[(\w+)\]/)[1];
            themeAttributes[themeAttrName] = $(this).val ();
        });
        themeAttributes['themeName'] = themeName;
        themeAttributes['owner'] = yii.profile.username;
        themeAttributes['private'] = $('.prefs-theme-privacy-setting').val ();
        x2.profileSettings.debug && console.log (themeAttributes);

        $.ajax ({
            url: "createTheme",
            data: {
                'themeAttributes': JSON.stringify (themeAttributes)
            },
            success: function (data) {
                var respObj = JSON.parse (data);
                x2.profileSettings.debug && console.log (respObj);

                if (respObj['success']) {
                    x2.profileSettings.debug && console.log ('success');
                    destroyErrorBox ($('#create-theme-box'));
                    $('#create-theme-box').slideUp ();

                    // select new theme from drop down
                    $('#themeName').children ().removeAttr ('selected');
                    $('#themeName').append ($('<option>', {
                        'selected': 'selected',
                        'value': respObj['id'],
                        'text': themeName
                    }));
                    //$('#themeName').data ('Select').reinit ();

                    // indicate successful creation
                    auxlib.createReqFeedbackBox ({
                        prevElem: $('#theme-mgmt-buttons').children ('button').last (),
                        message: respObj['msg'],
                        delay: 3000,
                        disableButton: $('#prefs-create-theme-button')
                    });
                    x2.profileSettings.uploadedByAttrs[respObj['id']] = 
                        yii.profile.username;

                    showHideThemeSaveButton ();
                    $('#new-theme-name').removeClass ('error');

                } else {
                    x2.profileSettings.debug && console.log ('failure');

                    // display error messages
                    destroyErrorBox ($('#create-theme-box'));
                    var errorBox = createErrorBox (
                        respObj['errorListHeader'], [respObj['errorMsg']]);
                    $('.prefs-theme-privacy-setting').after ($(errorBox));
                    $('#new-theme-name').addClass ('error');

                }
            },
            complete: function () {
                x2.profileSettings.debug && console.log ('complete');
                $('#create-theme-submit-button').removeAttr ('disabled');
            }
        });
    }

}

/*
Removes an error div created by createErrorBox ().  
Parameters:
    parentElem - a jQuery element which contains the error div
*/
function destroyErrorBox (parentElem) {
    var $errorBox = $(parentElem).find ('.error-summary-container');
    if ($errorBox.length !== 0) {
        $errorBox.remove ();
    }
}

/*
Returns a jQuery element corresponding to an error box. The error box will
contain the specified errorHeader and a bulleted list of the specified error
messages.
Parameters:
    errorHeader - a string
    errorMessages - an array of strings
*/
function createErrorBox (errorHeader, errorMessages) {
    var errorBox = $('<div>', {'class': 'error-summary-container'}).append (
        $("<div>", { 'class': "error-summary"}).append (
            $("<p>", { text: errorHeader }),
            $("<ul>")
    ));
    for (var i in errorMessages) {
        var msg = errorMessages[i];
        $(errorBox).find ('.error-summary').
            find ('ul').append ($("<li> " + msg + " </li>"));
    }
    return errorBox;
}

/*
Returns true if the user has edit permissions for the current predefined theme,
false otherwise.
*/
function checkPredefThemeEditPermissions () {
    var currentPredefTheme = $('#themeName').val ();
   x2.profileSettings.DEBUG && console.log ('x2.profileSettings.uploadedByAttrs[currentPredefTheme] = ');
    x2.profileSettings.DEBUG && console.log (x2.profileSettings.uploadedByAttrs[currentPredefTheme]);

    if (x2.profileSettings.uploadedByAttrs[currentPredefTheme] === 
        yii.profile.username) {

        return true;
    } else {
        return false;
    }
}

/*
Shows the save theme button if the user has edit permissions for the current 
predefined theme and hides it otherwise.
*/
function showHideThemeSaveButton () {
    var currentPredefTheme = $('#themeName').val ();
    x2.profileSettings.DEBUG && console.log ('currentPredefTheme = ');
    x2.profileSettings.DEBUG && console.log (currentPredefTheme);

    x2.profileSettings.debug && console.log (x2.profileSettings.uploadedByAttrs[currentPredefTheme]);
    x2.profileSettings.debug && console.log (yii.profile.username);
    if (currentPredefTheme === 'Custom') {
        // $('#prefs-save-theme-button').hide (); 
        /* x2plastart */ 
        // $('#prefs-export-theme-button').hide (); 
        /* x2plaend */ 
    } else if (checkPredefThemeEditPermissions ()) {
        $('#prefs-save-theme-button').show (); 
        $('#prefs-save-theme-hint').show (); 
        /* x2plastart */ 
        $('#prefs-export-theme-button').show (); 
        /* x2plaend */ 
    } else {
        /* x2plastart */ 
        // $('#prefs-export-theme-button').hide (); 
        /* x2plaend */ 
        // $('#prefs-save-theme-button').hide (); 
        // $('#prefs-save-theme-hint').hide (); 
    }
}

/*
Sets up behavior for predifined theme selection.
*/
function setupThemeSelection () {

    /*
    Request a JSON object containing the theme with the specified name.
    Populate the theme form with values contained in the JSON object.
    */
    function requestTheme (themeName) {
        x2.profileSettings.debug && console.log ('requestTheme, themeName = ' + themeName);
        $.ajax ({
            url: "loadTheme",
            data: {'themeId': themeName},
            success: function (data) {
                $('#themeName').unbind ('change', selectTheme);
                $('.theme-attr').unbind ('change', changeThemeAttr);
                x2.profileSettings.debug && console.log ('requestTheme ajax ret');
                x2.profileSettings.debug && console.log (data);
                if (data === '') return;
                var theme = JSON.parse (data);
                x2.profileSettings.debug && console.log (theme);
                for (var attrName in theme) {
                    x2.profileSettings.debug && console.log (attrName);
                    x2.profileSettings.debug && console.log ($('#' + attrName).length);
                    if (attrName === 'themeName') continue;
                    if ($('#' + attrName).length !== 0) {
                        if (attrName.match (/Color/)) {
                            theme[attrName] = theme[attrName];
                        }
                        $('#' + attrName).val (theme[attrName]);
                        $('#' + attrName).change ();
                    }
                }
                $('#themeName').bind ('change', selectTheme);
                $('.theme-attr').bind ('change', changeThemeAttr);
                showHideThemeSaveButton ();
            }
        });
    }

    function selectTheme () {
        if ($(this).find (':selected').attr ('id') === 'custom-theme-option') {
            $('#prefs-save-theme-button').hide (); 
            /* x2plastart */ 
            $('#prefs-export-theme-button').hide (); 
            /* x2plaend */ 
            $('#prefs-save-theme-hint').hide (); 
            return;
        }
        requestTheme ($('#themeName').val ());
    }

    $('#themeName').bind ('change', selectTheme);

}

/* x2plastart */
function setupThemeExport () {
    $('#prefs-export-theme-button').click (function () {
        $.ajax ({
            url: 'ajaxExportTheme', 
            data: {
                'themeId': $('#themeName').val ()
            },
            dataType: 'json',
            success: function (data) {
                if (typeof data['downloadUrl'] !== 'undefined') {
                    var url = data['downloadUrl'];
                    window.location.href = url;
                }
            }
        });
    });
}

function setupThemeImport () {
    $('#prefs-import-theme-button').click (function () {
        if ($('#theme-import-form').closest ('.ui-dialog').length) {
            $('#theme-import-form').dialog ('open');
        }
        $('#theme-import-form').dialog ({
            title: x2.profileSettings.translations['themeImportDialogTitle'],
            autoOpen: true,
            width: 500,
            buttons: [
                {
                    text: x2.profileSettings.translations['close'],
                    click: function () { $(this).dialog ('close'); }
                }
            ]
        });
    });
}
/* x2plaend */

function setupDeleteThemeButton(){
    $('#prefs-delete-theme-button').click(function() { 

        var activeTheme = $('.scheme-container.active');

        $.ajax( {
            url: yii.scriptUrl+'/profile/deleteTheme',
            data: {
                themeName: activeTheme.attr('name')
            },
            success: function(data) {
                console.log(data);
                if(data == 'error') {
                    return;
                }

                activeTheme.remove();
            }
        });

    });
}

// main function
$(document).ready(function profileSettingsMain () {
    setupPrefsEventListeners ();
    setupThemeSelection ();
    setupThemeCreation ();
    setupThemeSaving ();
    setupDeleteThemeButton();
    /* x2plastart */ 
    setupThemeExport ();
    setupThemeImport ();
    /* x2plaend */ 

    showHideThemeSaveButton ();

    $('#prefs-save-theme-hint').qtip({
       position:{'my':'top right','at':'bottom left'},
       content: x2.profileSettings.saveThemeHint
    });

    $('#prefs-create-theme-hint').qtip({
       position:{'my':'top right','at':'bottom left'},
       content: x2.profileSettings.createThemeHint
    });
});


}) ();
