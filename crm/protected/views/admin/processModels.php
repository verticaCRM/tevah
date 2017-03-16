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
<script>
var record=0;
</script>
<style>
    .clean-link{
        text-decoration:none;
    }
</style>
<?php
    if (isset($model))
        unset($_SESSION['model']);
?>
<div class="page-title"><h2><?php echo Yii::t('admin','{model} Import', array('{model}'=>X2Model::getModelTitle ($model))); ?></h2></div>
<div class="form" >
<div style="width:600px">
<?php
if ($preselectedMap) {
    echo Yii::t('admin', 'You have selected to upload and use the following import mapping: ').$_SESSION['mapName']."<br><br>";
    ?>
    <table id="importMapSummary">
    <tr>
        <td><strong><?php echo Yii::t('admin','Your Field'); ?></strong></td>
        <td><strong><?php echo Yii::t('admin','Our Field'); ?></strong></td>
    </tr>
    <?php foreach ($importMap as $key => $val) { ?>
        <tr>
            <td style='width: 50%'><?php echo $key; ?></td>
            <td style='width: 50%'><?php echo $val; ?></td>
        </tr>
    <?php
    }
    echo "</table>";
    echo CHtml::link(Yii::t('admin', 'Edit'), '#', array('id' => 'editPresetMap', 'class' => 'x2-button')).'<br /><br />';
} else {
    echo Yii::t('admin',"First, we'll need to make sure your fields have mapped properly for import. ");
    echo Yii::t('admin','Below is a list of our fields, the fields you provided, and a few sample records that you are importing.')."<br /><br />";
    echo Yii::t('admin','If the ID field is selected to be imported, the import tool will attempt to overwrite pre-existing records with that ID. Do not map the ID field if you don\'t want this to happen.');
    echo Yii::t('admin', 'Select the fields you wish to map. Fields that have been detected as matching an existing field have been selected.').'<br /><br />';
    echo Yii::t('admin', 'Fields that are not selected will not be mapped. To override a mapping, select the appropriate field from the corresponding drop down.').'<br /><br />';
    echo Yii::t('admin','Selecting "DO NOT MAP" will ignore the field from your CSV, and selecting "CREATE NEW FIELD" will generate a new text field within X2 and map your field to it.').'<br /><br />';
}
$maxExecTime = ini_get('max_execution_time');
if ($maxExecTime <= 30) {
    echo '<div class="flash-notice">'.Yii::t('admin', 'Warning: This server is configured with a short maximum execution time. This can result in the import being terminated before completion. You may wish to increase'
        .' this value. The current maximum execution time is {exec_time} seconds.', array('{exec_time}' => $maxExecTime)).'</div>';
}
?>

</div><br /></div>
<div id="import-container" class='form'>
<div id="super-import-map-box">
<h2><a href='#' class='clean-link' onclick="$('#import-map-box').toggle();">[-] </a><span class="import-hide"><?php echo Yii::t('admin', 'Import Map'); ?></span></h2>
<div id="import-map-box" class="import-hide form" style="width:600px">
    <div id="form-error-box" style="color:red">

    </div>
</br />

<div id='mapping-overrides'>
<?php echo Yii::t('admin','Below is a list of our fields, the fields you provided, and a few sample records that you are importing. ');?>
<?php echo Yii::t('admin','Selecting "DO NOT MAP" will ignore the field and use the settings chosen above. Selecting "CREATE NEW FIELD" will generate a new text field within X2 and map your field to it. ') ?>
<?php echo Yii::t('admin','This override takes precedence over the selector above.') ?>
<br /><br />
<table id="import-map" >
    <tr>
        <td><strong><?php echo Yii::t('admin','Your Field');?></strong></td>
        <td><strong><?php echo Yii::t('admin','Our Field');?></strong></td>
        <td><strong><?php echo Yii::t('admin','Sample Record');?></strong> <a href="#" class="clean-link" onclick="prevRecord();"><?php echo Yii::t('admin','[Prev]');?></a> <a href="#" class="clean-link" onclick="nextRecord();"><?php echo Yii::t('admin','[Next]');?></a></td>
    </tr>
