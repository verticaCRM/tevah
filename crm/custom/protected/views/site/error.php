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

$errorTitle = Yii::t('app','Error {code}',array('{code}'=>$code));
$this->pageTitle=Yii::app()->settings->appName . ' - ' . $errorTitle;
?>
<h1 style="font-weight:bold;color:#f00;"><?php echo Yii::t('app','Oops!'); ?></h1>
<div id='x2-php-error' class="form" style="width:600px;">
    <?php echo Yii::t('app','It looks like the application ran into an unexpected error.');?>
    <br><br>
    <?php echo Yii::t('app','We apologize for the inconvenience and would like to do our best to fix this issue.  If you would like to make a post on our forums we can actively interact with you in getting this resolved.  If not, simply sending the error report helps us immensely and will only improve the quality of the software. Thanks!');?>
</div>
<h2><?php echo Yii::t('app','Send Error Report');?></h2>
<div id="error-form" class="form" style="width:600px;">
    <?php echo Yii::t('app',"Here's a quick list of what will be included in the report:");?><br><br>
    <b><?php echo Yii::t('app','Error Code:');?></b> <?php echo $code; ?><br>
    <b><?php echo Yii::t('app','Error Message:');?></b> <?php echo CHtml::encode($message);?><br>
    <b><?php echo Yii::t('app','Stack Trace:');?> </b> <a href="#" id="toggle-trace" style="text-decoration:none;">[<?php echo Yii::t('app','click to toggle display');?>]</a><br><div id="stack-trace" style="display:none;"><?php echo nl2br($trace);?></div>
</div>

<script>
    var errorReport="<?php echo addslashes($errorReport); ?>";
    var phpInfoErrorReport="<?php echo addslashes($phpInfoErrorReport); ?>";
    $('#toggle-trace').click(function(e){
        e.preventDefault();
        $('#stack-trace').toggle();
        if($('#stack-trace').is(":visible")){
            $('#error-form').css({'width':'95%'});
        }else{
            $('#error-form').css({'width':'600px'});
        }
    });
    $('#error-report-link').click(function(e){
        e.preventDefault();
        if($('#phpinfo').attr('checked')=='checked'){
            data=phpInfoErrorReport;
        }else{
            data=errorReport;
        }
        $('#error-report-link').hide();
        $('#loading-text').show();
        var email=$('#email').val();
        $.ajax({
            url:'<?php echo $this->createUrl('/site/sendErrorReport'); ?>',
            type:'POST',
            data:{'report':data,'email':email},
            success:function(){
                $('#loading-text').hide();
                $('#sent-text').show();
            }
        });
    });
</script>
