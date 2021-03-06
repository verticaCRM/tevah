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

<div class="form-title"><?php echo CHtml::encode (Yii::t('reports', 'Bar Chart')); ?></div>
	
<?php
if ($this->report->type == 'grid') {
    // echo $this->rowCheckBox('isRow');
    // echo $this->axisSelector('values', 'row');
    echo "<div class='row'>".Yii::t('app', 'Click submit to create chart.')."</div>";
} else {
   echo $this->axisSelector('categories', 'column');
   echo $this->axisSelector('values', 'column');
   echo $this->axisSelector('groups', 'column');
}
?>