<?php
    foreach($meta as $attribute){
        echo "<tr>";
        echo "<td style='width:33%'>$attribute</td>";
        echo "<td style='width:33%'>".CHtml::dropDownList($attribute,
                isset($importMap[$attribute])?$importMap[$attribute]:'',
                array_merge(array(''=>Yii::t('admin','DO NOT MAP'),'createNew'=>Yii::t('admin','CREATE NEW FIELD')),X2Model::model($model)->attributeLabels()),
                array('class'=>'import-attribute')
                )."</td>";
        echo "<td style='width:33%'>";
        for ($i=0; $i < count($sampleRecords); $i++) {
            if (isset($sampleRecords[$i])) {
                if ($i>0) {
                    echo "<span class='record-$i' id='record-$i-$attribute' style='display:none;'>".$sampleRecords[$i][$attribute]."</span>";
                } else {
                    echo "<span class='record-$i' id='record-$i-$attribute'>".$sampleRecords[$i][$attribute]."</span>";
                }
            }
        }
        echo "</td>";
        echo "</tr>";
    }
?>
</table>
</div>
<br />
<?php
    echo X2Html::hint(Yii::t('admin', "A meaningful description of the data source will be helpful to identify the import mapping. The mapping name will "
                ." be generated in the form '{source} to X2Engine {version}' to identify the data sources for which the import map was intended."), false);
    echo CHtml::textField("mapping-name", "", array('id'=>'mapping-name', 'placeholder'=>Yii::t('admin', 'Import Source')))."&nbsp;";
    echo CHtml::link(Yii::t('admin', 'Export Mapping'), '#', array('id'=>'export-map', 'class'=>'x2-button'));
    echo CHtml::link(Yii::t('acmin', 'Download Mapping'), '#', array('id'=>'download-map', 'class'=>'x2-button', 'style'=>'display:none'));

?>
</div>
</div>
<br /><br />
<h2><?php echo Yii::t('admin','Process Import Data'); ?></h2>
<div class="form" style="width:600px">
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Create records for link fields?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"This will attempt to create a record for any field that links to another record type (e.g. Account)"),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('create-records-box','checked');?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('marketing','Tags'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"These tags will be applied to any record created by the import. Example: web,newlead,urgent."),false); ?></div>
        <div class="cell"><?php echo CHtml::textField('tags'); ?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically fill certain fields?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"These fields will be applied to all imported records and override their respective mapped fields from the import."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('fill-fields-box');?></div>

        <div id="fields" class="row" style="display:none;">
            <div>
                <div id="field-box">

                </div>
            </div>
            &nbsp;&nbsp;&nbsp;&nbsp;<a href="#" id="add-link" class="clean-link">[+]</a>
        </div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Automatically log a comment on these records?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"Anything entered here will be created as a comment and logged as an Action in the imported record's history."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('log-comment-box');?></div>
        <div class="row">
            <div id="comment-form" style="display:none;">
                <div class="text-area-wrapper" >
                    <textarea name="comment" id="comment" style="height:70px;"></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Assign records via lead-routing?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"If this box is checked, all records will be assigned to users based on your lead routing settings."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('lead-routing-box');?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Skip posting new records to activity feed?'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"If this box is checked, the activity feed will not be populated with the new records."),false); ?></div>
        <div class="cell"><?php echo CHtml::checkBox('activity-feed-box');?></div>
    </div>
    <div class="row">
        <div class="cell"><strong><?php echo Yii::t('admin','Batch Size'); ?></strong></div>
        <div class="cell"><?php echo X2Html::hint(Yii::t('admin',"Modify the number of records to be process per batch request."),false); ?></div>
        <div class="cell"><?php echo CHtml::textField('batch-size', 25, array('style' => 'width: 32px')); ?></div>
        <div class="cell"><?php
            $this->widget('zii.widgets.jui.CJuiSlider', array(
                'value' => 25,
                'options' => array(
                    'min' => 5,
                    'max' => 1000,
                    'step' => 5,
                    'change' => "js:function(event,ui) {
                                    $('#batch-size').val(ui.value);
                                }",
                    'slide' => "js:function(event,ui) {
                                    $('#batch-size').val(ui.value);
                                }",
                ),
                'htmlOptions' => array(
                    'style' => 'width:200px;margin:6px 9px;',
                    'id' => 'batch-size-slider',
                ),
            ));
        ?></div>
    </div>

