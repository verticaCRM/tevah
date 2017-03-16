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
/* @edition:pro */
?>
<div id='chart-form'>
    <label for="chart-selector" >Select a Chart Type</label>
    <div id='chart-selector'>
        <?php foreach(ChartForm::$forms as $formType) : ?>
            <div class='chart-selector-choice' id="<?php echo $formType; ?>" >
            <?php echo $formType; ?>
            </div>
        <?php endforeach ?>

    </div>
    <?php $this->getForms(); ?>
</div>

<style>
#chart-form form {
    display:none;
}

.chart-selector-choice {
    height:25px;
    width:25px;
    background: red;
}

</style>

<script>
$(function (){ 
    $('#chart-form .chart-selector-choice').click(function() {
        $('#chart-form form').hide();
        var id = $(this).attr('id');
        $('#chart-form form#'+id).show();
    });

});
</script>
