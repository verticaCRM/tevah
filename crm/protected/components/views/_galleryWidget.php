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

<?php

if(true) {//!preg_match('/(?i)msie/', $_SERVER['HTTP_USER_AGENT'])){
    if($model->galleryBehavior->getGallery() === null){
        echo Yii::t('app', 'A gallery will be created for this model the next time this model is saved.');
    }else{
        $this->widget('application.extensions.gallerymanager.GalleryManager', array(
            'gallery' => $model->galleryBehavior->getGallery(),
        ));
    }
}else{
    echo Yii::t('app','The Image Gallery widget currently does not support Internet Explorer. We are looking to resolve this issue for a later release of X2Engine.');
}
?>

