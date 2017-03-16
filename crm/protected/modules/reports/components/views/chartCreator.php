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

$imgUrl = $this->controller->module->assetsUrl.'/images/';

// Yii::app()->clientScript->registerScript('CreateChartJS',"
//     $(function (){ 
//         var chart$ = $('.chart-form');

//         chart$.find('.chart-selector .choice').click(function() {
//             chart$.find('.choice, form').removeClass('active');
//             var id = $(this).attr('value');
//             chart$.find('form#'+id).addClass('active');
//             $(this).addClass('active');
//         });

//         var active = chart$.find('.active-form').attr('value');
//         chart$.find('.choice[value=\"'+active+'\"]').trigger('click');

//     });
// ", CClientScript::POS_END);
?>

<!-- <div class='charts-page-title page-title'>
    <h2>New Chart</h2>
</div>
 -->
<div id='chart-creator' class='chart-form' style='display: none'>
    <div class='form-header'>
    <?php
    CHtml::encode (Yii::t('reports', 'Select a Chart Type'))   
    ?>
    </div>
    <div class='chart-selector'>
        <?php foreach($this->chartTypes as $chartType): ?>
            <div class='choice' value="<?php echo $chartType.'Form'; ?>" style='
                    background-image:url("<?php echo $imgUrl.$chartType.'Form.png'; ?>"); '></div>
        <?php endforeach ?>

    </div>
    <?php $this->renderForms(); ?>
</div>
