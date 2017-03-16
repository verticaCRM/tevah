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

if(typeof x2.fieldEditor == 'undefined')
    x2.fieldEditor = {};

x2.fieldEditor.load = function(type,reload,save,override) {
    reload = typeof reload == 'undefined' ? 0 : reload;
    save = typeof save == 'undefined' ? 0 : save;
    override = typeof override == 'undefined' ? 0 : override;
    var that = this;
    var ajaxConfig = {
        url:that.loadUrl+'?search='+(type == 'create' ? '0' : '1')+'&save='+save+'&override='+override,
        type:'GET',
        dataType:'html'
    };
    if(reload) {
        ajaxConfig.type = 'POST';
        ajaxConfig.data = that.formArea.find('form').serialize();
    }
    that.loading.css({
        "height":Math.floor(that.formArea.outerHeight())+'px',
        "width":Math.floor(that.formArea.outerWidth())+'px',
        "top":that.formArea.offset().top,
        "left":that.formArea.offset().left,
        "display":"block"
    });
    jQuery.ajax(ajaxConfig).done(function(data){
        that.formArea.html(data);
    }).always(function() {
        that.loading.hide();
    });
}

jQuery(document).ready(function() {
    x2.fieldEditor.formArea = $('#createUpdateField');
    x2.fieldEditor.form = x2.fieldEditor.formArea.find('form');
    x2.fieldEditor.loading = $("#createUpdateField-loading");

    x2.fieldEditor.formArea.on('change','#modelName-existing,#fieldName-existing',function(){
        // Refetch the page, in "customize" mode
        x2.fieldEditor.load('update',1);
    });
    x2.fieldEditor.formArea.on('change','#fieldType,#dropdown-type,#assignment-multiplicity', function() {
        // Refetch the page, either to customize or to create new, based on class
        var mode = $(this).hasClass('new') ? 'create' : 'update';
        x2.fieldEditor.load(mode,1,0,1);
    });
    x2.fieldEditor.formArea.on('click','#createUpdateField-savebutton',function(e) {
        e.preventDefault();
        var mode = $(this).hasClass('new') ? 'create' : 'update';
        x2.fieldEditor.load(mode,1,1);
        $.fn.yiiGridView.update("fields-grid");
    });

    // Event handler for using the insertable attributes dropdown:
    $('#createUpdateField').on('change',"#insertAttrToken",function(e) {
        // insert this.data.value at current cursor position
        var insertToken = $(e.target).val();
        $("#custom-field-template").each(function(e){
            var obj;
            if( typeof this[0] != 'undefined' && typeof this[0].name !='undefined' ) {
                obj = this[0];
            } else {
                obj = this;
            }

            if ($.browser.msie) {
                obj.focus();
                sel = document.selection.createRange();
                sel.text = insertToken;
                obj.focus();
            } else if ($.browser.mozilla || $.browser.webkit) {
                var startPos = obj.selectionStart;
                var endPos = obj.selectionEnd;
                var scrollTop = obj.scrollTop;
                obj.value = obj.value.substring(0, startPos)+insertToken+obj.value.substring(endPos,obj.value.length);
                obj.focus();
                obj.selectionStart = startPos + insertToken.length;
                obj.selectionEnd = startPos + insertToken.length;
                obj.scrollTop = scrollTop;
            } else {
                obj.value += insertToken;
                obj.focus();
            }
        });
               
    });

});
