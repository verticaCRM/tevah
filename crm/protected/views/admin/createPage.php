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
<div class='page-title'>
<h2><?php echo Yii::t('admin','Create Page'); ?></h2>
</div>
<div class='admin-form-container'>
    <?php
        echo Yii::t('admin','Create a Document that will be linked on the top menu bar, or select an existing '
                             .'document from the dropdown below.')."<br />";
        echo CHtml::dropDownList('existingDoc', '', $existingDocs, array(
            'empty' => Yii::t('admin', '--- Select an existing document ---'),
            'id' => 'existing-doc-dropdown'
        ));
        echo CHtml::submitButton(Yii::t('admin', "Create"), array(
            'class' => 'x2-button',
            'id' => 'create-existing',
            'style' => 'display: none;',
        ));
    ?>
    <br /><br />

</div>
<div id="create-static-doc">
    <?php echo $this->renderPartial('application.modules.docs.views.docs._form', array('model'=>$model,'users'=>$users)); ?>
</div>
<?php
    Yii::app()->clientScript->registerScript('toggle-create-doc', '
        $("#existing-doc-dropdown").change(function() {
            if ($(this).val() === "") {
                $("#create-static-doc").slideDown(500);
                $("#create-existing").hide();
            } else {
                $("#create-static-doc").slideUp(500);
                $("#create-existing").show();
            }
        });

        $("#create-existing").click(function() {
            var createPageUrl = "'.Yii::app()->createUrl('admin/createPage').'";
            var staticDocUrl = "'.Yii::app()->createUrl('/docs/docs/view', array('static'=>'true')).'";
            var existingDoc = $("#existing-doc-dropdown").children("option:selected").text();
            $.ajax({
                url: createPageUrl,
                type: "post",
                data: {
                    existingDoc: existingDoc
                },
                success: function(data) {
                    document.location = staticDocUrl + "&id=" + data;
                }
            });
        });
    ');

?>
