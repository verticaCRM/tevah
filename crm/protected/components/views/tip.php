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

Yii::app()->clientScript->registerScript('tipScript',"
    $('.tip-refresh').click(function() {
        $.ajax({
            url:yii.baseUrl+'/index.php/site/getTip',
            dataType: 'json',
            success:function(data){
                $('#tip-content').fadeOut(400,function(){
                    $('#tip-title').text(data['module'] + ' Tip');
                    $('#tip').text(data['tip']);
                    $('#tip-content').fadeIn();
                });
            }
        });
    });
", CClientScript::POS_END);

?>
<span class="tip-refresh fa fa-refresh fa-lg" title="Refresh Tip"></span>
<div id="tip-content">
    <div class='tip-title'>
        <div id="tip-title">
            <?php
            echo CHtml::encode ($module." Tip");
            ?>
        </div>
    </div>
    <div class='tip'>
        <div id="tip">
            <?php
            echo CHtml::encode ($tip);
            ?>
        </div>
    </div>
</div>