</div>
<br /><br />
<?php echo CHtml::link(Yii::t('admin',"Process Import"),"#",array('id'=>'process-link','class'=>'x2-button highlight'));?>
<br /><br />
</div>
<h3 id="import-status" style="display:none;"><?php echo Yii::t('admin','Import Status'); ?></h3>
<div id="import-progress-bar" style="display:none;">
<?php
    if (array_key_exists('csvLength', $_SESSION)) {
        $this->widget('X2ProgressBar', array(
            'uid' => 'x2import',
            'max' => $_SESSION['csvLength'],
            'label' => '',
        ));
    }
?>
</div>
<div id="prep-status-box" style="color:green">

</div>
<br />
<div id="status-box" style="color:green">

</div>
<div id="failures-box" style="color:red">

</div>
<div id="continue-box" style="display:none;">
<br />
<?php
    echo CHtml::link(Yii::t('admin', 'Import more {records}', array(
            '{records}' => X2Model::getModelTitle($model),
        )), 'importModels?model='.$model, array('class' => 'x2-button'));
    echo CHtml::link(Yii::t('admin', 'Import to another module'), 'importModels',
        array('class' => 'x2-button'));
    echo CHtml::link(Yii::t('admin', 'Rollback Import'),
        array('rollbackImport', 'importId' => $_SESSION['importId']),
        array('class' => 'x2-button', 'id' => 'revert-btn', 'style' => 'display:none;'));
