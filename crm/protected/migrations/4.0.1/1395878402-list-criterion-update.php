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

Yii::import('application.models.*');
Yii::import('application.controllers.X2Controller');
Yii::import('application.controllers.x2base');
Yii::import('application.components.*');
Yii::import('application.components.util.*');
Yii::import('application.components.permissions.*');
Yii::import('application.modules.media.models.Media');
Yii::import('application.modules.groups.models.Groups');
Yii::import('application.extensions.gallerymanager.models.*');

$arr = array();
$modulePath = implode(DIRECTORY_SEPARATOR,array(
    Yii::app()->basePath,
    'modules'
));
foreach(scandir($modulePath) as $module){
    $regScript = implode(DIRECTORY_SEPARATOR,array(
        $modulePath,
        $module,
        'register.php'
    ));
    if(file_exists($regScript)){
        $arr[$module] = ucfirst($module);
        Yii::import("application.modules.$module.models.*");
    }
}


/**
 * @file 1395878402-list-criterion-update.php
 *
 * Update and fix dynamic list criterion.
 */

$listCriterionUpdate = function(){
            // Step 1: get all link-type fields of the contacts model:
            $attributes = Yii::app()->db->createCommand()
                    ->select('fieldName,linkType')
                    ->from(Fields::model()->tableName())
                    ->where("modelName='Contacts' AND type='link'")
                    ->queryAll();
            foreach($attributes as $attribute){
                if($model = X2Model::model($attribute['linkType'])){
                    $params[':attr'] = $attribute['fieldName'];
                    $sql = 'UPDATE '.X2ListCriterion::model()->tableName().' lc INNER JOIN '.$model->tableName().' c'
                            .' ON lc.value=c.id SET lc.value=c.nameId WHERE lc.type="attribute" AND lc.attribute=:attr';
                    Yii::app()->db->createCommand($sql)
                            ->execute($params);
                }
            }
        };

$listCriterionUpdate();
?>
