/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website:  http: //www.x2engine.com 
 * Community and support website:  http: //www.x2community.com 
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
 * Subclasses x2.Fields, stripping out input naming convention depended on by x2flowEditor.js.
 * Instead, names of inputs in template are preserved, allowing form containing the field inputs
 * to be serialized correctly. 
 */

if (typeof x2 === 'undefined') x2 = {};

x2.FieldsGeneric = (function () {

function FieldsGeneric (argsDict) {
    var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: x2.DEBUG && false,
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    x2.Fields.call (this, argsDict);
}

FieldsGeneric.prototype = auxlib.create (x2.Fields.prototype);

FieldsGeneric.prototype.createInput = function(attributes) {
    var name;
    switch (attributes.name) {
        case 'operator':
            name = this.templates.conditionOpCell.find (':input').attr ('name');
            break;
        case 'value':
            name = this.templates.conditionValCell.find (':input').attr ('name');
            break;
        case 'attribute':
            name = this.templates.conditionAttrCell.find (':input').attr ('name');
            break;
        default:
            throw new Error ('invalid attribute name');
    }

    return x2.Fields.prototype.createInput.call (this, attributes, name);
};

return FieldsGeneric;

}) ();
