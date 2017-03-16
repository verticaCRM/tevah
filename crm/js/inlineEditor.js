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

x2.InlineEditor = (function() {

	function InlineEditor(argsDict) {
	    var defaultArgs = {
	    	inlineEdit: '.inline-edit',
	    	editIcon: '.edit-icon',
	    	confirmIcon: '.confirm-icon',
	    	cancelIcon: '.cancel-icon',
    		modelId: null,
	    	translations: {
	    		unsavedChanges: null
    		},
	    };

	    auxlib.applyArgs (this, defaultArgs, argsDict);
	    auxlib.generateSelectors(this);
	    this.init();
	}

	InlineEditor.prototype.init  = function () {
		var that = this;
		this.setUpUnsavedBehavior ();
		this.setUpEditButton ();
		this.setUpCancelButton ();
		this.setUpConfirmButton ();


	}

	InlineEditor.prototype.setUpEditButton  = function () {
		var that = this;

		this.$inlineEdit.find (this.editIcon).click( function(e) {
			e.preventDefault();

			var inlineEdit = $(this).closest (that.inlineEdit);
			var id = inlineEdit.attr('id');
			var inputContainer = $('#' + id + '-input');
			var input = inputContainer.find (':input');
			var field = $('#' + id + '-field');

			inlineEdit.
				find (that.confirmIcon + ', ' + that.cancelIcon).
				addClass('active'); 

			$(this).removeClass('active');

			inputContainer.height(field.height());
			inputContainer.show ();
			field.hide ();
			
			if (input.is ('textarea')) 
				input.height (field.height());

			// setTimeout(function(){
			// 	auxlib.onClickOutside (id, function(e) {
			// 		$(that.confirmIcon +'.active').trigger('click');
			// 	}, true);
			// }, 100);

		});


		// $('body').click (function() {
			// $(that.confirmIcon +'.active').trigger('click');
		// });

	}

	InlineEditor.prototype.setUpCancelButton = function () {
		var that = this;
		
		this.$inlineEdit.find (this.cancelIcon).click (function (e) {
			e.preventDefault();

			var inlineEdit = $(this).closest(that.inlineEdit);
			that.resetField(inlineEdit);
		});

		// Doesn't seem to work...
		// $(document).keypress(function(e) {
		// 	console.log(e.which);
		// 	if(e.which != 27)  return;

		// 	var active = $(this.activeElement);
		// 	console.log(active);
		// 	if (active.is('textarea')) return;

		// 	var inlineEdit = active.closest (that.inlineEdit);
		// 	console.log(inlineEdit);
		// 	if (inlineEdit.length == 0) return;

		// 	inlineEdit.find (that.cancelIcon+'.active').click ();
		// });

	
	}

	InlineEditor.prototype.setUpConfirmButton = function () { 
		var that = this;

		this.$inlineEdit.find (this.confirmIcon).click (function (e) {
			e.preventDefault();


			var inlineEdit = $(this).closest (that.inlineEdit);

		    var attributes = {};

		    inlineEdit.find ('.model-input input, .model-input select, .model-input textarea').
		    	each (function() {
			        attributes[$(this).attr('name')] = $(this).val();
			    }
			);

		    $.each(x2.InlineEditor.ratingFields, function(index, value) {
		        if (typeof value === 'undefined') {
		            attributes[index] = '';
		        } else {
		            attributes[index] = value;
		        }
		    });

		    inlineEdit.find ('.model-input :checkbox').each(function(){
		        if($(this).is(':checked')){
		            attributes[$(this).attr('name')] = 1;
		        }else{
		            attributes[$(this).attr('name')] = 0;
		        }
		    });

		    $.ajax({
		        url: yii.scriptUrl + '/site/ajaxSave',
		        type: 'POST',
				dataType: 'json',
		        data: {attributes: attributes, modelId: that.modelId},
		        success: function(data) {
		            $.each(data, function(index, value) {
		                $('#' + index + '_field-field').html(value);
		                $('#' + index + '_field-field input[type=radio]').rating();
		                $('#' + index + '_field-field input[type=radio]').rating('readOnly', true);
		            });
		            that.resetField(inlineEdit);
		        }
		    });

		});

		// Set up key functions
		$(document).keypress(function(e) {
			// 13 is the Enter Key
			if(e.which != 13)  return;

			// Dont trigger on textareas
			var active = $(this.activeElement);
			if (active.is('textarea')) return;

			// find the closest editable field if there is one
			var inlineEdit = active.closest (that.inlineEdit);
			if (inlineEdit.length == 0) return;

			e.preventDefault();
			// Trigger clicking the confirm icon;
			inlineEdit.find (that.confirmIcon +'.active').click ();
		});

	}


	InlineEditor.prototype.resetField = function ($parent) {

		$parent.find (this.confirmIcon).removeClass('active');
		$parent.find (this.cancelIcon).removeClass('active');
		$parent.find (this.editIcon).addClass('active');

		$parent.find ('.model-input').hide();
		$parent.find ('.model-attribute').show();
	}
	

	InlineEditor.prototype.setUpUnsavedBehavior  = function () {
		var that = this;

		$(window).bind('beforeunload', function() {
		    // if ($(that.inlineEdit + that.cancelIcon + '.active').length > 0) {
		    //     return that.translations.unsavedChanges;
		    // }
		});
	}

	return InlineEditor;

})();

x2.InlineEditor.ratingFields = {};

