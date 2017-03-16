﻿/*
Copyright (c) 2003-2012,CKSource - Frederico Knabben. All rights reserved.
For licensing,see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function(config)
{
	// Define changes to default configuration here. For example:
	// config.uiColor = '#AADC6E';
	config.language = yii.language || 'en';
	
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	
	config.insertableAttributes = [];
	
	config.toolbar = 'Full';
	config.height = 600;
	config.resize_enabled = true;
	config.removePlugins = 'autogrow,showblocks,elementspath,liststyle,tabletools,contextmenu';
	
	config.extraPlugins = 'insertattributes,confighelper';
    config.disableNativeSpellChecker = false; // allow use of browser's native spellchecker
	config.resize_dir = 'vertical';
    config.toolbarCanCollapse = true;
	
	config.toolbar_Full = [
		// {name: 'document',items: ['Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates']},
		// {name: 'clipboard',items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		 {name: 'editing',items: ['Find','Replace','-','SelectAll','-','SpellChecker','Scayt']},
		// {name: 'forms',items: ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField']},
		// '/',
		// {name: 'basicstyles',items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat']},
		// {name: 'paragraph',items: ['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl']},
		// {name: 'links',items: ['Link','Unlink','Anchor']},
		// {name: 'insert',items: ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe']},
		// '/',
		// {name: 'styles',items: ['Styles','Format','Font','FontSize']},
		// {name: 'colors',items: ['TextColor','BGColor']},
		// {name: 'tools',items: ['Maximize','ShowBlocks','-','About']}
	// ];
		{name: 'document',items: ['Save','DocProps','Preview','Print','-','Source']},
		{name: 'clipboard',items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		// {name: 'forms',items: ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','HiddenField']},
		{name: 'links',items: ['Link','Unlink','Anchor']},
		{name: 'insert',items: ['Image','Table','HorizontalRule','SpecialChar']},
		'/',
		{name: 'basicstyles',items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat','SpellChecker']},
		{name: 'styles',items: ['Font','FontSize']},
		{name: 'colors',items: ['TextColor','BGColor']},
		{name: 'paragraph',items: ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']}
	
	];
	config.toolbar_MyEmailToolbar = [
		{name: 'document',items: ['Source']},
		{name: 'insertAttributes',items:['Attribute']},
		{name: 'clipboard',items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		// {name: 'forms',items: ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','HiddenField']},
		{name: 'links',items: ['Link','Unlink','Anchor']},
		{name: 'insert',items: ['Image','Table','HorizontalRule','SpecialChar']},
		{name: 'colors',items: ['TextColor','BGColor']},
		'/',
		{name: 'basicstyles',items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat','SpellChecker']},
		{name: 'styles',items: ['Font','FontSize']},
		{name: 'paragraph',items: ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']}
	];
    // same as toolbar_MyEmailToolbar but with source button removed
	config.toolbar_MyTargetedContentToolbar = [
		{name: 'insertAttributes',items:['Attribute']},
		{name: 'clipboard',items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		// {name: 'forms',items: ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','HiddenField']},
		{name: 'links',items: ['Link','Unlink','Anchor']},
		{name: 'insert',items: ['Image','Table','HorizontalRule','SpecialChar']},
		{name: 'colors',items: ['TextColor','BGColor']},
		'/',
		{name: 'basicstyles',items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat','SpellChecker']},
		{name: 'styles',items: ['Font','FontSize']},
		{name: 'paragraph',items: ['NumberedList','BulletedList','-','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']}
	];
	
};
