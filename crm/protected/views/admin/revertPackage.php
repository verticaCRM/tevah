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
<div class="page-title"><h2><?php echo Yii::t('admin','Revert Package');?></h2></div>

<div class="form" style="width:600px;">
    <?php echo Yii::t('admin','To begin reverting this package, click the button below and wait '.
            'for the completion message. Note that if a package contained default fields that '.
            'were modified, they will not be removed.'); ?>
    <br><br>
    <?php echo Yii::t('admin','Package: '); ?><strong><?php echo $package['name'];?></strong>
    <br>
    <?php echo Yii::t('admin','Records to be Deleted: '); ?><strong><?php echo $package['count']; ?></strong>
    <br>
    <?php echo Yii::t('admin','Modules to be Deleted: '); ?><strong><?php echo (empty($package['modules'])) ? Yii::t('admin', 'None') : implode(',', $package['modules']); ?></strong>
    <br>
    <?php echo Yii::t('admin','Media to be Deleted: '); ?><strong><?php echo (empty($package['media'])) ? Yii::t('admin', 'None') : implode(',', $package['media']); ?></strong>
    <br>
    <br><br>
    <?php echo CHtml::link('Begin Revert','#',array('id'=>'revert-link','class'=>'x2-button'));?>
    <?php echo CHtml::link('Back','packager',array('id'=>'back-link','class'=>'x2-button'));?>
</div>
<div class="form" style="width:600px;color:green;display:none;" id="status-box">

</div>
<?php 
Yii::app()->clientScript->registerScript('pkg-revert', '
    var models='.CJSON::encode($typeArray).';
    var importId='.$package['importId'].';
    var stages=new Array("tags","relationships","actions","records","import");

    var rollbackStage = function(model, stage) {
        $.ajax({
            url:"rollbackStage",
            type:"GET",
            data:{model:models[model],stage:stages[stage],importId:importId},
            success:function(data){
                if(stages[stage]=="import"){
                    $("#status-box").append("<br>"+data+" <b>"+models[model]+"</b> successfully removed.");
                }
                if(model<models.length){
                    if(stage<stages.length-1){
                        rollbackStage (model,stage+1);
                    }else{
                        if(model!=models.length-1){
                            rollbackStage (model+1,0);
                        }else{
                            finishRevert();
                        }
                    }
                }else{
                    finishRevert();
                }
            }
        });
    };

    var finishRevert = function() {
        $.ajax({
            url: "'.$this->createUrl ('finishPackageRevert', array('name' => $package['name'])).'",
            type: "post",
            success: function() {
                $("#status-box").append("<br><br><b>'.Yii::t('admin', 'Finished reverting package').'</b>");
                $("#revert-link").hide();
                alert("'. Yii::t('admin', 'Finished!').'");
            }
        });
    };

    $("#revert-link").click(function(e){
        e.preventDefault();
        $("#status-box").show();
        $("#status-box").append("'.Yii::t('admin', 'Beginning to revert package...').'<br />");

        $.ajax({
            url: "'.$this->createUrl ('beginPackageRevert', array('name' => $package['name'])).'",
            type: "post",
            success: function() {
                $("#status-box").append("'.Yii::t('admin', 'Finished removing modules and media...').'<br />");
                if ('.$package['count'].' > 0) {
                    $("#status-box").append("'.Yii::t('admin', 'Beginning to rollback records...').'<br />");
                    rollbackStage(0,0);
                } else {
                    finishRevert();
                }
            }
        });

    });
', CClientScript::POS_READY);
