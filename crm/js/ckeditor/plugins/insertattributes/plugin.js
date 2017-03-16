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

CKEDITOR.plugins.add('insertattributes',{
	requires:['richcombo'],
	init:function(editor) {

		if(editor.config.insertableAttributes.length < 1)
			return;

		editor.addCommand('insertAttribute',{
			exec:function(editor) {

				var timestamp = new Date();
				editor.insertHtml('{attribute!}');
			}
		});
		
		var attributeDropdown = editor.ui.addRichCombo('Attribute',{
			label:"{attribute}",
			title:"Insert Record Attribute",
			voiceLabel:"Insert Record Attribute",
			className:'cke_format',
			multiSelect:false,

			panel:{
				css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( CKEDITOR.config.contentsCss ),
				multiSelect: false,
				attributes: { 'aria-label': editor.lang.panelTitle }
			},

			init:function() {
			
				var attributes = editor.config.insertableAttributes;
			
				for(var model in attributes) { // Each model type gets its own section
					if(attributes.hasOwnProperty(model)) {
						this.startGroup(model); 
						
						var attributeLabels = [];
                        var attributeTokens = [];
						for(var key in attributes[model]) {
							if(attributes[model].hasOwnProperty(key)) {
								attributeLabels.push(key);
                            }
						}
						attributeLabels.sort();

						for(var i in attributeLabels) {
                            // Internal convention for referencing properties of
                            // editor.config.insertableAttributes:
                            // {property of insertableAttributes}-@-{attribute label}
                            //
                            // It is done this way to avoid storing the values
                            // inside attributes of HTML elements...which can
                            // break stuff (since the value can potentially
                            // include invalid characters like carriage returns.
                            // JSON encoding is not used because that too can
                            // cause problems.
                            //
                            // The following method call ("add") has arguments in the order:
                            // value, dropdown text, dropdown label
							this.add(model +'-@-'+attributeLabels[i],attributeLabels[i],attributeLabels[i]);
						}
					}
				}
			},

			onClick:function(value) {
				editor.focus();
				editor.fire("saveSnapshot");
                var modelAttribute = value.split('-@-');
				editor.insertHtml(editor.config.insertableAttributes[modelAttribute[0]][modelAttribute[1]]);
				editor.fire("saveSnapshot");
			}
		});
	}

});


