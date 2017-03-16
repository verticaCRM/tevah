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

?>
<div class="page-title icon contacts"><h2><?php echo Yii::t('contacts','Export {model}', array('{model}'=>$model)); ?></h2></div>
<div class="form">
    
<?php if (!empty($model)) { ?>
    <?php echo '<div style="width:600px;">'; ?>
    <?php echo Yii::t('admin','Please click the button below to begin the export. Do not close this page until the export is finished, which may take some time if you have a large number of records. A counter will keep you updated on how many records have been successfully updated.'); ?><br><br>
    <?php echo isset($listName)?Yii::t('admin','You are currently exporting: ')."<b>$listName</b>":''; ?>
    </div>
    <br>
    <?php echo CHtml::button(Yii::t('app','Export'),array('class'=>'x2-button','id'=>'export-button')); ?>
    <div id="status-text" style="color:green">

    </div>

    <div style="display:none" id="download-link-box">
        <?php echo Yii::t('admin','Please click the link below to download {model}.', array('{model}'=>$model));?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app','Download');?>!</a>
    </div>
    <script>
$('#export-button').on('click',function(){
    exportModelData(0);
});
function exportModelData(page){
    if($('#export-status').length==0){
       $('#status-text').append("<div id='export-status'><?php echo Yii::t('admin','Exporting <b>{model}</b> data...', array('{model}'=>$model)); ?><br></div>");
    }
    $.ajax({
        url:'exportModelRecords?page='+page+'&model=<?php echo $model; ?>',
        success:function(data){
            if(data>0){
                $('#export-status').html(((data)*100)+" <?php echo Yii::t('admin','records from <b>{model}</b> successfully exported.', array('{model}'=>$model));?><br>");
                exportModelData(data);
            }else{
                $('#export-status').html("<?php echo Yii::t('admin','All {model} data successfully exported.', array('{model}'=>$model));?><br>");
                $('#download-link-box').show();
                alert("<?php echo Yii::t('admin','Export Complete!');?>");
            }
        }
    });
}
$('#download-link').click(function(e) {
    e.preventDefault();  //stop the browser from following
    window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>$_SESSION['modelExportFile'])); ?>';
});</script>
<?php } else {
    echo "<h3>".Yii::t('admin','Please select a module to export from.')."</h3>";
    foreach ($modelList as $class => $modelName) {
        echo CHtml::link($modelName, array('/admin/exportModels', 'model'=>$class))."<br />";
    }
} ?>

</div>