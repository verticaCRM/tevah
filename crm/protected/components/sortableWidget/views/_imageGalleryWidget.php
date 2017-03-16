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

Yii::app()->clientScript->registerCss('imageGalleryCss',"

.GalleryEditor .x2-button-group .x2-button {
    margin-right: -4px;
}

.GalleryEditor .x2-button {
    cursor: pointer;
}

.GalleryEditor .x2-button-group {
    display: inline-block;
    margin-top: 7px;
}

.GalleryEditor .fileinput-button {
    display: inline-block;
}

.GalleryEditor [type='checkbox'] {
    position: relative;
    top: 3px;
}

.gallery-widget-dialog {
	position: fixed !important;
	padding: 0 0 0 0 !important;
}

.gallery-widget.input-xlarge {
	margin-bottom: 10px;
}

.gallery-widget-image {
	margin: auto;
	position: absolute;
	overflow: auto;
	top: 0;
	left: 0;
	bottom: 0;
	right: 0;
}


");

if($model->galleryBehavior->getGallery() === null){
    echo Yii::t(
        'app', 'A gallery will be created for this model the next time this model is saved.');
}else{
    $this->widget('application.extensions.gallerymanager.GalleryManager', array(
        'gallery' => $model->galleryBehavior->getGallery(),
    ));
}
