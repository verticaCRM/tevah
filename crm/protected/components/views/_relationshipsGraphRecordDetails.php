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

$title = X2Model::getModelTitle (get_class ($model), true);
$summaryFields = $model->getSummaryFields ();

?>
<div class='record-details form2'>
<div class='title'>
<?php
echo CHtml::encode ($title.':');
echo '<br/>';
?>
</div>
<h2><?php 
echo $model->link; 
?></h2>
<?php
AuxLib::debugLogR ('$summaryFields= ');
    AuxLib::debugLogR ($summaryFields);

foreach ($summaryFields as $fieldName) {
    if ($fieldName === 'name') continue;
    echo '<div>';
    echo CHtml::label ($model->getAttributeLabel ($fieldName).':', null);
    echo '<br/>';
    echo $model->renderAttribute ($fieldName);
    echo '</div>';
}
echo '<br/>';
echo CHtml::label (CHtml::encode (Yii::t('app', 'Relationships:')), null);
echo '&nbsp;';
$i = 0;
foreach ($neighborData as $data) {
    if ($data['id'] === $model->id && $data['type'] === get_class ($model)) continue;
    if ($i++ > 0) echo ',&nbsp;';
    echo $data['link'];
}
?>
</div>
