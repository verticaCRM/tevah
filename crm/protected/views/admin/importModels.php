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
<div class="page-title"><h2><?php echo Yii::t('admin','Import {model} from Template', array('{model}'=>(empty($model)? "" : X2Model::getModelTitle ($model)))); ?></h2></div>
<div class="form">

<?php if(!empty($errors)){
    echo "<span class='error' style='font-weight:bold'>".Yii::t('admin',$errors)."</span>";
    unset($_SESSION['errors']);
} ?>

<?php if (!empty($model)) { ?>
    <div id="info-box" style="width:600px;">
    <?php echo Yii::t('admin','To import your records, please fill out a CSV file where the first row contains the column headers for your records (e.g. first_name, last_name, title etc.).  A properly formatted example can be found below.'); ?>
    <br><br>
    <?php echo Yii::t('admin','The application will attempt to automatically map your column headers to our fields in the database.  If a match is not found, you will be given the option to choose one of our fields to map to, ignore the field, or create a new field within X2.'); ?>
    <br><br>
    <?php echo Yii::t('admin','If you decide to map the "Create Date", "Last Updated", or any other explicit date field, be sure that you have a valid date format entered so that the software can convert to a UNIX Timestamp (if it is already a UNIX Timestamp even better).  Visibility should be either "1" for Public or "0" for Private (it will default to 1 if not provided).'); ?>

    <br><br><?php echo Yii::t('admin','Example');?> <a class='pseudo-link' id="example-link"><?php echo X2Html::fa('fa-caret-down')?></a>
    <div id="example-box" style="display:none;"><img src="<?php echo Yii::app()->getBaseUrl()."/images/examplecsv.png" ?>"/></div>
    <br><br>
    </div>
    <div class="form" style="width:600px;">
<?php
    unset($_SESSION['model']);
    echo "<h3>".Yii::t('admin','Upload File')."</h3>";
    echo CHtml::form('importModels','post',array('enctype'=>'multipart/form-data','id'=>'importModels'));
    echo CHtml::fileField('data', '', array('id'=>'data'))."<br>";
    echo CHtml::hiddenField('model', $model);
    echo "<i>".Yii::t('app','Allowed filetypes: .csv')."</i><br><br>";
    echo "<h3>".Yii::t('admin', 'Import Map')." <a class='pseudo-link' id='toggle-map-upload'>".X2Html::fa('fa-caret-down')."</a></h3>";
    echo "<div id='upload-map' style='display:none;'>";
    echo Yii::t('admin', "You may select a predefined map here, or upload your own.")."<br />";
    $availImportMaps = $this->availableImportMaps($model);
    if (empty($availImportMaps)) {
        echo "<div style='color:red'>";
        echo Yii::t('app', "No related import maps were found.");
        echo "</div>";
    } else {
        echo CHtml::radioButtonList('x2maps', null, $availImportMaps, array(
            'labelOptions'=>array('style'=>'display:inline')
        ));
    }
    echo "<br /><br />";
    echo CHtml::fileField('mapping', '', array('id'=>'mapping'))."<br>";
    echo "<i>".Yii::t('app','Allowed filetypes: .json')."</i>";
    echo "</div><br><br>";
    echo CHtml::submitButton(Yii::t('app','Submit'),array('class'=>'x2-button'));
    echo CHtml::endForm();
    echo "</div>";
} else {
    echo "<h3>".Yii::t('admin','Please select a module to import records into.')."</h3>";
    foreach ($modelList as $class => $modelName) {
        echo CHtml::link($modelName, array('/admin/importModels', 'model'=>$class))."<br />";
    }
}
?>

</div>
<script>
    $('#example-link').click(function(){
       $('#example-box').slideToggle(); 
    });
    $('#toggle-map-upload').click(function() {
        $('#upload-map').slideToggle();
    });
    $('#x2maps').change(function() {
        // Reset the file upload if a radio button is selected
        $('#mapping').val("");
    });
    $('#mapping').change(function() {
        // Deselect the radio buttons when a file is selected instead
        $('#x2maps').find('input:radio:checked').prop('checked', false);
    });
    $(document).on('submit','#importModels',function(){
        var fileName=$("#data").val();
        var pieces=fileName.split('.');
        var ext=pieces[pieces.length-1];
        if(ext!='csv'){
            $("#data").val("");
            alert("File must be a .csv file.");
            return false;
        }
        var mapfileName = $('#mapping').val();
        if (mapfileName != '') {
            var pieces = mapfileName.split('.');
            var ext = pieces[pieces.length - 1];
            if (ext != 'json'){
                $('#mapping').val("");
                alert('Map file must be a .json file.');
                return false;
            }
        }
    });
</script>
    