?>
</div>
<script>
    $(function() {
        // Hide the import map box if a mapping was uploaded
        if (<?php echo ($preselectedMap)? 'true':'false'; ?>)
            $('#super-import-map-box').hide();
    });
    
    var attributeLabels = <?php echo json_encode(X2Model::model($model)->attributeLabels(), false);?>;
    var modifiedPresetMap = false;
    var loadingThrobber;
    $('#process-link').click(function(){
       prepareImport();
    });
    $('#fill-fields-box').change(function(){
        $('#fields').toggle();
    });
    $('#log-comment-box').change(function(){
       $('#comment-form').toggle();
    });
    $('#batch-size').change(function() {
        $('#batch-size-slider').slider('value', $('#batch-size').val ());
    });

    function prepareImport(){
        $('#import-container').hide();
        var attributes=[];
        var keys=[];
        var forcedAttributes=[];
        var forcedValues=[];
        var comment="";
        var routing=0;
        var skipActivityFeed=0;
        var batchSize = $('#batch-size').val();
        $('.import-attribute').each(function(){
            attributes.push ($(this).val());
            keys.push ($(this).attr('name'));
        });
        if($('#fill-fields-box').attr('checked')=='checked'){
            $('.forced-attribute').each(function(){
            forcedAttributes.push($(this).val());
            });
            $('.forced-value').each(function(){
                forcedValues.push($(this).val());
            });
        }
        if($('#log-comment-box').attr('checked')=='checked'){
            comment=$("#comment").val();
        }
        if($('#lead-routing-box').attr('checked')=='checked'){
            routing=1;
        }
        if($('#activity-feed-box').attr('checked')=='checked'){
            skipActivityFeed=1;
        }

        function showPreparationResult (success, msg) {
            if (success) {
                $('#import-status').show();
                $('#import-progress-bar').show();
                loadingThrobber = auxlib.pageLoading();
                importData (batchSize);
                $('#prep-status-box').html (msg);
            } else {
                $('#super-import-map-box').show();
                $('#import-container').show();
                $('#form-error-box').html (msg);
            }
        }

        $.ajax({
            url:'prepareModelImport',
            type:"POST",
            data:{
                attributes:attributes,
                keys:keys,
                forcedAttributes:forcedAttributes,
                forcedValues:forcedValues,
                createRecords:$('#create-records-box').attr('checked')=='checked'?'checked':'',
                tags:$('#tags').val(),
                comment:comment,
                routing:routing,
                skipActivityFeed:skipActivityFeed,
                model:"<?php echo $model; ?>",
                preselectedMap: (<?php echo ($preselectedMap)? 'true' : 'false'; ?> && !modifiedPresetMap)
            },
            success:function(data){
                data=JSON.parse(data);
                var resp = data[0];
                switch (resp) {
                    case '0':
                        var str="<?php echo Yii::t('admin', "Import setup completed successfully...<br />Beginning import."); ?>";
                        showPreparationResult (true, str);
                        break;
                    case '1':
                        var str="<?php echo Yii::t('admin', "Import preparation failed.  Failed to create the following fields: "); ?>";
                        str = str + data[1] + "<br /><br />";
                        showPreparationResult (false, str);
                        break;
                    case '2':
                        var str="<?php echo Yii::t('admin', "Import preparation failed.  The following fields already exist: "); ?>";
                        str = str + data[1] + "<br /><br />";
                        showPreparationResult (false, str);
                        break;
                    case '3':
                        var str="<?php echo Yii::t('admin', "Import Preparation failed. The following required fields were not mapped: "); ?>";
                        str = str + data[1] + "<br /><br />";
                        showPreparationResult (false, str);
                        break;
                    case '4':
                        var fields = data[1];
                        var confirmMsg = '<?php echo Yii::t('admin', "You have mapped multiple columns to the same field, are you sure ".
                                        "you would like to proceed? The following fields were mapped more than once: "); ?>' + fields;
                        if (window.confirm(confirmMsg)) {
                            var str="<?php echo Yii::t('admin', "Import setup completed successfully...<br />Beginning import."); ?>";
                            showPreparationResult (true, str);
                        } else {
                            var str="<?php echo Yii::t('admin', "Import cancelled."); ?>";
                            showPreparationResult (false, str);
                        }
                        break;
                }
            },
            error:function(){
                var str="<?php echo Yii::t('admin', "Import preparation failed.  Aborting import."); ?>";
                $('#prep-status-box').css({'color':'red'});
                $('#prep-status-box').html(str);
            }
        });
    }
    function importData(count){
        $.ajax({
            url:'importModelRecords',
            type:"POST",
            data:{
                count:count,
                model:"<?php echo $model; ?>"
            },
            success:function(data){
                data=JSON.parse(data);
                if(data[0]!=1){
                    str=data[1]+"<?php echo Yii::t('admin', " <b>{model}</b> have been successfully imported.",
                            array('{model}' => $model)); ?>";
                    created=JSON.parse(data[3]);
                    for(type in created){
                        if(created[type]>0){
                            str+="<br />"+created[type]+" <b>"+type+"</b> <?php echo Yii::t('admin', "were created and linked to {model}.",
                                    array('{model}' => $model)); ?>";
                        }
                    }
                    $('#status-box').html(str);
                    if(data[2]>0){
                        str=data[2]+"<?php echo Yii::t('admin', " <b>{model}</b> have failed validation and were not imported.",
                                array('{model}' => $model)); ?>";
                        $("#failures-box").html(str);
                    }
                    // Increment the progress bar counter
                    $('#x2-progress-bar-container-x2import').data('progressBar').incrementCount (Number(count));
                    importData(count);
                }else{
                    str=data[1]+"<?php echo Yii::t('admin', " <b>{model}</b> have been successfully imported.",
                            array('{model}' => $model)); ?>";
                    created=JSON.parse(data[3]);
                    for(type in created){
                        if(created[type]>0){
                            str+="<br />"+created[type]+" <b>"+type+"</b> <?php echo Yii::t('admin', "were created and linked to {model}.",
                                array('{model}' => $model)); ?>";
                        }
                    }
                    $('#status-box').html(str);
                    if(data[2]>0){
                        str=data[2]+'<?php echo Yii::t('admin', " <b>{model}</b> have failed validation and were not imported. ".
                                "Click here to recover them: ", array('{model}' => $model))."<a href=\"#\" id=\"download-link\" class=\"x2-button\">".
                                Yii::t('admin', "Download")."</a>"; ?>';
                        $("#failures-box").html(str);
                        $('#download-link').click(function(e) {
                            e.preventDefault();  //stop the browser from following
                            window.location.href = '<?php echo $this->createUrl('/admin/downloadData',array('file'=>'failedRecords.csv')); ?>';
                        });
                    }

                    $('#continue-box').show();
                    if ($('#failures-box').html().trim().length > 0) {
                        // Present a button to rollback if there were any failures
                        $('#revert-btn').show();
                    }

                    // Fill the progress bar
                    var maxCsvLines = $('#x2-progress-bar-container-x2import').data('progressBar').getMax();
                    $('#x2-progress-bar-container-x2import').data('progressBar').updateCount (maxCsvLines);

                    $.ajax({
                        url:'cleanUpModelImport',
                        complete:function(){
                            var str="<strong><?php echo Yii::t('admin', "Import Complete."); ?></strong>";
                            $('#prep-status-box').html(str);
                            loadingThrobber.remove();
                            alert('<?php echo Yii::t('admin', 'Import Complete!'); ?>');
                        }
                    });
                }
            }
        });
    }
    function prevRecord(){
        $('.record-'+record).hide();
        if (record==0) {
            record = <?php echo count($sampleRecords) - 1; ?>;
        } else {
            record--;
        }
        $('.record-'+record).show();
    }

    function nextRecord(){
        $('.record-'+record).hide();
        if (record == <?php echo count($sampleRecords) - 1; ?>) {
            record=0;
        } else {
            record++;
        }
        $('.record-'+record).show();
    }

    function createDropdown(list, ignore) {
        var sel = $(document.createElement('select'));
        $.each(list, function(key, value) {
            if ($.inArray(key, ignore) == -1) {
                sel.append('<option value=\"' + key  + '\">' + value + '</option>');
            }
        });
        return sel;
    }

    function createAttrCell(){
        var div = $(document.createElement('div'));
        div.attr('class', 'field-row');
        var dropdown = createDropdown(attributeLabels);
        dropdown.attr('class', 'forced-attribute');
        var input = $('<input size="30" type="text" value="" class="forced-value">');
        input.attr('name', 'force-values[]');
        var link= $('<a href="#" class="del-link clean-link">[x]</a>');
        return div.append(dropdown).append(input).append(link);
    }
    $('#add-link').click(function(e){
       e.preventDefault();
       $('#field-box').append(createAttrCell());
       $('.del-link').click(function(e){
            e.preventDefault();
            $(this).closest('.field-row').remove();;
        });
    });

    $('#export-map').click(function() {
        var keys = new Array();
        var attributes = new Array();
        $('.import-attribute').each(function(){
            if ($(this).val() != '') {
                // Add mapping overrides that are not marked 'DO NOT MAP'
                attributes.push($(this).val());
                keys.push($(this).attr('name'));
            }
        });
        $.ajax({
            url: 'exportMapping',
            type: 'POST',
            data: {
                model: "<?php echo $model; ?>",
                name: $('#mapping-name').val(),
                attributes: attributes,
                keys: keys
            },
            success: function() {
                $('#download-map').show();
            },
            error: function() {
                var str="<?php echo Yii::t('admin', "Preparing the import map failed.  Aborting."); ?>";
                $('#prep-status-box').css({'color':'red'});
                $('#prep-status-box').html(str);
            }
        });
    });

    $('#download-map').click(function(e) {
        e.preventDefault();
        window.location.href = '<?php echo $this->createUrl('admin/downloadData', array('file'=>'importMapping.json')) ?>';
    });

    $('#editPresetMap').click(function(e) {
        e.preventDefault();
        $(this).hide();
        $('#importMapSummary').hide();
        $('#super-import-map-box').slideDown(500);
        modifiedPresetMap = true;
    });

</script>
