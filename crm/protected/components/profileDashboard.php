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
 * Description
 * @package application.components
 * @author Alex Rowe <alex@x2engine.com>
 */
class ProfileDashboard extends CWidget {

	public $model;

	public $viewFile = 'profileDashboard';

	public function init() {
		Yii::app()->clientScript->registerPackages($this->getPackages(), true);
		$this->instantiateJS();

		parent::init ();
	}

	public function run() {
		$this->render('profileDashboard');
		parent::run ();
	}


	public function getPackages () {
		$packages = array(
		    'sortableWidgetJS' => array(
		    	'baseUrl' => '/js/sortableWidgets/',
		    	'js' => array(
		    		'SortableWidget.js',
				    'SortableWidgetManager.js',
				    'ProfileWidgetManager.js'
				),
				'depends' => array('auxlib')
		    )
		);

		return $packages;
	}

	public function displayWidgets ($containerNumber){
		$layout = $this->model->profileWidgetLayout;

		foreach ($layout as $widgetClass => $settings) {
		    if ($settings['containerNumber'] == $containerNumber) {
		        SortableWidget::instantiateWidget ($widgetClass, $this->model);
		    }

		}
	}

	public function instantiateJS () {

		$JSParams = CJSON::encode (array(
            'setSortOrderUrl' => Yii::app()->controller->createUrl ('/profile/setWidgetOrder'),
            'showWidgetContentsUrl' => Yii::app()->controller->createUrl ('/profile/view', array ('id' => 1)),
            'connectedContainerSelector' => 'connected-sortable-profile-container',
            'translations' => $this->getTranslations(),
            'createProfileWidgetUrl' => Yii::app()->controller->createUrl ('/profile/createProfileWidget')
        ));

        $script = "
        	$('#content').addClass('profile-content');
        	x2.profileWidgetManager = new ProfileWidgetManager ($JSParams);
        ";

        Yii::app()->clientScript->registerScript('profilePageInitScript', $script, CClientScript::POS_READY);

       
	}

	public function getTranslations () {
		return array(
			'createProfileWidgetDialogTitle' => Yii::t('profile', 'Create Profile Widget'),
			'Create' => Yii::t('app',  'Create'),
			'Cancel' => Yii::t('app',  'Cancel'),
		);

	}

}

?>