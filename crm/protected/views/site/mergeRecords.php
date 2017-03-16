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

echo "<div class='page-title'><h2>".Yii::t('app','Merge Records')."</h2></div>"; 
echo "<div class='form' style='width:600px'>";
echo Yii::t('app',"This page allows for merging multiple records of the same type into one new record.")." ";
echo Yii::t('app',"Select the value for each field you would like to use.")." ";
echo Yii::t('app',"Dropdowns which are greyed out are fields for which all records have the same value.");
echo "</div>";
$this->renderPartial(
    'application.components.views._form', 
    array('model' => $model, 'modelName' => strtolower($modelName),
        'idArray'=>$idArray, 'suppressQuickCreate'=>true)); 
