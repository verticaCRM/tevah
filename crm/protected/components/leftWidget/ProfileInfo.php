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
 * Shows profile info
 * @package application.components.leftWidget
 * @author Alex Rowe <alex@x2engine.com>
 */

class ProfileInfo extends LeftWidget {

	public $id = 'profile-info-widget';

	public $viewFile = 'profileInfo';

	public $widgetLabel = 'Profile';

	public $model;

	public function getPackages () {
		$packages = array(
		    'ProfileInfoWidgetCSS' => array(
		    	'baseUrl' => Yii::app()->theme->baseUrl,
		    	'css' => array(
		    		'css/components/leftWidgets/profileInfo.css'
				),
		    )
		);

		return $packages;
	}


	public function init () {
		Yii::app()->clientScript->registerPackages($this->getPackages());

		parent::init ();
	}	

	protected function renderContent() {
		$this->render($this->viewFile, array(
			'actionList' => $this->getActionList()
		));
	}

	public function getActionList(){
		return array(
			array (
				'id' => 'show-profile-widget-button',
				'content' => Yii::t('app', 'Show Widget')
			),
			array (
				'id' => 'create-profile-widget-button',
				'content' => Yii::t('app', 'Create Widget')
			),
			array (
				'id' => 'edit-layout',
				'content' => Yii::t('app', 'Edit Layout')
			)
		);
	}
}


?>