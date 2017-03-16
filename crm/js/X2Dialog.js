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

$.widget ('x2.x2Dialog', $.ui.dialog, {

    /**
     * Modified to enable button rows. To use button rows add a dummy button with the property
     * lineBreak set to true. Currently this supports only one additional row.
     * This function is a modified version of a base jQuery UI function
     * jQuery UI Sortable @VERSION
     * http://jqueryui.com
     *
     * Copyright 2012 jQuery Foundation and other contributors
     * Released under the MIT license.
     * http://jquery.org/license
     *
     * http://api.jqueryui.com/sortable/
     */
	_createButtons: function() {
		var that = this,
			buttons = this.options.buttons;

		// if we already have a button pane, remove it
		this.uiDialogButtonPane.remove();
		this.uiButtonSet.empty();

		if ( $.isEmptyObject( buttons ) || ($.isArray( buttons ) && !buttons.length) ) {
			this.uiDialog.removeClass( "ui-dialog-buttons" );
			return;
		}

        /* x2modstart */ 
        row$ = null;
        var startedRow = false;
        /* x2modend */ 
		$.each( buttons, function( name, props ) {
			var click, buttonOptions;
			props = $.isFunction( props ) ?
				{ click: props, text: name } :
				props;

            /* x2modstart */ 
            if (props.lineBreak) {
                if (startedRow) {
                    that.uiButtonSet.append ($('<br>'));
                    that.uiButtonSet.append (row$);
                }
                row$ = $('<div>', {
                        'class': 'dialog-button-row'
                });
                startedRow = true;
                return;
            }
            /* x2modend */ 

			// Default to a non-submitting button
			props = $.extend( { type: "button" }, props );
			// Change the context for the click callback to be the main element
			click = props.click;
			props.click = function() {
				click.apply( that.element[ 0 ], arguments );
			};
			buttonOptions = {
				icons: props.icons,
				text: props.showText
			};
			delete props.icons;
			delete props.showText;

            /* x2modend */ 
            if (startedRow) {
                button = $( "<button></button>", props )
				    .button( buttonOptions )
                    .appendTo( row$ );
            } else {
                button = $( "<button></button>", props )
				    .button( buttonOptions )
                    .appendTo( that.uiButtonSet );
            }
            /* x2modend */ 
		});

        /* x2modstart */ 
        if (row$) {
            that.uiButtonSet.append ($('<br>'));
            that.uiButtonSet.append (row$);
        }
        /* x2modend */ 

		this.uiDialog.addClass( "ui-dialog-buttons" );
		this.uiDialogButtonPane.appendTo( this.uiDialog );
	},
});

