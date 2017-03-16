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
$defaultLogRoutes = array_merge($defaultLogRoutes, array(
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.automation.*',
        'logFile' => 'automation.log',
        'maxLogFiles' => 10,
        'maxFileSize' => 128,
    ),
    array(
        'class' => 'CFileLogRoute',
        'categories' => 'application.emailcapture',
        'logFile' => 'emailcapture.log',
        'maxLogFiles' => 3,
        'maxFileSize' => 64,
    ),
));



return array(
    'controllerMap' => array(
        'gallery' => array(
            'class' => 'application.extensions.gallerymanager.GalleryController',
            'pageTitle' => 'Gallery Administration',
        ),
    ),
    'components' => array(
        'image' => array(
            'class' => 'application.extensions.image.CImageComponent',
            // GD or ImageMagick
            'driver' => 'GD',
            // ImageMagick setup path
            'params' => array('directory' => ''),
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => (YII_DEBUG && YII_LOGGING 
                ? array_merge($defaultLogRoutes,$debugLogRoutes)
                : (YII_LOGGING ? $defaultLogRoutes : array()))
        ),
    ),
);
?>
