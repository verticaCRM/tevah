/***********************************************************************************
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
 * ****************************************************************************** */

/* @edition:pro */


function setUpDialogs (saveChanges, translations) {
	$('.editor-dialog').dialog ({
		title: translations['editDialogTitle'],
		dialogClass: 'gallery-widget-dialog',
		autoOpen: false,
		height: "auto",
		width: "auto",
		resizable: false,
		draggable: false,
		show: 'fade',
		modal: true,
		hide: 'fade',
		position: {
			my: 'center',
			at: 'center',
			of: window
		},
		open: function () {
			var $dialog = $('.editor-dialog');
			var $img = $dialog.find ('img');
			var imageNum = $dialog.find ('img').length;
			$dialog.dialog ('option', 'width', 600)
			$dialog.dialog ('option', 'height', 500)
			$img.on ('load', function () {
				var width = $(this).width ();
				var height = $(this).height ();
				if (width > 600 || height > 250) {
					if (height > 250 && width > 600 ) {
						if (height > width) {	
							$(this).width ((250 / height) * width);
							$(this).height (250);
						} else {
							$(this).height ((600 / width) * height);
							$(this).width (600);
						}
					} else if (height > 250) {
						$(this).width ((250 / height) * width);
						$(this).height (250);
					} else if (width > 600) {
						$(this).height ((600 / width) * height);
						$(this).width (600);
					}
				}
				$dialog.dialog ('option', 'position', 'center');
			});

			$('.ui-widget-overlay').one ('click', function () {
				$dialog.dialog ('close');
			})
		},
		buttons: [
			{ 
				text: translations['editDialogSaveButtonLabel'],
				click: saveChanges,
				'class': 'editor-dialog-save-button'
			},
			{ 
				text: translations['editDialogCloseButtonLabel'],
				click: function () {
					$('.editor-dialog').dialog ('close');
				},
				'class': 'editor-dialog-close-button'
			}
		]
	});
	$('.preview-dialog').dialog ({
		title: translations['viewDialogTitle'],
		dialogClass: 'gallery-widget-dialog',
		autoOpen: false,
		height: "auto",
		width: 'auto',
		resizable: false,
		draggable: false,
		show: 'fade',
		modal: true,
		hide: 'fade',
		position: {
			my: 'center',
			at: 'center',
			of: window
		},
		open: function () {
			var $dialog = $('.preview-dialog');
			var $img = $dialog.find ('img');
			$dialog.dialog ('option', 'width', 600)
			$dialog.dialog ('option', 'height', 500)
			$img.on ('load', function () {
				var width = $(this).width ();
				var height = $(this).height ();
				if (width > 600 || height > 500) {
					if (height > 400 && width > 600 ) {
						if (height > width) {	
							$(this).width ((400 / height) * width);
							$(this).height (400);
						} else {
							$(this).height ((600 / width) * height);
							$(this).width (600);
						}
					} else if (height > 400) {
						$(this).width ((400 / height) * width);
						$(this).height (400);
					} else if (width > 600) {
						$(this).height ((600 / width) * height);
						$(this).width (600);
					}
				}
				$dialog.dialog ('option', 'position', 'center');
			});
			$('.ui-widget-overlay').one ('click', function () {
				$dialog.dialog ('close');
			})
		},
		buttons: [
			{ 
				text: translations['viewDialogCloseButtonLabel'],
				click: function () {
					$('.preview-dialog').dialog ('close');
				},
				'class': 'preview-dialog-close-button'
			},
		]
	});

	// re-center dialog
	$(window).on ('resize', function () {
		if ($('.preview-dialog').is (':visible')) {
			$('.preview-dialog').dialog ('option', 'position', 'center');
		} else if ($('.editor-dialog').is (':visible')) {
			$('.editor-dialog').dialog ('option', 'position', 'center');
		}
	});

}



