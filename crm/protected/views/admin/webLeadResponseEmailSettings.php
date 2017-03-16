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

?>
	<br /><hr />
	<h4><?php echo Yii::t('admin','Web Lead Response Email Settings'); ?></h4>
	<p><?php echo Yii::t('admin','Configure how X2Engine sends email when responding to new web leads.'); ?></p>
        <div class="row">
            <div class="cell">
            <?php 
	    echo $form->labelEx($model,'webLeadEmailAccount'); 
	    echo Credentials::selectorField($model,'webLeadEmailAccount','email',Credentials::$sysUseId['systemResponseEmail'],array('class'=>'email-selector','id'=>'email-selector-weblead'));
	    ?>
	    </div>
	<div class="row email-selector-weblead-legacy">
	    <div class="cell">
	    <?php 
	    echo $form->labelEx($model,'webLeadEmail'); 
	    echo $form->textField($model,'webLeadEmail');
	    ?>
	    </div>
        </div>

