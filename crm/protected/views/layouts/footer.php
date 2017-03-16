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
<div id="footer">

    <?php

    if(X2_PARTNER_DISPLAY_BRANDING){
        $brandingFile = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'footer.php';
        $brandingFileTemplate = Yii::getPathOfAlias('application.partner').DIRECTORY_SEPARATOR.'footer_example.php';
        if(file_exists($brandingFile)){
            require_once $brandingFile;
            echo "<br /><br /><hr />";
        }else{
            require_once $brandingFileTemplate;
            echo "<br /><br /><hr />";
        }
    }
    ?><!-- /* x2plaend */-->
<?php if(Yii::app()->edition==='opensource') { ?>
		Released as free software without warranties under the <a href="<?php echo Yii::app()->getBaseUrl(); ?>/LICENSE.txt" title="GNU Affero General Public License version 3">GNU Affero GPL v3</a>.
	<?php } ?>
	<br>
	<?php
    echo CHtml::link(
        CHtml::image(
            Yii::app()->params->x2Power,
            '',
            array(
                'id'=>'powered-by-x2engine',
            )
        ),'http://www.x2engine.com/'); 
    ?>
	<div id="response-time">
	<?php
	echo round(Yii::getLogger()->getExecutionTime()*1000), 'ms ';
	$peak_memory = memory_get_peak_usage(true);
    echo FileUtil::formatSize($peak_memory,2);
	?></div>
	
</div>
