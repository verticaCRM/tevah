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
/* @edition:pro */

x2.BarForm = (function () {

function BarForm (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.ChartForm.call (this, argsDict);
}

BarForm.prototype = auxlib.create (x2.ChartForm.prototype);

BarForm.prototype.setUpCheckBoxBehavior = function () {
	var that = this;

	$('.checkbox-group .option').click(function(){
	    var field = $(this).addClass('active').siblings().removeClass('active');
	    
	    var val = $(this).attr('value');
	    $(this).siblings ('input').val (val);

	    if (val == 0){
	    	that._form$.find('.axis-selector').attr('axis', 'column');
	    } else {
	    	that._form$.find('.axis-selector').attr('axis', 'row');
	    }

		x2.chartCreator.enterSelection (that._form$.find('.axis-selector').attr('axis'));
	});
}
	
BarForm.prototype._init = function(){
	x2.ChartForm.prototype._init.call(this);
	this.setUpCheckBoxBehavior();
};

return BarForm;

}) ();
