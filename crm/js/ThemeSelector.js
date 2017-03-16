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
 * Class to handle the theme Selector
 */

x2.ThemeSelector = (function(){
	function ThemeSelector(argsDict) {
		var defaultArgs = {
			defaults: ['Default', 'Terminal'],
			active: null,
			user: null,
			translations: {}
		};

		auxlib.applyArgs(this, defaultArgs,argsDict);

		this.active = this.active || this.defaults[0];

		this.active$ = this.getSelector(this.active);

		if ( $(this.active$).length == 0 ) {
			this.active = this.defaults[0];
			this.active$ = this.getSelector(this.active);
		}

		this.fillColorFields(this.active$);
		this.setUpClickBehavior();
	}

	ThemeSelector.prototype.getSelector = function(themeName) {
		return '.scheme-container[name="'+themeName+'"]';
	}

	ThemeSelector.prototype.changeSelectBox = function(parent, name) {
			var element = $(parent).find('.hidden#'+name).attr('value');

			var options = $('select#'+name+' > option');
			options.removeAttr('selected');
			options.filter('[value=\"'+element+'\"]').attr('selected','selected');
		}


	ThemeSelector.prototype.fillColorFields = function(themeBox) {

			$('.color-picker-input').val('');

			// this.changeSelectBox(themeBox, 'backgroundTiling');
			// this.changeSelectBox(themeBox, 'backgroundImg');

			$(themeBox).find('.scheme-color').each( function(){
				var name = $(this).attr('name');
				var color = $(this).attr('color');
				$('input#preferences_'+name).val(color);
			});

			$('.color-picker-input').trigger('blur');
			$('.color-picker-input').trigger('change');

			var themeName = $(themeBox).attr('name')
			$('input#themeName').val(themeName);
			$('.scheme-container').removeClass('active');
			$(themeBox).addClass('active');

			var user = $(themeBox).find('#uploadedBy').attr('value');

            if (this.user !== user || $.inArray (themeName, this.defaults) >= 0) {
				$('.color-picker-input').attr('readonly','').attr('title', this.translations.createNew );
				$('.sp-replacer.sp-light').hide();
			    x2.forms.disableButton ($('#prefs-delete-theme-button, #prefs-save-theme-button'));
            } else {
			    x2.forms.enableButton ($('#prefs-delete-theme-button, #prefs-save-theme-button'));
            }


	};


	ThemeSelector.prototype.setUpClickBehavior = function() {
		var that = this;
		$('.scheme-container').click( function() { 
			if ($(that.active$).attr('name') == $(this).attr('name')) {
				return;
			}

			that.fillColorFields(this);
			that.active = this; 

			$('#settings-form').submit();
		} );

	};

	return ThemeSelector;
})();
