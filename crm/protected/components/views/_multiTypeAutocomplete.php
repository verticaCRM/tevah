<?php
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

$namespacedId = $this->namespace."-multi-type-autocomplete-container";

Yii::app()->clientScript->registerScript('multiTypeAutocompleteJS'.$this->namespace,"

$(function () {
    var container$ = $('#".$namespacedId."');

    container$.find ('select').change (function () {
        var modelType = container$.find ('select').val ();
        var throbber$ = x2.forms.inputLoading (container$.find ('.record-name-autocomplete'));
        $.ajax ({
            type: 'GET',
            url: 'ajaxGetModelAutocomplete',
            data: {
                modelType: modelType
            },
            success: function (data) {
                // remove span element used by jQuery widget
                container$.find ('input').
                    first ().next ('span').remove ();

                // replace old autocomplete with the new one
                container$.find ('input').first ().replaceWith (data); 
     
                // remove the loading gif
                throbber$.remove ();
            }
        });
    });
});

", CClientScript::POS_END);

?>
<div id="<?php echo $namespacedId ?>" 
 class="multi-type-autocomplete-container form2">
<?php
    echo CHtml::dropDownList (
        $this->selectName, $this->value, $this->options, 
        array (
            'class' => 'x2-select type-select',
        ));

    X2Model::renderModelAutocomplete ($this->value, false, array ());
    echo CHtml::hiddenField ($this->hiddenInputName, '', array (
        'class' => 'hidden-id',
    ));
?>
</div>
