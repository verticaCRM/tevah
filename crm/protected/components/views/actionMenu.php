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
Yii::app()->clientScript->registerCss('actionMenu',"

#action-menu-right-widget a {
    text-decoration: none;
    color: black;
}

");

$Action = Modules::displayName(false, 'Actions');
$Actions = Modules::displayName(true, 'Actions');

Yii::app()->clientScript->registerScript('setShowActions', '
    if (typeof x2 == "undefined")
        x2 = {};
    x2.setShowActions = function(type) {
        var saveShowActionsUrl = '.json_encode(Yii::app()->controller->createUrl('/actions/actions/saveShowActions')).';
        var viewUrl = "'.Yii::app()->controller->createUrl('/actions/actions/viewAll').'";
        $.post(
            saveShowActionsUrl,
            { ShowActions: type }
        );
    };
');

?>
<ul id='action-menu-right-widget'>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
            onclick="x2.setShowActions('all')">
            <?php echo $total; ?></a>
        </strong><?php 
        echo Yii::t('app','Total {Action}|Total {Actions}', array(
            $total,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('uncomplete')">
            <?php echo $unfinished; ?></a>
        </strong><?php 
        echo Yii::t('app','Incomplete {Action}|Incomplete {Actions}', array(
            $unfinished,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('overdue')">
            <?php echo $overdue; ?></a>
        </strong><?php 
        echo Yii::t('app','Overdue {Action}|Overdue {Actions}', array(
            $overdue,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('complete')">
            <?php echo $complete; ?></a>
        </strong><?php 
        echo Yii::t('app','Completed {Action}|Completed {Actions}', array(
            $complete,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
</ul>

