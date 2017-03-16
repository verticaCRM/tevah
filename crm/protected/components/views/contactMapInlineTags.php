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

if(empty($tags))
    $tags = array();

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2Tags/TagContainer.js', CClientScript::POS_BEGIN);
Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2Tags/MapTagsContainer.js', CClientScript::POS_BEGIN);
?>
<b><?php echo Yii::t('app', 'Tags'); ?></b>
<div id="x2-tags-container" class="form">
    <?php
		echo '<div class="x2-tag-list" style="min-height:15px;">';
		foreach($tags as $tag) {
			echo '<span class="tag link-disable"><span class="delete-tag filter">[x]</span>'.
                    CHtml::link(CHtml::encode ($tag),'#').
                '</span>';
    } 
    ?>
    <span class='tag-container-placeholder' 
     <?php echo (sizeof ($tags) > 0 ? 'style="display: none;"' : ''); ?>>
        <?php echo Yii::t('contacts','Drop a tag here to filter map results.');?>
    </span>
    <?php
		echo "</div>";
    ?>
</div>    
<?php
	Yii::app()->clientScript->registerScript('tags-list','
	$(document).on ("ready", function () {
        new MapTagsContainer ({
            containerSelector: "#x2-tags-container .x2-tag-list"
        }); 
	});',CClientScript::POS_HEAD);
