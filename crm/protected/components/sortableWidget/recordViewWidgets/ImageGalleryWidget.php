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

 Yii::import ('application.components.sortableWidget.SortableWidget');

/**
 * @package application.components.sortableWidget
 */
class ImageGalleryWidget extends SortableWidget {

    /**
     * @var CActiveRecord 
     */
	public $model;

    public $viewFile = '_imageGalleryWidget';

    private static $_JSONPropertiesStructure;

    public static function getJSONPropertiesStructure () {
        if (!isset (self::$_JSONPropertiesStructure)) {
            self::$_JSONPropertiesStructure = array_merge (
                parent::getJSONPropertiesStructure (),
                array (
                    'label' => 'Image Gallery',
                    'hidden' => false,
                )
            );
        }
        return self::$_JSONPropertiesStructure;
    }

    public function getPackages () {
        if (!isset ($this->_packages)) {
            Yii::import('application.extensions.gallerymanager.GalleryManager');
            $galleryWidget = new GalleryManager ();
            $galleryWidget->init ();
            $galleryWidgetAssets = $galleryWidget->assets;

            $this->_packages = array_merge (
                parent::getPackages (),
                array (
                    'ImageGalleryWidgetJS' => array(
                        'baseUrl' => Yii::app()->request->baseUrl,
                        'js' => array(
                            'js/galleryManagerDialogSetup.js',
                            'js/gallerymanager/bootstrap/js/bootstrap.js',
                        ),
                        'depends' => array ('auxlib'),
                    ),
                    'ImageGalleryWidgetJSExt' => array(
                        'baseUrl' => $galleryWidgetAssets,
                        'js' => array(
                            'jquery.iframe-transport.js',
                            'jquery.galleryManager.js',
                        ),
                    ),
                )
            );
        }
        return $this->_packages;
    }

    public function getViewFileParams () {
        if (!isset ($this->_viewFileParams)) {
            $this->_viewFileParams = array_merge (
                parent::getViewFileParams (),
                array (
                    'model' => $this->model,
                )
            );
        }
        return $this->_viewFileParams;
    } 

}

?>
