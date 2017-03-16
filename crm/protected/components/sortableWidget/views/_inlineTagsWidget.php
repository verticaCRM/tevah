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
?>
<div id="x2-tags-container">
	<div class="x2-tag-list">
		<?php 
        foreach($tags as $tag) {
            echo '<span class="tag"><span class="delete-tag">[x]</span> '.
                     CHtml::link(
                         CHtml::encode ($tag['tag']),
                         array(
                            '/search/search','term'=>'#'.ltrim($tag['tag'],'#')
                         ),
                         array('class'=>'')
                     ).
                 '</span>';
        }
        ?> 
        <span class='tag-container-placeholder' 
         <?php echo (sizeof ($tags) > 0 ? 'style="display: none;"' : ''); ?>>
            <?php echo Yii::t('contacts','Drag tags here from the tag cloud widget or click to '.
                'create a custom tag.');?>
        </span>
        <?php
        ?>
	</div>
</div>
<?php
	// give javascript URLs, model type, and model id
	$appendTag = $this->controller->createUrl('/site/appendTag');
	$removeTag = $this->controller->createUrl('/site/removeTag');
	$searchUrl = $this->controller->createUrl('/search/search');
	
	Yii::app()->clientScript->registerScript('tags-list','
	$(function() {
        x2.inlineTagsContainer = new InlineTagsContainer ({
            appendTagUrl: "'.$appendTag.'",
            removeTagUrl: "'.$removeTag.'",
            searchUrl: "'.$searchUrl.'",
            modelType: "'.get_class ($model).'",
            modelId: '.$model->id.',
            containerSelector: "#x2-tags-container .x2-tag-list",
        });
	});',CClientScript::POS_END);
