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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/packager.css');

?>

<div class="page-title"><h2><?php 
    echo CHtml::encode (Yii::t('admin', 'Importing Package: {package}', array(
        '{package}' => $manifest['name']
    ))); 
?></h2></div>

<div id='packager-form' class="form">
    <?php 
    echo Yii::t('admin', 
       'You are about to import the following package. Please review '.
       'the pending changes before proceeding.'
    );
    echo '<h3>'.Yii::t('admin', 'Package Components').'</h3>';
    ?>
    <div class="packageSummary">
    <div class="row">
        <div class="cell">
             <label>Description</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['description']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Version</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['version']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Edition</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['edition']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Modules</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode(implode (',', $manifest['modules'])); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <label>Includes Contact Data?</label>
        </div>
        <div class="cell">
            <?php echo $manifest['contacts'] ? "Yes" : "No" ; ?>
        </div>
    </div>
    </div>
    <?php

    echo X2Html::getFlashes();
    echo CHtml::button(Yii::t('admin','Apply Package'), array(
        'class' => 'x2-button',
        'id' => 'import-button'
    ));
    echo '<div id="status"></div>';

    Yii::app()->clientScript->registerScript ('previewPackage','
        (function () {
            $("#import-button").click(function() {
                var throbber = auxlib.pageLoading();

                $.ajax({
                    url: "'.$this->createUrl (
                        'importPackage', array('package' => $manifest['name']
                    )).'",
                    type: "post",
                    success: function(data) {
                        throbber.remove();
                        $("#status").html("'.Yii::t('admin', 'Finished applying package. Redirecting...').'");
                        $("#status").addClass("flash-success");
                        window.location.href = "'.$this->createUrl ('packager').'";
                    },
                    error: function(data) {
                        throbber.remove();
                        $("#status").html("'.Yii::t('admin', 'Failed to apply package!').'");
                        $("#status").addClass("flash-error");
                        window.location.href = "'.$this->createUrl ('packager').'";
                    }
                });
            });
        }) ();
    ', CClientScript::POS_READY);
?>
</div>
