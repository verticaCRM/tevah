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

/**
 * Base module class for all modules following the convention of self-contained
 * assets, components, etc.
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2WebModule extends CWebModule {

    private $_assetsUrl;

    /**
     * Automatically publishes assets and returns the assets base URL.
     *
     * If constant YII_DEBUG is enabled, assets will be forcefully copied. This
     * is recommended when working on assets; otherwise it would be necessary to
     * clear assets after every change to see changes.
     * @return type
     */
	public function getAssetsUrl() {
		if (!isset($this->_assetsUrl)) {
			$this->_assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.modules.'.$this->id.'.assets'),false,-1,YII_DEBUG?true:null);
        }
		return $this->_assetsUrl;
	}

}

?>
